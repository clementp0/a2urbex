<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AppController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(): Response
    {

        $first_name = "Florence";
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'first_name' => $first_name,
        ]);
    }
}
