<?php

use Symfony\Component\Dotenv\Dotenv;

include_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

include_once __DIR__ . '/../helpers/token_actions.php';