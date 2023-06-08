<?php

namespace App\Service;

use App\Repository\ChannelRepository;

class ChannelService {
    public function __construct(private ChannelRepository $channelRepository) {}

    public function hasAccess($channelName, $user) {
        $channel = $this->channelRepository->findOneBy(['name' => $channelName]);
        if(!$channel) return false;

        if(!$channel->getRole()) {
            return true;
        } elseif($user & ($user->hasRole('ROLE_SERVER') || $user->hasRole($channel->getRole()))) {
            return true;
        }
        return false;
    }
}