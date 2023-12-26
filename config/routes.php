<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

/**
 * FastRoute route configuration
 *
 * @see https://github.com/nikic/FastRoute
 *
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/{id:\d+}', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */
return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/', App\Handler\HomePageHandler::class, 'home');
    $app->get('/redirect-uri', App\Handler\RedirectUriHandler::class, 'redirect-uri');
    $apiPrefix = '/api/v1';
    $app->get($apiPrefix . '/ping', App\Handler\PingHandler::class, 'api.ping');
    $app->get($apiPrefix . '/sum/{a:\d+}/{b:\d+}', App\Handler\SumHandler::class, 'api.sum');
    $app->get($apiPrefix . '/token', App\Handler\GetTokenHandler::class, 'api.token');
    $app->get($apiPrefix . '/contacts', App\Handler\GetContactsHandler::class, 'api.contacts');
    $app->get($apiPrefix . '/unisender-contact', App\Handler\UnisenderContactHandler::class, 'api.unisender-contact');
    $app->get($apiPrefix . '/send-contacts-to-unisender', App\Handler\SendContactsToUnisenderHandler::class, 'api.send-contacts-to-unisender');
    $app->get($apiPrefix . '/uni-api-key', App\Handler\GetUniApiKeyHandler::class, 'api.uni-api-key');
};
