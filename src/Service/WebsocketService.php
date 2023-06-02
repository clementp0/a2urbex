<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\WebsocketChannelRepository;
use App\Repository\WebsocketTokenRepository;
use App\Entity\WebsocketToken;
use Symfony\Component\Uid\Uuid;

class WebsocketService {
    public function __construct(
        private SessionInterface $session,
        private WebsocketChannelRepository $websocketChannelRepository,
        private WebsocketTokenRepository $websocketTokenRepository
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
        $channel = $this->websocketChannelRepository->findOneBy(['name' => $channelName]);
        
        if(!$channel) return true;
        if(!$channel->getRole()) return true;
        if($user && $user->hasRole($channel->getRole())) return true;

        return false;
    }

    public function getToken($user) {
        if(!$user) return;
        $lifetime = (int)$_ENV['WEBSOCKET_TOKEN_LIFETIME'];
        
        $token = $this->websocketTokenRepository->findOneBy(['user' => $user]);
        
        if($token && $token->getExpiry() >= time()) {
            return $token->getValue();
        } elseif(!$token) {
            $token = new WebsocketToken();
            $token->setUser($user);
        }

        $token
            ->setExpiry(time() + $lifetime)
            ->setValue($this->generateToken())
        ;
        $this->websocketTokenRepository->save($token, true);
        
        return $token->getValue();
    }

    private function generateToken() {
        return UUid::v4()->toBase32();
    }
}