<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use App\Helper\AmoApi;
use App\Helper\Relations;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;
use App\Helper\TokenActions;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GetTokenHandler implements RequestHandlerInterface
{
    public function __construct(
        private AmoCRMApiClient $apiClient
    ) {
    }
    /**
     * Сохраняет токен в TOKEN_FILE локально, если он еще не получен или истек
     * Возвращает redirect ответ на маршрут /redirect-uri
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        /**
         * Параметры запроса
         */
        $params = $request->getQueryParams();
        $apiClient = $this->apiClient;
        $accountId = $_SESSION['accountId'] ?? null;

        /**
         * Полчаем токен по account_id из сессии, если он есть
         */
        if ($accountId) {
            $accessToken = TokenActions::getToken($accountId);
        } else {
            $accessToken = null;
        }

        /**
         * Если токен есть и он не истек, то редиректим на /redirect-uri
         */
        if ($accessToken !== null && !$accessToken->hasExpired()) {
            return new RedirectResponse('/redirect-uri');
        } 

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
                $authorizationUrl = $apiClient->getOAuthClient()->getAuthorizeUrl(
                    [
                        'state' => $state,
                        'mode' => 'post_message',
                    ]
                );
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
            $accountId = $apiClient->setAccessToken($accessToken)->account()->getCurrent()->toArray()['id'];
            /**
             * Сохраняем account_id в сессию
             */
            $_SESSION['accountId'] = $accountId;
            if (!$accessToken->hasExpired()) {
                TokenActions::saveToken(
                    $accountId, [
                        'accessToken' => $accessToken->getToken(),
                        'refreshToken' => $accessToken->getRefreshToken(),
                        'expires' => $accessToken->getExpires(),
                        'baseDomain' => $apiClient->getAccountBaseDomain(),
                    ]
                );
            }
        } catch (Exception $e) {
            die((string) $e);
        }

        return new RedirectResponse('/redirect-uri');
    }
}
