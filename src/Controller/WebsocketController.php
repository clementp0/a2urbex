<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Service\UserOnlineService;

class WebsocketController extends AbstractController
{
    #[Route('/chat', name: 'app_chat')]
    public function index(): Response
    {
        return $this->render('websocket/index.html.twig', [
            'controller_name' => 'WebsocketController',
            'user' => $this->getUser(),
            'user_role' => $this->getUser()->getRoles(),
            'user_id' => $this->getUser()->getId()

        ]);
    }

    #[Route('/general_chat', name: 'app_general_chat')]
    public function general(UserOnlineService $userOnlineService): Response
    {

        $onlineUsers = $userOnlineService->getOnlineUsers();

        return $this->render('websocket/general.html.twig', [
            'controller_name' => 'WebsocketController',
            'user' => $this->getUser(),
            'user_role' => $this->getUser()->getRoles(),
            'user_id' => $this->getUser()->getId(),
            'onlineUsers' => $onlineUsers

        ]);
    }
}
