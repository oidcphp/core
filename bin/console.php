<?php

// Import Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Run application
$app = new App\Cli();
$app->run();
