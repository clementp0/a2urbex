<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ChangeAccountType;
use App\Form\ChangePasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;

class AccountController extends AppController
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }     

    #[Route('/account', name: 'app_account')]
    public function index(Request $request) {
        $notification = null;
        $user = $this->getUser();
        $form = $this->createForm(ChangeAccountType::class, $user);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            $user
                ->setFirstname($form->get('firstname')->getData())
                ->setLastname($form->get('lastname')->getData())
            ;
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $notification = 'Your account has been successfully updated.';
        } else {
            if(str_replace($request->server->get('SYMFONY_PROJECT_DEFAULT_ROUTE_URL'), '', $request->server->get('HTTP_REFERER')) === 'account/password') {
                $notification = $request->getSession()->get('password_notification');
                $request->getSession()->set('password_notification', null);
            }
            $request->getSession()->set('password_notification', null);
        }

        return $this->render('account/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }

    #[Route('/account/password', name: 'app_account_password')]
    public function password(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('old_password')->getData();

            if($encoder->isPasswordValid($user, $oldPassword)) {
                $newPassword = $form->get('new_password')->getData();
                $password = $encoder->encodePassword($user, $newPassword);

                $user->setPassword($password);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $request->getSession()->set('password_notification', 'Your password has been successfully updated.');
                return $this->redirectToRoute('app_account');
            } else {
                $request->getSession()->set('password_notification', 'Your old password is invalid.');
            } 
        } else {
            $request->getSession()->set('password_notification', null);
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $request->getSession()->get('password_notification')
        ]);
    }
}
