<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ChangeAccountType;
use App\Form\ChangePasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\FriendService;
use App\Repository\LocationRepository;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use App\Repository\FavoriteRepository;
use App\Service\HashService;

class AccountController extends AppController
{
    public function __construct(
        private FriendService $friendService,
        private UserRepository $userRepository,
        private LocationRepository $locationRepository,
        private FavoriteRepository $favoriteRepository,
        private FriendRepository $friendRepository,
        private HashService $hashService
    ) {}     

    #[Route('/account', name: 'app_account')]
    public function account(Request $request) {
        $notification = $this->getNotification($request);
        
        $user = $this->getUser();
        $image = $user->getImage();
        $banner = $user->getBanner();
        $user->removeImage();
        $user->removeBanner();
        $form = $this->createForm(ChangeAccountType::class, $user, [
            'previousImage' => $image,
            'previousBanner'=> $banner
        ]);

        $form->handleRequest($request);
        
        if($form->isSubmitted()) {
            if($form->isValid()) {
                if($user->getImage() === null && $user->getPreviousImage()) $user->setImageDirect($image);
                if($user->getBanner() === null && $user->getPreviousBanner()) $user->setBannerDirect($banner);
                $this->userRepository->add($user);
    
                $this->setNotification($request, 'Your profile was successfully updated', 1);
                return $this->redirectToRoute('app_user', ["key" => $this->hashService->encodeUsr($user->getId())]);
            } else {
                $this->setNotification($request, 'An error occured while updating your profile', 0);
                $notification = $this->getNotification($request);
            }
        }

        return $this->render('account/index.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }

    #[Route('/account/password', name: 'app_account_password')]
    public function password(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $notification = $this->getNotification($request);

        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->isValid()) {
                $oldPassword = $form->get('old_password')->getData();
    
                if($encoder->isPasswordValid($user, $oldPassword)) {
                    $newPassword = $form->get('new_password')->getData();
                    $password = $encoder->encodePassword($user, $newPassword);
    
                    $user->setPassword($password);
                    $this->userRepository->add($user);
                    
                    $this->setNotification($request, 'Your password was successfully updated', 1);
                    return $this->redirectToRoute('app_user', ["key" => $this->hashService->encodeUsr($user->getId())]);
                } else {
                    $this->setNotification($request, 'Your old password is invalid', 0);
                    $notification = $this->getNotification($request);
                } 
            } else {
                $this->setNotification($request, 'An error occured while updating your password', 0);
                $notification = $this->getNotification($request);
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }

    #[Route('/user/{key}', name: 'app_user', methods: ['GET'])]
    public function user($key, Request $request) {
        // USER
        $id = $this->hashService->decodeUsr($key);
        $user = $this->userRepository->findOneById($id);
        // LOCATIONS
        $urbex_count = $this->locationRepository->createQueryBuilder('a')->select('count(a.id)')->andWhere('a.user = '.$user->getId())->getQuery()->getSingleScalarResult();
        // FAVORITES 
        $favorites = $this->favoriteRepository->createQueryBuilder('a')->select('count(a.id)')->leftJoin('a.users', 'u')->groupBy('a.id')->andWhere('u.id = '.$user->getId())->getQuery()->getScalarResult();
        $favorites_count = $favorites ? count($favorites) : 0;
        // FRIENDS
        $friends = $this->friendRepository->findFriends($user);
        $friends_count = $friends ? count($friends) : 0;

        return $this->render('account/user.html.twig', [
            'connected_user' => $this->getUser(),
            'user' => $user,
            'urbex_count' => $urbex_count,
            'favorites_count' => $favorites_count,
            'friends_count' => $friends_count,
            'friend_status' => $this->friendService->isFriend($this->getUser(), $user),
            'notification' => $this->getNotification($request)
        ]);
    }

    private function setNotification($request, $value, $type = null) {
        $val = $value === null ? null : $value.'|'.$type;
        $request->getSession()->set('account_notification', $val);
    }
    private function getNotification($request) {
        $notification = $request->getSession()->get('account_notification');
        $this->setNotification($request, null);

        if($notification === null) return null;
        
        $split = explode('|', $notification);

        $class = '';
        if($split[1] === '0') $class = 'error';
        if($split[1] === '1') $class = 'success';

        return [
            'text' => $split[0],
            'class' => $class
        ];
    }
}

