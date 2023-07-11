<?php

namespace App\Service;

use App\Repository\ChannelRepository;
use App\Repository\ChatRepository;

class ChannelService {
    public function __construct(
        private ChannelRepository $channelRepository,
        private ChatRepository $chatRepository,
    ) {}

    public function hasAccess($channelName, $user = null) {
        $channel = $this->get($channelName);

        return $channel && ((
            !$channel->getRole() 
            && ($channel->getUsers()->isEmpty() || $channel->getUsers()->contains($user))
        ) || (
            $channel->getRole() && $user
            && ($user->hasRole('ROLE_SERVER') || $user->hasRole($channel->getRole()))
        ));
    }

    public function get($channelName) {
        return $this->channelRepository->findOneBy(['name' => $channelName]);
    }

    public function hasChatAccess($chatName, $user, $op = false) {
        $chatChannel = $_ENV['CHAT_CHANNEL'];
        if(!$this->hasAccess($chatChannel, $user)) return false;

        $chat = $this->getChat($chatName);
        if(!$op) return $chat && ($chat->getChatUsers()->isEmpty() || $this->chatRepository->containUser($chat, $user));
        else return $chat && !$chat->getChatUsers()->isEmpty() && $this->chatRepository->containUser($chat, $user, $op);
    }

    public function getChat($chatName) {
        return $this->chatRepository->findOneBy(['name' => $chatName]);
    }
}