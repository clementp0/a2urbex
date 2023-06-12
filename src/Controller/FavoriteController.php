<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

use App\Repository\UserRepository;
use App\Entity\Favorite;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use App\Repository\LocationRepository;
use App\Service\HashService;

class FavoriteController extends AppController
{
    public function __construct(
        private HashService $hashService, 
        private LocationRepository $locationRepository, 
        private FavoriteRepository $favoriteRepository
    ) {}
    
    #[Route('/favorite', name: 'app_favorite')] 
    public function index(UserRepository $userRepository): Response {
        return $this->render('favorite/index.html.twig', [
            'favorites' => $this->favoriteRepository->findByDefault(),
            'users' => $userRepository->findAllButCurrent()
        ]);
    }

    #[Route('/favorite/user', name: 'app_favorite_user')] 
    public function user(Request $request): Response {
        $lid = $request->get('lid');

        $locid = $this->hashService->decodeLoc($lid);
        $loc = $this->locationRepository->findById($locid);

        $serializer = $this->container->get('serializer');
        $favorites = $this->favoriteRepository->findByEnabled();
        $favorites = $serializer->serialize(['favs' => $favorites, 'fids' => $loc['fids']], 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['users', 'locations']]);
        return new Response($favorites);
    }

    #[Route('/favorite/{id}/delete', name: 'app_favorite_delete')] 
    public function delete($id): Response { // secure
        $favorite = $this->favoriteRepository->find($id);
        
        if(!$favorite->isMaster()) {
            $favorite->removeUser($this->getUser());
            if(count($favorite->getUsers()) === 0) {
                $this->favoriteRepository->remove($favorite, true);
            } else {
                $this->favoriteRepository->save($favorite, true);
            }
        }
        return $this->redirectToRoute('app_favorite');
    }

    #[Route('/favorite/{id}/disable', name: 'app_favorite_disable')] 
    public function disable($id): Response { // secure
        $favorite = $this->favoriteRepository->find($id);

        if($favorite->getUsers()->contains($this->getUser())) {
            $favorite->setDisabled(!$favorite->isDisabled());
            $this->favoriteRepository->save($favorite, true);
        }

        return $this->redirectToRoute('app_favorite');
    }

    #[Route('/favorite/{id}/share/link', name: 'app_favorite_share_link')] 
    public function shareLink($id): Response { // secure
        $favorite = $this->favoriteRepository->find($id);

        if($favorite->getUsers()->contains($this->getUser())) {
            // allow share button
            if($favorite->isShare()) $favorite->setShare(0);
            else $favorite->setShare(1);
        
            $this->favoriteRepository->save($favorite, true);
        }
    
        return $this->redirectToRoute('app_favorite');
    }
    
    #[Route('/favorite/{id}/share/user/', name: 'app_favorite_share_user_default')]
    #[Route('/favorite/{id}/share/user/{uid}', name: 'app_favorite_share_user')]
    public function shareUser($id, $uid, UserRepository $userRepository): Response { // secure
        $favorite = $this->favoriteRepository->find($id);

        if($favorite->getUsers()->contains($this->getUser())) {
            $user = $userRepository->find($uid);
            $favorite->addUser($user);
            
            $this->favoriteRepository->save($favorite, true);
        }
        
        return $this->redirectToRoute('app_favorite');
    }


    #[Route('/favorite/item/toggle', name: 'app_favorite_item_toggle')]
    public function additem(Request $request) : Response { // secure
        $success = true;
        $lid = $request->get('lid');
        $fid = $request->get('fid');
        $name = $request->get('name');
        $fids = '';

        $locid = $this->hashService->decodeLoc($lid);
        $location = $this->locationRepository->find($locid);

        if(!$location) {
            $success = false;
        } else {
            if($fid) {
                $fav = $this->favoriteRepository->find($fid);
                if($fav->getUsers()->contains($this->getUser())) {
                    if((int)$request->get('checked') === 1) $fav->addLocation($location);
                    else $fav->removeLocation($location);
                    $this->favoriteRepository->save($fav, true);
                }
            } elseif(strlen($name) && $name !== 'like') {
                $fav = new Favorite();
                $fav->setName($name)->addUser($this->getUser())->addLocation($location);
                $this->favoriteRepository->save($fav, true);
            }
            $fids = $this->locationRepository->findById($lid)['fids'];
        }
        
        echo json_encode(['success' => $success, 'fids' => $fids]);exit();
    }

    #[Route('/list/{key}',name: 'app_favorite_locations')] 
    public function item(Request $request, PaginatorInterface $paginator, $key): Response {
        $listId = $this->hashService->decodeFav($key);
        $locations = $this->locationRepository->findByIdFav($listId, true);
        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            50
        );
        
        $favorite = $this->favoriteRepository->find($listId);
        $name = $favorite->getName();
        
        $private = !$favorite->isShare();
        if($favorite->getUsers()->contains($this->getUser())) $private = false;

        if(!$private) {
            return $this->render('favorite/locations.html.twig', [
                'locations' => $locationData,
                'id' => $listId,
                'title' => $name
            ]);
        }
        else{
            return $this->render('favorite/locations.html.twig', [
                'locations' => $locationData,
                'private' => 'yes',
            ]);
        }
    }
}
