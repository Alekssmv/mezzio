<?php

declare(strict_types=1);

namespace App\Factory;

use App\Handler\UnisenderContactHandler;
use Psr\Container\ContainerInterface;


class UnisenderContactHandlerFactory
{
    public function __invoke(ContainerInterface $container): UnisenderContactHandler
    {
        return new UnisenderContactHandler(
            $container->get(UnisenderApiClientFactory::class),
            $container->get(AmoCRMApiClientFactory::class),
        );
    }
}
