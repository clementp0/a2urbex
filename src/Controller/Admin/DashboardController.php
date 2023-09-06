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
use App\Entity\Category;
use App\Entity\CategoryOption;
use App\Entity\Source;
use App\Repository\LocationRepository;
use App\Repository\MessageRepository;
use App\Service\DataService;
use App\Repository\ConfigRepository;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use App\Service\WebsocketService;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private DataService $dataService,
        private ConfigRepository $configRepository,
        private EntryFilesTwigExtension $entryFilesTwigExtension,
        private WebsocketService $websocketService
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repoLocation = $em->getRepository(Location::class);
        $repoCountry = $em->getRepository(Country::class);
        $repoCategory = $em->getRepository(Category::class);
        $repoSource = $em->getRepository(Source::class);

        $location_count = $repoLocation->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $pending_count = $repoLocation->createQueryBuilder('a')->select('count(a.id)')->where('a.pending = 1')->getQuery()->getSingleScalarResult();
        $country_count = $repoCountry->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $category_count = $repoCategory->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $source_count = $repoSource->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $ai_waiting_count = $repoLocation->createQueryBuilder('a')->select('count(a.id)')->where('a.image IS NULL')->getQuery()->getSingleScalarResult();
        $ai_count = $repoLocation->createQueryBuilder('a')->where('a.ai = true')->select('count(a.id)')->getQuery()->getSingleScalarResult();

        //AI Generation
        $ai_port = $_ENV['STABLE_PORT'];
        $url = 'http://127.0.0.1:7860/';
        $headers = @get_headers($url);
        $ai_status = $headers && strpos($headers[0], '200') !== false;

        //Return data 
        return $this->render('admin/index.html.twig', [
            'location_count' => $location_count,
            'pending_count' => $pending_count,
            'country_count' => $country_count,
            'category_count' => $category_count,
            'source_count' => $source_count,
            'ai_waiting_count' => $ai_waiting_count,
            'ai_count' => $ai_count,

            'ai_port' => $ai_port,
            'ai_status' => $ai_status,

            'current_time' => date("d/m/Y H:i", time()),
            'sources' => $repoSource->findAll(),
            'websocket' => $_ENV["WEBSOCKET_URL"],                  

            'pinterest' => $this->configRepository->get('pinterest'),
            'wikimapia' => $this->configRepository->get('wikimapia'),
            'wikimapia_zoom' => (int)$_ENV['WIKIMAPIA_ZOOM']
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
            MenuItem::linkToCrud('Category', 'fas fa-clinic-medical', Category::class),
            MenuItem::linkToCrud('Category Options', 'fas fa-wrench', CategoryOption::class)
        ]);

        yield MenuItem::subMenu('Source', 'fa fa-upload')->setSubItems([
            MenuItem::linkToRoute('Import File', 'fa fa-upload', 'source_upload'),
            MenuItem::linkToCrud('Source', 'fas fa-file', Source::class),
        ]);

        yield MenuItem::subMenu('Settings', 'fa fa-gear')->setSubItems([
            MenuItem::linkToUrl('Add User', 'fas fa-file', 'register'),
            MenuItem::linkToUrl('Password', 'fas fa-file', 'password'),
            MenuItem::linkToCrud('Users', 'fas fa-users', User::class),
        ]);

    }

    // configure assets
    public function configureAssets(): Assets
    {
        $scripts = $this->entryFilesTwigExtension->renderWebpackScriptTags('admin-script', null, 'adminConfig');
        $styles = $this->entryFilesTwigExtension->renderWebpackLinkTags('admin-style', null, 'adminConfig');

        return Assets::new()
            ->addHtmlContentToHead($scripts)
            ->addHtmlContentToHead($styles)
        ;
    }

    // Download Database
    #[Route('/download-database', name: 'download_database')]
    public function downloadDatabase() {
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

    #[Route('/private/build/{file}', name: 'private/build')]
    public function publicAdmin($rootDirectory, $file) {
        $path = $rootDirectory.'private/build/'.$file;
        if(file_exists($path)) {
            $contentType = mime_content_type($path);
            $content = $this->dataService->getFile($path);
            
            $response = new Response($content);

            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'css') {
                $response->headers->set('Content-Type', 'text/css');
            } else {
                $response->headers->set('Content-Type', $contentType);
            }
            
            return $response;
        } else {
           return $this->redirect('/not-found');
        }
    }
}