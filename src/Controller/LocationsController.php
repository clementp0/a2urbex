<?php

namespace App\Controller;

use App\Entity\Locations;
use App\Form\LocationsType;
use App\Repository\LocationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/locations')]
class LocationsController extends AbstractController
{
    #[Route('/', name: 'app_locations_index', methods: ['GET'])]
    public function index(LocationsRepository $locationsRepository): Response
    {
        return $this->render('locations/index.html.twig', [
            'locations' => $locationsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_locations_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LocationsRepository $locationsRepository): Response
    {
        $location = new Locations();
        $form = $this->createForm(LocationsType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $locationsRepository->add($location);
            return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('locations/new.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_locations_show', methods: ['GET'])]
    public function show(Locations $location): Response
    {
        return $this->render('locations/show.html.twig', [
            'location' => $location,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_locations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Locations $location, LocationsRepository $locationsRepository): Response
    {
        $form = $this->createForm(LocationsType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $locationsRepository->add($location);
            return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('locations/edit.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_locations_delete', methods: ['POST'])]
    public function delete(Request $request, Locations $location, LocationsRepository $locationsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
            $locationsRepository->remove($location);
        }

        return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
    }
}
