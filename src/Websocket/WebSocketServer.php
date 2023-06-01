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
        dd($user);

        $data = json_decode($message, true);

        switch ($data['type']) {
            case 'subscribe':
                $channelName = $data['channel'];
                $this->subscribe($channelName, $connection);
                break;

            case 'unsubscribe':
                $channelName = $data['channel'];
                $this->unsubscribe($channelName, $connection);
                break;

            case 'publish':
                $channelName = $data['channel'];
                $messageData = [
                    'channel' => $channelName,
                    'content' => $data['message']
                ];

                $this->publish($channelName, json_encode($messageData));
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

    protected function publish($channelName, $message) {
        if (isset($this->channels[$channelName])) {
            foreach ($this->channels[$channelName] as $connection) {
                $connection->send($message);
            }
        }
    }

    public function getCurrentUser() {
        $token = $this->tokenStorage->getToken();

        if ($token) {
            $user = $token->getUser();
            dd($user);

            if ($user instanceof \Symfony\Component\Security\Core\User\UserInterface) {
                return $user;
            }
        }

        return null;
    }
}