<?php

declare(strict_types=1);

namespace Module\Worker\Factory;

use App\Interfaces\Service\AccountServiceInterface;
use Module\Config\Beanstalk;
use Module\Worker\Webhooks;
use Psr\Container\ContainerInterface;
use App\Factory\AmoCRMApiClientFactory;

/**
 * Фабрика для создания экземпляра воркера по установке вебхуков
 */
class WebhooksFactory
{
    public function __invoke(ContainerInterface $container): Webhooks
    {
        return new Webhooks(
            new Beanstalk($container),
            'webhooks',
            $container->get(AccountServiceInterface::class),
            $container->get(AmoCRMApiClientFactory::class),
        );
    }
}
