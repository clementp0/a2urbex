<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserOnlineService;


class ExplorersController extends AbstractController
{
    #[Route('/explorers', name: 'app_explorers')]
    public function index(
        UserOnlineService $userOnlineService
    ): Response
    {
        $user = $this->getUser();
        $userOnlineService->addUser($user);
        
        return $this->render('explorers/index.html.twig', [
            'friends' => $userOnlineService->getFriends($user),
            'explorers' => $userOnlineService->getExplorers()
        ]);
    }
}
