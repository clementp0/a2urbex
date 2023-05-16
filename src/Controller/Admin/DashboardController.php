<?php

namespace App\Controller\Admin;


use Symfony\Component\Process\Process;
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
use App\Repository\MessageRepository;
use App\Service\MessageService;
use App\Service\DataService;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private DataService $dataService) {}

    #[Route('/admin', name: 'admin')]
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
        $array = $this->dataService->getJson($this->getParameter('data_directory').'export.json');
        $last_fetched = $array["last_fetched"];

        //Last updated
        $array_updated = $this->dataService->getJson($this->getParameter('data_directory').'update.json');
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
            'websocket' => $_ENV["WEBSOCKET_URL"],
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
            'remaining' => $this->dataService->getFile($this->getParameter('data_directory').'count.txt')
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
            MenuItem::linkToUrl('Password', 'fas fa-file', 'password'),
            MenuItem::linkToCrud('Users', 'fas fa-users', User::class),
        ]);

    }


    //Clear Chat
    public function clearChat(MessageRepository $messageRepository, MessageService $messageService)
    {
        $messageRepository->clearGlobalChat();
        $messageService->saveMessage('WELCOME TO A2URBEX');
        return $this->redirect('admin');
    }


    // Download Database

    public function downloadDatabase()
    {
        $url = $_ENV['DATABASE_URL'];

        $parsedUrl = parse_url($url);

        $databaseUser = isset($parsedUrl['user']) ? $parsedUrl['user'] : null;
        $databasePassword = isset($parsedUrl['pass']) ? $parsedUrl['pass'] : null;
        $databaseName = isset($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : null;
        $dumpFile = 'a2urbex_dump.sql';

        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            $databaseUser,
            $databasePassword,
            $databaseName,
            $dumpFile
        );
        exec($command);

        $response = new Response(file_get_contents($dumpFile));
        $response->headers->set('Content-Type', 'application/sql');
        $response->headers->set('Content-Disposition', 'attachment; filename="a2urbex_dump.sql"');

        unlink($dumpFile);

        return $response;

    }
}