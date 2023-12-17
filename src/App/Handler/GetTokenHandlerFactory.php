<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use App\Handler\GetTokenHandler;

class GetTokenHandlerFactory
{
    public function __invoke(ContainerInterface $container) : GetTokenHandler
    { 
        $apiClient = $container->get(ApiClient::class);
        $httpClient = $container->get(HttpClient::class);
        
        return new GetTokenHandler($apiClient, $httpClient);
    }
}
