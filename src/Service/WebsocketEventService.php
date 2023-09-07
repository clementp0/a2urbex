<?php

namespace App\Service;

use App\Websocket\WebsocketClient;

class WebsocketEventService {
    public function __construct(
        private WebsocketClient $websocketClient
    ) {}

    public function sendAdminProgress($type, $percent, $params = []) {
        $data = array_merge([
            'type' => $type,
            'percent' => $percent
        ], $params);

        $this->websocketClient->sendEvent('admin_progress', $data);
    }
}