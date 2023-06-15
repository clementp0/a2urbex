<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\ChatService;
use App\Service\ChannelService;

class ChatController extends AppController
{
    public function __construct(
        private ChatService $chatService, 
        private MessageRepository $messageRepository, 
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
    public function clearChat() { // rework
        $global = $_ENV['CHAT_CHANNEL_GLOBAL'];
        $chat = $this->channelService->getChat($global);

        $this->messageRepository->clearChat($chat->getId());
        $this->chatService->saveMessage($global, 'WELCOME TO A2URBEX', null, true);
        return $this->redirect('/admin');
    }

    private function chatReturn($success) {
        return new JsonResponse(['success' => $success ? true : false]);
    }
}