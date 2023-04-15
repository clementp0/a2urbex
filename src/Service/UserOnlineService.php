<?php
// src/Service/UserOnlineService.php

namespace App\Service;

use App\Entity\User;
use App\Entity\Friend;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class UserOnlineService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function addUser(User $user): void
    {
        $user->setLastActiveAt(new \DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }


    public function removeUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }


    public function getOnlineUsers(): array
    {
        $threshold = new \DateTime('-5 minutes');
        $cuser = $this->security->getUser();

        if(!$cuser->hasRole('ROLE_ADMIN')) {
            $users = [$cuser->getId()];
            $f = $this->entityManager->getRepository(Friend::class)->findFriendForSearch($cuser->getId());
            if($f) foreach($f as $item) $users[] = $item['id'];
        }
        
        $onlineUsers = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->select('u.firstname', 'u.lastname')
            ->where('u.lastActiveAt >= :threshold')
            ->setParameter('threshold', $threshold);

        if(!$cuser->hasRole('ROLE_ADMIN')) $onlineUsers->andWhere('u.id IN ('.implode(', ', $users).')');
        $onlineUsers = $onlineUsers->getQuery()->getResult();

        

        $offlineUsers = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.lastActiveAt < :threshold')
            ->setParameter('threshold', $threshold);

        if(!$cuser->hasRole('ROLE_ADMIN')) $offlineUsers->andWhere('u.id IN ('.implode(', ', $users).')');
        $offlineUsers = $offlineUsers->getQuery()->getResult();

        foreach ($offlineUsers as $user) {
            $lastActiveAt = $user->getLastActiveAt();
            $diff = $lastActiveAt->diff(new \DateTime())->format('%a:%h:%i');
            list($days, $hours, $minutes) = explode(':', $diff);
            $totalMinutes = ($days * 24 * 60) + ($hours * 60) + $minutes;
            if ($totalMinutes < 59) {
                $user = [
                    'firstname' => $user->getFirstname(),
                    'lastname' => substr($user->getLastname(), 0, 1),
                    'active' => ' (' . $minutes . 'm ago)',
                    'status' => 'away'
                ];
            }
            else {
                $user = [
                    'firstname' => $user->getFirstname(),
                    'lastname' => substr($user->getLastname(), 0, 1),
                    'active' => '(Offline)',
                    'status' => 'offline'
                ];
            }
            $onlineUsers[] = $user;
        }

        return $onlineUsers;
    }
}
