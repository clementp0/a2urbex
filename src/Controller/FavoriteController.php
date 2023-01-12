<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Knp\Component\Pager\PaginatorInterface;

use App\Entity\Favorite;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use App\Repository\LocationRepository;
use App\Repository\UserRepository;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class FavoriteController extends AbstractController
{
    public function __construct(LocationRepository $locationRepository, FavoriteRepository $favoriteRepository, Security $security) {
        $this->security = $security;
        $this->locationRepository = $locationRepository;
        $this->favoriteRepository = $favoriteRepository;
    }
    
    #[Route('/favorite', name: 'app_favorite')] 
    public function index(UserRepository $userRepository): Response {
        return $this->render('favorite/index.html.twig', [
            'favorites' => $this->favoriteRepository->findByDefault(),
            'users' => $userRepository->findAllButCurrent()
        ]);
    }

    #[Route('/favorite/user', name: 'app_favorite_user')] 
    public function user(): Response {
        $serializer = $this->container->get('serializer');
        $favorites = $this->favoriteRepository->findByDefault();
        $favorites = $serializer->serialize($favorites, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['users', 'locations']]);
        echo $favorites;exit();
    }
    
    #[Route('/favorite/{id}',name: 'app_favorite_locations')] 
    public function item(Favorite $favorite, Request $request, PaginatorInterface $paginator): Response { // todo rework
        $locations = $this->locationRepository->findByIdFav($favorite->getId());
        
        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            50
        );
        
        return $this->render('favorite/locations.html.twig', [
            'locations' => $locationData
        ]);
    }

    // add security block deletion from other user ?
    #[Route('/favorite/{id}/delete', name: 'app_favorite_delete')] 
    public function delete(Favorite $favorite): Response {
        if(!$favorite->isMaster()) {
            $favorite->removeUser($this->security->getUser());
            if(count($favorite->getUsers()) === 0) {
                $this->favoriteRepository->remove($favorite, true);
            } else {
                $this->favoriteRepository->save($favorite, true);
            }
        }
        return $this->redirectToRoute('app_favorite');
    }

    // add security block share link from other user ?
    #[Route('/favorite/{id}/share/link', name: 'app_favorite_share_link')] 
    public function shareLink(Favorite $favorite): Response {
        // allow share button
        if($favorite->isShare()) $favorite->setShare(0);
        else $favorite->setShare(1);
    
        $this->favoriteRepository->save($favorite, true);
    
        return $this->redirectToRoute('app_favorite');
    }
    
    // add security block share user from other user ?
    #[Route('/favorite/{id}/share/user/{uid}', name: 'app_favorite_share_user')]
    
    public function shareUser(Favorite $favorite, UserRepository $userRepository, $uid): Response {
        $user = $userRepository->find($uid); // todo replace by param converter
        $favorite->addUser($user);
        
        $this->favoriteRepository->save($favorite, true);
        
        return $this->redirectToRoute('app_favorite');
    }


    #[Route('/favorite/item/toggle', name: 'app_favorite_item_toggle')]
    public function additem(Request $request) : Response {
        $success = true;
        $lid = $request->get('lid');
        $fid = $request->get('fid');
        $name = $request->get('name');
        $fids = '';

        $user = $this->security->getUser();
        $location = $this->locationRepository->find($lid);

        if(!$user && !$location) {
            $success = false;
        } else {
            if($fid) {
                $fav = $this->favoriteRepository->find($fid);
                if((int)$request->get('checked') === 1) $fav->addLocation($location);
                else $fav->removeLocation($location);
                $this->favoriteRepository->save($fav, true);
            } elseif(strlen($name) && $name !== 'like') {
                $fav = new Favorite();
                $fav->setName($name)->addUser($user)->addLocation($location);
                $this->favoriteRepository->save($fav, true);
            }
            $fids = $this->locationRepository->findById($lid)['fids'];
        }
        
        echo json_encode(['success' => $success, 'fids' => $fids]);exit();
    }
}
