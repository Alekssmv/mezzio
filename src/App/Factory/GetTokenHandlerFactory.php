<?php

declare(strict_types=1);

namespace App\Factory;

use App\Interfaces\Service\AccountServiceInterface;
use Psr\Container\ContainerInterface;
use App\Handler\GetTokenHandler;
use Module\Config\Beanstalk as BeanstalkConfig;

class GetTokenHandlerFactory
{
    public function __invoke(ContainerInterface $container): GetTokenHandler
    {
        return new GetTokenHandler(
            $container->get(AmoCRMApiClientFactory::class),
            $container->get(AccountServiceInterface::class),
            new BeanstalkConfig($container),
        );
    }
}
