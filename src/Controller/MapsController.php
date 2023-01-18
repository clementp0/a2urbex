<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LocationRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;

class MapsController extends AbstractController
{
    public function __construct(LocationRepository $locationRepository) {
        $this->locationRepository = $locationRepository;
    }

    #[Route('/maps', name: 'app_maps')]
    public function index(): Response {
        $locations = $this->locationRepository->findByIdFav(1);
        $serializer = $this->container->get('serializer');
        $locations = $serializer->serialize($locations, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['favorites', 'country', 'typeOptions', 'locations', '__initializer__', '__cloner__', '__isInitialized__', 'description'],
        ]);

        return $this->render('maps/index.html.twig', [
            'maps_api_key' => $_ENV['MAPS_API_KEY'],
            'locations' => $locations
        ]);
    }
}
