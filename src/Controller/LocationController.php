<?php

namespace App\Controller;

use App\Entity\Location;
use App\Class\Search;
use App\Form\LocationType;
use App\Form\SearchType;
use App\Repository\UploadRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use App\Service\UserOnlineService;
use Symfony\Component\Security\Core\Security;
use App\Repository\LocationRepository;
use App\Service\LocationService;
use App\Repository\FriendRepository;
use App\Service\WebsocketService;
use App\Service\HashService;

class LocationController extends AppController
{

    public function __construct(
        private Security $security,
        private HashService $hashService,
        private LocationService $locationService,
        private LocationRepository $locationRepository
    ) {}
    
    #[Route('/locations', name: 'app_location_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request, 
        PaginatorInterface $paginator, 
        UserOnlineService $userOnlineService,
        WebsocketService $websocketService
    ): Response
    {   
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        $locations = $this->locationService->findSearch($search, $form->isSubmitted() && $form->isValid(), true);
        
        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            50
        );
        $totalResults = $locationData->getTotalItemCount();
        
        $user = $this->getUser();
        $userOnlineService->addUser($user);
        $onlineUsers = $userOnlineService->getOnlineUsers();
        $onlineExplorers = $userOnlineService->getOnlineExplorers();

        return $this->render('location/index.html.twig', [
            'websocket' => $_ENV["WEBSOCKET_URL"],
            'websocket_token' => $websocketService->getToken($this->getUser()),
            'user' => $this->getUser(),
            'user_role' => $this->getUser()->getRoles(),
            'user_id' => $this->getUser()->getId(),
            'locations' => $locationData,
            'search_form' => $form->createView(),
            'total_result' => $totalResults,
            'onlineUsers' => $onlineUsers,
            'onlineExplorers' => $onlineExplorers,
        ]);
    }

    #[Route('/new', name: 'new_location')]
    public function newLocation(Request $request, PaginatorInterface $paginator): Response
    {
        $location = new Location();
    
        $form = $this->createForm(LocationType::class, $location, [
            'title' => 'Create Location'
        ]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $location->setUser($this->getUser());
            $this->locationService->addCountry($location);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($location);
            $entityManager->flush();
    
            return $this->redirectToRoute('new_location');
        }

        return $this->getForUserLocationPage($request, $paginator, $location, $form);
    }

    #[Route('location/{key}/edit', name: 'app_location_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PaginatorInterface $paginator, $key): Response
    {
        $location = $this->getLocationFromKey($key, true);
        $image = $location->getImage();

        $location->removeImage();
        $form = $this->createForm(LocationType::class, $location, [
            'title' => 'Edit Location',
            'previousImage' => $image
        ]);
        $form->handleRequest($request);
        
        if($this->isOwned($location) && $form->isSubmitted() && $form->isValid()) {
            if($location->getImage() === null && $location->getPreviousImage()) {
                $location->setImageDirect($image);
            }
            $this->locationRepository->add($location);

            return $this->redirectToRoute('new_location', [], Response::HTTP_SEE_OTHER);
        } elseif($image) {
            $location->setImageDirect($image);
        }

        return $this->getForUserLocationPage($request, $paginator, $location, $form);
    }

    private function getForUserLocationPage($request, $paginator, $location, $form) {
        $locations = $this->locationRepository->findByUser(true);
        
        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            6
        );
        $totalResults = $locationData->getTotalItemCount();

        return $this->render('location/new.html.twig', [
            'locations' => $locationData,
            'total_result' => $totalResults,
            'location' => $location,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/location/{key}/delete', name: 'app_location_delete', methods: ['POST'])]
    public function delete_location(Request $request, $key): Response
    {
        $location = $this->getLocationFromKey($key, true);
        $referer = $request->headers->get('referer');

        if($this->isOwned($location) && $this->isCsrfTokenValid('delete'.$location->getId(), $request->request->get('_token'))) {
            $image = $location->getImage();
            if($image && file_exists($this->getParameter('public_directory').$image)) {
                unlink($this->getParameter('public_directory').$image);
            }
            $this->locationRepository->remove($location);

            if(strpos($referer, $key) !== false) return $this->redirectToRoute('new_location');
        }

        return $this->redirect($referer);
    }

    #[Route('/location/{key}', name: 'app_location_show', methods: ['GET'])]
    public function show($key): Response
    {
        $location = $this->getLocationFromKey($key);
        return $this->render('location/show.html.twig', [
            'item' => $location,
        ]);
    }

    #[Route('/location/{key}/admin', name: 'app_location_admin')]
    public function admin($key) {
        $id = $this->getLocationFromKey($key, false, true);
        return $this->redirect('/admin?crudAction=edit&crudControllerFqcn=App%5CController%5CAdmin%5CLocationCrudController&entityId='.$id);
    }

    #[Route('/delete/{source}', name: 'delete_location_source', methods: ['GET'])]
    public function delete(ManagerRegistry $doctrine, Request $request, UploadRepository $uploadRepository): Response
    {
        $publicDir = $this->getParameter('public_directory');
        $source = $request->get('source');

        if($source) {
            $removeSources = $this->locationRepository->findBySource($source);
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

    #[Route('/delete', name: 'delete_location_source_empty', methods: ['GET'])]
    public function deleteEmpty(){
        return $this->redirect('/admin');
    }

    private function isOwned($location) {
        $user = $this->security->getUser();        
        if(!$user) return false;
        elseif($user->hasRole('ROLE_ADMIN')) return true;
        elseif($location->getUser() && $user->getId() === $location->getUser()->getId()) return true;
    }

    private function getLocationFromKey($key, $data = false, $id = false) {
        $locationId = $this->hashService->decodeLoc($key);

        if($id === true) return (int)$locationId;

        $location = $this->locationRepository->findById($locationId);
        
        if(!$location) return null;
        if($data == true) return $location['loc'];
        return $location;
    }
}
