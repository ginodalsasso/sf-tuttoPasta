<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

class BlogController extends AbstractController
{

    private $htmlSanitizer;
    private $csrfTokenManager;


    public function __construct(HtmlSanitizerInterface  $htmlSanitizer, CsrfTokenManagerInterface $csrfTokenManager) {
        $this->htmlSanitizer = $htmlSanitizer;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    
//________________________________________________________________CRUD________________________________________________________________
//____________________________________________________________________________________________________________________________
//____________________________________________________________________________________________________________________
    // ---------------------------------Ajout/Edition d'un commentaire article--------------------------------- //
    #[IsGranted('ROLE_USER')]
    #[Route('blog/{slug}/comment', name: 'app_article_addComment', methods: ['POST'], requirements: ['slug' => '[a-z0-9\-]+'])]
    #[Route('blog/{slug}/comment/{id}/edit', name: 'app_article_editComment', methods: ['POST'], requirements: ['slug' => '[a-z0-9\-]+', 'id' => '\d+'])]
    public function add_editComment(string $slug, Request $request,  ?int $id = null, ?int $commentId = null, CommentRepository $commentRepository, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {
        // Récupère le jeton CSRF depuis les en-têtes
        $csrfToken = $request->headers->get('X-CSRF-TOKEN');

        // Vérifier la validité du jeton CSRF
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('', $csrfToken))) {
            return new JsonResponse(['error' => 'Jeton CSRF invalide.'], 403);
        }
        
        // Vérifie si l'utilisateur est connecté
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non connecté.'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Recherche de l'article correspondant au slug fourni
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            return new JsonResponse(['error' => 'Article non trouvé !'], Response::HTTP_NOT_FOUND);
        }

        if ($id !== null) {
            $comment = $commentRepository->find($id);
            if (!$comment) {
                return new JsonResponse(['error' => 'Commentaire non trouvé !'], Response::HTTP_NOT_FOUND);
            }
            if ($comment->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
                return new JsonResponse(['error' => 'Vous n\'êtes pas autorisé à modifier ce commentaire.'], Response::HTTP_FORBIDDEN);
            }
        } else {
            $comment = new Comment();
            $comment->setUser($user);
            $comment->setArticle($article);
            $comment->setCommentDate(new \DateTime());
        }
    
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            // HoneyPot 
            $honeypotValue = $form->get('firstname')->getData();
    
            if (!empty($honeypotValue)) {
                // Le champ a été rempli, probablement un bot
                return $this->redirectToRoute('app_home');
            }

            // Sanitize le contenu du commentaire
            $sanitizedCommentContent = $this->htmlSanitizer->sanitize($comment->getCommentContent());
            // Enregistre le contenu du commentaire
            $comment->setCommentContent($sanitizedCommentContent);

            $entityManager->persist($comment);
            $entityManager->flush();
    
            return new JsonResponse([
                'success' => true,
                'comment' => [
                    'id' => $comment->getId(),
                    'username' => $comment->getUser() ? htmlspecialchars($comment->getUser()->getUsername()) : 'Utilisateur supprimé',
                    'commentContent' => $sanitizedCommentContent,
                    'date' => $comment->getCommentDate()->format('d/m/Y à H:i')
                ]
            ]);
        }
    
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = htmlspecialchars($error->getMessage());
        }
    
        return new JsonResponse(['success' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
    }
    
    
    // ---------------------------------Suppression d'un commentaire article--------------------------------- //
    #[IsGranted('ROLE_USER')]
    #[Route('/blog/{slug}/comment/{id}/delete', name: 'app_article_deleteComment', methods: ['DELETE'], requirements: ['slug' => '[a-z0-9\-]+', 'id' => '\d+'])]
    public function deleteComment(string $slug, int $id, Request $request, ArticleRepository $articleRepository, EntityManagerInterface $entityManager, Security $security): JsonResponse
    {

        // Récupère le jeton CSRF depuis les en-têtes
        $csrfToken = $request->headers->get('X-CSRF-TOKEN');

        // Vérifier la validité du jeton CSRF
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken('', $csrfToken))) {
            return new JsonResponse(['error' => 'Jeton CSRF invalide.'], 403);
        }
        
        // Récupère l'article associé au slug
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if (!$article) {
            return new JsonResponse(['success' => false, 'error' => 'Article non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        // Récupère l'utilisateur actuel
        $user = $security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur non authentifié'], Response::HTTP_UNAUTHORIZED);
        }
    
        // Recherche le commentaire à supprimer
        $comment = $entityManager->getRepository(Comment::class)->find($id);
        if (!$comment) {
            return new JsonResponse(['success' => false, 'error' => 'Commentaire non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        // Vérifie si l'utilisateur est autorisé à supprimer le commentaire
        if ($user !== $comment->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Accès refusé');
        }
    
        // Supprime le commentaire
        $entityManager->remove($comment);
        $entityManager->flush();
    
        // Retourne une réponse indiquant le succès de la suppression
        return new JsonResponse(['success' => true]);
    }

}


