<?php

namespace App;

use Jose\Component\Console\EcKeyGeneratorCommand;
use Jose\Component\Console\OctKeyGeneratorCommand;
use Jose\Component\Console\PublicKeyCommand;
use Jose\Component\Console\RsaKeyGeneratorCommand;
use Jose\Component\Core\Converter\StandardConverter;
use Symfony\Component\Console\Application;

class Cli extends Application
{
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $jsonConverter = new StandardConverter();

        $this->addCommands([
            new Commands\Discover('discover'),
            new EcKeyGeneratorCommand($jsonConverter),
            new OctKeyGeneratorCommand($jsonConverter),
            new PublicKeyCommand($jsonConverter),
            new RsaKeyGeneratorCommand($jsonConverter),
        ]);
    }
}
