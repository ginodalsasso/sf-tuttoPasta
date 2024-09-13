<?php

namespace App\Services;

use App\Entity\User;
use App\Entity\Contact;
use App\Entity\Appointment;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailService extends AbstractController
{
    private $mailer;
    private $templating;

    public function __construct(MailerInterface $mailer, \Twig\Environment $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }


    // _______________________________________________________MAILING DES RDV___________________________________________________________________

    // Gestion de l'envoi de confiration de prise de RDV pour le client
    public function sendConfirmationEmailTo(MailerInterface $mailer, string $emailAddress, \DateTime $startDate): void
    {
        $emailContent = $this->renderView('emails/appointment_confirmation.html.twig', [
            'appointmentDate' => $startDate->format('d/m/Y à H:i')
        ]);

        $email = (new TemplatedEmail())
            ->from(new Address('admin@tuttoPasta.com', 'TuttoPasta'))
            ->to($emailAddress)
            ->subject('Confirmation de votre rendez-vous')
            ->html($emailContent);

        $mailer->send($email);
    }

    // Gestion de l'envoi de confiration de prise de RDV pour tuttoPasta
    public function sendConfirmationEmailFrom(MailerInterface $mailer, string $emailAddress, \DateTime $startDate): void
    {
        $emailContent = $this->renderView('emails/appointment_confirmation.html.twig', [
            'appointmentDate' => $startDate->format('d/m/Y à H:i')
        ]);

        $email = (new TemplatedEmail())
            ->from(new Address($emailAddress))
            ->to(new Address('admin@tuttoPasta.com', 'TuttoPasta'))
            ->subject('Nouveau Rendez vous')
            ->html($emailContent);

        $mailer->send($email);
    }

    // _______________________________________________________MAILING DE LA CONTACT___________________________________________________________________

    // Gestion de l'envoi de confirmation du contact à l'utilisateur
    public function sendConfirmationEmail(MailerInterface $mailer, string $emailAddress, Contact $contact): void
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
    public function sendAdminNotificationEmail(MailerInterface $mailer, Contact $contact): void
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
    
    // _______________________________________________________MAILING DE LA CONTACT___________________________________________________________________

    // Gestion de l'envoi de notification de réception d'un nouveau message
    public function notificationEmailToRecipient(MailerInterface $mailer, string $emailAddress, $message): void
    {
        $emailContent = $this->renderView('emails/message_notification.html.twig', [
            'titleMessage' => $message->getTitle(),
            'contentMessage' => $message->getContent(),
            'dateMessage' => $message->getCreatedAt()->format('d/m/Y à H:i')
        ]);

        $email = (new TemplatedEmail())
            ->from(new Address('admin@tuttoPasta.com', 'TuttoPasta'))
            ->to($emailAddress)
            ->subject('Nouveau message reçu')
            ->html($emailContent);

        $mailer->send($email);
    }

    // _______________________________________________________MAILING DE USER___________________________________________________________________

    // Gestion de l'envoi de notification d'annulation de RDV
    public function sendCancellationEmail(MailerInterface $mailer, Appointment $appointment): void
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
    public function sendAccountDeletionEmail(MailerInterface $mailer, UserInterface $user): void
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
}
