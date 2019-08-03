<?php

namespace App;

use Symfony\Component\Console\Application;

class Cli extends Application
{
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->addCommands([
            new Commands\Discover('discover'),
        ]);
    }
}
