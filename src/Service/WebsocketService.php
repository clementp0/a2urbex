<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\WebsocketTokenRepository;
use App\Entity\WebsocketToken;
use Symfony\Component\Uid\Uuid;
use App\Repository\UserRepository;

class WebsocketService {
    public function __construct(
        private SessionInterface $session,
        private WebsocketTokenRepository $websocketTokenRepository,
        private UserRepository $userRepository
    ) {}

    public function getUser($token) {
        $token = $this->websocketTokenRepository->findOneBy(['value' => $token]);
        if($token && $token->getUser()) return $token->getUser();
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
        return Uuid::v4()->toBase32();
    }

    public function getServerToken() {
        $server = $this->userRepository->find($_ENV['WEBSOCKET_SERVER_USER']);
        return $this->getToken($server);
    }
}