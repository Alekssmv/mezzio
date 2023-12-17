<?php

declare(strict_types=1);

namespace App\Client;

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

class HttpClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        return new Client();
    }
}
