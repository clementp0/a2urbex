<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Danilovl\HashidsBundle\Service\HashidsService;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    public function __construct(
        private Security $security,
        private HashidsServiceInterface $hashidsService,
        private UserRepository $userRepository
    ){}


    #[Route('/user/{key}', name: 'app_user', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->getUserFromKey($request->get('key'));
        return $this->render('user/index.html.twig', [
            'hashkey' => $_ENV["HASH_KEY"],
            'user' => $user
        ]);
    }



    private function getUserFromKey($key, $data = false, $id = false) {
        $hashKey = $_ENV["HASH_KEY"];
        $userKey = $this->hashidsService->decode($key);
        $userId = str_replace($hashKey,'',$userKey);
        $userId = is_array($userId) ? $userId : $userId;

        if($id === true) return (int)$userId;
        
        $user = $this->userRepository->findById($userId);
        
        if(!$user) return null;
        if($data == true) return $user;
        return $user[0];
    }
}