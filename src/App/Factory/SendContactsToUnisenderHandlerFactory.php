<?php

declare(strict_types=1);

namespace App\Factory;

use App\Handler\SendContactsToUnisenderHandler;
use App\Interfaces\ContactsServiceInterface;
use Psr\Container\ContainerInterface;

class SendContactsToUnisenderHandlerFactory
{
    public function __invoke(ContainerInterface $container): SendContactsToUnisenderHandler
    {
        return new SendContactsToUnisenderHandler(
            $container->get(UnisenderApiClientFactory::class),
            $container->get(ContactsServiceInterface::class),
        );
    }
}
