<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Favorite;
use App\Form\RegisterType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\FavoriteRepository;

class RegisterController extends AbstractController
{

    private $entityManger;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManger = $entityManager;         
    }


    /**
     * @Route("/register", name="app_register")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder, FavoriteRepository $favoriteRepository)
    { 
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           
            $user = $form->getData();

            $password = $encoder->encodePassword($user,$user->getPassword());

            $user->setPassword($password);

            $this->entityManger->persist($user);
            $this->entityManger->flush();

            $fav = new Favorite();
            $fav->setName('like')->setMaster(1)->addUser($user);
            $favoriteRepository->save($fav, true);
        }
        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
