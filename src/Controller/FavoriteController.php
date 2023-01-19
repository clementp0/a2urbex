<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Knp\Component\Pager\PaginatorInterface;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\HashidsBundle\Service\HashidsService;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

use App\Repository\UserRepository;
use App\Entity\Favorite;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use App\Repository\LocationRepository;

class FavoriteController extends AbstractController
{
    public function __construct(HashidsServiceInterface $hashidsService, LocationRepository $locationRepository, FavoriteRepository $favoriteRepository, Security $security) {
        $this->security = $security;
        $this->locationRepository = $locationRepository;
        $this->favoriteRepository = $favoriteRepository;
        $this->hashidsService = $hashidsService;
    }
    
    #[Route('/favorite', name: 'app_favorite')] 
    public function index(UserRepository $userRepository): Response {
        $hash_key = $_ENV["HASH_KEY"];
        return $this->render('favorite/index.html.twig', [
            'hashkey' => $hash_key,
            'favorites' => $this->favoriteRepository->findByDefault(),
            'users' => $userRepository->findAllButCurrent()
        ]);
    }

    #[Route('/favorite/user', name: 'app_favorite_user')] 
    public function user(Request $request): Response {
        $lid = $request->get('lid');
        $loc = $this->locationRepository->findById($lid);

        $serializer = $this->container->get('serializer');
        $favorites = $this->favoriteRepository->findByDefault();
        $favorites = $serializer->serialize(['favs' => $favorites, 'fids' => $loc['fids']], 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['users', 'locations']]);
        echo $favorites;exit();
    }

    // add security block deletion from other user ?
    #[Route('/favorite/{id}/delete', name: 'app_favorite_delete')] 
    public function delete($id): Response {
        $favorite = $this->favoriteRepository->find($id);
        
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

    #[Route('/favorite/{id}/share/link', name: 'app_favorite_share_link')] 
    public function shareLink($id): Response {
        $favorite = $this->favoriteRepository->find($id);
        // allow share button
        if($favorite->isShare()) $favorite->setShare(0);
        else $favorite->setShare(1);
    
        $this->favoriteRepository->save($favorite, true);
    
        return $this->redirectToRoute('app_favorite');
    }
    
    #[Route('/favorite/{id}/share/user/{uid}', name: 'app_favorite_share_user')]
    public function shareUser($id, $uid, UserRepository $userRepository): Response {
        $favorite = $this->favoriteRepository->find($id);
        $user = $userRepository->find($uid);
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

    #[Route('/list/{key}',name: 'app_favorite_locations')] 
    public function item(Request $request, PaginatorInterface $paginator): Response {

        $hash_key = $_ENV["HASH_KEY"];
        $list_key = $this->hashidsService->decode($request->get('key'));
        $list_id = str_replace($hash_key,'',$list_key);

        $locations = $this->locationRepository->findByIdFav($list_id[0]);
        $locationData = $paginator->paginate(
            $locations,
            $request->query->getInt('page', 1),
            50
        );
        
        $favorite = $this->favoriteRepository->find($list_id[0]);
        

        if($favorite->isShare()){
            return $this->render('favorite/locations.html.twig', [
                'locations' => $locationData,
                'hashkey' => $_ENV["HASH_KEY"],
                'id' => $list_id[0]
            ]);
        }
        else{
            return $this->render('favorite/locations.html.twig', [
                'locations' => $locationData,
                'hashkey' => $_ENV["HASH_KEY"],
                'private' => 'yes',
            ]);
        }
    }
}
