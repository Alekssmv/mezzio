<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class SumHandlerFactory
{
    public function __invoke(ContainerInterface $container) : SumHandler
    {
        return new SumHandler();
    }
}
