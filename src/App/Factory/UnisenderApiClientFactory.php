<?php

declare(strict_types=1);

namespace App\Factory;

use Psr\Container\ContainerInterface;
use Unisender\ApiWrapper\UnisenderApi;

/**
 * Фабрика для создания экземпляра клиента для работы с API Unisender
 */
class UnisenderApiClientFactory
{
    public function __invoke(ContainerInterface $container) : UnisenderApi
    { 
        $uniSenderApiClient = new UnisenderApi($_ENV["UNISENDER_API_KEY"]);
        return $uniSenderApiClient;
    }
}
