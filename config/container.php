<?php

declare(strict_types=1);

use Laminas\ServiceManager\ServiceManager;
use Dotenv\Dotenv;

// Load configuration
$config = require __DIR__ . '/config.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$dependencies                       = $config['dependencies'];
$dependencies['services']['config'] = $config;

// Build container
return new ServiceManager($dependencies);
