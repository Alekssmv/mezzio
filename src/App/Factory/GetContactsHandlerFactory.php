<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\AmoCRMApiClientFactory;
use App\Handler\GetContactsHandler;
use Psr\Container\ContainerInterface;

class GetContactsHandlerFactory
{
    public function __invoke(ContainerInterface $container): GetContactsHandler
    {
        return new GetContactsHandler($container->get(AmoCRMApiClientFactory::class));
    }
}
