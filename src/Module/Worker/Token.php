<?php

namespace Module\Worker;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Module\Worker\BaseWorker;
use Module\Config\Beanstalk as BeanstalkConfig;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use DateTimeImmutable;

/**
 * Воркер для проверки, создания и обновления токенов
 */
class Token extends BaseWorker
{
    /**
     * @var AccountService - сервис для работы с аккаунтами
     */
    private AccountService $accountService;

    /**
     * @var AmoCRMApiClient - клиент для работы с API amoCRM
     */
    private AmoCRMApiClient $apiClient;

    /**
     * @var string $messagesPrefix - префикс для сообщений
     */
    private string $messagesPrefix;

    public function __construct(
        BeanstalkConfig $beanstalkConfig,
        string $tube,
        AccountService $accountService,
        AmoCRMApiClient $apiClient
    ) {
        parent::__construct($beanstalkConfig, $tube);
        $this->accountService = $accountService;
        $this->apiClient = $apiClient;
        $this->messagesPrefix = $tube . ': ';
    }

    /**
     * @var string $data - данные для обработки
     */
    public function process($data): void
    {
        $params = $data;
        $accountService = $this->accountService;
        $apiClient = $this->apiClient;
        $messagesPrefix = $this->messagesPrefix;
        $accessToken = null;

        /**
         * Ищем аккаунт по параметру account_id, если он задан,
         * берем токен из него
         */
        if (isset($params['account_id']) && !empty($params['account_id'])) {
            $accessToken = $accountService
                ->findOrCreate((int) $params['account_id'])
                ->amo_access_jwt;

            if ($accessToken !== null) {
                try {
                    $accessToken = json_decode((string) $accessToken, true);
                    $accessToken = new AccessToken([
                        'access_token' => $accessToken['accessToken'],
                        'refresh_token' => $accessToken['refreshToken'],
                        'expires' => $accessToken['expires'],
                        'base_domain' => $accessToken['baseDomain'],
                    ]);
                } catch (Exception $e) {
                    echo $messagesPrefix .
                        'Can\'t get access token by account_id' .
                        PHP_EOL;

                    echo $messagesPrefix . $e->getMessage() . PHP_EOL;
                    return;
                }
            }
        }

        /**
         * Если токен есть и он не истек, то возвращаем ответ success. Также проверяем наличие параметра force_refresh
         */
        if ($accessToken !== null && (!$accessToken->hasExpired() && !isset($params['force_refresh']))) {
            echo $messagesPrefix .
                'Access token is valid for account with id ' .
                $params['account_id'] .
                PHP_EOL;
            return;
        }

        /**
         * Если токен есть и он истек или есть параметр force_refresh, то обновляем токен
         */
        if (
            $accessToken !== null && ($accessToken->hasExpired() || isset($params['force_refresh']))
        ) {
            try {
                $apiClient
                    ->getOAuthClient()
                    ->getAccessTokenByRefreshToken($accessToken);

                echo $messagesPrefix .
                    'Access token was refreshed for account with id ' .
                    $params['account_id'] .
                    PHP_EOL;
            } catch (Exception $e) {
                echo $messagesPrefix .
                    'Can\'t refresh access token by account_id' .
                    PHP_EOL;
                echo $messagesPrefix .
                    $e->getMessage() .
                    PHP_EOL;
                return;
            }
            return;
        }

        /**
         * Задаем домен для apiClient для взаимодействия с amoCRM
         */
        if (isset($params['referer'])) {
            $apiClient->setAccountBaseDomain($params['referer']);
        }

        /**
         * Ловим обратный код
         */
        try {
            $accessToken = $apiClient
                ->getOAuthClient()
                ->getAccessTokenByCode($params['code']);

            $apiClient->setAccessToken($accessToken);
            $accountId = $apiClient->account()->getCurrent()->toArray()['id'];

            /**
             * Если полученный токен не истек, то добавляем его в базу данных
             */
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
            echo $messagesPrefix . $e->getMessage() . PHP_EOL;
            return;
        }
        echo $messagesPrefix .
            'Access token was added to account with id ' .
            $accountId .
            PHP_EOL;
        return;
    }

    /**
     * Добавляем описание команды
     */
    public function configure(): void
    {
        $this
            ->setDescription('Воркер для добавления access token в базу данных');
    }
}
