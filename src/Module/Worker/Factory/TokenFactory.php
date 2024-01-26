<?php

declare(strict_types=1);

namespace Module\Worker\Factory;

use App\Factory\AmoCRMApiClientFactory;
use App\Factory\DateTimeImmutableFactory;
use App\Interfaces\Service\AccountServiceInterface;
use Module\Config\Beanstalk;
use Module\Worker\Token;
use Psr\Container\ContainerInterface;

/**
 * Фабрика для создания экземпляра воркера по установке токена
 */
class TokenFactory
{
    public function __invoke(ContainerInterface $container): Token
    {
        return new Token(
            new Beanstalk($container),
            'token',
            $container->get(AccountServiceInterface::class),
            $container->get(AmoCRMApiClientFactory::class),
        );
    }
}
