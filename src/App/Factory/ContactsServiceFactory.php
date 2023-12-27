<?php

declare(strict_types=1);

namespace App\Factory;

use AmoCRM\Client\AmoCRMApiClient;
use Psr\Container\ContainerInterface;
use App\Services\ContactsService;

/**
 * Фабрика для создания экземпляра сервиса для работы с контактами
 */
class ContactsServiceFactory
{
    public function __invoke(ContainerInterface $container): ContactsService
    {
        return new ContactsService();
    }
}
