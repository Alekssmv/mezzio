<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\TokenService;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;
use App\Helper\TokenActions;

class GetTokenHandler implements RequestHandlerInterface
{
    /**
     * @var AmoCRMApiClient - клиент для работы с API amoCRM
     */
    private AmoCRMApiClient $apiClient;

    /**
     * @var TokenService - сервис для работы с токенами
     */
    private TokenService $tokenService;
    public function __construct(
        AmoCRMApiClient $apiClient,
        TokenService $tokenService
    ) {
        $this->apiClient = $apiClient;
        $this->tokenService = $tokenService;
    }
    /**
     * Сохраняет токен в TOKEN_FILE локально, если он еще не получен или истек
     * Возвращает redirect ответ на маршрут /redirect-uri с параметром account_id
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        /**
         * Параметры запроса
         */
        $params = $request->getQueryParams();
        $apiClient = $this->apiClient;
        $tokenService = $this->tokenService;

        /**
         * Полчаем токен по url параметру account_id
         */
        if (isset($params['account_id'])) {
            $accessToken = TokenActions::getToken((int) $params['account_id']);
        } else {
            $accessToken = null;
        }

        /**
         * Если токен есть и он не истек, то редиректим на /redirect-uri с параметром account_id
         */
        if ($accessToken !== null && !$accessToken->hasExpired()) {
            return new RedirectResponse('/redirect-uri' . '?account_id=' . $params['account_id']);
        }

        if (isset($params['referer'])) {
            $apiClient->setAccountBaseDomain($params['referer']);
        }

        if (!isset($params['code'])) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth2state'] = $state;
            if (isset($params['button'])) {
                $test = $apiClient->getOAuthClient()->getOAuthButton(
                    [
                        'title' => 'Установить интеграцию',
                        'compact' => true,
                        'class_name' => 'className',
                        'color' => 'default',
                        'error_callback' => 'handleOauthError',
                        'state' => $state,
                    ]
                );
                echo $test;
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
            if (!$accessToken->hasExpired()) {
                TokenActions::saveToken(
                    $accountId,
                    [
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

        return new RedirectResponse('/redirect-uri' . '?account_id=' . $accountId);
    }
}
