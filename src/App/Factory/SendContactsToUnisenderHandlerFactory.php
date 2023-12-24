<?php

declare(strict_types=1);

namespace App\Factory;

use App\Handler\SendContactsToUnisenderHandler;
use Psr\Container\ContainerInterface;
use App\Handler\RedirectUriHandler;

class SendContactsToUnisenderHandlerFactory
{
    public function __invoke(ContainerInterface $container): SendContactsToUnisenderHandler
    {
        return new SendContactsToUnisenderHandler(
            $container->get(UnisenderApiClientFactory::class),
            $container->get(AmoCRMApiClientFactory::class),
        );
    }
}
