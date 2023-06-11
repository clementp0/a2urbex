<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\FriendRepository;

class FriendService {

    public function __construct(
        private FriendRepository $friendRepository,
        private UserRepository $userRepository
    ){}

    public function isFriend($id, $user) {
        $friend = $this->getFriend($id, $user);
        $status = $friend ? 'friend' : 'not_friend' ;
        if($friend && $friend->isPending()) $status = 'pending';

        return $status;
    }

    public function getFriend($id, $user){
        $friend = $this->friendRepository->findOneBy([
            'user' => $user, 
            'friend' => $this->userRepository->find($id)
        ]);
        return $friend;
    }
}