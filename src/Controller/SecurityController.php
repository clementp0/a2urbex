<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FavoriteRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\User;
use App\Entity\Favorite;
use App\Form\RegisterType;

class SecurityController extends AppController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManger = $entityManager;         
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_location_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/register', name: 'app_register')]
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

            return new RedirectResponse($this->generateUrl('app_login'));

        }
        return $this->render('register/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/user/modal', name: 'app_user_modal')]
    public function modal(Request $request): Response 
    {
        return $this->render('security/user-modal.html.twig', [
            'fav' => $request->get('fav')
        ]);
    }

    #[Route('/user/search', name: 'app_user_search')]
    public function search(Request $request, Security $security, UserRepository $userRepository): Response 
    {
        $search = $request->get('search');
        $user = $security->getUser();
        $exclude = $request->get('exclude') === 'true';
        $favId = $request->get('fav');
        $type = $favId ? 'fav' : 'friend';

        if($type === 'friend') $result = $userRepository->findForSearchFriend($search, $user->getId(), $exclude);
        if($type === 'fav') $result = $userRepository->findForSearchFav($search, $favId, $exclude);
        
        $serializer = $this->container->get('serializer');
        $serialized = $serializer->serialize($result, 'json');
        
        return new Response($serialized);
    }
}
