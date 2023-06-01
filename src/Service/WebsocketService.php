<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class WebsocketService {
    public function __construct(private SessionInterface $session) {}

    public function getUser($sessionId) {
        $this->session->setId($sessionId);
        $this->session->start();

        $token = $this->session->get('_security_main');
        if(is_string($token)) $token = unserialize($token);
    
        if ($token && $token->getUser()) return $token->getUser();
        return null;
    }
}