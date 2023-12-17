<?php

declare(strict_types=1);

namespace App\Handler;

use App\Client\ApiClient;
use App\Client\HttpClient;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class RedirectUriHandlerFactory
{
    public function __invoke(ContainerInterface $container) : RedirectUriHandler
    {
        return new RedirectUriHandler($container->get(TemplateRendererInterface::class), $container->get(ApiClient::class), $container->get(HttpClient::class));
    }
}
