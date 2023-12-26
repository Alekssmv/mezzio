<?php

declare(strict_types=1);

namespace App\Factory;

use App\Handler\GetUniApiKeyHandler;
use Psr\Container\ContainerInterface;
use App\Interfaces\Repository\AccountRepositoryInterface;

class GetUniApiKeyHandlerFactory
{
    public function __invoke(ContainerInterface $container) : GetUniApiKeyHandler
    { 
        dd($container);
        return new GetUniApiKeyHandler(
            $container->get(AmoCRMApiClientFactory::class),
        );
    }
}
