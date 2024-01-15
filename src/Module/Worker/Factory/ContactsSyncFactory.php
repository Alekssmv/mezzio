<?php

declare(strict_types=1);

namespace Module\Worker\Factory;

use App\Factory\AmoCRMApiClientFactory;
use App\Interfaces\Service\AccountServiceInterface;
use App\Services\ContactFormatterService;
use App\Services\ContactService;
use App\Services\EmailEnumService;
use Module\Config\Beanstalk;
use Module\Worker\ContactsSync;
use Module\Worker\Enums;
use Psr\Container\ContainerInterface;
use Unisender\ApiWrapper\UnisenderApi;

/**
 * Фабрика для создания экземпляра воркера по установке unisender api key
 */
class ContactsSyncFactory
{
    public function __invoke(ContainerInterface $container): ContactsSync
    {
        return new ContactsSync(
            new Beanstalk($container),
            'contacts-sync',
            $container->get(AccountServiceInterface::class),
            $container->get(AmoCRMApiClientFactory::class),
            new EmailEnumService(),
            new ContactFormatterService(),
            new ContactService()
        );
    }
}
