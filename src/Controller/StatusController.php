<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Location;
use App\Entity\Country;
use App\Entity\Category;
use App\Entity\Source;
use App\Repository\LocationRepository;
use App\Repository\MessageRepository;
use App\Service\DataService;
use App\Repository\ConfigRepository;
use Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\WebsocketService;

class StatusController extends AbstractController
{
    public function __construct(
        private DataService $dataService,
        private ConfigRepository $configRepository,
        private EntryFilesTwigExtension $entryFilesTwigExtension,
        private WebsocketService $websocketService
    ) {}

    #[Route('/status', name: 'app_status')]
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $repoLocation = $em->getRepository(Location::class);
        $repoCountry = $em->getRepository(Country::class);
        $repoCategory = $em->getRepository(Category::class);
        $repoSource = $em->getRepository(Source::class);
        $repoUser = $em->getRepository(User::class);

        $user_count = $repoUser->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $location_count = $repoLocation->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $pending_count = $repoLocation->createQueryBuilder('a')->select('count(a.id)')->where('a.pending = 1')->getQuery()->getSingleScalarResult();
        $country_count = $repoCountry->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $category_count = $repoCategory->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $source_count = $repoSource->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $ai_wainting_count = $repoLocation->createQueryBuilder('a')->select('count(a.id)')->where('a.image IS NULL')->getQuery()->getSingleScalarResult();
        $ai_count = $repoLocation->createQueryBuilder('a')->where('a.ai = true')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $wikimapia_finished_count = $this->getWikimapiaCount(0);
        $wikimapia_pending_count = $this->getWikimapiaCount(1);
        $pinterest_count = $repoLocation->createQueryBuilder('a')->where('a.source = :p')->setParameter('p','pinterest')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $kml_count = $repoLocation->createQueryBuilder('a')->where('a.source IS NOT NULL')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $user_count = $repoLocation->createQueryBuilder('a')->where('a.user IS NOT NULL')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $castle = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','1')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $hostel = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','2')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $cinema = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','3')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $train = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','4')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $hospital = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','5')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $house = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','6')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $factory = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','7')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $building = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','8')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $restaurant = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','9')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $military = $repoLocation->createQueryBuilder('a')->where('a.category = :p')->setParameter('p','10')->select('count(a.id)')->getQuery()->getSingleScalarResult();
        $undefined = $repoLocation->createQueryBuilder('a')->where('a.category IS NULL')->select('count(a.id)')->getQuery()->getSingleScalarResult();


        //AI Generation
        $ai_port = $_ENV['STABLE_PORT'];
        $url = 'http://127.0.0.1:7860/';
        $headers = @get_headers($url);
        $ai_status = $headers && strpos($headers[0], '200') !== false;

        //Return data 
        return $this->render('/status/index.html.twig', [
            'location_count' => $location_count,
            'pending_count' => $pending_count,
            'country_count' => $country_count,
            'category_count' => $category_count,
            'source_count' => $source_count,
            'ai_wainting_count' => $ai_wainting_count,
            'ai_count' => $ai_count,
            'user_count' => $user_count,
            'wikimapia_finished_count' => $wikimapia_finished_count,
            'wikimapia_pending_count' => $wikimapia_pending_count,

            'ai_port' => $ai_port,
            'ai_status' => $ai_status,

            'current_time' => date("d/m/Y H:i", time()),
            'sources' => $repoSource->findAll(),
            'websocket' => $_ENV["WEBSOCKET_URL"],                  

            'pinterest' => $this->configRepository->get('pinterest'),
            'wikimapia' => $this->configRepository->get('wikimapia'),
            'wikimapia_zoom' => (int)$_ENV['WIKIMAPIA_ZOOM'],

            'pinterest_count' => $pinterest_count,
            'globalmap_count' => $wikimapia_finished_count,
            'kml_count' =>  $kml_count - $pinterest_count - $wikimapia_finished_count,
            'user_count' => $user_count,

            'castle' => $castle,
            'hostel' => $hostel,
            'cinema' => $cinema,
            'train' => $train,
            'hospital' => $hospital ,
            'house' => $house,
            'factory' =>  $factory,
            'building' => $building,
            'restaurant' => $restaurant,
            'military' => $military,
            'undefined' => $undefined
        ]);
    }

    private function getWikimapiaCount($pending = 0) {
        $em = $this->getDoctrine()->getManager();
        $repoLocation = $em->getRepository(Location::class);
        
        return $repoLocation->createQueryBuilder('a')->select('count(a.id)')
            ->andWhere('a.source = :s')->setParameter('s', 'wikimapia')
            ->andWhere('a.pending = :p')->setParameter('p', $pending)
            ->getQuery()->getSingleScalarResult();
    }



    //Basic config
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<i class="fas fa-globe-europe"></i> a2urbex ')
            ->setFaviconPath('favicon.ico');
    }

}