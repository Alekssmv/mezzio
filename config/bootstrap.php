<?php

include_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

define('ROOT_DIR', __DIR__ . '/..');

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');
