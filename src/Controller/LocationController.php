<?php

namespace App\Controller;

use App\Entity\Location;
use App\Class\Search;
use App\Form\LocationType;
use App\Form\SearchType;
use App\Repository\LocationRepository;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/locations')]
class LocationController extends AbstractController
{
    #[Route('/', name: 'app_location_index', methods: ['GET', 'POST'])]
    public function index(Request $request, LocationRepository $locationRepository, PaginatorInterface $paginator, Security $security): Response
    {
        $locations = $locationRepository->findByAllJoinUser($security->getUser()->getId());

        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $locations = $locationRepository->findWithSearch($search, $security->getUser()->getId());
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
            'form' => $form->createView(),
            'total_result' => $totalResults,
        ]);
    }

    #[Route('/new', name: 'app_location_new', methods: ['GET', 'POST'])]
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

    #[Route('how/{id}', name: 'app_location_show', methods: ['GET'])]
    public function show(Location $location, LocationRepository $locationRepository, Security $security): Response
    {
        $location = $locationRepository->findByIdJoinUser($location->getId(), $security->getUser()->getId());

        return $this->render('location/show.html.twig', [
            'item' => $location,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_location_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Location $location, LocationRepository $locationRepository): Response
    {
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $locationRepository->add($location);
            return $this->redirectToRoute('app_location_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('location/edit.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_location_delete', methods: ['POST'])]
    public function delete(Request $request, Location $location, LocationRepository $locationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
            $locationRepository->remove($location);
        }

        return $this->redirectToRoute('app_location_index', [], Response::HTTP_SEE_OTHER);
    }
}
