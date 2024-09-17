<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Form\AppointmentType;
use App\Services\EmailService;
use App\Services\PdfGenerator;
use App\Services\SmsGenerator;
use App\Repository\DayOffRepository;
use App\Repository\ServiceRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AppointmentController extends AbstractController
{
    private $htmlSanitizer;
    private $pdfGenerator;
    private $csrfTokenManager;
    private $emailService;


    public function __construct(HtmlSanitizerInterface  $htmlSanitizer, PdfGenerator $pdfGenerator, CsrfTokenManagerInterface $csrfTokenManager, EmailService $emailService)
    {
        $this->htmlSanitizer = $htmlSanitizer;
        $this->pdfGenerator = $pdfGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->emailService = $emailService;
    }

    //________________________________________________________________APPOINTMENT______________________________________________________________
    // Vue et gestion du processus de création d'un rendez-vous
    #[Route('/home/appointment', name: 'app_appointment')]
    public function addAppointment(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        ServiceRepository $serviceRepository,
        DayOffRepository $dayOffRepository,
        MailerInterface $mailer,
        PdfGenerator $pdfGenerator,
        EmailService $emailService,
        SmsGenerator $smsGenerator
    ): Response {
        $services = $serviceRepository->findAll();
        $categories = $categoryRepository->findAll();

        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);

        $form->handleRequest($request);

        // Récupère tous les jours de congé depuis le repository
        $dayOffs = $dayOffRepository->findAll();

        $dayOffDates = [];
        // Convertit les objets DayOff en un tableau de dates
        foreach ($dayOffs as $dayOff) {
            $dayOffDates[] = $dayOff->getDayOff()->format('Y-m-d');
        }
        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // HoneyPot 
            $honeypotValue = $form->get('firstname')->getData();

            if (!empty($honeypotValue)) {
                // Le champ a été rempli, probablement un bot
                return $this->redirectToRoute('app_home');
            }

            // Récupère les données du formulaire
            $appointment = $form->getData();

            // Sanitize les champs du formulaire
            $appointment->setName($this->htmlSanitizer->sanitize($appointment->getName()));
            $appointment->setFirstName($this->htmlSanitizer->sanitize($appointment->getFirstName()));
            $appointment->setMessage($this->htmlSanitizer->sanitize($appointment->getMessage()));
            // Vérifie si l'adresse email est valide
            $emailAddress = $appointment->getEmail();
            if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->redirectToRoute('app_appointment');
            }
            // Récupère le créneau horaire sélectionné depuis la requête
            $selectedSlot = $request->request->get('selectedSlot');

            // si un créneau horaire a été sélectionné
            if ($selectedSlot) {
                // Crée des objets DateTime pour le début et la fin du rendez-vous
                $startDate = new \DateTime($selectedSlot);
                $endDate = clone $startDate; // Clone la date de début
                $endDate->modify('+1 hour'); // Ajoute une heure à la date de fin

                //si dans le tableau des jours de congé on trouve la date sélectionnée
                if (in_array($startDate->format('Y-m-d'), $dayOffDates)) {
                    $this->addFlash('error', 'Vous ne pouvez pas prendre RDV durant nos congés.');
                } else {
                    // Définit les dates de début et de fin du rendez-vous
                    $appointment->setStartDate($startDate);
                    $appointment->setEndDate($endDate);

                    //Vérifie si un utilisateur est connecté
                    $user = $security->getUser();

                    // Si un utilisateur est connecté, associe ses informations au rendez-vous
                    if ($user) {
                        $appointment->setUser($user);
                    }

                    // Création du devis
                    $quote = $pdfGenerator->createQuote($appointment);

                    // Génération et stockage du PDF
                    $reference = $quote->getReference();
                    $pdfGenerator->generateAndStorePdf($pdfGenerator, $quote, $reference);

                    // Persiste le rendez-vous dans la base de données
                    $entityManager->persist($appointment);
                    $entityManager->persist($quote);
                    $entityManager->flush();

                    $emailService->sendConfirmationEmailTo($mailer, $emailAddress, $startDate); // Envoie un email de confirmation au client
                    $emailService->sendConfirmationEmailFrom($mailer, $emailAddress, $startDate); // Envoie un email de confirmation à tuttoPasta
                    $message = 'Nouveau message de ' . $appointment->getName() . ' ' . $appointment->getFirstName() . ' : ' . $appointment->getMessage();
                    $smsGenerator->sendSms($message); // Envoie un SMS à tuttoPasta

                    // Ajoute un message de succès et redirige vers la page d'accueil
                    $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès. Un email de confirmation vous a été envoyé.');
                    return $this->redirectToRoute('app_home');
                }
            } else {
                // Si aucun créneau horaire n'est sélectionné, ajoute un message d'erreur
                $this->addFlash('error', 'Veuillez sélectionner un créneau horaire.');
            }
        }

        return $this->render('home/appointment.html.twig', [
            'form' => $form->createView(),
            'title' => 'Prise de rendez-vous',
            'services' => $services,
            'categories' => $categories,
        ]);
    }

    // Récupère les créneaux horaires disponibles pour une date donnée
    #[Route('/available_rdv', name: 'available_rdv', methods: ['POST'])]
    public function getAvailableTimes(Request $request, AppointmentRepository $appointmentRepository): JsonResponse
    {
        // Récupère le jeton CSRF depuis les en-têtes
        $csrfToken = $request->headers->get('X-CSRF-TOKEN');

        // Vérifier la validité du jeton CSRF
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('', $csrfToken))) {
            return new JsonResponse(['error' => 'Jeton CSRF invalide.'], 403);
        }

        // Crée un objet DateTime à partir de la date de début postée
        $startDate = new \DateTime($request->request->get('startDate'));

        // Récupère les créneaux horaires disponibles
        $availabilities = $appointmentRepository->findAllRDV($startDate);

        // Retourne les disponibilités sous forme de réponse JSON
        return new JsonResponse([
            'availabilities' => $availabilities,
        ]);
    }


    // Récupère toutes les dates de congés
    #[Route('/get_dayoff_dates', name: 'get_dayoff_dates', methods: ['POST'])]
    public function getDayOffDates(DayOffRepository $dayOffRepository): JsonResponse
    {
        // Récupère tous les jours de congé depuis le repository
        $dayoffs = $dayOffRepository->findAllDayoffs();

        // Convertit les objets DateTime en format string pour JavaScript
        $dayoffDates = [];

        foreach ($dayoffs as $dayoff) {
            $dayoffDates[] = $dayoff->format('Y-m-d');
        }

        // Retourne les dates de congé sous forme de réponse JSON
        return new JsonResponse([
            'dayoffDates' => $dayoffDates,
        ]);
    }
}