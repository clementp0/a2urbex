<?php 

// src/EventListener/UserLastActiveListener.php

namespace App\EventListener;

use App\Entity\User;
use App\Service\UserOnlineService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class UserLastActiveListener implements EventSubscriberInterface
{
    private $userOnlineService;
    private $session;

    public function __construct(UserOnlineService $userOnlineService, SessionInterface $session)
    {
        $this->userOnlineService = $userOnlineService;
        $this->session = $session;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $user = $this->session->get('user');

        if ($user instanceof User) {
            $this->userOnlineService->addUser($user);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
