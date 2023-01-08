<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use App\Repository\LocationRepository;

class FavoriteController extends AbstractController
{
    private $security;

    public function __construct(Security $security) {
       $this->security = $security;
    }

    #[Route('/favorite/toggle', name: 'app_favorite')]
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
}
