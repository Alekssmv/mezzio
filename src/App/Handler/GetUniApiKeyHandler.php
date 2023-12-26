<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetUniApiKeyHandler implements RequestHandlerInterface
{
    private AmoCRMApiClient $apiClient;

    public function __construct(
        AmoCRMApiClient $apiClient
    ) {
        $this->apiClient = $apiClient;
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        
        if ($request->getMethod() !== 'POST') {
            die('Wrong request method');
        }

        $params = $request->getParsedBody();
        if (!isset($params['unisender_key']) && !isset($params['account_id'])) {
            die('Unisender key or account id is not set');
        }


    }
}