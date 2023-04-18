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
use Symfony\Component\Security\Core\Security;
use App\Class\Search;
use App\Form\SearchType;

class MapController extends AppController
{
    public function __construct(LocationRepository $locationRepository, private HashidsServiceInterface $hashidsService, Security $security) {
        $this->locationRepository = $locationRepository;
        $this->security = $security;
    }

    private function default($locations) {
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
        // todo filter out location name if not connected

        $hashKey = $_ENV["HASH_KEY"];
        foreach($locations as $loc) {
            $loc['loc']->lid = $this->hashidsService->encode($loc['loc']->getId().$hashKey);
        }

        $serializer = $this->container->get('serializer');
        $locations = $serializer->serialize($locations, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => $ignoreList,
        ]);


        return $this->render('map/index.html.twig', [
            'maps_api_key' => $_ENV['MAPS_API_KEY'],
            'pin_location_path' => $_ENV['PIN_LOCATION_PATH'],
            'locations' => $locations
        ]);
    }

    #[Route('/map/list/{key}', name: 'app_map_favorite')]
    public function fav(Request $request): Response {
        $hashKey = $_ENV["HASH_KEY"];
        $mapKey = $this->hashidsService->decode($request->get('key'));
        $mapId = str_replace($hashKey,'',$mapKey);
        $locations = $this->locationRepository->findByIdFav($mapId[0]);
        return $this->default($locations);
    }

    #[Route('/map/filter', name: 'app_map_filter')]
    public function filter(Request $request): Response {
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $locations = $this->locationRepository->findWithSearch($search);
        } else {
            $locations = $this->locationRepository->findByAll();
        }

        return $this->default($locations);
    }
}
