<?php

require_once('autoload.php');

use Framework\Cli\CreateApplicationCommand;
use Symfony\Component\Console\Application;

$app = new Application('bun', '0.1');
$app->add(new CreateApplicationCommand());
$app->run();