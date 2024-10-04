<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Producer;
use App\Entity\ProductionUnit;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    #[Route('', name: 'dashboard')]
    public function index(): Response
    {
        return $this->redirect(
            $this->adminUrlGenerator->unsetAll()->setController(ProducerCrudController::class)->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Electricity Prod');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Unités de production');
        yield MenuItem::linkToCrud('Producteurs', 'fas fa-list', Producer::class);
        yield MenuItem::linkToCrud('Unité de production', 'fas fa-list', ProductionUnit::class);

        yield MenuItem::section('Production');

    }
}
