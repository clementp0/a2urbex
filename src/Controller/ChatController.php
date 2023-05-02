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

    #[Route('/chat-add', name: 'save_chat_history', methods: ['GET', 'POST'])]
    public function saveChatHistory(Request $request, MessageRepository $messageRepository): Response
    {
        $user = $this->getUser();
        if($user) {
            $messageContent = $request->getContent();

            $message = new Message();
            $message
                ->setSender($user)
                ->setMessage($messageContent)
                ->setGlobal(1)
                ->setDateTime(new \DateTime('@'.strtotime('now')))
            ;

            $messageRepository->save($message, true);

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
                AbstractNormalizer::IGNORED_ATTRIBUTES => $this->ignoreList
            ]
        );

        return new Response($return);
    }
}