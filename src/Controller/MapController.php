<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LocationRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Class\Search;
use App\Form\SearchType;
use App\Service\LocationService;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\HashService;

class MapController extends AppController
{
    public function __construct(
        private LocationRepository $locationRepository,
        private HashService $hashService,
        private LocationService $locationService,
        private PaginatorInterface $paginator
    ) {}
    
    #[Route('/map/list/{key}', name: 'app_map_favorite')]
    public function fav(): Response {
        return $this->default('key');
    }

    #[Route('/map/filter', name: 'app_map_filter')]
    public function filter(Request $request): Response {
        return $this->default('filter', $request);
    }

    private function default($type = 'key', $request = null) {
        $twig = [
            'maps_api_key' => $_ENV['MAPS_API_KEY'],
            'pin_location_path' => $_ENV['PIN_LOCATION_PATH'],
            'map_type' => $type,
        ];

        if($type === 'filter') {
            $search = new Search();
            $form = $this->createForm(SearchType::class, $search);
            $form->handleRequest($request);

            $qb = $this->locationService->findSearch($search, $form->isSubmitted() && $form->isValid(), true);
            $pag = $this->paginator->paginate($qb, 1, 1);

            $twig['search_form'] = $form->createView();
            $twig['total_result'] = $pag->getTotalItemCount();
        }

        return $this->render('map/index.html.twig', $twig);
    }

    #[Route('/map/async/', name: 'app_map_async')]
    public function asyncMap(Request $request) {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        $locations = $this->locationService->findSearch($search, $form->isSubmitted() && $form->isValid());
        return $this->defaultLocations($locations);
    }

    #[Route('/map/async/{key}/', name: 'app_map_async_key')]
    public function asyncMapKey($key) {
        $favId = $this->hashService->decodeFav($key);
        $locations = $this->locationRepository->findByIdFav($favId);
        return $this->defaultLocations($locations);
    }

    private function defaultLocations($locations) {
        $ignoreList = [
            'id',
            'favorites', 
            'country', 
            'categoryOptions', 
            'locations', 
            'description', 
            'url', 
            'pid',
            '__initializer__', 
            '__cloner__', 
            '__isInitialized__',
            'user'
        ];

        foreach($locations as $loc) {
            $loc['loc']->lid = $this->hashService->encodeLoc($loc['loc']->getId());
        }

        $serializer = $this->container->get('serializer');
        $locations = $serializer->serialize($locations, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoreList,
        ]);

        return new Response($locations);
    }

}
