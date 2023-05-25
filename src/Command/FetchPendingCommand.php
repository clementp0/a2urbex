<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class FetchPendingCommand extends Command
{
    protected static $defaultName = "run:fetch-pending";

    public function __construct(private ContainerInterface $container, private RouterInterface $router)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $controller = $this->container->get('App\Controller\FetchController');
        $response = $controller->fetchWikimapiaPending();
        
        return Command::SUCCESS;
    }
}
