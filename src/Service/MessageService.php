<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

use App\Repository\MessageRepository;
use App\Entity\Message;
use App\Service\ChannelService;
use App\Websocket\WebsocketClient;

class MessageService {
    public function __construct(
        private MessageRepository $messageRepository,
        private SerializerInterface $serializer,
        private ChannelService $channelService,
        private WebsocketClient $websocketClient
    ) {}

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
        'lastActiveAt',
        'websocketToken',
        'channel',
        'channels'
    ];

    public function saveMessage($channelName, $messageContent, $sender = null, $server = false) { // rework
        if($server === false && !$sender) return;
        if($server === false && !$this->channelService->hasAccess($channelName, $sender)) return;
        if(!mb_strlen($messageContent)) return;
        
        $channel = $this->channelService->get($channelName);

        $message = new Message();
        $message
            ->setChannel($channel)
            ->setMessage($messageContent)
            ->setDateTime(new \DateTime('@'.strtotime('now')))
        ;
        if($sender) $message->setSender($sender);

        $this->messageRepository->save($message, true);

        $json = $this->serialize($message);
        $this->websocketClient->sendEvent($channelName, $json);

        return true;
    }

    public function getMessages($channelName, $user) { // rework
        if(!$this->channelService->hasAccess($channelName, $user)) return;

        $channel = $this->channelService->get($channelName);
        $messages = $this->messageRepository->getChat($channel);

        return $this->serialize($messages);
    }

    private function serialize($data) {
        return $this->serializer->serialize(
            $data, 
            'json', 
            [
                'circular_reference_handler' => function ($object) {return $object->getId(); },
                AbstractNormalizer::IGNORED_ATTRIBUTES => $this->ignoreList
            ]
        );
    }
}