<?php

namespace App\Service;

use App\Websocket\WebsocketClient;

class WebsocketEventService {
    public function __construct(
        private WebsocketClient $websocketClient
    ) {}

    public function sendAdminProgress($type, $percent, $text = '') {
        $this->websocketClient->sendEvent('admin_progress', [
            'type' => $type,
            'percent' => $percent,
            'text' => $text
        ]);
    }
}