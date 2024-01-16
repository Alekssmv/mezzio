<?php

use Symfony\Component\Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

$config = require __DIR__ . '/autoload/database.global.php';

/**
 * Добавляем capsule в bootstrap, чтобы можно было использовать модели таблиц бд
 */
$capsule = new Capsule();
$capsule->addConnection($config['database']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
