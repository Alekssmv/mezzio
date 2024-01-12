<?php

declare(strict_types=1);

namespace Module\Worker;

use Module\Worker\Time;
use Psr\Container\ContainerInterface;

/**
 * Фабрика для создания экземпляра сервиса для работы с аккаунтами
 */
class TimeFactory
{
    public function __invoke(ContainerInterface $container): Time
    {
        return new Time();
    }
}
