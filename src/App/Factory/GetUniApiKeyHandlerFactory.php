<?php

declare(strict_types=1);

namespace App\Factory;

use App\Handler\GetUniApiKeyHandler;
use Psr\Container\ContainerInterface;

class GetUniApiKeyHandlerFactory
{
    public function __invoke(ContainerInterface $container) : GetUniApiKeyHandler
    { 
        return new GetUniApiKeyHandler(
            $container->get(AmoCRMApiClientFactory::class),
        );
    }
}
