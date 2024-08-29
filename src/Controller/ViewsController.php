<?php

namespace App\Controller;

use App\Form\ChatType;
use App\Entity\Comment;
use App\Entity\Project;
use App\Form\CommentType;
use App\Form\ServiceType;
use App\Form\UserFormType;
use App\Form\EditPasswordType;
use App\Services\PdfGenerator;
use App\Services\MistralService;
use App\Repository\QuoteRepository;
use App\Repository\ArticleRepository;
use App\Repository\ProjectRepository;
use App\Repository\ServiceRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProjectImgRepository;
use App\Repository\AppointmentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\AdministrationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class ViewsController extends AbstractController
{
    // ---------------------------------Vue des Erreurs--------------------------------- //
    // Vue error 404 = page non trouvée
    #[Route('/error/404', name: 'app_error_404')]
    public function showError404(): Response
    {
        return $this->render('errors/error404.html.twig');
    }


    // Vue error 500 = erreur serveur
    #[Route('/error/500', name: 'app_error_500')]
    public function showError500(): Response
    {
        return $this->render('errors/error500.html.twig');
    }


    // ---------------------------------Vue Home--------------------------------- //
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_home');
    }


    #[Route('/home', name: 'app_home')]
    public function homeShow(
        AdministrationRepository $administrationRepository,
        ProjectRepository $projectRepository,
        ProjectImgRepository $projectImgRepository,
        ServiceRepository $serviceRepository,
        CategoryRepository $categoryRepository,
    ): Response {
        $administrations = $administrationRepository->findAll();
        $projects = $projectRepository->findAll();
        $projectImgs = $projectImgRepository->findAll();
        $services = $serviceRepository->findAll();
        $categories = $categoryRepository->findAll();

        // Formulaire pour la génération d'offre de prix
        $offerPriceForm = $this->createForm(ServiceType::class, null, [
            'action' => $this->generateUrl('app_generate_offerPrice'), // URL de l'action du formulaire
            'method' => 'POST',
        ]);

        return $this->render('home/index.html.twig', [
            'administrations' => $administrations,
            'projects' => $projects,
            'projectImgs' => $projectImgs,
            'services' => $services,
            'categories' => $categories,
            'offerPriceForm' => $offerPriceForm->createView(),
        ]);
    }


    // ---------------------------------Vue de génération d'offre de prix--------------------------------- //
    #[Route('/offerPrice', name: 'app_generate_offerPrice', methods: ['POST'])]
    public function generateOfferPrice(Request $request, PdfGenerator $pdfGenerator): Response
    {
        $offerPriceForm = $this->createForm(ServiceType::class);
        $offerPriceForm->handleRequest($request);

        if ($offerPriceForm->isSubmitted() && $offerPriceForm->isValid()) {
            $formData = $offerPriceForm->getData();

            $selectedServices = [];
            // Récupére les services sélectionnés
            foreach (['services_identite_visuelle', 'services_site_internet', 'services_presta_a_la_carte'] as $category) {
                // Vérifie si la catégorie existe dans le formulaire
                if (isset($formData[$category])) {
                    foreach ($formData[$category] as $service) {
                        // Ajoute les services sélectionnés dans le tableau
                        $selectedServices[] = [
                            'serviceName' => $service->getServiceName(),
                            'servicePrice' => $service->getServicePrice(),
                            'category' => [
                                'categoryName' => $service->getCategory()->getCategoryName()
                            ]
                        ];
                    }
                }
            }
            // Vérifie si aucun service n'a été sélectionné
            if (empty($selectedServices)) {
                $this->addFlash('error', 'Aucun service n\'a été sélectionné.');
                return $this->redirectToRoute('app_home');
            }

            // Génére le PDF
            $pdfResponse = $pdfGenerator->generateOfferPricePdf($selectedServices);

            return $pdfResponse;
        }

        return $this->redirectToRoute('app_home');
    }


    // ---------------------------------Vue profil utilisateur--------------------------------- //
    #[Route('/profil', name: 'app_profil', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profil(Security $security, AppointmentRepository $appointmentRepository, QuoteRepository $quoteRepository): Response
    {
        $user = $security->getUser();

        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new AccessDeniedException('Accès refusé');
        }

        // Formulaire pour les informations utilisateur
        $form = $this->createForm(UserFormType::class, $user);

        // Formulaire pour le changement de mot de passe
        $passwordForm = $this->createForm(EditPasswordType::class, $user);

        $appointments = $appointmentRepository->findByUser($user);
        $quotes = $quoteRepository->findByUser($user);

        return $this->render('user/profil.html.twig', [
            'form' => $form->createView(),
            'passwordForm' => $passwordForm->createView(),
            'user' => $user,
            'appointments' => $appointments,
            'quotes' => $quotes,
        ]);
    }


    // ---------------------------------Vue liste projets--------------------------------- //
    #[Route('/projects', name: 'app_projectList')]
    public function listProjectsShow(ProjectRepository $projectRepository, ProjectImgRepository $projectImgRepository, CategoryRepository $categoryRepository): Response
    {
        $projects = $projectRepository->findAll();
        $projectImgs = $projectImgRepository->findAll();
        $categories = $categoryRepository->findAll();

        // Vérifie si les projets et les images de projets existent
        if (!$projects || !$projectImgs || !$categories) {
            throw new NotFoundHttpException('Page non trouvée');
        }

        return $this->render('projects/project_list.html.twig', [
            'projects' => $projects,
            'projectImgs' => $projectImgs,
            'categories' => $categories,
        ]);
    }


    // ---------------------------------Vue détail projets--------------------------------- //
    #[Route('/projects/{slug}', name: 'app_project', requirements: ['slug' => '[a-z0-9\-]*'])]
    public function projectShow(?Project $project, string $slug): Response
    {
        if (!$project) {
            throw new NotFoundHttpException('Aucun projet trouvé');
            return $this->redirectToRoute('app_home');
        }

        // Vérifie si le slug de l'objet project correspond au slug de l'URL
        if ($project->getSlug() !== $slug) {
            throw new NotFoundHttpException('Page non trouvée');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('projects/project.html.twig', [
            'project' => $project
        ]);
    }


    // ---------------------------------Vue liste articles--------------------------------- //
    #[Route('/blog', name: 'app_blog')]
    public function listArticlesShow(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        // Vérifie si les articles existent
        if (!$articles) {
            throw new NotFoundHttpException('Aucun article trouvé');;
            return $this->redirectToRoute('app_blog');
        }

        return $this->render('blog/article_list.html.twig', [
            'articles' => $articles,
        ]);
    }


    // ---------------------------------Vue détail article--------------------------------- //
    #[Route('blog/{slug}', name: 'app_article', requirements: ['slug' => '[a-z0-9\-]*'])]
    public function articleShow(string $slug, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        $articles = $articleRepository->findAll();
        // Récupération des articles aléatoires
        $randomArticles = $articleRepository->findRandomArticles($slug);
        dd($randomArticles);

        // Vérifie si l'article existe
        if (!$article) {
            throw new NotFoundHttpException('Aucun article trouvé');;
            return $this->redirectToRoute('app_blog');
        }
        // Vérifie si le slug de l'objet article correspond au slug de l'URL
        if ($article->getSlug() !== $slug) {
            throw new NotFoundHttpException('Aucun article trouvé');;
            return $this->redirectToRoute('app_blog');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        return $this->render('blog/article.html.twig', [
            // 'articles' => $articles,

            'articles' => $randomArticles,
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    // ---------------------------------Vue CGU --------------------------------- //
    #[Route('/cgu', name: 'app_cgu')]
    public function showCGU(): Response
    {
        return $this->render('legal/cgu.html.twig');
    }

    // ---------------------------------Vue Mentions légales --------------------------------- //
    #[Route('/mentions-legales', name: 'app_mentions')]
    public function showMentions(): Response
    {
        return $this->render('legal/mentions.html.twig');
    }

    // ---------------------------------CGV --------------------------------- //
    #[Route('/cgv', name: 'app_cgv')]
    public function showCGV(): Response
    {
        return $this->render('legal/cgv.html.twig');
    }


    // ---------------------------------Vue Chat --------------------------------- //
    #[Route('/chat', name: 'app_chat')]
    public function showChat(Request $request, MistralService $mistralService): Response
    {
        $form = $this->createForm(ChatType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $response = $mistralService->getResponse($data['message']);
            // dd($response);
    
                return new JsonResponse(['response' => $response]);
        
        }
    
        return $this->render('home/chat.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}
