<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

use App\Repository\MessageRepository;
use App\Entity\Message;

class MessageService {
    public function __construct(MessageRepository $messageRepository, SerializerInterface $serializer) {
        $this->messageRepository = $messageRepository;
        $this->serializer = $serializer;
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

    public function saveMessage($messageContent, $sender = null, $receiver = null) {
        $message = new Message();
        $message
            ->setMessage($messageContent)
            ->setGlobal(1)
            ->setDateTime(new \DateTime('@'.strtotime('now')))
        ;
        if($sender) $message->setSender($sender);
        if($receiver) $message->setReceiver($receiver);

        $this->messageRepository->save($message, true);
        
        return $this->serializer->serialize(
            $message, 
            'json', 
            [
                'circular_reference_handler' => function ($object) {return $object->getId(); },
                AbstractNormalizer::IGNORED_ATTRIBUTES => $this->ignoreList
            ]
        );
    }

    public function getMessages($sender = null, $receiver = null) {
        if($sender === null && $receiver === null) {
            $messages = $this->messageRepository->getGlobalChat();
        } else {
            $messages = $this->messageRepository->getChat($sender, $receiver);
            dd($messages);
        }

        return $this->serializer->serialize(
            $messages, 
            'json', 
            [
                'circular_reference_handler' => function ($object) {return $object->getId(); },
                AbstractNormalizer::IGNORED_ATTRIBUTES => $this->ignoreList
            ]
        );
    }
}