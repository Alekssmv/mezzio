<?php

declare(strict_types=1);

namespace App\Handler;

use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpClient 
{
    public function __construct(private Client $httpClient)
    {
    }

    public function handle(ServerRequestInterface $request) : Client
    {
        return $this->httpClient;
    }
}
