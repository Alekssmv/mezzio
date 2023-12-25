<?php

include __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/config/bootstrap.php';

use \Phpmig\Adapter;
use Illuminate\Database\Capsule\Manager as Capsule;
use function Laminas\Stratigility\host;

$container = new ArrayObject();

// replace this with a better Phpmig\Adapter\AdapterInterface
$container['phpmig.adapter'] = new Adapter\File\Flat(__DIR__ . DIRECTORY_SEPARATOR . 'migrations/.migrations.log');
$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

$conf = require __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'autoload' . DIRECTORY_SEPARATOR . 'database.global.php';
$conf['database']['host'] = '127.0.0.1';

$container['config'] = $conf['database'];

$container['db'] = function ($c) {
    $capsule = new Capsule();
    $capsule->addConnection($c['config']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
};

// You can also provide an array of migration files
// $container['phpmig.migrations'] = array_merge(
//     glob('migrations_1/*.php'),
//     glob('migrations_2/*.php')
// );

return $container;