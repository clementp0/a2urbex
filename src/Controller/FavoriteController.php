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
    private $security;

    public function __construct(Security $security) {
       $this->security = $security;
    }

    #[Route('/favorite/toggle', name: 'app_favorite_toggle')]
    public function toggle(Request $request, FavoriteRepository $favoriteRepository, LocationRepository $locationRepository) : Response {
        $success = true;
        $user = $this->security->getUser();
        $location = $locationRepository->find($request->get('id'));

        if(!$user) {
            $success = false;
        } else {
            $fav = $favoriteRepository->findByLocationAndUser($location->getId(), $user->getId());
            if($fav) {
                $favoriteRepository->remove($fav, true);
            } else {
                $fav = new Favorite();
                $fav->setLocation($location)->setUser($user);
                $favoriteRepository->save($fav, true);
            }
        }
        
        echo json_encode(['success' => $success]);exit();
    }

    #[Route('/favorite/locations', name: 'app_favorite_locations')]
    public function location(Request $request, LocationRepository $locationRepository, PaginatorInterface $paginator, Security $security) : Response {
        $locations = $locationRepository->findByUser($security->getUser()->getId());
        
        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            50
        );

        return $this->render('favorite/index.html.twig', [
            'locations' => $locationData
        ]);
    }
}
