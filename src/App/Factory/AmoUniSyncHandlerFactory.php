<?php

declare(strict_types=1);

namespace App\Factory;

use App\Interfaces\Service\AccountServiceInterface;
use App\Services\ContactService;
use App\Services\ContactFormatterService;
use App\Services\EmailEnumService;
use Psr\Container\ContainerInterface;
use App\Handler\AmoUniSyncHandler;

class AmoUniSyncHandlerFactory
{
    public function __invoke(ContainerInterface $container): AmoUniSyncHandler
    {
        return new AmoUniSyncHandler(
            $container->get(UnisenderApiClientFactory::class),
            new ContactFormatterService(),
            $container->get(AccountServiceInterface::class),
            $container->get(AmoCRMApiClientFactory::class),
            new ContactService(),
            new EmailEnumService(),
        );
    }
}
