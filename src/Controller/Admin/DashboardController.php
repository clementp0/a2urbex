<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\ResumeEntity;
use App\Entity\Intro;
use App\Entity\Archive;
use App\Entity\ResumeDetails;
use App\Entity\Socials;

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
            ->setTitle('Resume Data');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Intro', 'fas fa-home', Intro::class);
        yield MenuItem::subMenu('Resume', 'fa fa-list')->setSubItems([
                MenuItem::linkToCrud('Resume Details', 'fas fa-box', ResumeDetails::class),
                MenuItem::linkToCrud('Resume Entry', 'fas fa-box', ResumeEntity::class),
                MenuItem::linkToCrud('Lang', 'fas fa-list', Category::class),]);
        yield MenuItem::linkToCrud('Socials', 'fas fa-share-square', Socials::class);
        yield MenuItem::linkToCrud('Archive', 'fas fa-box', Archive::class);
        yield MenuItem::subMenu('Parametres', 'fa fa-gear')->setSubItems([
                MenuItem::linkToUrl('Deconnexion', 'fas fa-file', 'logout'),
                MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class),]);
       
    }
}
