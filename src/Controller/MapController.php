<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LocationRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use App\Class\Search;
use App\Form\SearchType;

class MapController extends AbstractController
{
    public function __construct(LocationRepository $locationRepository) {
        $this->locationRepository = $locationRepository;
    }

    private function default($locations) {
        $serializer = $this->container->get('serializer');
        $locations = $serializer->serialize($locations, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['favorites', 'country', 'typeOptions', 'locations', '__initializer__', '__cloner__', '__isInitialized__', 'description'],
        ]);

        return $this->render('map/index.html.twig', [
            'maps_api_key' => $_ENV['MAPS_API_KEY'],
            'locations' => $locations
        ]);
    }

    #[Route('/map/favorite/{id}', name: 'app_map_favorite')]
    public function fav($id): Response {
        $locations = $this->locationRepository->findByIdFav($id);
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
