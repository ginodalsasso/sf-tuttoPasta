<?php

namespace App\Controller;

use App\Entity\Quote;
use App\Entity\Service;
use App\Form\QuoteType;
use App\Entity\Category;
use App\Services\PdfGenerator;
use App\Repository\UserRepository;
use App\Repository\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;



class QuoteController extends AbstractController
{
    private $pdfGenerator;
    private $csrfTokenManager;


    public function __construct(PdfGenerator $pdfGenerator, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->pdfGenerator = $pdfGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
    }


    // ---------------------------------Vue PDF DEVIS--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/{id}', name: 'quote_pdf')]
    public function viewQuotePdf(int $id, EntityManagerInterface $entityManager, PdfGenerator $pdfGenerator): Response
    {
        $quote = $entityManager->getRepository(Quote::class)->find($id);

        $imagePath = $this->getParameter('kernel.project_dir') . '/public/img/logo_black.svg';
        $imageData = base64_encode(file_get_contents($imagePath));

        if (!$quote) {
            throw $this->createNotFoundException('Ce devis n\'existe pas');
        }

        $html = $this->renderView('admin/quote.html.twig', [
            'quote' => $quote,
            'appointment' => $quote->getAppointments(),
            'logo' => $imageData,
        ]);
        // Générer le contenu PDF
        return $pdfGenerator->showPdfFile($html);
    }


    // ---------------------------------Vue LISTE DES DEVIS--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quotes', name: 'app_quotes')]
    public function listQuotesShow(Request $request, QuoteRepository $quoteRepository, UserRepository $userRepository): Response
    {
        // Récupérer le résultat de la recherche
        $searchName = $request->request->get('name');

        $searchNameSanitized = filter_var($searchName, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // s'il y a une recherche
        if ($searchNameSanitized) {
            $quotes = $quoteRepository->findOneByNameOrEmail($searchNameSanitized);
        } else {
            $quotes = $quoteRepository->findAll();
        }

        // Initialiser un tableau pour stocker les devis et les utilisateurs associés
        $quoteWithUsers = [];
        foreach ($quotes as $quote) {
            // Récupérer l'utilisateur lié au devis par l'email
            $user = $userRepository->findOneBy(['email' => $quote->getCustomerEmail()]);
            // Ajoute le devis et l'utilisateur à un tableau
            $quoteWithUsers[] = [
                'quote' => $quote,
                'user' => $user,
            ];
        }

        // Si la requête est AJAX, renvoyer le HTML des résultats seulement
        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/_quote_list.html.twig', [
                'quoteWithUsers' => $quoteWithUsers,
            ]);
        }
        // Sinon, renvoyer la page entière
        return $this->render('admin/quote_list.html.twig', [
            'quoteWithUsers' => $quoteWithUsers,
        ]);
    }


    // ---------------------------------Formulaire d'Edition du devis PDF--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/edit/{id}', name: 'quote_edit')]
    public function editQuote(int $id, Request $request, EntityManagerInterface $entityManager, HtmlSanitizerInterface $htmlSanitizer): Response
    {
        // Récupérer le devis
        $quote = $entityManager->getRepository(Quote::class)->find($id);
        // Récupérer l'appointment lié
        $appointment = $quote ? $quote->getAppointments() : null;

        if (!$quote) {
            throw $this->createNotFoundException('Ce devis n\'existe pas');
        }
        if (!$appointment) {
            throw $this->createNotFoundException('Ce RDV n\'existe pas');
        }

        // Créer le formulaire d'édition du devis
        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sanitize et valider les champs du formulaire
            $reference = $htmlSanitizer->sanitize($form->get('reference')->getData());
            $clientLastName = $htmlSanitizer->sanitize($form->get('customerName')->getData());
            $clientFirstName = $htmlSanitizer->sanitize($form->get('customerFirstName')->getData());
            $email = $form->get('customerEmail')->getData();
            $services = $form->get('services')->getData();

            // Vérifier si un nouveau service est défini et le sanitize si nécessaire
            
            $newServiceCategory = $form->get('newServiceCategory')->getData();
            $newServiceName = $form->get('newService')->getData();
            $newServicePrice = $form->get('newServicePrice')->getData();

            if ($newServiceName !== null) {
                $newServiceName = $htmlSanitizer->sanitize($newServiceName);
            }
            if ($newServicePrice !== null) {
                // Valider que le prix est un nombre positif
                if (!is_numeric($newServicePrice) || $newServicePrice <= 0) {
                    $this->addFlash('error', 'Le prix du service doit être un nombre positif.');
                    return $this->render('admin/edit_quote.html.twig', [
                        'quote' => $quote,
                        'form' => $form->createView(),
                    ]);
                }
                $newServicePrice = $htmlSanitizer->sanitize($newServicePrice);
            }

            // Valider l'email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Adresse email invalide.');
                return $this->render('admin/edit_quote.html.twig', [
                    'quote' => $quote,
                    'form' => $form->createView(),
                ]);
            }

            // Vérifier si un nouveau service est défini
            if ($newServiceName && $newServicePrice) {
                // Ajouter le nouveau service
                $this->addNewService($newServiceName, $newServicePrice, $newServiceCategory, $services, $entityManager);
            }
            // // Vérifier si un nouveau service a été ajouté
            // if ($newServiceName && $newServicePrice) {
            //     // Créer et sauvegarder le nouveau service
            //     $newService = new Service();
            //     $newService->setServiceName($newServiceName);
            //     $newService->setServicePrice($newServicePrice);
            //     $newService->setCategory($newServiceCategory);

            //     $entityManager->persist($newService);
            //     $entityManager->flush();

            //     // Ajouter le nouveau service aux services sélectionnés
            //     $services[] = $newService;
            // }

            // Mettre à jour les services de l'appointment lié
            foreach ($appointment->getServices() as $service) {
                // Si le service n'est pas sélectionné, le retirer
                $appointment->removeService($service);
            }
            // Ajouter les services sélectionnés
            foreach ($services as $service) {
                $appointment->addService($service);
            }

            // Recalculer le total
            $totalPrice = $quote->calculateTotal($appointment->getServices());
            $quote->setTotalTTC($totalPrice);

            // Transformer le status du devis afin de l'afficher dans le profil user
            $quote->setStatus(1);
            $quote->setState(Quote::STATE_IN_PROGRESS);

            $entityManager->persist($appointment);
            $entityManager->persist($quote);
            $entityManager->flush();

            // Rediriger vers la vue du devis mis à jour
            return $this->redirectToRoute('quote_pdf', ['id' => $quote->getId()]);
        }

        return $this->render('admin/edit_quote.html.twig', [
            'quote' => $quote,
            'form' => $form->createView(),
        ]);
    }

    // Ajout d'un nouveau service dans l'édition de devis
    private function addNewService(string $newServiceName, float $newServicePrice, $newServiceCategory, $services, EntityManagerInterface $entityManager
    ): void {
        // Créer et sauvegarder le nouveau service
        $newService = new Service();
        $newService->setServiceName($newServiceName);
        $newService->setServicePrice($newServicePrice);
        $newService->setCategory($newServiceCategory);

        $entityManager->persist($newService);
        $entityManager->flush();

        // Ajouter le nouveau service aux services sélectionnés
        $services[] = $newService;
    }

    // ---------------------------------Suppression du devis PDF--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/{id}/delete', name: 'app_delete_quote', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteQuote(Request $request, EntityManagerInterface $entityManager, int $id, Security $security, PdfGenerator $pdfGenerator): JsonResponse
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

        // Récupère le devis
        $quote = $entityManager->getRepository(Quote::class)->find($id);

        // Vérifie si l'utilisateur est autorisé à le supprimer
        if (!$quote || !($this->isGranted('ROLE_ADMIN'))) {
            return new JsonResponse(['success' => false, 'message' => 'Devis non trouvé ou vous n\'avez pas les droits pour le supprimer.'], 403);
        }

        // Supprime le fichier PDF associé
        $pdfGenerator->unlinkPdfFile($quote);

        // Supprime le devis de la base de données
        $entityManager->remove($quote);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }


    // ---------------------------------Archivage du devis(Etat) PDF--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/{id}/archive', name: 'app_archive_quote', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function archiveQuote(Request $request, EntityManagerInterface $entityManager, int $id, Security $security, PdfGenerator $pdfGenerator): JsonResponse
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

        // Récupère le devis
        $quote = $entityManager->getRepository(Quote::class)->find($id);

        // Vérifie si l'utilisateur est autorisé
        if (!$quote || !($this->isGranted('ROLE_ADMIN'))) {
            return new JsonResponse(['success' => false, 'message' => 'Rendez-vous non trouvé ou vous n\'avez pas les droits pour le supprimer.'], 403);
        }
        // Archive le devis en changeant son état
        $quote->setState(Quote::STATE_ARCHIVED);
        $reference = $quote->getReference();

        // Supprime le fichier PDF associé
        $pdfGenerator->unlinkPdfFile($quote);  

        // Archive le PDF dans le dossier associé
        $pdfGenerator->generateAndArchivePdf($pdfGenerator, $quote, $reference);

        // Persiste les modifications
        $entityManager->persist($quote);
        $entityManager->flush();


        return new JsonResponse(['success' => true]);
    }


    // ---------------------------------Transformation du devis en etat payé--------------------------------- //
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/quote/{id}/completed', name: 'app_completed_quote', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function completedQuote(Request $request, EntityManagerInterface $entityManager, int $id, Security $security): JsonResponse
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

        // Récupère le devis
        $quote = $entityManager->getRepository(Quote::class)->find($id);

        // Vérifie si l'utilisateur est autorisé
        if (!$quote || !($this->isGranted('ROLE_ADMIN'))) {
            return new JsonResponse(['success' => false, 'message' => 'Rendez-vous non trouvé ou vous n\'avez pas les droits pour le supprimer.'], 403);
        }
        // Archive le devis en changeant son état
        $quote->setState(Quote::STATE_COMPLETED);

        // Persiste les modifications
        $entityManager->persist($quote);
        $entityManager->flush();


        return new JsonResponse(['success' => true]);
    }
}
