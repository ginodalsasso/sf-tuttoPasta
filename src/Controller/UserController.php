<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Quote;
use App\Form\UserFormType;
use App\Entity\Appointment;
use App\Form\EditPasswordType;
use App\Services\PdfGenerator;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Email;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Repository\QuoteRepository;
use Symfony\Component\Mime\Address;
use App\Repository\CommentRepository;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AppointmentRepository;
use App\Domain\AntiSpam\ChallengeInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



class UserController extends AbstractController
{

    private $htmlSanitizer;
    private $pdfGenerator;
    private $csrfTokenManager;



    public function __construct(HtmlSanitizerInterface $htmlSanitizer, private EmailVerifier $emailVerifier, PdfGenerator $pdfGenerator, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->htmlSanitizer = $htmlSanitizer;
        $this->pdfGenerator = $pdfGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    //_____________________________________________________________REGISTER/LOGIN/LOGOUT_____________________________________________________________
    // ---------------------------------Méthode d'inscription--------------------------------- //
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager, ChallengeInterface $challenge): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // HoneyPot 
            $honeypotValue = $form->get('firstname')->getData();

            if (!empty($honeypotValue)) {
                // Le champ a été rempli, probablement un bot
                return $this->redirectToRoute('app_home');
            }

            // Sanitize les entrées utilisateur
            $user->setUsername($this->htmlSanitizer->sanitize($user->getUsername()));

            // Valider l'email
            $email = $user->getEmail();
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->render('user/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'challenge' => $challenge->generateKey()
                ]);
            }
            // Hashage du mot de passe
            $user->setPassword(
                // Utilisation de l'interface UserPasswordHasherInterface pour hasher le mot de passe
                // hashPassword prend en paramètre l'entité User et le mot de passe en clair
                $userPasswordHasher->hashPassword( 
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            // Ajoute le rôle ROLE_USER
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            // Envoi d'un email de confirmation
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('admin@tuttoPasta.com', 'TuttoPasta'))
                    ->to($user->getEmail())
                    ->subject('Merci de bien confirmer votre compte afin de pouvoir vous connecter.')
                    ->htmlTemplate('emails/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Un email de confirmation vous a été envoyé, pour confirmer votre compte');
            return  $this->redirectToRoute('app_login');
        }

        return $this->render('user/register.html.twig', [
            'registrationForm' => $form,
            'challenge' => $challenge->generateKey()
        ]);
    }
    #endregion

    // ---------------------------------Méthode de connexion--------------------------------- //
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Intercepte l'erreur d'authentification s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier username entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Méthode de déconnexion
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion de votre pare-feu.');
    }

    #region CRUD
    //________________________________________________________________CRUD________________________________________________________________
    //____________________________________________________________________________________________________________________________
    //____________________________________________________________________________________________________________________
    // ---------------------------------Edition infos utilisateur--------------------------------- //
    #[Route('/profil/update-info', name: 'app_profil_update_info', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateInfo(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();
        // Formulaire pour les informations utilisateur
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        // Gestion du formulaire des informations utilisateur
        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var User|null $user
             */

            // Désinfecter les champs du formulaire
            $user->setUsername($this->htmlSanitizer->sanitize($user->getUsername()));

            // Valider l'email
            $email = $user->getEmail();
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->redirectToRoute('app_profil');
            }
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
        }

        return $this->redirectToRoute('app_profil');
    }

    // ---------------------------------Edition password utilisateur--------------------------------- //
    #[Route('/profil/update-password', name: 'app_profil_update_password', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updatePassword(Request $request, Security $security, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /**
         * @var User|null $user
         */

        $user = $security->getUser();

        // Formulaire pour le changement de mot de passe
        $passwordForm = $this->createForm(EditPasswordType::class, $user);
        $passwordForm->handleRequest($request);

        // Gestion du formulaire de changement de mot de passe
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $oldPassword = $passwordForm->get('oldPassword')->getData();

            // Vérifiez que l'ancien mot de passe est correct
            if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                $this->addFlash('error', 'L\'ancien mot de passe est incorrect.');
            } else {
                // Hashage et mise à jour du mot de passe
                $newPassword = $passwordForm->get('plainPassword')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été changé avec succès.');
            }
        }

        return $this->redirectToRoute('app_profil');
    }


    // --------------------------------- Suppression d'un compte utilisateur--------------------------------- //
    #[Route('/delete_account', name: 'app_delete_account')]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        CommentRepository $commentRepository,
        ContactRepository $contactRepository,
        AppointmentRepository $appointmentRepository,
        MailerInterface $mailer,
        QuoteRepository $quoteRepository,
        PdfGenerator $pdfGenerator,
        CsrfTokenManagerInterface $csrfTokenManager,
        Request $request
    ): RedirectResponse {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Récupère le jeton CSRF depuis la requête
        $csrfToken = $request->request->get('csrf');
        // Vérifie la validité du jeton CSRF
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('', $csrfToken))) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Récupére et anonymise les commentaires de l'utilisateur
        $comments = $commentRepository->findBy(['user' => $user]);
        foreach ($comments as $comment) {
            $comment->setUser(null);
             // Anonymise le nom de l'utilisateur
            $comment->setUsername('Utilisateur anonyme');
            $entityManager->persist($comment);
        }

        // Récupére et rends l'user associé au contact null
        $contacts = $contactRepository->findBy(['user' => $user]);
        foreach ($contacts as $contact) {
            $contact->setUser(null);
            $entityManager->persist($contact);
        }

        // Récupére et rends l'user associé au RDV null 
        $appointments = $appointmentRepository->findBy(['user' => $user]);
        foreach ($appointments as $appointment) {
            // Récupére les devis associés aux rendez-vous
            $quotes = $quoteRepository->findBy(['appointments' => $appointment]);
            foreach ($quotes as $quote) {
                // Génére et stocke le PDF dans les archives
                $reference = $quote->getReference();
                $pdfGenerator->generateAndArchivePdf($pdfGenerator, $quote, $reference);

                // Marque le devis comme archivé
                $quote->setState(Quote::STATE_ARCHIVED);
                $entityManager->persist($quote);
            }
            $appointment->setUser(null);
            $entityManager->persist($appointment);
        }

        // Supprime l'utilisateur de la base de données
        $entityManager->remove($user);
        $entityManager->flush();

        // Déconnecte l'utilisateur après la suppression du compte
        $tokenStorage->setToken(null);

        // Envoie un email de notification de suppression de compte
        $this->sendAccountDeletionEmail($mailer, $user);

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        // Redirige vers la page d'accueil après la suppression du compte
        return $this->redirectToRoute('app_home');
    }


    // ---------------------------------Annulation d'un rendez vous sur le profil utilisateur--------------------------------- //
    #[Route('/profil/appointment/{id}/delete', name: 'app_cancel_appointment', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function cancelAppointment(EntityManagerInterface $entityManager, int $id, Security $security, MailerInterface $mailer, Request $request,): JsonResponse
    {
        // Récupère le jeton CSRF depuis les en-têtes
        $csrfToken = $request->headers->get('X-CSRF-TOKEN');
        // Vérifier la validité du jeton CSRF
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('', $csrfToken))) {
            return new JsonResponse(['error' => 'Jeton CSRF invalide.'], 403);
        }

        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();
        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Récupère le rendez-vous
        $appointment = $entityManager->getRepository(Appointment::class)->find($id);
        // Vérifie si le rendez-vous existe et si l'utilisateur est autorisé à le supprimer
        if (!$appointment || !($user === $appointment->getUser() || $this->isGranted('ROLE_ADMIN'))) {
            return new JsonResponse(['success' => false, 'message' => 'Rendez-vous non trouvé ou vous n\'avez pas les droits pour le supprimer.'], 403);
        }

        // Récupère le devis associé au rendez-vous
        $quote = $appointment->getQuote();
        // Vérifie si le devis existe et si le fichier PDF est présent
        if ($quote) {
            // Récupère le chemin absolu du fichier PDF
            $pdfPath = realpath($this->getParameter('kernel.project_dir') . '/var/uploads/pdf/' . $quote->getPdfContent());
            $expectedDirectory = realpath($this->getParameter('kernel.project_dir') . '/var/uploads/pdf/');
            // realpath:  obtenir le chemin absolu et vérifier que le fichier est dans le répertoire sécurisé prévu

            // Si le fichier PDF existe et est dans le répertoire sécurisé, le supprime
            if ($pdfPath && str_starts_with($pdfPath, $expectedDirectory) && file_exists($pdfPath)) {
                unlink($pdfPath);
            }
            $entityManager->remove($quote);
        }

        // Supprime le rendez-vous de la base de données
        $entityManager->remove($appointment);
        $entityManager->flush();

        // Envoie un email de notification d'annulation
        $this->sendCancellationEmail($mailer, $appointment);

        return new JsonResponse(['success' => true]);
    }
    #endregion


    #region EMAIL

    // ---------------------------------Méthode de vérification d'email--------------------------------- //
    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, TranslatorInterface $translator): Response
    {

        $id = $request->query->get('id'); // retrieve the user id from the url

        // Verify the user id exists and is not null
        if (null === $id) {
            return $this->redirectToRoute('app_home');
        }

        $user = $userRepository->find($id);

        // Ensure the user exists in persistence
        if (null === $user) {
            return $this->redirectToRoute('app_home');
        }
        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre mail à été vérifié !');

        return $this->redirectToRoute('app_login');
    }



    // Gestion de l'envoi de notification d'annulation de RDV
    private function sendCancellationEmail(MailerInterface $mailer, Appointment $appointment): void
    {
        $user = $appointment->getUser();
        $emailContent = $this->renderView('emails/appointment_cancellation.html.twig', [
            'appointmentDate' => $appointment->getStartDate()->format('d/m/Y à H:i'),
            'username' => $user ? $user->getUsername() : 'Utilisateur anonyme',
        ]);

        $email = (new Email())
            ->from(new Address('no-reply@tuttoPasta.com', 'TuttoPasta'))
            ->to('admin@tuttoPasta.com')
            ->subject('Annulation de rendez-vous')
            ->html($emailContent);

        $mailer->send($email);
    }


    // Gestion de l'envoi de notification de suppression de compte
    private function sendAccountDeletionEmail(MailerInterface $mailer, UserInterface $user): void
    {
        /**
         * @var User|null $user
         */
        $emailContent = $this->renderView('emails/account_deletion.html.twig', [
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        ]);

        $email = (new Email())
            ->from(new Address('no-reply@tuttoPasta.com', 'TuttoPasta'))
            ->to('admin@tuttoPasta.com')
            ->subject('Suppression de compte utilisateur')
            ->html($emailContent);

        $mailer->send($email);
    }
    #endregion

}
