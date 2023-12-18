<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GuzzleHttp\Client;
use Exception;

class GetTokenHandler implements RequestHandlerInterface
{
    public function __construct(
        private AmoCRMApiClient $apiClient,
        private Client $httpClient,)
    {
    }
    /**
     * Получение кода авторизации
     * @param ServerRequestInterface $request
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $apiClient = $this->apiClient;

        if (isset($_GET['referer'])) {
            $apiClient->setAccountBaseDomain($_GET['referer']);
        }

        if (!isset($_GET['code'])) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth2state'] = $state;
            if (isset($_GET['button'])) {
                echo $apiClient->getOAuthClient()->getOAuthButton(
                    [
                        'title' => 'Установить интеграцию',
                        'compact' => true,
                        'class_name' => 'className',
                        'color' => 'default',
                        'error_callback' => 'handleOauthError',
                        'state' => $state,
                    ]
                );
                die;
            } else {
                $authorizationUrl = $apiClient->getOAuthClient()->getAuthorizeUrl([
                    'state' => $state,
                    'mode' => 'post_message',
                ]);
                header('Location: ' . $authorizationUrl);
                die;
            }
        } elseif (!isset($_GET['from_widget']) && (empty($_GET['state']) || empty($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state']))) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }
        
        /**
         * Ловим обратный код
         */
        try {
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);
        
            if (!$accessToken->hasExpired()) {
                saveToken([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $apiClient->getAccountBaseDomain(),
                ]);
            }
        } catch (Exception $e) {
            die((string)$e);
        }

        $apiClient->getOAuthClient()->getHttpClient()->request('GET', $_ENV["AMO_REDIRECT_URI"] . '?token=' . $accessToken->getToken());

        return new JsonResponse([
            'success' => 'true',
        ]);
    }
}
