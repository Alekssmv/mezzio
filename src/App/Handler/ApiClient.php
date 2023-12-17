<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use AmoCRM\Client\AmoCRMApiClient;

class ApiClient implements RequestHandlerInterface
{
    public function __construct(private AmoCRMApiClient $apiClient)
    {
    }

    public function handle(ServerRequestInterface $request) : AmoCRMApiClient
    {
        return $this->apiClient;
    }
}
