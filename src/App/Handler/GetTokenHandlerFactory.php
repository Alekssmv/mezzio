<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Psr\Container\ContainerInterface;
use App\Handler\GetTokenHandler;
use GuzzleHttp\Client;

class GetTokenHandlerFactory
{
    public function __invoke(ContainerInterface $container) : GetTokenHandler
    { 
        $apiClient = new AmoCRMApiClient(
            $_ENV["AMO_CLIENT_ID"],
            $_ENV["AMO_CLIENT_SECRET"],
            $_ENV["AMO_REDIRECT_URI"],
        );
        $httpClient = new Client();
        
        return new GetTokenHandler($apiClient, $httpClient);
    }
}
