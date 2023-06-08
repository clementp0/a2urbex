<?php

namespace App\Service;

use App\Repository\ChannelRepository;

class ChannelService {
    public function __construct(private ChannelRepository $channelRepository) {}

    public function hasAccess($channelName, $user = null) {
        $channel = $this->get($channelName);

        return $channel && ((
            !$channel->getRole() 
            && ($channel->getUsers()->count() === 0 || $channel->getUsers()->contains($user))
        ) || (
            $channel->getRole() && $user
            && ($user->hasRole('ROLE_SERVER') || $user->hasRole($channel->getRole()))
        ));
    }

    public function get($channelName) {
        return $this->channelRepository->findOneBy(['name' => $channelName]);
    }
}