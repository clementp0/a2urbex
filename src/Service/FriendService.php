<?php

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\FriendRepository;

class FriendService {

    public function __construct(
        private FriendRepository $friendRepository,
        private UserRepository $userRepository
    ){}

    public function isFriend($u1, $u2) {
        $friend = $this->friendRepository->findOneBy(['user' => $u1, 'friend' => $u2]);
        $status = $friend ? 'friend' : 'not_friend' ;
        if($friend && $friend->isPending()) $status = 'pending';

        return $status;
    }
}