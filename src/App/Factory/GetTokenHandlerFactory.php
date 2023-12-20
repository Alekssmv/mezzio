<?php

declare(strict_types=1);

namespace App\Factory;

use AmoCRM\Client\AmoCRMApiClient;
use Psr\Container\ContainerInterface;
use App\Handler\GetTokenHandler;

class GetTokenHandlerFactory
{
    public function __invoke(ContainerInterface $container) : GetTokenHandler
    { 
        $apiClient = new AmoCRMApiClient(
            $_ENV["AMO_CLIENT_ID"],
            $_ENV["AMO_CLIENT_SECRET"],
            $_ENV["AMO_REDIRECT_URI"],
        );
        return new GetTokenHandler($apiClient);
    }
}
