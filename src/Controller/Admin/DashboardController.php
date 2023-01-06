<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Location;
use App\Entity\Type;
use App\Entity\Country;
use App\Repository\LocationRepository;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */

    public function index(): Response
    {

        $em = $this->getDoctrine()->getManager();
        
    //Location_count
        $repoLocation = $em->getRepository(Location::class);
        $pins_count = $repoLocation->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

    //Country_count
        $repoCountry = $em->getRepository(Country::class);
        $country_count = $repoCountry->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

    //Type_count
        $repoType = $em->getRepository(Type::class);
        $type_count = $repoType->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

    //Curent time
        $current_time = date("d/m/Y H:i", time());

    //Last fetched
        $export_date = './assets/export.json';
        $strJsonFileContents = file_get_contents($export_date);
        $array = json_decode($strJsonFileContents, true);
        $last_fetched = $array["last_fetched"];
    
    //Output
        $board = $array["board"];
        $finished = $array["finished"];
        $error = $array["error"];
        $total = $array["total"];
        $newpins = $array["newpins"];
        $token = $array["token"];
    //Return data 

        return $this->render('admin/index.html.twig', [
            'pins' => $pins_count,
            'country' => $country_count,
            'type' => $type_count, 
            'current_time' => $current_time,
            'last_fetched' => $last_fetched,
            'board' => $board,
            'finished' => $finished,
            'error' => $error,
            'total' => $total,
            'newpins' => $newpins,
            'token' => $token
        ]);
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<i class="fas fa-globe-europe"></i> _Urbex ');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::subMenu('Locations', 'fas fa-map')->setSubItems([
                MenuItem::linkToCrud('Pins', 'fas fa-map-marker', Location::class),
                MenuItem::linkToCrud('Country', 'fas fa-globe-europe', Country::class),
                MenuItem::linkToCrud('Type', 'fas fa-clinic-medical', Type::class)
        ]);

        yield MenuItem::subMenu('Settings', 'fa fa-gear')->setSubItems([
                MenuItem::linkToUrl('Account', 'fas fa-user', 'compte'),
                MenuItem::linkToUrl('Log Out', 'fas fa-file', 'deconnexion'),
                MenuItem::linkToCrud('Users', 'fas fa-users', User::class),]);
                
    }
}
