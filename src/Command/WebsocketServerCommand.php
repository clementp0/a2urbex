<?php
namespace App\Command;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use App\Websocket\WebSocketServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\WebsocketService;

class WebsocketServerCommand extends Command
{
    public function __construct(private WebsocketService $websocketService) {
        parent::__construct();
    }

    protected static $defaultName = "run:websocket-server";

    protected function execute(InputInterface $input, OutputInterface $output) {
        $port = (int)$_ENV['WESOCKET_LOCAL_PORT'];
        $ip = $_ENV['WESOCKET_LOCAL_IP'];

        $output->writeln("Starting server on port " . $port);
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketServer($this->websocketService)
                )
            ),
            $port,
            $ip
        );
        $server->run();
    }

}