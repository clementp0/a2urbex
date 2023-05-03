<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

use App\Repository\MessageRepository;
use App\Service\MessageService;

class ChatController extends AbstractController
{
    public function __construct(MessageService $messageService) {
        $this->messageService = $messageService;
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
            return new Response($this->messageService->saveMessage($messageContent, $user));
        } else {
            return new JsonResponse(['error' => 'reload']);
        }
    }

    #[Route('/chat-get', name: 'chat_history', methods: ['GET', 'POST'])]
    public function getChatHistory(MessageRepository $messageRepository): Response
    {
        $messages = $messageRepository->findBy(['global' => 1]);

        $serializer = $this->container->get('serializer');
        $return = $serializer->serialize(
            $messages, 
            'json', 
            [
                'circular_reference_handler' => function ($object) {return $object->getId(); },
                AbstractNormalizer::IGNORED_ATTRIBUTES => $this->messageService->ignoreList
            ]
        );

        return new Response($return);
    }
}