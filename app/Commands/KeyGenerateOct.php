<?php

namespace App\Commands;

use Jose\Component\KeyManagement\JWKFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see https://web-token.spomky-labs.com/components/key-jwk-and-key-set-jwkset/key-management#generate-a-new-key
 */
class KeyGenerateOct extends Command
{
    protected function configure()
    {
        parent::configure();

        $this->setDescription('Generate oct key')
            ->addOption('size', null, InputOption::VALUE_REQUIRED, 'Key size')
            ->addOption('secret', null, InputOption::VALUE_REQUIRED, 'Secret');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $size = $input->getOption('size');
        $secret = $input->getOption('secret');

        if (null === $size && null === $secret) {
            $output->writeln('Must supply one option');
            return 1;
        }

        if (null !== $size && null !== $secret) {
            $output->writeln('Only supply one option');
            return 1;
        }

        if ($size) {
            $key = JWKFactory::createOctKey((int)$size, [
                'alg' => 'HS256',
                'use' => 'sig',
            ]);
        }

        if ($secret) {
            $key = JWKFactory::createFromSecret($secret, [
                'alg' => 'HS256',
                'use' => 'sig',
            ]);
        }

        $output->writeln(json_encode($key->all(), JSON_PRETTY_PRINT));

        return 0;
    }
}
