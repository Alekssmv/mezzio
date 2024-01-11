<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\PhpFileProvider;
use Mezzio\Helper\ConfigProvider;

define('ROOT_DIR', __DIR__ . '/..');

/**
 * Имена кастомных полей, которые будут добавлены в элементы массива $contacts перед отправкой в Unisender.
 * Ключ - имя кастомного поля. Значение - ключ, который добавится в элемент $contacts.
 */
define('CUSTOM_FIELD_NAMES', [
    'Телефон' => 'phone',
    'Должность' => 'job_title',
]);

/**
 * Обычные поля, которые будут добавлены в элементы массива $contacts перед отправкой в Unisender
 * Ключ - имя поля. Значение - ключ, который добавится в элемент $contacts.
 */
define('FIELDS', [
    'name' => 'Name',
    'delete' => 'delete',
    'id' => 'id',
]);

/**
 * Поля которые будут содержать множество значений
 * Ключ - имя поля. Значение - ключ, который добавится в элемент $contacts.
 */
define('FIELDS_MULTI_VAL', [
    'Email' => 'email',
]);

/**
 * Обязательные поля, которые должны быть в элементах массива $contacts перед отправкой в Unisender
 */
define('REQ_FIELDS', [
    'email' => 'email'
]);

// To enable or disable caching, set the `ConfigAggregator::ENABLE_CACHE` boolean in
// `config/autoload/local.php`.
$cacheConfig = [
    'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    \Mezzio\Twig\ConfigProvider::class,
    \Mezzio\Tooling\ConfigProvider::class,
    \Mezzio\Helper\ConfigProvider::class,
    \Mezzio\Router\FastRouteRouter\ConfigProvider::class,
    \Laminas\HttpHandlerRunner\ConfigProvider::class,
    // Include cache configuration
    new ArrayProvider($cacheConfig),
    ConfigProvider::class,
    \Mezzio\ConfigProvider::class,
    \Mezzio\Router\ConfigProvider::class,
    \Laminas\Diactoros\ConfigProvider::class,
    // Swoole config to overwrite some services (if installed)
    class_exists(\Mezzio\Swoole\ConfigProvider::class)
    ? \Mezzio\Swoole\ConfigProvider::class
    : function (): array {
        return [];
    },
        // Default App module config
    App\ConfigProvider::class,
    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),
    // Load development config if it exists
    new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
