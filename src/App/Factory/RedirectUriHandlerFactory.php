<?php

declare(strict_types=1);

namespace App\Factory;

use AmoCRM\Client\AmoCRMApiClient;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use App\Handler\RedirectUriHandler;

class RedirectUriHandlerFactory
{
    public function __invoke(ContainerInterface $container): RedirectUriHandler
    {
        return new RedirectUriHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(AmoCRMApiClientFactory::class),
        );
    }
}
