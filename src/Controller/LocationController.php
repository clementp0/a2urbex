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
use App\Service\LocationService;
use App\Repository\FriendRepository;

class LocationController extends AppController
{

    public function __construct(private Security $security, private HashidsServiceInterface $hashidsService)
    {
    }
    
    #[Route('/locations', name: 'app_location_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request, 
        LocationRepository $locationRepository, 
        FavoriteRepository $favoriteRepository, 
        PaginatorInterface $paginator, 
        UserOnlineService $userOnlineService,
        Security $security,
        FriendRepository $friendRepository
    ): Response
    {
        
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        
        $form->handleRequest($request);
        
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles(), true) || in_array('ROLE_SUPERUSER', $this->getUser()->getRoles(), true)) {
            if ($form->isSubmitted() && $form->isValid()){
                $locations = $locationRepository->findWithSearch($search);
            } else {
                $locations = $locationRepository->findByAll();
            }
        }else{
            $user = $security->getUser();
            $users = [$user->getId()];

            $f = $friendRepository->findFriendForSearch($user->getId());
            if($f) {
                foreach($f as $item) {
                    $users[] = $item['id'];
                }
            }

            if ($form->isSubmitted() && $form->isValid()){
                $locations = $locationRepository->findWithSearchAndUsers($search, $users);
            } else {
                $locations = $locationRepository->findByUsers($users);
            }
            
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

        return $this->render('location/index.html.twig', [
            'websocket' => $_ENV["WEBSOCKET_URL"],
            'user' => $this->getUser(),
            'user_role' => $this->getUser()->getRoles(),
            'user_id' => $this->getUser()->getId(),
            'locations' => $locationData,
            'hashkey' => $_ENV["HASH_KEY"],
            'form' => $form->createView(),
            'total_result' => $totalResults,
            'onlineUsers' => $onlineUsers,
        ]);
    }

    #[Route('/new', name: 'new_location')]
    public function newLocation(Request $request, LocationRepository $locationRepository, PaginatorInterface $paginator, LocationService $locationService): Response
    {
        $location = new Location();
    
        $form = $this->createForm(NewLocationType::class, $location);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $location->setUser($this->getUser());
            $locationService->addCountry($location);

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

    #[Route('locations/{key}/edit', name: 'app_location_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LocationRepository $locationRepository, PaginatorInterface $paginator, LocationService $locationService): Response
    {
        $hashKey = $_ENV["HASH_KEY"];
        $locationKey = $this->hashidsService->decode($request->get('key'));
        $locationId = str_replace($hashKey,'',$locationKey);
        $locationData = $locationRepository->findById(is_array($locationId) ? $locationId[0] : $locationId);
        $location = $locationData["loc"];

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if($this->isOwned($location) && $form->isSubmitted() && $form->isValid()) {
            $locationService->addCountry($location);
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
    public function delete_location(Request $request, LocationRepository $locationRepository): Response
    {
        $hashKey = $_ENV["HASH_KEY"];
        $locationKey = $this->hashidsService->decode($request->get('key'));
        $locationId = str_replace($hashKey,'',$locationKey);
        $locationData = $locationRepository->findById(is_array($locationId) ? $locationId[0] : $locationId);
        $location = $locationData["loc"];

        if($this->isOwned($location) && $this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
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

    private function isOwned($location) {
        $user = $this->security->getUser();        
        if(!$user) return false;
        elseif($user->hasRole('ROLE_ADMIN')) return true;
        elseif($location->getUser() && $user->getId() === $location->getUser()->getId()) return true;
    }
}
