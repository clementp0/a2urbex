<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ChannelRepository;

class WebsocketService {
    public function __construct(
        private SessionInterface $session,
        private ChannelRepository $channelRepository
    ) {}

    public function getUser($sessionId) {
        $this->session->setId($sessionId);
        // return $sessionId; // session start block websocket
        // $this->session->start();
        // $this->session->invalidate();
        // $this->session->clear(); 
        
        $token = $this->session->get('_security_main');
        if(is_string($token)) $token = unserialize($token);

    
        if ($token && $token->getUser()) return $token->getUser();
        return null;
    }

    public function hasAccess($user, $channelName) {
        $channel = $this->channelRepository->findOneBy(['name' => $channelName]);
        
        if(!$channel) return true;
        if(!$channel->getRole()) return true;
        if($user && $user->hasRole($channel->getRole())) return true;

        return false;
    }
}