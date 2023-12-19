<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;
use Mezzio\Application;

class GetTokenHandler implements RequestHandlerInterface
{
    public function __construct(
        private AmoCRMApiClient $apiClient
    ) {
    }
    /**
     * Сохраняет токен в TOKEN_FILE локально
     * Отправляет токен на вебхук
     * Возвращает redirect на ngrok public url туннеля на маршрут /redirect-uri
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /**
         * Праметры запроса
         */
        $params = $request->getQueryParams();
        $apiClient = $this->apiClient;


        if (isset($params['referer'])) {
            $apiClient->setAccountBaseDomain($params['referer']);
        }

        if (!isset($params['code'])) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth2state'] = $state;
            if (isset($params['button'])) {
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
        } elseif (!isset($params['from_widget']) && (empty($params['state']) || empty($_SESSION['oauth2state']) || ($params['state'] !== $_SESSION['oauth2state']))) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }

        /**
         * Ловим обратный код
         */
        try {
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($params['code']);

            if (!$accessToken->hasExpired()) {
                saveToken([
                    'accessToken' => $accessToken->getToken(),
                    'refreshToken' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'baseDomain' => $apiClient->getAccountBaseDomain(),
                ]);
            }
        } catch (Exception $e) {
            die((string) $e);
        }

        /**
         * Отправляем токен на вебхук
         */
        try {
            $apiClient->getOAuthClient()->getHttpClient()->request('POST', $_ENV["AMO_REDIRECT_URI"], [
                'token' => [
                    'access_token' => $accessToken->getToken(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'base_domain' => $apiClient->getAccountBaseDomain(),
                ],
            ]);
        } catch (Exception $e) {
            die((string) $e);
        }

        /**
         * Полчение ngrok public url туннеля
         */
        $response = $this->$apiClient->getHttpClient()->request('GET', 'https://api.ngrok.com/tunnels', [
            'headers' => [
                'Authorization' => 'Bearer ' . $_ENV["NGROK_API_TOKEN"],
                'Ngrok-Version' => '2'
            ]
        ]);
        $url = json_decode($response->getBody()->getContents(), true)['tunnels'][0]['public_url'];

        return new RedirectResponse($url . '/redirect-uri');
    }
}
