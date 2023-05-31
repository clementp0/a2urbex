<?php 

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AppController
{
    public function showNotFound(): Response
    {
        return $this->render('404.html.twig');
    }
}
