<?php

declare(strict_types=1);

namespace Module\Command\Factory;

use App\Factory\AmoCRMApiClientFactory;
use App\Interfaces\Service\AccountServiceInterface;
use Module\Command\RefreshTokens;
use Psr\Container\ContainerInterface;

/**
 * Фабрика для создания экземпляра команды обновления токенов
 */
class RefreshTokensFactory
{
    public function __invoke(ContainerInterface $container): RefreshTokens
    {
        return new RefreshTokens(
            $container->get(AccountServiceInterface::class),
            $container->get(AmoCRMApiClientFactory::class)
        );
    }
}
