<?php

declare(strict_types=1);

namespace App;

use App\Client\ApiClient;
use App\Client\ApiClientFactory;
use App\Client\HttpClient;
use App\Client\HttpClientFactory;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'aliases' => [
                Interfaces\ContactsServiceInterface::class => Services\ContactsService::class,
            ],
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,

            ],
            'factories' => [
                Factory\AmoCRMApiClientFactory::class => Factory\AmoCRMApiClientFactory::class,
                Factory\UnisenderApiClientFactory::class => Factory\UnisenderApiClientFactory::class,
                Handler\HomePageHandler::class => Factory\HomePageHandlerFactory::class,
                Handler\SumHandler::class => Factory\SumHandlerFactory::class,
                Handler\GetTokenHandler::class => Factory\GetTokenHandlerFactory::class,
                Handler\RedirectUriHandler::class => Factory\RedirectUriHandlerFactory::class,
                Handler\GetContactsHandler::class => Factory\GetContactsHandlerFactory::class,
                Handler\UnisenderContactHandler::class => Factory\UnisenderContactHandlerFactory::class,
                Handler\SendContactsToUnisenderHandler::class => Factory\SendContactsToUnisenderHandlerFactory::class,
                Services\ContactsService::class => Factory\ContactsServiceFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app' => ['templates/app'],
                'error' => ['templates/error'],
                'layout' => ['templates/layout'],
            ],
        ];
    }
}
