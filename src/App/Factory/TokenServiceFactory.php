<?php

declare(strict_types=1);

namespace App\Factory;

use App\Services\TokenService;
use Psr\Container\ContainerInterface;
use App\Repositories\TokenRepository;

/**
 * Фабрика для создания экземпляра сервиса для работы с аккаунтами
 */
class TokenServiceFactory
{
    public function __invoke(ContainerInterface $container): TokenService
    {
        return new TokenService(new TokenRepository());
    }
}
