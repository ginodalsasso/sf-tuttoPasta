<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactFormType;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{

    private $htmlSanitizer;

    public function __construct(HtmlSanitizerInterface  $htmlSanitizer) {
        $this->htmlSanitizer = $htmlSanitizer;
    }


    #[Route('/contact', name: 'app_contact')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security, 
        MailerInterface $mailer
        ): Response
    {
        $contact = new Contact();
        // Crée le formulaire de contact
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // HoneyPot 
            $honeypotValue = $form->get('firstname')->getData();

            if (!empty($honeypotValue)) {
                // Le champ a été rempli, probablement un bot
                return $this->redirectToRoute('app_home');
            }

            $contact = $form->getData();

            // Sanitize les champs du formulaire
            $contact->setName($this->htmlSanitizer->sanitize($contact->getName()));
            $contact->setMessage($this->htmlSanitizer->sanitize($contact->getMessage()));

            // Vérifie le sujet et utilise une valeur par défaut si null
            $subject = $contact->getSubject();
            if ($subject !== null) {
                $contact->setSubject($this->htmlSanitizer->sanitize($subject));
            }

            // Vérifie si l'adresse email est valide
            $emailAddress = $contact->getEmail();
            if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->redirectToRoute('app_appointment');
            }

            //Vérifie si un utilisateur est connecté
            $user = $security->getUser();

            // Si un utilisateur est connecté, associe ses informations au contact
            if ($user) {
                $contact->setUser($user);
            }

            $entityManager->persist($contact);
            $entityManager->flush();

            // Envoie un email de confirmation à l'utilisateur
            $this->sendConfirmationEmail($mailer, $emailAddress, $contact);
            // Envoie une notification à l'admin
            $this->sendAdminNotificationEmail($mailer, $contact);


            $this->addFlash('success', 'Votre message a bien été envoyé !');

            return $this->redirectToRoute('app_home');
        }
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    // Gestion de l'envoi de confirmation du contact à l'utilisateur
    private function sendConfirmationEmail(MailerInterface $mailer, string $emailAddress, Contact $contact): void
    {
        $emailContent = $this->renderView('emails/contact_confirmation.html.twig');

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@tuttoPasta.com', 'TuttoPasta'))
            ->to($emailAddress)
            ->subject('Confirmation de prise de contact')
            ->html($emailContent);

        $mailer->send($email);
    }


    // Gestion de l'envoi de notification à l'admin
    private function sendAdminNotificationEmail(MailerInterface $mailer, Contact $contact): void
    {
        $adminEmail = 'admin@tuttoPasta.com';
        $emailContent = $this->renderView('emails/admin_contact_notification.html.twig', [
            'contact' => $contact
        ]);

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@tuttoPasta.com', 'TuttoPasta'))
            ->to($adminEmail)
            ->subject('Nouvelle demande de contact')
            ->html($emailContent);

        $mailer->send($email);
    }
}
