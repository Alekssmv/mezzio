<?php

declare(strict_types=1);

namespace Module\Worker\Factory;

use App\Interfaces\Service\AccountServiceInterface;
use Module\Config\Beanstalk;
use Module\Worker\Enums;
use Psr\Container\ContainerInterface;

/**
 * Фабрика для создания экземпляра воркера по установке unisender api key
 */
class EnumsFactory
{
    public function __invoke(ContainerInterface $container): Enums
    {
        return new Enums(
            new Beanstalk($container),
            'enums',
            $container->get(AccountServiceInterface::class),
        );
    }
}
