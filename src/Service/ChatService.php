<?php

namespace App\Service;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

use App\Repository\MessageRepository;
use App\Entity\Message;
use App\Entity\Chat;
use App\Entity\ChatUser;
use App\Repository\ChatRepository;
use App\Repository\ChatUserRepository;
use App\Service\ChannelService;
use App\Websocket\WebsocketClient;

class ChatService {
    public function __construct(
        private MessageRepository $messageRepository,
        private ChatRepository $chatRepository,
        private ChatUserRepository $chatUserRepository,
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
            ->setValue($messageContent)
            ->setDateTime(new \DateTime('@'.strtotime('now')))
        ;

        if($sender) {
            $message->setSender($sender);
            $chatUser = $this->chatUserRepository->findOneBy(['chat' => $chat, 'user' => $sender]);
            $message->setChatUser($chatUser);
        }
        
        $this->messageRepository->save($message, true);
        
        if($sender && !$chat->isMulti() && $chat->getMessages()->count() === 1) {
            $this->formatChat($chat, $sender, true);
        } else {
            $this->formatChat($chat);
        }
        
        $chatChannel = $_ENV['CHAT_CHANNEL'];
        $this->websocketClient->sendEvent(
            $chatChannel, 
            json_decode($this->serialize($message)), 
            json_decode($this->serialize($chat))
        );

        return true;
    }

    public function getMessages($chatName, $user) {
        if(!$this->channelService->hasChatAccess($chatName, $user)) return;

        $chat = $this->channelService->getChat($chatName);

        return $this->serialize($chat->getMessages());
    }

    public function createChat($users, $multi = false, $admin = null) {
        $chatName = Uuid::v4()->toBase32();

        $chat = new Chat();
        $chat->setName($chatName);
        foreach($users as $user) {
            $chatUser = new ChatUser();
            $chatUser->setUser($user);
            if($admin && $admin === $user) $chatUser->setOp(true);
            $chat->addChatUser($chatUser);
        } 

        if($multi === true || count($users) > 2) $chat->setMulti(true);
        
        $this->chatRepository->save($chat, true);

        return $chat;
    }

    public function getUserChat($user1, $user2) {
        $chat = $this->chatRepository->findOneBy2User($user1, $user2);
        if(!$chat) $chat = $this->createChat([$user1, $user2]);
        $this->formatChat($chat, $user1);

        return $this->serialize($chat);
    }

    public function getChats($user) {
        $chats = $this->chatRepository->findByUser($user);
        $chats[] = $this->chatRepository->findOneBy(['name' => $_ENV['CHAT_CHANNEL_GLOBAL']]);

        foreach($chats as $k => $chat) {
            $chat->lastMessage = $chat->getMessages()->last();
            if(!$chat->lastMessage) {
                unset($chats[$k]);
                continue;
            }

            $this->formatChat($chat, $user);
        }

        usort($chats, [$this, 'sortByDate']);
        $chats = array_reverse($chats);
        
        return $this->serialize($chats);
    }

    public function getInfo($chatName, $user) {
        if(!$this->channelService->hasChatAccess($chatName, $user)) return;

        $chat = $this->channelService->getChat($chatName);
        return $this->serialize($chat, ['chatInfo']);
    }

    public function addChatUser($chatName, $user, $newUser) {
        if(!$newUser || !$this->channelService->hasChatAccess($chatName, $user)) return;

        $chat = $this->channelService->getChat($chatName);
        if($this->chatUserRepository->findOneBy(['chat' => $chat, 'user' => $newUser])) return;
        
        $chatUser = new ChatUser();
        $chatUser->setUser($newUser)->setChat($chat);

        $this->chatUserRepository->save($chatUser, true);

        return $this->serialize($chatUser, ['chatInfo']);
    }

    private function formatChat($chat, $user = null, $invert = false) {
        $users = $this->chatRepository->findUsers($chat);

        if(!$chat->getTitle()) {
            $names = [];
            foreach($users as $u) {
                if(
                    ($invert === false && $u !== $user) 
                    || ($invert === true && $u === $user)
                ) {
                    $names[] = $u->getUsername();
                }
                if(!$chat->isMulti() && count($names)) break;
            }
            $chat->setTitle(implode(', ', $names));
        }

        if(!$chat->getImage() && !$chat->isMulti()) {
            foreach($users as $u) {
                if(
                    ($invert === false && $u !== $user) 
                    || ($invert === true && $u === $user)
                ) {
                    $chat->setImage($u->getImage());
                    break;
                }
            }
        }
    }

    private function serialize($data, $extra = []) {
        return $this->serializer->serialize($data, 'json', ['groups' => ['chat', ...$extra]]);
    }

    private function sortByDate($a, $b) {
        $dateA = strtotime($a->lastMessage->getDatetime()->format('Y-m-d H:i:s'));
        $dateB = strtotime($b->lastMessage->getDatetime()->format('Y-m-d H:i:s'));
        return $dateA < $dateB ? -1 : 1;
    }
}