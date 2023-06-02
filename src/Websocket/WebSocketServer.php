<?php
namespace App\Websocket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use App\Service\WebsocketService;

class WebSocketServer implements MessageComponentInterface {
    protected $channels = [];

    public function __construct(private WebsocketService $websocketService) {}

    public function onOpen(ConnectionInterface $connection) {
        $this->channels[$connection->resourceId] = new \SplObjectStorage;
    }

    public function onClose(ConnectionInterface $connection) {
        unset($this->channels[$connection->resourceId]);
    }

    public function onMessage(ConnectionInterface $connection, $message) {
        $sessionId = $connection->httpRequest->getUri()->getQuery();
        $user = $this->websocketService->getUser($sessionId);
        
        dump($message);
        //return;
        

        // $data = json_decode($message, true);
        // if(!isset($data['type']) || !isset($data['channel'])) return;
        // $type = $data['type'];
        // $channel = $data['channel'];
        
        
        //if(!$this->websocketService->hasAccess($user, $channel))

        // switch ($type) {
        //     case 'subscribe':
        //         $this->subscribe($channel, $connection);
        //         break;

        //     case 'unsubscribe':
        //         $this->unsubscribe($channel, $connection);
        //         break;

        //     case 'publish':
        //         dd('publish');
        //         if(isset($data['message']) && mb_strlen($data['message'])) {
        //             $messageData = [
        //                 'channel' => $channel,
        //                 'content' => $data['message']
        //             ];
        //             $this->publish($channel, json_encode($messageData));
        //         }
        //         break;
        // }
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

    protected function publish($channelName, $message) {
        if (isset($this->channels[$channelName])) {
            foreach ($this->channels[$channelName] as $connection) {
                $connection->send($message);
            }
        }
    }
}