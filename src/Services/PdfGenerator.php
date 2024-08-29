<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Quote;
use App\Entity\Appointment;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PdfGenerator
{
    private $domPdf;
    private $twig;
    private $params;

    // Définition des dépendances
    public function __construct(Environment $twig, ParameterBagInterface $params)
    {

        $this->domPdf = new Dompdf(); // Crée une nouvelle instance de Dompdf

        $pdfOptions = new Options(); // Crée une nouvelle instance de Options

        $pdfOptions->set('defaultFont', 'Arial'); // Définit la police par défaut

        $this->domPdf->setPaper('A4', 'portrait'); // Définit le format de la page

        $this->domPdf->setOptions($pdfOptions); // Applique les options

        $this->twig = $twig; // Injecte le moteur de rendu Twig

        $this->params = $params; // Injecte les paramètres de l'application
    }


    // ---------------------------------Affichage du PDF--------------------------------- //
    public function showPdfFile($html): Response
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();

        $pdfContent = $this->domPdf->output();

        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="document.pdf"'
            ]
        );
    }

    // ---------------------------------Génération du PDF--------------------------------- //
    public function generatePDF($html): string
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->render();
        return $this->domPdf->output();
    }


    // ---------------------------------Création d'un devis--------------------------------- //
    public function createQuote(Appointment $appointment): Quote
    {
        $services = $appointment->getServices();

        // Crée un nouveau devis
        $quote = new Quote();
        $reference = 'DEVIS-' . uniqid(); // Génère une référence unique
        $quote->setReference($reference);
        $quote->setQuoteDate(new \DateTime()); // Définit la date du devis
        $quote->setCustomerName($appointment->getName());
        $quote->setCustomerFirstName($appointment->getFirstName());
        $quote->setCustomerEmail($appointment->getEmail());
        $quote->setStatus(0);
        $quote->setState(Quote::STATE_PENDING); // Définit l'état du devis

        // Associe le rendez-vous au devis
        $quote->setAppointments($appointment);

        // Calcul du prix total des services sélectionnés*
        if (!$services->isEmpty()) {
            $totalPrice = $quote->calculateTotal($services);
        } else {
            $totalPrice = 0;
        }

        // Calcul du prix total des services selectionnés
        $quote->setTotalTTC($totalPrice);

        return $quote;
    }


    // ---------------------------------Génération et stockage du PDF--------------------------------- //
    public function generateAndStorePdf(PdfGenerator $pdfGenerator, Quote $quote, string $reference): string
    {
        $imagePath = $this->params->get('kernel.project_dir') . '/public/img/logo_black.svg';
        $imageData = base64_encode(file_get_contents($imagePath));

        // Génère un fichier PDF à partir du template Twig
        $html = $this->twig->render('admin/quote.html.twig', [
            'quote' => $quote,
            'appointment' => $quote->getAppointments(),
            'logo' => $imageData,
        ]);

        $pdfContent = $pdfGenerator->generatePDF($html);

        // Utilisation d'un répertoire sécurisé non accessible publiquement
        $pdfDirectory = $this->params->get('kernel.project_dir') . '/var/uploads/pdf/';
        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0750, true); // Création du dossier avec des permissions sécurisées
        }

        // Nettoyage du nom de fichier pour éviter path Injection
        $safeReference = preg_replace('/[^a-zA-Z0-9_\-]/', '', $reference);
        $pdfFilename = $safeReference . '.pdf';
        $pdfFilepath = $pdfDirectory . $pdfFilename;

        file_put_contents($pdfFilepath, $pdfContent);

        // Enregistre le chemin relatif sécurisé
        $quote->setPdfContent('/uploads/pdf/' . $pdfFilename);

        return $quote->getPdfContent();
    }


// ---------------------------------Archivage et stockage du PDF--------------------------------- //
    public function generateAndArchivePdf(PdfGenerator $pdfGenerator, Quote $quote, string $reference): string
    {
        $imagePath = $this->params->get('kernel.project_dir') . '/public/img/logo_black.svg';
        $imageData = base64_encode(file_get_contents($imagePath));

        // Génère un fichier PDF à partir du template Twig
        $html = $this->twig->render('admin/quote.html.twig', [
            'quote' => $quote,
            'appointment' => $quote->getAppointments(),
            'logo' => $imageData,
        ]);

        // Génère le contenu PDF
        $pdfContent = $pdfGenerator->generatePDF($html);

        // Utilisation d'un répertoire sécurisé non accessible publiquement
        $pdfDirectory = $this->params->get('kernel.project_dir') . '/var/uploads/pdf/archive/';
        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0750, true); // Crée le répertoire avec des permissions sécurisées
        }

        // Nettoyage du nom de fichier pour éviter path Injection
        $safeReference = preg_replace('/[^a-zA-Z0-9_\-]/', '', $reference);
        $pdfFilename = $safeReference . '.pdf';
        $pdfFilepath = $pdfDirectory . $pdfFilename;

        // Sauvegarde le PDF sur le système de fichiers avec un contrôle de permission sécurisé
        file_put_contents($pdfFilepath, $pdfContent);

        // Stocke le chemin relatif sécurisé pour le PDF
        $quote->setPdfContent('/uploads/pdf/archive/' . $pdfFilename);

        // Mets à jour l'entité Quote
        return $quote->getPdfContent();
    }


    // ---------------------------------Génération de l'offre de prix PDF--------------------------------- //
    public function generateOfferPricePdf(array $selectedServices): Response
    {
        $imagePath = $this->params->get('kernel.project_dir') . '/public/img/logo_black.svg';
        $imageData = base64_encode(file_get_contents($imagePath));

        $html = $this->twig->render('admin/offerPrice.html.twig', [
            'services' => $selectedServices,
            'logo' => $imageData,
        ]);
        // Génére le contenu PDF
        $this->domPdf->loadHtml($html);
        // Rendu du PDF
        $this->domPdf->render();
        // Renvoie le PDF en réponse HTTP
        $pdfContent = $this->domPdf->output();

        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="devis_services.pdf"'
            ]
        );
    }
}
