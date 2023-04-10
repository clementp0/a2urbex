<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\Country;
use App\Form\NewLocationType;
use App\Repository\LocationRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\HashidsBundle\Service\HashidsService;
use Knp\Component\Pager\PaginatorInterface;

class NewLocationController extends AbstractController
{

    public function __construct(private HashidsServiceInterface $hashidsService)
    {
    }

    #[Route('/new', name: 'new_location')]
    public function newLocation(Request $request, LocationRepository $locationRepository, PaginatorInterface $paginator): Response
    {
        $location = new Location();
    
        $form = $this->createForm(NewLocationType::class, $location);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $location->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($location);
            $entityManager->flush();
    
            return $this->redirectToRoute('new_location', ['id' => $location->getId()]);
        }

        $locations =$locationRepository->findByUser();

        $totalResults = 0;
        $totalResults = count($locations);

        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            6
        );  

        return $this->render('location/new.html.twig', [
            'locations' => $locationData,
            'hashkey' => $_ENV["HASH_KEY"],
            'form' => $form->createView(),
            'total_result' => $totalResults,
        ]);
    }

    public function create(Request $request): Response
    {
        $location = new Location();

        $form = $this->createForm(NewLocationType::class, $location, [
            'user' => $this->security->getUser(),
        ]);
    }
    
}
