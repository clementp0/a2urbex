<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\MessageRepository;
use App\Repository\ChatRepository;
use App\Repository\ChatUserRepository;
use App\Repository\UserRepository;
use App\Service\ChatService;
use App\Service\ChannelService;
use App\Entity\Chat;

class ChatController extends AppController
{
    public function __construct(
        private ChatService $chatService, 
        private MessageRepository $messageRepository, 
        private ChatRepository $chatRepository, 
        private ChatUserRepository $chatUserRepository, 
        private UserRepository $userRepository,
        private ChannelService $channelService, 
    ) {}

    #[Route('/chat/admin/add', name: 'chat_add_admin', methods: ['GET', 'POST'])]
    public function addAdminChat(Request $request): Response {
        $global = $_ENV['CHAT_CHANNEL_GLOBAL'];
        $messageContent = $request->getContent();

        $success = $this->chatService->saveMessage($global, $messageContent, null, true);
        return $this->chatReturn($success);
    }

    #[Route('/chat/{channel}/add', name: 'chat_add', methods: ['GET', 'POST'])]
    public function addChat($channel, Request $request): Response {
        $user = $this->getUser();
        $messageContent = $request->getContent();
        $success = $this->chatService->saveMessage($channel, $messageContent, $user);

        return $this->chatReturn($success);
    }

    #[Route('/chat/{channel}/get', name: 'chat_get', methods: ['GET', 'POST'])]
    public function getChat($channel): Response {
        $user = $this->getUser();
        return new Response($this->chatService->getMessages($channel, $user));
    }

    // Clear Chat 
    #[Route('/chat/global/clear', name: 'chat_clear_global')]
    public function clearChat() {
        $global = $_ENV['CHAT_CHANNEL_GLOBAL'];
        $chat = $this->channelService->getChat($global);

        $this->messageRepository->clearChat($chat->getId());
        $this->chatService->saveMessage($global, 'WELCOME TO A2URBEX', null, true);
        return $this->redirect('/admin');
    }

    private function chatReturn($success) {
        return new JsonResponse(['success' => $success ? true : false]);
    }

    // get all user chats
    #[Route('/chat/get', name: 'chat_get_all')]
    public function getUserChats() {
        $user = $this->getUser();
        if(!$user) return;

        return new Response($this->chatService->getChats($user));
    }

    // get chat info with a user
    #[Route('/chat/user/{id}', name: 'chat_get_user')]
    public function getChatName($id) {
        $u1 = $this->getUser();
        $u2 = $this->userRepository->find($id);
        
        if(!$u1 || !$u2 || $u1 === $u2) return new JsonResponse(null);

        return new Response($this->chatService->getUserChat($u1, $u2));
    }

    // new group chat
    #[Route('/chat/new', name: 'chat_new')]
    public function newChat(Request $request) {
        $title = $request->get('title');
        $image = $request->get('image');
        $ids = $request->get('ids');

        $user = $this->getUser();
        $newUsers = $this->userRepository->findBy(['id' => $ids]);
        $users = array_merge([$user], $newUsers);

        if(!mb_strlen($title) || !count($newUsers)) $this->chatReturn(false);
        
        $chat = $this->chatService->createChat($users, true, $user);
        $chat->setTitle($title);

        if(mb_strlen($image)) $chat->setImageCustom($image);

        $this->chatRepository->save($chat, true);

        $usernames = [];
        foreach($newUsers as $u) $usernames[] = $u->getUsername();
        $message = $user->getUsername().' created a new chat with ' . implode(', ', $usernames);
        $success = $this->chatService->saveMessage($chat->getName(), $message, null, true);

        return $this->chatReturn($success);
    }

    #[Route('/chat/{channel}/info', name: 'chat_info')]
    public function getInfo($channel) {
        $user = $this->getUser();
        return new Response($this->chatService->getInfo($channel, $user));
    }

    #[Route('/chat/{channel}/title', name: 'chat_title')]
    public function updateTitle($channel, Request $request) {
        $user = $this->getUser();
        $success = false;
        $title = $request->get('title');

        if($this->channelService->hasChatAccess($channel, $user, true)) {
            $chat = $this->channelService->getChat($channel);
            $chat->setTitle($title);
            $this->chatRepository->save($chat, true);

            $message = $user->getUsername().' named the group ' . $title;
            $success = $this->chatService->saveMessage($chat->getName(), $message, null, true);
        }
        
        return $this->chatReturn($success);
    }
    
    #[Route('/chat/{channel}/image', name: 'chat_image')]
    public function updateImage($channel, Request $request) {
        $user = $this->getUser();
        $success = false;
        $image = $request->get('image');

        if($this->channelService->hasChatAccess($channel, $user) && mb_strlen($image)) {
            $chat = $this->channelService->getChat($channel);
            $chat->setImageCustom($image);
            $this->chatRepository->save($chat, true);

            $message = $user->getUsername().' changed the group image';
            $success = $this->chatService->saveMessage($chat->getName(), $message, null, true);
        }

        return $this->chatReturn($success);
    }

    #[Route('/chat/{channel}/user/{id}/add', name: 'chat_user_add')]
    public function newUser($channel, $id) {
        $user = $this->getUser();
        $newUser = $this->userRepository->findOneById($id);

        $data = $this->chatService->addChatUser($channel, $user, $newUser);
        if(!$data) return $this->chatReturn(false);

        $chat = $this->channelService->getChat($channel);
        $message = $user->getUsername() . ' added ' . $newUser->getUsername();
        $success = $this->chatService->saveMessage($chat->getName(), $message, null, true);

        return new Response($data);
    }

    #[Route('/chat/{channel}/user/{id}/rename', name: 'chat_user_rename')]
    public function renamerUser($channel, $id, Request $request) {
        $user = $this->getUser();
        $user2 = $this->userRepository->findOneById($id);
        $name = $request->get('name');
        $success = false;

        if($user2 || $this->channelService->hasChatAccess($channel, $user)) {
            $chat = $this->channelService->getChat($channel);
            $chatUser = $this->chatUserRepository->findOneBy(['chat' => $chat, 'user' => $user2]);

            if($chatUser && mb_strlen($name)) {
                $oldPseudo = $chatUser->getPseudo();
                $chatUser->setPseudo($name);
                $this->chatUserRepository->save($chatUser, true);
    
                $message = $user->getUsername() . ' renamed ' . $oldPseudo . ' to ' . $name;
                $success = $this->chatService->saveMessage($chat->getName(), $message, null, true);
            }
        }

        return $this->chatReturn($success);
    }

    #[Route('/chat/{channel}/user/{id}/op', name: 'chat_user_op')]
    public function opUser($channel, $id) {
        $user = $this->getUser();
        $user2 = $this->userRepository->findOneById($id);
        $success = false;

        if($user2 || $this->channelService->hasChatAccess($channel, $user, true)) {
            $chat = $this->channelService->getChat($channel);
            $chatUser = $this->chatUserRepository->findOneBy(['chat' => $chat, 'user' => $user2]);

            if($chatUser) {
                $chatUser->setOp(true);
                $this->chatUserRepository->save($chatUser, true);
                $success = true;
            }
        }

        return $this->chatReturn($success);
    }

    #[Route('/chat/{channel}/user/{id}/delete', name: 'chat_user_delete')]
    public function deleteUser($channel, $id) {
        $user = $this->getUser();
        $user2 = $this->userRepository->findOneById($id);
        $success = false;

        if($user2 || $this->channelService->hasChatAccess($channel, $user, true)) {
            $chat = $this->channelService->getChat($channel);
            $chatUser = $this->chatUserRepository->findOneBy(['chat' => $chat, 'user' => $user2]);
            $this->chatUserRepository->remove($chatUser, true);

            $message = $user->getUsername() . ' removed ' . $user2->getUsername();
            $success = $this->chatService->saveMessage($chat->getName(), $message, null, true);
        }

        return $this->chatReturn($success);
    }
}