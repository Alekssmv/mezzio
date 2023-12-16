<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;
use GuzzleHttp\Client;

class GetTokenHandler implements RequestHandlerInterface
{
    public function __construct(
        private AmoCRMApiClient $apiClient,
        private Client $httpClient)
    {
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $apiClient = $this->apiClient;
        try {
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);

        } catch (Exception $e) {
            die((string) $e);
        }

        $httpClient = $this->httpClient;
        $httpClient->post('https://webhook.site/3998a479-aa49-49b7-afb5-52dfb082c57b', [
            'json' => $accessToken->jsonSerialize(),
        ]);
        dd($accessToken->getToken());
    }
}
