<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;
use GuzzleHttp\Client;

class GetTokenHandler implements RequestHandlerInterface
{
    public function __construct(
        private AmoCRMApiClient $apiClient,
        private $httpClient)
    {
    }
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        session_start();
        $apiClient = $this->apiClient->setAccountBaseDomain('testasmirnov.amocrm.ru');
        try {
            if (empty($_GET['code'])) {
                throw new Exception('Parameter code is empty');
            }
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);
            saveToken( 
                [
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $apiClient->getAccountBaseDomain(),
                ]
            );
        } catch (Exception $e) {
            die((string) $e);
        }

        $httpClient = $this->httpClient;
        $httpClient->post($_ENV["AMO_REDIRECT_URI"], [
            'json' => $accessToken->jsonSerialize(),
        ]);
        return new JsonResponse($accessToken->jsonSerialize());
    }
}
