<?php

declare(strict_types=1);

namespace App\Factory;

use App\Services\UserService;
use App\Repositories\UserRepository;
use Psr\Container\ContainerInterface;

/**
 * Фабрика для создания экземпляра UserService для работой с репозиторием UserRepository
 */
class UserServiceFactory
{
    public function __invoke(ContainerInterface $container): UserService
    {
        return new UserService(new UserRepository());
    }
}
