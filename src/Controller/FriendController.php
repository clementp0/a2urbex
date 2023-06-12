<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FriendService;
use App\Repository\UserRepository;
use App\Repository\FriendRepository;
use App\Entity\Friend;

class FriendController extends AppController {

    public function __construct(
        private FriendService $friendService,
        private FriendRepository $friendRepository,
        private UserRepository $userRepository
    ) {}

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
    public function add($id): Response {
        $cuser = $this->getUser();
        $fuser = $this->userRepository->find($id);

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

        if($this->isAsync()) return $this->state($fuser);
        else return $this->redirectToRoute('app_friend');
    }

    #[Route('/friend/accept/{id}', name: 'app_friend_accept')]
    public function accept($id) {
        $user = $this->getUser();
        $fuser = $this->userRepository->find($id);
        $friend = $this->friendRepository->findOneBy(['user' => $fuser, 'friend' => $user, 'pending' => true]);

        if($friend) {
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
        $fuser = $this->userRepository->find($id);
        $friend = $this->friendRepository->findOneBy(['user' => $fuser, 'friend' => $user, 'pending' => true]);

        if($friend) $this->friendRepository->remove($friend, true);

        return $this->redirectToRoute('app_friend');
    }

    #[Route('/friend/remove/{id}', name: 'app_friend_remove')]
    public function remove($id) {
        $user = $this->getUser();
        $fuser = $this->userRepository->find($id);
        $friend = $this->friendRepository->findOneBy(['user' => $user, 'friend' => $fuser, 'pending' => false]);

        if($friend) {
            $this->friendRepository->remove($friend, true);
            
            $oldFriend = $this->friendRepository->findOneBy(['user' => $friend->getFriend(), 'friend' => $friend->getUser()]);
            $this->friendRepository->remove($oldFriend, true);
        }

        if($this->isAsync()) return $this->state($fuser);
        else return $this->redirectToRoute('app_friend');
    }

    #[Route('/friend/cancel/{id}', name: 'app_friend_cancel')]
    public function cancel($id) {
        $user = $this->getUser();
        $fuser = $this->userRepository->find($id);
        $friend = $this->friendRepository->findOneBy(['user' => $user, 'friend' => $fuser, 'pending' => true]);

        if($friend) $this->friendRepository->remove($friend, true);
        
        if($this->isAsync()) return $this->state($fuser);
        else return $this->redirectToRoute('app_friend');
    }

    private function state($user) {
        $state = $this->friendService->isFriend($this->getUser(), $user);
        return new JsonResponse(['state' => $state]);
    }
}