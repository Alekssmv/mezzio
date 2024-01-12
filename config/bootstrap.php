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

// /**
//  * Создаем очередь
//  */
// $queue = Pheanstalk::create('103.106.2.148', 11300);

// /**
//  * Кладем в очередь задачу на вывод текущего времени
//  */
// $queue->useTube('times')->put(json_encode('now'));

// /**
//  * Кладем в очередь задачу на вывод текущего времени на момент создания задачи
//  */
// $queue->useTube('times')->put(json_encode(time()));
