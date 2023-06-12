<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ChangeAccountType;
use App\Form\ChangePasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Danilovl\HashidsBundle\Interfaces\HashidsServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FriendService;
use App\Repository\LocationRepository;
use App\Repository\FriendRepository;
use App\Repository\UserRepository;
use App\Repository\FavoriteRepository;

class AccountController extends AppController
{
    public function __construct(
        private FriendService $friendService,
        private EntityManagerInterface $entityManager,
        private HashidsServiceInterface $hashidsService,
        private UserRepository $userRepository,
        private LocationRepository $locationRepository,
        private FavoriteRepository $favoriteRepository,
        private FriendRepository $friendRepository,
    ) {}     

    #[Route('/account', name: 'app_account')]
    public function account(Request $request) {
        $notification = null;
        $user = $this->getUser();
        $image = $user->getImage();
        $banner = $user->getBanner();
        $user = $this->getUser();
        $user->removeImage();
        $user->removeBanner();
        $form = $this->createForm(ChangeAccountType::class, $user, [
            'previousImage' => $image,
            'previousBanner'=> $banner
        ]);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
            if($user->getImage() === null && $user->getPreviousImage()) {
                $user->setImageDirect($image);
            }
            if($user->getBanner() === null && $user->getPreviousBanner()) {
                $user->setBannerDirect($banner);
            }
            $user
                ->setFirstname($form->get('firstname')->getData())
                ->setLastname($form->get('lastname')->getData());
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_user',[
                "key" => $this->hashidsService->encode($user->getId().$_ENV["HASH_KEY"])]);
        } else {
            $base = ($request->server->get('HTTPS') ? 'https://' : 'http://') . $request->server->get('HTTP_HOST') . '/';
            if(str_replace($base, '', $request->server->get('HTTP_REFERER')) === 'account/password') {
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

    #[Route('/user/{key}', name: 'app_user', methods: ['GET'])]
    public function user(Request $request) {
        // USER
        $user = $this->getUserFromKey($request->get('key'));
        // LOCATIONS
        $urbex_count = $this->locationRepository->createQueryBuilder('a')->select('count(a.id)')->andWhere('a.user = '.$user->getId())->getQuery()->getSingleScalarResult();
        // FAVORITES 
        $favorites = $this->favoriteRepository->createQueryBuilder('a')->select('count(a.id)')->leftJoin('a.users', 'u')->groupBy('a.id')->andWhere('u.id = '.$user->getId())->getQuery()->getScalarResult();
        $favorites_count = $favorites ? count($favorites) : 0;
        // FRIENDS
        $friends = $this->friendRepository->findFriends($user);
        $friends_count = $friends ? count($friends) : 0;

        return $this->render('account/user.html.twig', [
            'hashkey' => $_ENV["HASH_KEY"],
            'connected_user' => $this->getUser(),
            'user' => $user,
            'urbex_count' => $urbex_count,
            'favorites_count' => $favorites_count,
            'friends_count' => $friends_count,
            'friend_status' => $this->friendService->isFriend($this->getUser(), $user),
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

