<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

use App\Repository\MessageRepository;
use App\Entity\Message;

class ChatController extends AbstractController
{
    public function __construct(MessageRepository $messageRepository) {
        $this->messageRepository = $messageRepository;
    }

    private $ignoreList = [
        'favorites', 
        'locations', 
        'friends', 
        'friendRequests', 
        'password',
        'lastname',
        'salt',
        'username',
        'userIdentifier',
        'email',
        'lastActiveAt'
    ];

    #[Route('/chat-admin-add', name: 'chat_admin_add', methods: ['GET', 'POST'])]
    public function addAdminChat(Request $request): Response
    {
        $messageContent = $request->getContent();
        return $this->saveMessage($messageContent);
    }

    #[Route('/chat-add', name: 'chat_add', methods: ['GET', 'POST'])]
    public function addChat(Request $request): Response
    {
        $user = $this->getUser();
        
        if($user) {
            $messageContent = $request->getContent();
            return $this->saveMessage($messageContent, $user);
        } else {
            return new JsonResponse(['error' => 'reload']);
        }
    }

    private function saveMessage($messageContent, $user = null) {
        $message = new Message();
        $message
            ->setMessage($messageContent)
            ->setGlobal(1)
            ->setDateTime(new \DateTime('@'.strtotime('now')))
        ;
        if($user) $message->setSender($user);

        $this->messageRepository->save($message, true);

        $serializer = $this->container->get('serializer');
        
        $return = $serializer->serialize(
            $message, 
            'json', 
            [
                'circular_reference_handler' => function ($object) {return $object->getId(); },
                AbstractNormalizer::IGNORED_ATTRIBUTES => $this->ignoreList
            ]
        );

        return new Response($return);
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
                AbstractNormalizer::IGNORED_ATTRIBUTES => $this->ignoreList
            ]
        );

        return new Response($return);
    }
}