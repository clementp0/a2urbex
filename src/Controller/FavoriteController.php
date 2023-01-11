<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use App\Repository\LocationRepository;

class FavoriteController extends AbstractController
{
    public function __construct(LocationRepository $locationRepository) {
        $this->locationRepository = $locationRepository;
    }

    
    #[Route('/favorite', name: 'app_favorite')] 
    public function index(FavoriteRepository $favoriteRepository): Response {
        
        return $this->render('favorite/index.html.twig', [
            
        ]);
    }
    
    #[Route('/favorite/{id}',name: 'app_favorite_locations')] 
    public function item(Favorite $favorite, Request $request, PaginatorInterface $paginator): Response { // todo rework
        $locations = $this->locationRepository->findByUser($this->security->getUser()->getId());
        
        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            50
        );
        
        return $this->render('favorite/locations.html.twig', [
            'locations' => $locationData
        ]);
    }

    #[Route('/favorite/{id}/delete', name: 'app_favorite_delete')] 
    public function delete(): Response {

    }

    #[Route('/favorite/{id}/share', name: 'app_favorite_share')] 
    public function share(): Response {

    }



    // todo rework
    #[Route('/favorite/toggle', name: 'app_favorite_toggle')]
    public function toggle(Request $request) : Response {
        return false;

        $success = true;
        $user = $this->security->getUser();
        $location = $this->locationRepository->find($request->get('id'));

        if(!$user) {
            $success = false;
        } else {
            $fav = $this->favoriteRepository->findByLocationAndUser($location->getId(), $user->getId());
            if($fav) {
                $this->favoriteRepository->remove($fav, true);
            } else {
                $fav = new Favorite();
                $fav->setLocation($location)->setUser($user);
                $this->favoriteRepository->save($fav, true);
            }
        }
        
        echo json_encode(['success' => $success]);exit();
    }
}
