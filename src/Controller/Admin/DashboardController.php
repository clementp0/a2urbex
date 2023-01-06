<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Locations;


class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Urbex');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::linkToCrud('Locations', 'fa fa-home', Locations::class);

        yield MenuItem::subMenu('Parametres', 'fa fa-gear')->setSubItems([
                MenuItem::linkToUrl('Mon Compte', 'fas fa-user', 'compte'),
                MenuItem::linkToUrl('Deconnexion', 'fas fa-file', 'deconnexion'),
                MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class),]);
                
    }
}
