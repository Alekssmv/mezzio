<?php

declare(strict_types=1);

namespace App\Handler;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Laminas\Diactoros\Response\JsonResponse;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pheanstalk\Pheanstalk;
use Module\Config\Beanstalk as BeanstalkConfig;

class GetTokenHandler implements RequestHandlerInterface
{
    /**
     * @var AmoCRMApiClient - клиент для работы с API amoCRM
     */
    private AmoCRMApiClient $apiClient;

    /**
     * @var Pheanstalk - подключение к beanstalk
     */
    private $beanstalk;

    /**
     * @var AccountService - сервис для работы с аккаунтами
     */
    private AccountService $accountService;

    public function __construct(
        AmoCRMApiClient $apiClient,
        AccountService $accountService,
        BeanstalkConfig $beanstalkConfig
    ) {
        $this->apiClient = $apiClient;
        $this->accountService = $accountService;
        $this->beanstalk = $beanstalkConfig->getConnection();
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
        $accountService = $this->accountService;
        $apiClient = $this->apiClient;
        $beanstalk = $this->beanstalk;
        $tasks = [];
        $token = null;

        /**
         * Ищем аккаунт по account_id, берем токен из него
         */
        if (isset($params['account_id']) && !empty($params['account_id'])) {
            $account = $accountService
                ->findByAccountId((int) $params['account_id']);
            $token = $account->amo_access_jwt;

            /**
             * Если есть параметр account_id, то добавляем задачи на добавление
             * вебхуков и enum в очередь
             */
            $beanstalk->useTube('webhooks')->put(json_encode($params));
            $tasks[] = 'webhooks';
            $beanstalk->useTube('enums')->put(json_encode($params));
            $tasks[] = 'enums';
        }

        /**
         * Если токен есть, то добавляем задачу в очередь,
         * чтобы проверить токен на валидность
         * и обновить его, если он истек
         */
        if ($token !== null) {
            $beanstalk->useTube('token')->put(json_encode($params));
            $tasks[] = 'token';
        }

        /**
         * Если запрос пришел с виджета, то добавляем задачу в очередь,
         * чтобы получить токен, если его нет
         */
        if (
            $token === null &&
            isset($params['code'], $params['from_widget'], $params['referer'])
        ) {
            $beanstalk->useTube('token')->put(json_encode($params));
            $tasks[] = 'token';
        }

        /**
         * Если нет параметра code и токена нет, то перенаправляем на страницу
         * авторизации
         */
        if (!isset($params['code']) && $token === null) {
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
            && $token === null
            && (empty($params['state'])
                || empty($_SESSION['oauth2state'])
                || ($params['state'] !== $_SESSION['oauth2state']))
        ) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }

        return new JsonResponse(['success' => true, 'tasks' => implode(', ', $tasks)]);
    }
}
