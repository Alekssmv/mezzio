<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;
use AmoCRM\Client\AmoCRMApiClient;

class ApiClientFactory
{
    public function __invoke(ContainerInterface $container) : AmoCRMApiClient
    {
        return new AmoCRMApiClient(
            $_ENV['AMO_CLIENT_ID'],
            $_ENV['AMO_CLIENT_SECRET'],
            $_ENV['AMO_REDIRECT_URI'],
        );
    }
}
