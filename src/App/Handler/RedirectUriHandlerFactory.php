<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use App\Client\ApiClient;
use App\Client\HttpClient;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class RedirectUriHandlerFactory
{
    public function __invoke(ContainerInterface $container) : RedirectUriHandler
    {
        return new RedirectUriHandler($container->get(TemplateRendererInterface::class), new AmoCRMApiClient(
            $_ENV["AMO_CLIENT_ID"],
            $_ENV["AMO_CLIENT_SECRET"],
            $_ENV["AMO_REDIRECT_URI"],
        ));
    }
}
