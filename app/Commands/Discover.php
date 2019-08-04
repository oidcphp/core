<?php

namespace App\Commands;

use OpenIDConnect\Discoverer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Discover extends Command
{
    protected function configure()
    {
        parent::configure();

        $this->setDescription('Discover the OIDC provider')
            ->addArgument('url', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');

        $config = (new Discoverer())->discover($url);

        dump($config);

        $jwks = (new Discoverer())->keystore($config->jwksUri());

        dump($jwks);

        return 0;
    }
}
