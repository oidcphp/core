<?php

namespace App\Commands;

use OpenIDConnect\Core\Issuer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Discover extends Command
{
    protected function configure()
    {
        parent::configure();

        $this->setDescription('Discover the OIDC provider')
            ->addOption('format', '-f', InputOption::VALUE_REQUIRED, 'dump / json / yaml', 'dump')
            ->addOption('type', '-t', InputOption::VALUE_REQUIRED, 'all / meta / jwks', 'all')
            ->addArgument('url', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        $format = $input->getOption('format');
        $type = $input->getOption('type');

        $config = Issuer::create($url)->discover();

        // TODO
        return 0;
    }
}
