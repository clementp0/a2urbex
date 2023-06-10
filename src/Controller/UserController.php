<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use App\Repository\FavoriteRepository;
use Danilovl\HashidsBundle\Service\HashidsService;
use Symfony\Component\Security\Core\Security;
use App\Repository\LocationRepository;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    public function __construct(
        private Security $security,
        private HashidsServiceInterface $hashidsService,
        private UserRepository $userRepository,
        private LocationRepository $locationRepository,
        private FavoriteRepository $favoriteRepository,
        private FriendRepository $friendRepository,
    ){}


    #[Route('/user/{key}', name: 'app_user', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // USER
        $user = $this->getUserFromKey($request->get('key'));
        // LOCATIONS
        $urbex_count = $this->locationRepository->createQueryBuilder('a')->select('count(a.id)')->andWhere('a.user = '.$user->getId())->getQuery()->getSingleScalarResult();
        // FAVORITES 
        $favorites = $this->favoriteRepository->createQueryBuilder('a')->select('count(a.id)')->leftJoin('a.users', 'u')->groupBy('a.id')->andWhere('u.id = '.$user->getId())->getQuery()->getScalarResult();
        $favorites_count = $favorites ? count($favorites) : 0;
        // FRIENDS
        $friends = $this->friendRepository->findFriends($user);
        $friends_count = $friends ? count($friends) : 0;

        return $this->render('user/index.html.twig', [
            'hashkey' => $_ENV["HASH_KEY"],
            'connected_user' => $this->getUser(),
            'user' => $user,
            'urbex_count' => $urbex_count,
            'favorites_count' => $favorites_count,
            'friends_count' => $friends_count
        ]);
    }



    private function getUserFromKey($key, $data = false, $id = false) {
        $hashKey = $_ENV["HASH_KEY"];
        $userKey = $this->hashidsService->decode($key);
        $userId = str_replace($hashKey,'',$userKey);
        $userId = is_array($userId) ? $userId : $userId;

        if($id === true) return (int)$userId;
        
        $user = $this->userRepository->findById($userId);
        
        if(!$user) return null;
        if($data == true) return $user;
        return $user[0];
    }
}