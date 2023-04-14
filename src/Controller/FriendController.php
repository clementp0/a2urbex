<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



class FriendController extends AppController {

    #[Route('/friend', name: 'app_friend')]
    public function index(): Response {
        return $this->render('friend/index.html.twig');
    }

    #[Route('/friend/add/', name: 'app_friend_add_default')]
    #[Route('/friend/add/{id}', name: 'app_friend_add')]
    public function add(): Response {

    }
}