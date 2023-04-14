<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SecurityController extends AppController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_location_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route('/user/modal', name: 'app_user_modal')]
    public function modal(): Response 
    {
        return $this->render('security/user-modal.html.twig');
    }

    #[Route('/user/search', name: 'app_user_search')]
    public function search(Request $request, Security $security, UserRepository $userRepository): Response 
    {
        $search = $request->get('search');
        $excludeFriends = $request->get('exclude_friends') === 'true';
        $user = $security->getUser();

        $result = $userRepository->findForSearch($search, $user->getId(), $excludeFriends);
        
        $serializer = $this->container->get('serializer');
        $serialized = $serializer->serialize($result, 'json');
        
        return new Response($serialized);
    }
}
