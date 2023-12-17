<?php

declare(strict_types=1);

namespace App\Handler;

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

class HttpClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        return new Client();
    }
}
