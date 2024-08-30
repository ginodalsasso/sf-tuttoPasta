<?php
namespace App\Controller\Admin;

use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Quote;
use App\Entity\DayOff;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\Service;
use App\Entity\Category;
use App\Entity\ProjectImg;
use App\Entity\Appointment;
use App\Entity\Administration;
use App\Repository\AppointmentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    private $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Récupère les 3 derniers rendez-vous
        $latestAppointments = $this->appointmentRepository->findLatestAppointments();

        // Passer les rendez-vous et les éléments du menu à la vue du tableau de bord
        return $this->render('admin/dashboard.html.twig', [
            'appointments' => $latestAppointments,
        ]);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('css/easyAdmin.css');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Studio TuttoPasta')
            ->setFaviconPath('img/logo_white.svg');       // Définit le chemin vers l'image du logo
        }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Portfolio');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkToUrl('Home', 'fas fa-home', $this->generateUrl('app_home'));
        yield MenuItem::linkToCrud('Catégories', 'fas fa-th-list', Category::class);
        yield MenuItem::linkToCrud('Administration', 'fas fa-cogs', Administration::class);
        yield MenuItem::linkToCrud('Projets', 'fas fa-project-diagram', Project::class);
        yield MenuItem::linkToCrud('Images projets', 'fas fa-images', ProjectImg::class);
        yield MenuItem::linkToCrud('Services', 'fas fa-concierge-bell', Service::class);

        yield MenuItem::section('Blog');
        yield MenuItem::linkToCrud('Articles', 'fas fa-newspaper', Article::class);
        yield MenuItem::linkToCrud('Tag', 'fas fa-newspaper', Tag::class);
        yield MenuItem::linkToCrud('Commentaires', 'fas fa-comments', Comment::class);
            
        yield MenuItem::section('Administration');
        yield MenuItem::linkToCrud('Rendez-vous', 'fas fa-calendar-check', Appointment::class);
        yield MenuItem::linkToCrud('Jours de congés', 'fas fa-calendar-alt', DayOff::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Quotes', 'fas fa-users', Quote::class);
        yield MenuItem::linkToUrl('Gestion des devis', 'fas fa-regular fa-money-bills', $this->generateUrl('app_quotes'));
    }
}
