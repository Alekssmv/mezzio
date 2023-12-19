<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;

class GetTokenHandler implements RequestHandlerInterface
{
    public function __construct(
        private AmoCRMApiClient $apiClient
    ) {
    }
    /**
     * Сохраняет токен в TOKEN_FILE
     * Отправляет токен на вебхук
     * Возвращает json ответ, если все прошло успешно
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /**
         * Параметры запроса
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

        return new JsonResponse([
            'success' => 'true',
        ]);
    }
}
