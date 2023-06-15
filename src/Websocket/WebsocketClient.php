<?php

namespace App\Websocket;

use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use React\EventLoop\Factory;
use App\Service\WebsocketService;

class WebsocketClient {

    public function __construct(WebsocketService $websocketService) {
        $this->url = $_ENV['WEBSOCKET_URL'].'?'.$websocketService->getServerToken();
    }

    public function sendEvent($channel, $value, $chat = null) {
        $loop = Factory::create();
        $reactConnector = new \React\Socket\Connector($loop, ['tls' => ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);
        $connector = new Connector($loop, $reactConnector);

        $connector($this->url, [], ['Origin' => 'http://localhost'])
            ->then(function (WebSocket $conn) use ($channel, $value, $chat) {
                $object = [
                    'type' => 'publish',
                    'channel' => $channel,
                    'message' => $value
                ];
                if($chat !== null) $object['chat'] = $chat;

                $conn->send(json_encode($object));
                $conn->close();
            }, function (Exception $e) use ($loop) {
                echo "Could not connect: {$e->getMessage()}\n";
                $loop->stop();
            })
        ;
        $loop->run();
    }
}