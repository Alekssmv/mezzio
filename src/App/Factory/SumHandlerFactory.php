<?php

declare(strict_types=1);

namespace App\Factory;

use Psr\Container\ContainerInterface;
use App\Handler\SumHandler;

class SumHandlerFactory
{
    public function __invoke(ContainerInterface $container) : SumHandler
    {
        return new SumHandler();
    }
}
