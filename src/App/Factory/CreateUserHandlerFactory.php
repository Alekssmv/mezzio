<?php

declare(strict_types=1);

namespace App\Factory;

use App\Factory\UserServiceFactory;
use Psr\Container\ContainerInterface;
use App\Handler\CreateUserHandler;

/**
 * Фабрика для создания экземпляра CreateUserHandler
 */
class CreateUserHandlerFactory
{
    public function __invoke(ContainerInterface $container): CreateUserHandler
    {
        $userService = $container->get(UserServiceFactory::class);
        return new CreateUserHandler($userService);
    }
}
