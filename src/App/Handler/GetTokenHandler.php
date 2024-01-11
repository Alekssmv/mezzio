<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use AmoCRM\Models\WebhookModel;

class GetTokenHandler implements RequestHandlerInterface
{
    /**
     * @var AmoCRMApiClient - клиент для работы с API amoCRM
     */
    private AmoCRMApiClient $apiClient;

    /**
     * @var AccountService - сервис для работы с аккаунтами
     */
    private AccountService $accountService;

    public function __construct(
        AmoCRMApiClient $apiClient,
        AccountService $accountService
    ) {
        $this->apiClient = $apiClient;
        $this->accountService = $accountService;
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

        /**
         * @var AccountService $accountService
         */
        $accountService = $this->accountService;
        $accessToken = null;

        /**
         * Полчаем токен по url параметру account_id
         */
        try {
            if ($params['account_id'] !== null) {
                $accessToken = $accountService->findOrCreate((int) $params['account_id'])->amo_access_jwt;
                $accessToken = json_decode((string) $accessToken, true);
                $accessToken = new AccessToken([
                    'access_token' => $accessToken['accessToken'],
                    'refresh_token' => $accessToken['refreshToken'],
                    'expires' => $accessToken['expires'],
                    'base_domain' => $accessToken['baseDomain'],
                ]);
            }
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        /**
         * Если токен есть и он не истек, то возвращаем ответ success
         */
        if ($accessToken !== null && !$accessToken->hasExpired()) {
            $apiClient->setAccessToken($accessToken);
            $apiClient->setAccountBaseDomain($accessToken->getValues()['base_domain']);

            /**
             * Устанавливаем вебхук
             */
            if ($apiClient->webhooks()->get() === null) {
                $webhookModel = new WebhookModel();
                $webhookModel->setDestination($_ENV['NGROK_HOSTNAME'] . '/api/v1/amo-uni-sync');
                $webhookModel->setSettings(['add_contact', 'update_contact', 'delete_contact']);
                $apiClient->webhooks()->subscribe($webhookModel);
            }

            /**
             * Устанавливаем enum_code для аккаунта
             */
            $accountService->addEnumCodes((int) $params['account_id'], ['WORK']);

            return new JsonResponse(['success' => true]);
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
        } elseif (
            !isset($params['from_widget'])
            && (empty($params['state'])
            || empty($_SESSION['oauth2state'])
            || ($params['state'] !== $_SESSION['oauth2state']))
        ) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }

        /**
         * Ловим обратный код
         */
        try {
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($params['code']);
            $apiClient->setAccessToken($accessToken);

            /**
             * Устанавливаем вебхук
             */
            if ($apiClient->webhooks()->get() === null) {
                $webhookModel = new WebhookModel();
                $webhookModel->setDestination($_ENV['NGROK_HOSTNAME'] . '/api/v1/amo-uni-sync');
                $webhookModel->setSettings(['add_contact', 'update_contact', 'delete_contact']);
                $apiClient->webhooks()->subscribe($webhookModel);
            }

            /**
             * Устанавливаем enum_code для аккаунта
             */
            $accountService->addEnumCodes((int) $params['account_id'], ['WORK', 'PRIV']);

            $accountId = $apiClient->account()->getCurrent()->toArray()['id'];
            if (!$accessToken->hasExpired()) {
                $accountService->findOrCreate((int) $accountId);
                $accountService->addAmoToken(
                    $accountId,
                    json_encode(
                        [
                            'accessToken' => $accessToken->getToken(),
                            'refreshToken' => $accessToken->getRefreshToken(),
                            'expires' => $accessToken->getExpires(),
                            'baseDomain' => $params['referer'],
                        ]
                    )
                );
            }
        } catch (Exception $e) {
            die((string) $e);
        }

        return new JsonResponse(['success' => true]);
    }
}
