<?php

declare(strict_types=1);

namespace Module\Worker\Factory;

use App\Interfaces\Service\AccountServiceInterface;
use Module\Config\Beanstalk;
use Module\Worker\UniSenderApiKey;
use Psr\Container\ContainerInterface;

/**
 * Фабрика для создания экземпляра воркера по установке unisender api key
 */
class UniSenderApiKeyFactory
{
    public function __invoke(ContainerInterface $container): UniSenderApiKey
    {
        return new UniSenderApiKey(
            new Beanstalk($container),
            'unisender-api-key',
            $container->get(AccountServiceInterface::class)
        );
    }
}
