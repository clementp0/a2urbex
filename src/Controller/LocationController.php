<?php

namespace App\Controller;

use App\Entity\Location;
use App\Class\Search;
use App\Form\LocationType;
use App\Form\NewLocationType;
use App\Form\SearchType;
use App\Repository\LocationRepository;
use App\Repository\FavoriteRepository;
use App\Repository\UploadRepository;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\HashidsBundle\Service\HashidsService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use App\Service\UserOnlineService;
use Symfony\Component\Security\Core\Security;

class LocationController extends AppController
{

    public function __construct(private HashidsServiceInterface $hashidsService)
    {
    }
    
    #[Route('/locations', name: 'app_location_index', methods: ['GET', 'POST'])]
    public function index(Request $request, LocationRepository $locationRepository, FavoriteRepository $favoriteRepository, PaginatorInterface $paginator, UserOnlineService $userOnlineService): Response
    {
        
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()){
            $locations = $locationRepository->findWithSearch($search);
        } else {
            $locations = $locationRepository->findByAll();
        }

        $totalResults = 0;
        $totalResults = count($locations);

        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            50
        );  
        
        $user = $this->getUser();
        $userOnlineService->addUser($user);
        $onlineUsers = $userOnlineService->getOnlineUsers();
        // dd($onlineUsers);

        return $this->render('location/index.html.twig', [
            'locations' => $locationData,
            'hashkey' => $_ENV["HASH_KEY"],
            'form' => $form->createView(),
            'total_result' => $totalResults,
            'onlineUsers' => $onlineUsers,
        ]);
    }

    #[Route('locations/{key}/edit', name: 'app_location_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LocationRepository $locationRepository, PaginatorInterface $paginator): Response
    {
        $hashKey = $_ENV["HASH_KEY"];
        $locationKey = $this->hashidsService->decode($request->get('key'));
        $locationId = str_replace($hashKey,'',$locationKey);
        $locationData = $locationRepository->findById(is_array($locationId) ? $locationId[0] : $locationId);
        $location = $locationData["loc"];

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $locationRepository->add($location);
            return $this->redirectToRoute('new_location', [], Response::HTTP_SEE_OTHER);
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
            'location' => $location,
            'hashkey' => $_ENV["HASH_KEY"],
            'form' => $form->createView(),
            'total_result' => $totalResults,
        ]);
    }
    

    #[Route('/locations/{key}/delete', name: 'app_location_delete', methods: ['POST'])]
    public function delete_location(Request $request, LocationRepository $locationRepository, Security $security): Response
    {
        $hashKey = $_ENV["HASH_KEY"];
        $locationKey = $this->hashidsService->decode($request->get('key'));
        $locationId = str_replace($hashKey,'',$locationKey);
        $locationData = $locationRepository->findById(is_array($locationId) ? $locationId[0] : $locationId);
        $location = $locationData["loc"];

        $user = $security->getUser();        
        if(!$user) $deletable = false;
        elseif($user->hasRole('ROLE_ADMIN')) $deletable = true;
        elseif($location->getUser() && $user->getId() === $location->getUser()->getId()) $deletable = true;

        if($deletable && $this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
            $locationRepository->remove($location);
        }
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
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

    #[Route('delete/{source}', name: 'delete_location_source', methods: ['GET'])]
    public function delete(ManagerRegistry $doctrine, Request $request, LocationRepository $locationRepository,  UploadRepository $uploadRepository): Response
    {
        $publicDir = $this->getParameter('public_directory');
        $source = $request->get('source');

        if($source) {
            $removeSources = $locationRepository->findBySource($source);
            $entityManager = $doctrine->getManager();
            foreach ($removeSources as $removeSource) {
                $entityManager->remove($removeSource['loc']);

                $image = $removeSource['loc']->getImage();
                if(strlen($image) > 4 && file_exists($publicDir.$image)) {
                    unlink($publicDir.$image);
                }
            }

            $entityManager->flush();
        }
        return $this->redirect('/admin');
    }

    #[Route('delete/', name: 'delete_location_source_empty', methods: ['GET'])]
    public function deleteEmpty(){
        return $this->redirect('/admin');
    }
}
