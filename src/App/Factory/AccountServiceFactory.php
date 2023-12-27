<?php

declare(strict_types=1);

namespace App\Factory;

use AmoCRM\Client\AmoCRMApiClient;
use App\Repositories\AccountRepository;
use App\Services\AccountService;
use Psr\Container\ContainerInterface;

/**
 * Фабрика для создания экземпляра сервиса для работы с контактами
 */
class AccountServiceFactory
{
    public function __invoke(ContainerInterface $container): AccountService
    {
        return new AccountService(new AccountRepository());
    }
}
