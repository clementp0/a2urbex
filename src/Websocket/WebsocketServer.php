<?php
namespace App\Websocket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use App\Service\WebsocketService;
use App\Service\ChannelService;

class WebsocketServer implements MessageComponentInterface {
    protected $channels = [];

    public function __construct(
        private WebsocketService $websocketService,
        private ChannelService $channelService
    ) {}

    public function onOpen(ConnectionInterface $connection) {
        $this->channels[$connection->resourceId] = new \SplObjectStorage;
    }

    public function onClose(ConnectionInterface $connection) {
        unset($this->channels[$connection->resourceId]);
    }

    public function onMessage(ConnectionInterface $connection, $message) {
        dump($message);
        $token = $connection->httpRequest->getUri()->getQuery();
        $user = $this->websocketService->getUser($token);

        $data = json_decode($message, true);
        $type = $data['type'];
        $message = isset($data['message']) ? $data['message'] : null;
        $channel = $data['channel'];
        $chat = isset($data['chat']) ? $data['chat'] : null;

        if($chat && !$this->channelService->hasChatAccess($chat, $user)) return;
        elseif(!$this->channelService->hasAccess($channel, $user)) return;

        switch ($type) {
            case 'subscribe':
                $this->subscribe($channel, $connection);
                break;

            case 'unsubscribe':
                $this->unsubscribe($channel, $connection);
                break;

            case 'publish':
                if(!$message || !mb_strlen($message)) break;

                $messageData = [
                    'channel' => $channel,
                    'content' => $message
                ];
                if($chat) $messageData['chat'] = $chat;

                $this->publish($channel, json_encode($messageData), $chat);
                break;
        }
    }

    public function onError(ConnectionInterface $connection, \Exception $exception) {
        // Handle errors
    }

    protected function subscribe($channelName, ConnectionInterface $connection) {
        if (!isset($this->channels[$channelName])) {
            $this->channels[$channelName] = new \SplObjectStorage;
        }

        $this->channels[$channelName]->attach($connection);
    }

    protected function unsubscribe($channelName, ConnectionInterface $connection) {
        if (isset($this->channels[$channelName])) {
            $this->channels[$channelName]->detach($connection);
        }
    }

    protected function publish($channelName, $message, $chat) {
        if (isset($this->channels[$channelName])) {
            foreach ($this->channels[$channelName] as $connection) {
                $token = $connection->httpRequest->getUri()->getQuery();
                $user = $this->websocketService->getUser($token);
                if($chat && !$this->channelService->hasChatAccess($chat, $user)) continue;
                
                $connection->send($message);
            }
        }
    }
}