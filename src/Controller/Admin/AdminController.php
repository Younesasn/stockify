<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Upload;
use App\Entity\Category;
use App\Entity\Extension;
use App\Entity\Subscription;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class AdminController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UploadCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Stockify');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Uploads', 'fa fa-file', Upload::class);
        yield MenuItem::linkToRoute('Dashboard', 'fa fa-home', 'dashboard');
        yield MenuItem::linkToCrud('Category', 'fa fa-list', Category::class);
        yield MenuItem::linkToCrud('Extension', 'fa fa-ellipsis', Extension::class);
        yield MenuItem::linkToCrud('Subscription', 'fa fa-dollar-sign', Subscription::class);
    }
}
