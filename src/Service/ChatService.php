<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

use App\Repository\MessageRepository;
use App\Entity\Message;
use App\Service\ChannelService;
use App\Websocket\WebsocketClient;

class ChatService {
    public function __construct(
        private MessageRepository $messageRepository,
        private SerializerInterface $serializer,
        private ChannelService $channelService,
        private WebsocketClient $websocketClient
    ) {}

    public function saveMessage($chatName, $messageContent, $sender = null, $server = false) {
        if($server === false && !$this->channelService->hasChatAccess($chatName, $sender)) return;
        if(!mb_strlen($messageContent)) return;
        
        $chat = $this->channelService->getChat($chatName);
        
        $message = new Message();
        $message
            ->setChat($chat)
            ->setMessage($messageContent)
            ->setDateTime(new \DateTime('@'.strtotime('now')))
        ;
        if($sender) $message->setSender($sender);
        
        $this->messageRepository->save($message, true);

        $json = $this->serialize($message);
        $chatChannel = $_ENV['CHAT_CHANNEL'];
        $this->websocketClient->sendEvent($chatChannel, $json, $chatName);

        return true;
    }

    public function getMessages($chatName, $user) {
        if(!$this->channelService->hasChatAccess($chatName, $user)) return;

        $chat = $this->channelService->getChat($chatName);

        return $this->serialize($chat->getMessages());
    }

    private function serialize($data) {
        return $this->serializer->serialize($data, 'json', ['groups' => ['chat']]);
    }
}