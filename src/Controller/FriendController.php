<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\UserRepository;
use App\Repository\FriendRepository;
use App\Entity\Friend;

class FriendController extends AppController {

    public function __construct(private FriendRepository $friendRepository) {
        parent::__construct();
    }

    #[Route('/friend', name: 'app_friend')]
    public function index(): Response {
        $user = $this->getUser();

        return $this->render('friend/index.html.twig', [
            'pending' => $this->friendRepository->findPending($user),
            'waiting' => $this->friendRepository->findWaiting($user),
            'friends' => $this->friendRepository->findFriends($user)
        ]);
    }

    #[Route('/friend/add/', name: 'app_friend_add_default')]
    #[Route('/friend/add/{id}', name: 'app_friend_add')]
    public function add($id, UserRepository $userRepository): Response {
        dd('here');
        $cuser = $this->getUser();
        $fuser = $userRepository->find($id);

        if($cuser && $fuser) {
            $exist = $this->friendRepository->findOneBy(['user' => $cuser, 'friend' => $fuser]);

            if(!$exist) {
                $pending = $this->friendRepository->findOneBy(['user' => $fuser, 'friend' => $cuser]);
                if($pending) {
                    $pending->setPending(false);
                    $this->friendRepository->save($pending, true);
                }

                $friend = new Friend();
                $friend
                    ->setUser($cuser)
                    ->setFriend($fuser)
                    ->setPending($pending ? false : true)
                ;
                $this->friendRepository->save($friend, true);
            }

        }

        return $this->redirectToRoute('app_friend');
    }

    #[Route('/friend/accept/{id}', name: 'app_friend_accept')]
    public function accept($id) {
        $user = $this->getUser();
        $friend = $this->friendRepository->find($id);

        if($friend && $user === $friend->getFriend()) {
            $friend->setPending(false);
            $this->friendRepository->save($friend, true);

            $newFriend = new Friend();
            $newFriend
                ->setUser($friend->getFriend())
                ->setFriend($friend->getUser())
                ->setPending(false)
            ;

            $this->friendRepository->save($newFriend, true);
        }

        return $this->redirectToRoute('app_friend');
    }

    #[Route('/friend/decline/{id}', name: 'app_friend_decline')]
    public function decline($id) {
        $user = $this->getUser();
        $friend = $this->friendRepository->find($id);
        if($friend && $user === $friend->getFriend()) $this->friendRepository->remove($friend, true);

        return $this->redirectToRoute('app_friend');
    }

    #[Route('/friend/remove/{id}', name: 'app_friend_remove')]
    public function remove($id) {
        $user = $this->getUser();
        $friend = $this->friendRepository->find($id);
        if($friend && $user === $friend->getUser()) {
            $this->friendRepository->remove($friend, true);
            
            $oldFriend = $this->friendRepository->findOneBy(['user' => $friend->getFriend(), 'friend' => $friend->getUser()]);
            $this->friendRepository->remove($oldFriend, true);
        }
        return $this->redirectToRoute('app_friend');
    }
}