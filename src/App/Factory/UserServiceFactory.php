<?php

declare(strict_types=1);

namespace App\Factory;

use App\Services\UserService;
use App\Repositories\UserRepository;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    public function __invoke(ContainerInterface $container): UserService
    {
        return new UserService(
            new UserRepository()
        );
    }
}
