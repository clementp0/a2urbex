<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Service\FileUploader;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Location;
use App\Entity\Country;
use App\Entity\Type;
use App\Entity\TypeOption;
use App\Entity\Upload;
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

        //Last updated
        $update_date = './assets/update.json';
        $strJsonFileContentsu = file_get_contents($update_date);
        $array_updated = json_decode($strJsonFileContentsu, true);
        $last_updated = $array_updated["last_updated"];
        //Output
        $board = $array["board"];
        $finished = $array["finished"];
        $error = $array["error"];
        $total = $array["total"];
        $newpins = $array["newpins"];
        $token = $array["token"];
        $updated = $array_updated["last_updated"];

        //Upload list
        $repoUpload = $em->getRepository(Upload::class);
        $uploads = $repoUpload->findAll();
        $uploads_count = $repoUpload->createQueryBuilder('a')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();

        //AI Generation
        $to_be_generated = $repoLocation->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.image IS NULL')
            ->getQuery()
            ->getSingleScalarResult();
        $ai = $repoLocation->createQueryBuilder('a')
            ->where('a.ai = true')
            ->select('count(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $port = $_ENV['STABLE_PORT'];

        $url = 'http://127.0.0.1:7860/';
        $headers = @get_headers($url);
        if ($headers && strpos($headers[0], '200') !== false) {
            $stable_status = "<p class='online'>Running on Port : " . $port . "</p>";
            $stable_status_current = "on";
        } else {
            $stable_status = "<p class='offline'>Currently offline..</p>";
            $stable_status_current = "off";
        }

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
            'token' => $token,
            'last_updated' => $updated,
            'uploads' => $uploads,
            'uploads_count' => $uploads_count,
            'to_be_generated' => $to_be_generated,
            'port' => $port,
            'stable_status' => $stable_status,
            'stable_status_current' => $stable_status_current,
            'ai' => $ai,
        ]);
    }



    //Basic config
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<i class="fas fa-globe-europe"></i> a2urbex ')
            ->setFaviconPath('favicon.ico');
    }


    //Side Menu Config
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Home', 'fas fa-home', 'locations');

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-columns');

        yield MenuItem::subMenu('Locations', 'fas fa-map')->setSubItems([
            MenuItem::linkToCrud('Pins', 'fas fa-map-marker', Location::class),
            MenuItem::linkToCrud('Country', 'fas fa-globe-europe', Country::class),
            MenuItem::linkToCrud('Type', 'fas fa-clinic-medical', Type::class),
            MenuItem::linkToCrud('Type Options', 'fas fa-wrench', TypeOption::class)
        ]);

        yield MenuItem::subMenu('Upload', 'fa fa-upload')->setSubItems([
            MenuItem::linkToUrl('Import File', 'fa fa-upload', 'upload'),
            MenuItem::linkToCrud('Upload', 'fas fa-file', Upload::class),
        ]);

        yield MenuItem::subMenu('Settings', 'fa fa-gear')->setSubItems([
            MenuItem::linkToUrl('Add User', 'fas fa-file', 'register'),
            MenuItem::linkToUrl('Password', 'fas fa-file', 'compte/password'),
            MenuItem::linkToCrud('Users', 'fas fa-users', User::class),
        ]);

    }
}