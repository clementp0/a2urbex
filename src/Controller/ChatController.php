<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Service\MessageService;

class ChatController extends AbstractController
{
    public function __construct(MessageService $messageService, UserRepository $userRepository) {
        $this->messageService = $messageService;
        $this->userRepository = $userRepository;
    }

    #[Route('/chat-admin-add', name: 'chat_admin_add', methods: ['GET', 'POST'])]
    public function addAdminChat(Request $request): Response
    {
        $messageContent = $request->getContent();
        return new Response($this->messageService->saveMessage($messageContent));
    }

    #[Route('/chat-add', name: 'chat_add', methods: ['GET', 'POST'])]
    public function addChat(Request $request): Response
    {
        $messageContent = $request->getContent();
        if(!strlen($messageContent)) return new JsonResponse(['error' => 'invalid string']);

        $user = $this->getUser();
        if($user) {
            $messageContent = $request->getContent();
            return new Response($this->messageService->saveMessage($messageContent, $user));
        } else {
            return new JsonResponse(['error' => 'reload']);
        }
    }


    #[Route('/chat-add/{id}', name: 'chat_add_user', methods: ['GET', 'POST'])]
    public function addChatUser(Request $request, $id): Response
    {
        $messageContent = $request->getContent();
        if(!strlen($messageContent)) return new JsonResponse(['error' => 'invalid string']);
        
        $sender = $this->getUser();
        if($sender) {
            $receiver = $this->userRepository->find($id);
            if($receiver) {
                return new Response($this->messageService->saveMessage($messageContent, $sender, $receiver));
            } else {
                return new JsonResponse(['error' => 'User doesn\'t exist']);
            }
        } else {
            return new JsonResponse(['error' => 'reload']);
        }
    }

    #[Route('/chat-get', name: 'chat_history', methods: ['GET', 'POST'])]
    public function getChatHistory(MessageRepository $messageRepository): Response
    {
        return new Response($this->messageService->getMessages());
    }

    #[Route('/chat-get/{id}', name: 'chat_history_user', methods: ['GET', 'POST'])]
    public function getChatHistoryUser(MessageRepository $messageRepository, $id): Response
    {
        $sender = $this->getUser();
        if($sender) {
            $receiver = $this->userRepository->find($id);
            if($receiver && $sender !== $receiver) {
                return new Response($this->messageService->getMessages($sender, $receiver));
            } else {
                return new JsonResponse(['error' => 'User doesn\'t exist']);
            }
        } else {
            return new JsonResponse(['error' => 'reload']);
        }
        dd($receiver);
    }
}