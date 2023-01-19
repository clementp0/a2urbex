<?php

namespace App\Controller;

use App\Entity\Location;
use App\Class\Search;
use App\Form\LocationType;
use App\Form\SearchType;
use App\Repository\LocationRepository;
use App\Repository\FavoriteRepository;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\HashidsBundle\Service\HashidsService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{

    public function __construct(private HashidsServiceInterface $hashidsService)
    {
    }
    
    #[Route('/locations', name: 'app_location_index', methods: ['GET', 'POST'])]
    public function index(Request $request, LocationRepository $locationRepository, FavoriteRepository $favoriteRepository, PaginatorInterface $paginator): Response
    {
        $locations = $locationRepository->findByAll();

        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $locations = $locationRepository->findWithSearch($search);
        }

        $totalResults = 0;
        $totalResults = count($locations);

        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            50
        );  

        return $this->render('location/index.html.twig', [
            'locations' => $locationData,
            'hashkey' => $_ENV["HASH_KEY"],
            'form' => $form->createView(),
            'total_result' => $totalResults,
        ]);
    }

    #[Route('location/new', name: 'app_location_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LocationRepository $locationRepository): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $locationRepository->add($location);
            return $this->redirectToRoute('app_location_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('location/new.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('location/{key}', name: 'app_location_show', methods: ['GET'])]
    public function show(Request $request, LocationRepository $locationRepository): Response
    {
        $hashKey = $_ENV["HASH_KEY"];
        $locationKey = $this->hashidsService->decode($request->get('key'));
        $locationId = str_replace($hashKey,'',$locationKey);
        $location = $locationRepository->findById(is_array($locationId) ? $locationId[0] : $locationId);
        return $this->render('location/show.html.twig', [
            'item' => $location,
        ]);
    }
}