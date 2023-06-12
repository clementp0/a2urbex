<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController {
    public function isAsync() {
        return isset($_GET['async']) && $_GET['async'] === '1';
    }
}