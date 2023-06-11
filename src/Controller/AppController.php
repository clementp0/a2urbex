<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController {
    protected $async;
    
    public function getAsync() {
        $this->async = isset($_GET['async']) && $_GET['async'] === '1';
    }
}