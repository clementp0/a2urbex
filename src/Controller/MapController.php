<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LocationRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\HashidsBundle\Service\HashidsService;
use Symfony\Component\HttpFoundation\Request;
use App\Class\Search;
use App\Form\SearchType;
use App\Service\LocationService;

class MapController extends AppController
{
    public function __construct(
        private LocationRepository $locationRepository,
        private HashidsServiceInterface $hashidsService
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

            $twig['search_form'] = $form->createView();
            $twig['total_result'] = 1;
        }

        return $this->render('map/index.html.twig', $twig);
    }

    #[Route('/map/async/', name: 'app_map_async')]
    public function asyncMap(Request $request, LocationService $locationService) {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        $locations = $locationService->findSearch($search, $form->isSubmitted() && $form->isValid());
        return $this->defaultLocations($locations);
    }

    #[Route('/map/async/{key}/', name: 'app_map_async_key')]
    public function asyncMapKey($key) {
        $hashKey = $_ENV["HASH_KEY"];
        $mapKey = $this->hashidsService->decode($key);
        $mapId = str_replace($hashKey,'',$mapKey);

        $locations = $this->locationRepository->findByIdFav($mapId[0]);
        return $this->defaultLocations($locations);
    }

    private function defaultLocations($locations) {
        $ignoreList = [
            'id',
            'favorites', 
            'country', 
            'typeOptions', 
            'locations', 
            'description', 
            'url', 
            'pid',
            '__initializer__', 
            '__cloner__', 
            '__isInitialized__',
            'user'
        ];

        $hashKey = $_ENV["HASH_KEY"];
        foreach($locations as $loc) {
            $loc['loc']->lid = $this->hashidsService->encode($loc['loc']->getId().$hashKey);
        }

        $serializer = $this->container->get('serializer');
        $locations = $serializer->serialize($locations, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoreList,
        ]);

        return new Response($locations);
    }

}
