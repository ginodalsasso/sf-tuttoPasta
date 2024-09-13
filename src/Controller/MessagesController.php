<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Form\MessageType;
use App\Services\EmailService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

//________________________________________________________________MESSAGERIE______________________________________________________________
#[IsGranted('ROLE_USER')]
class MessagesController extends AbstractController
{
    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    //________________________________________________________________MESSAGE RECUS______________________________________________________________
    #[Route('/received', name: 'app_received')]
    public function received(Security $security): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        return $this->render('messages/received.html.twig');
    }

    //________________________________________________________________MESSAGES ENVOYES______________________________________________________________
    #[Route('/sent', name: 'app_sent')]
    public function sent(Security $security): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        return $this->render('messages/sent.html.twig');
    }

    //________________________________________________________________ENVOI D'UN MESSAGE______________________________________________________________
    #[Route('/sendMessage', name: 'app_sendMessage')]
    public function sendMessage(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        MailerInterface $mailer,
        EmailService $emailService
    ): Response {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les données du formulaire
            $message = $form->getData();

            $this->handleRecipient($user, $form, $message, $entityManager);

            $message->setSender($user);
            $entityManager->persist($message);
            $entityManager->flush();
            /**
             * @var User|null $user
             */
            $emailAddress = $user->getEmail();

            $emailService->notificationEmailToRecipient($mailer, $emailAddress, $message);

            $this->addFlash('success', 'Message envoyé avec succès');
            return $this->redirectToRoute('app_sent');
        }

        return $this->render('messages/send.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Gère le destinataire du message
    public function handleRecipient(UserInterface $user, $form, Message $message, EntityManagerInterface $entityManager): void
    {
        // Si l'utilisateur est un ROLE_USER, on assigne automatiquement le recipient à un admin
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            // Si c'est un admin qui envoie un message, le destinataire est celui choisi dans le formulaire
            $recipient = $form->get('recipient')->getData();
            if (!$recipient) {
                throw new \Exception('Aucun destinataire sélectionné');
            }
            $message->setRecipient($recipient);
        } elseif (in_array('ROLE_USER', $user->getRoles(), true)) {
            $admin = $entityManager->getRepository(User::class)->findOneByRole('ROLE_ADMIN'); // Récupère le premier admin trouvé
            if (!$admin) {
                throw new \Exception('Aucun administrateur trouvé');
            }
            $message->setRecipient($admin);
        } else {
            throw new \Exception('Rôle utilisateur non pris en charge');
        }
    }



    //________________________________________________________________REPONSSE A UN MESSAGE______________________________________________________________
    #[Route('/reply/{id}', name: 'app_reply')]
    public function reply(Message $originalMessage, Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Vérifie que l'utilisateur est autorisé à répondre
        if ($user !== $originalMessage->getSender() && $user !== $originalMessage->getRecipient()) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à répondre à ce message.');
        }

        $reply = new Message();
        $reply->setSender($user);
        // Si l'utilisateur est l'expéditeur, le destinataire est le récepteur et vice versa
        $reply->setRecipient($user === $originalMessage->getSender() ? $originalMessage->getRecipient() : $originalMessage->getSender());
        $form = $this->createForm(MessageType::class, $reply);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reply->setContent($form->get('content')->getData());

            $entityManager->persist($reply);
            $entityManager->flush();

            $this->addFlash('success', 'Réponse envoyée avec succès');
            return $this->redirectToRoute('app_received');
        }

        return $this->render('messages/reply.html.twig', [
            'form' => $form->createView(),
            'originalMessage' => $originalMessage,
        ]);
    }

    //________________________________________________________________LECTURE D'UN MESSAGE______________________________________________________________
    #[Route('/read/{id}', name: 'app_read')]
    public function read(Message $message, EntityManagerInterface $entityManager, Security $security): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérifie si l'utilisateur est valide
        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Vérifie que l'utilisateur est autorisé à lire le message
        if ($message->getRecipient() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return new AccessDeniedException('Vous n\'êtes pas autorisé à accéder à cette section.');
        }

        $message->setRead(true);
        $entityManager->persist($message);
        $entityManager->flush();

        return $this->render('messages/read.html.twig', compact('message'));
    }

    //________________________________________________________________SUPPRESSION D'UN MESSAGE______________________________________________________________
    #[Route('/delete/{id}', name: 'app_deleteMessage')]
    public function deleteMessage(
        Message $message,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
        Security $security,
        Request $request
    ): Response {
        // Récupère l'utilisateur actuellement connecté
        $user = $security->getUser();

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

        $entityManager->remove($message);
        $entityManager->flush();

        return $this->redirectToRoute('app_received');
    }
}
