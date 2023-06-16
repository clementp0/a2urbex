<?php

namespace App\Service;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

use App\Repository\MessageRepository;
use App\Entity\Message;
use App\Entity\Chat;
use App\Repository\ChatRepository;
use App\Service\ChannelService;
use App\Websocket\WebsocketClient;

class ChatService {
    public function __construct(
        private MessageRepository $messageRepository,
        private ChatRepository $chatRepository,
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

        $chatChannel = $_ENV['CHAT_CHANNEL'];
        $this->websocketClient->sendEvent($chatChannel, json_decode($this->serialize($message)), json_decode($this->serialize($chat)));

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

    public function createChat($users, $multi = false) {
        $chatName = Uuid::v4()->toBase32();

        $chat = new Chat();
        $chat->setName($chatName);
        foreach($users as $user) $chat->addUser($user);
        if($multi === true || count($users) > 2) $chat->setMulti(true);

        $this->chatRepository->save($chat, true);

        return $chatName;
    }

    public function getUserChat($user1, $user2) {
        $chat = $this->chatRepository->findOneBy2User($user1, $user2);
        if(!$chat) return $this->createChat([$user1, $user2]);
        else return $chat->getName();
    }

    public function getChats($user) {
        $chats = $user->getChats();
        foreach($chats as $k => $chat) {
            $chat->lastMessage = $chat->getMessages()->last();
            if(!$chat->lastMessage) {
                unset($chats[$k]);
                continue;
            }

            if(!$chat->getTitle()) {
                $title = '';
                if($chat->getUsers()->count() === 2) {
                    foreach($chat->getUsers() as $u) {
                        if($u === $user) {
                            continue;
                        } else {
                            $title = $u->getFirstname().'#'.$u->getId();
                            $chat->user = $u;
                            break;
                        }
                    }
                } else {
                    $names = [];
                    foreach($chat->getUsers() as $u) {
                        $names[] =  $u->getFirstname();
                    }
                    $title = implode(', ', $names);
                }
                $chat->setTitle($title);
            }
        }

        $chats = $chats->toArray();
        usort($chats, [$this, 'sortByDate']);
        $chats = array_reverse($chats);
        
        return $this->serialize($chats);
    }

    private function sortByDate($a, $b) {
        $dateA = strtotime($a->lastMessage->getDatetime()->format('Y-m-d H:i:s'));
        $dateB = strtotime($b->lastMessage->getDatetime()->format('Y-m-d H:i:s'));
        return $dateA < $dateB ? -1 : 1;
    }
}