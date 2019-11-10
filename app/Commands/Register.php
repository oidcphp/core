<?php

namespace App\Commands;

use GuzzleHttp\Exception\RequestException;
use OpenIDConnect\Core\Issuer;
use OpenIDConnect\OAuth2\Metadata\ClientMetadata;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Register extends Command
{
    protected function configure()
    {
        parent::configure();

        $this->setDescription('Register the OIDC client')
            ->addOption('contacts', null, InputOption::VALUE_IS_ARRAY, 'redirect_uris')
            ->addOption('redirect-uris', null, InputOption::VALUE_IS_ARRAY, 'redirect_uris')
            ->addArgument('url', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        $contacts = $input->getOption('contacts');
        $redirectUris = $input->getOption('redirect-uris');

        $clientMetadata = new ClientMetadata([
            'contacts' => $contacts,
            'redirect_uris' => $redirectUris,
        ]);

        try {
            $clientRegistration = Issuer::create($url)->register($clientMetadata);
        } catch (RequestException $e) {
            die((string)$e->getResponse()->getBody());
        }

        dump($clientRegistration->toArray());

        return 0;
    }
}
