<?php
require __DIR__ . DIRECTORY_SEPARATOR.'../vendor/autoload.php';

$app = new \Symfony\Component\Console\Application();

$app->add(new \FrpRiddimDispatcher\Command\DispatchCommand());

$app->run();
