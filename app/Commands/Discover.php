<?php

namespace App\Commands;

use OpenIDConnect\Issuer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

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

        $config = Issuer::discover($url, true);

        switch ($type) {
            case 'meta':
                $config = $config[0];
                break;
            case 'jwks':
                $config = $config[1];
                break;
            case 'all':
            default:
                // Nothing to do
        }

        switch ($format) {
            case 'json':
                $output->writeln(json_encode($config, JSON_PRETTY_PRINT));
                break;
            case 'yaml':
                $output->writeln(Yaml::dump($config, 4, 2));
                break;
            case 'dump':
            default:
                dump($config);
        }

        return 0;
    }
}
