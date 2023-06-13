<?php
// src/Service/UserOnlineService.php

namespace App\Service;

use App\Entity\User;
use App\Entity\Friend;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;

class UserOnlineService {
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private Security $security,
        private FriendService $friendService,
        private UserRepository $userRepository, 
    ) {}

    public function addUser(User $user): void {
        $user->setLastActiveAt(new \DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }


    public function removeUser(User $user): void {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function getFriends($user) {
        $users = $this->userRepository->findUsers($user, true);
        return $this->processUsers($users); 
    }

    public function getExplorers() {
        $users = $this->userRepository->findUsers();
        $proccessed = $this->processUsers($users);
        return array_merge($proccessed['online'], $proccessed['offline']);
    }

    private function processUsers($users) {
        $online = [];
        $offline = [];

        foreach($users as $user) {
            $lastActiveAt = $user->getLastActiveAt();
            $diff = $lastActiveAt->diff(new \DateTime())->format('%a:%h:%i');
            list($days, $hours, $minutes) = explode(':', $diff);
            $totalMinutes = ($days * 24 * 60) + ($hours * 60) + $minutes;

            $status = 'offline';
            if ($totalMinutes < 5) $status = 'online';
            elseif ($totalMinutes < 60) $status = 'away';
            
            $active = '';
            if((int)$days > 365) $active = floor($days / 365).'y';
            elseif((int)$days > 0) $active = $days.'d';
            elseif($totalMinutes > 60) $active = $hours.'h';
            elseif($totalMinutes > 5) $active = $minutes.'m';

            $item = [
                'id' =>  $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => substr($user->getLastname(), 0, 1),
                'image' => $user->getImage(),
                'active' => $active ? ' (' . $active . ' ago)' : '',
                'status' => $status
            ];

            if($status === 'offline') $offline[] = $item;
            else $online[] = $item;
        }

        return [
            'online' => $online,
            'offline' => $offline
        ];
    }
}
