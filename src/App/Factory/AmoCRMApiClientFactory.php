<?php

declare(strict_types=1);

namespace App\Factory;

use AmoCRM\Client\AmoCRMApiClient;
use Psr\Container\ContainerInterface;

/**
 * Фабрика для создания экземпляра клиента для работы с API AmoCRM
 */
class AmoCRMApiClientFactory
{
    public function __invoke(ContainerInterface $container) : AmoCRMApiClient
    { 
        $apiClient = new AmoCRMApiClient(
            $_ENV["AMO_CLIENT_ID"],
            $_ENV["AMO_CLIENT_SECRET"],
            $_ENV["AMO_REDIRECT_URI"],
        );
        return $apiClient;
    }
}
