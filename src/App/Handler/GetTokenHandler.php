<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GuzzleHttp\Client;

class GetTokenHandler implements RequestHandlerInterface
{
    public function __construct(
        private AmoCRMApiClient $apiClient,
        private Client $httpClient,)
    {
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $apiClient = $this->apiClient;
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth2state'] = $state;
        $authorizationUrl = $apiClient->getOAuthClient()->getAuthorizeUrl([
            'state' => $state,
            'mode' => 'post_message',
        ]);
        header('Location: ' . $authorizationUrl);
        die;
    }
}
