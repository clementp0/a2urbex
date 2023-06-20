<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\MessageRepository;
use App\Repository\ChatRepository;
use App\Repository\UserRepository;
use App\Service\ChatService;
use App\Service\ChannelService;

class ChatController extends AppController
{
    public function __construct(
        private ChatService $chatService, 
        private MessageRepository $messageRepository, 
        private ChatRepository $chatRepository, 
        private UserRepository $userRepository,
        private ChannelService $channelService, 
    ) {}

    #[Route('/chat/add/admin', name: 'chat_add_admin', methods: ['GET', 'POST'])]
    public function addAdminChat(Request $request): Response {
        $global = $_ENV['CHAT_CHANNEL_GLOBAL'];
        $messageContent = $request->getContent();

        $success = $this->chatService->saveMessage($global, $messageContent, null, true);
        return $this->chatReturn($success);
    }

    #[Route('/chat/add/{channel}', name: 'chat_add', methods: ['GET', 'POST'])]
    public function addChat($channel, Request $request): Response {
        $user = $this->getUser();
        $messageContent = $request->getContent();
        $success = $this->chatService->saveMessage($channel, $messageContent, $user);

        return $this->chatReturn($success);
    }

    #[Route('/chat/get/{channel}', name: 'chat_get', methods: ['GET', 'POST'])]
    public function getChat($channel): Response {
        $user = $this->getUser();
        return new Response($this->chatService->getMessages($channel, $user));
    }

    // Clear Chat 
    #[Route('/chat/clear/global', name: 'chat_clear_global')]
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
}