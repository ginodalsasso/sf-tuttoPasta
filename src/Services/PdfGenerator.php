<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Quote;
use App\Entity\Appointment;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PdfGenerator extends AbstractController
{
    private $domPdf;
    private $twig;
    private $params;

    // Définition des dépendances du service
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
        // Charge le contenu HTML
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
        $this->domPdf->loadHtml($html); // Charge le contenu HTML
        $this->domPdf->render(); // Rendu du PDF
        return $this->domPdf->output(); // Renvoie le contenu du PDF
    }


    // ---------------------------------Création d'un devis--------------------------------- //
    public function createQuote(Appointment $appointment): Quote
    {
        $services = $appointment->getServices();

        // Crée un nouveau devis
        $quote = new Quote();
        $reference = 'DEVIS-' . uniqid(); // Génère une référence unique
        $quote->setReference($reference);
        $timezone = new \DateTimeZone('Europe/Paris');

        $quote->setQuoteDate(new \DateTime('now', $timezone)); // Définit la date du devis
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
        $pdfDirectory = $this->params->get('kernel.project_dir') . '/public/uploads/pdf/';
        // Si le répertoire n'existe pas
        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0750, true); // Création du dossier avec des permissions sécurisées
        }
    
        $pdfFilename = $reference . '.pdf'; // Utilisation directe de la référence comme nom de fichier
        $pdfFilepath = $pdfDirectory . $pdfFilename; // Chemin du fichier PDF
    
        // Enregistre le PDF sur le système de fichiers avec des permissions sécurisées
        file_put_contents($pdfFilepath, $pdfContent);
        
        // Enregistre le chemin relatif sécurisé
        $quote->setPdfContent('/uploads/pdf/' . $pdfFilename);
    
        // retourne le chemin relatif du PDF
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
        $pdfDirectory = $this->params->get('kernel.project_dir') . '/public/uploads/pdf/archive/';
        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0750, true); // Crée le répertoire avec des permissions sécurisées
        }

        // Utilisation directe de la référence comme nom de fichier
        $pdfFilename = $reference . '.pdf';
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

    public function unlinkPdfFile($quote)
    {
        // Supprime le fichier PDF associé au devis
        $pdfPath = $this->getParameter('kernel.project_dir') . '/public' . $quote->getPdfContent();

        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        } else {
            error_log('Fichier non trouvé: ' . $pdfPath);
        } 
    }
}
