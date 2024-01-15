<?php

namespace Module\Worker;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Module\Worker\BaseWorker;
use Module\Config\Beanstalk as BeanstalkConfig;
use Exception;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Воркер для обработки задач по выводу времени
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
        $accessToken = null;
        $accountService = $this->accountService;
        $apiClient = $this->apiClient;
        $messagesPrefix = $this->messagesPrefix;

        /**
         * Полчаем токен по url параметру account_id
         */
        try {
            if (isset($params['account_id']) && $params['account_id'] !== null) {
                $accessToken = $accountService->findOrCreate((int) $params['account_id'])->amo_access_jwt;
                if ($accessToken === null) {
                    echo $messagesPrefix . 'Access token is not found in account with id ' . $params['account_id'] . PHP_EOL;
                    return;
                }
                $accessToken = json_decode((string) $accessToken, true);
                $accessToken = new AccessToken([
                    'access_token' => $accessToken['accessToken'],
                    'refresh_token' => $accessToken['refreshToken'],
                    'expires' => $accessToken['expires'],
                    'base_domain' => $accessToken['baseDomain'],
                ]);
            }
        } catch (Exception $e) {
            echo $messagesPrefix . 'Can\'t get access token by account_id' . PHP_EOL;
            echo $messagesPrefix . $e->getMessage() . PHP_EOL;
            return;
        }

        /**
         * Если токен есть и он не истек, то возвращаем ответ success
         */
        if ($accessToken !== null && !$accessToken->hasExpired()) {
            echo $messagesPrefix . 'Access token is valid for account with id ' . $params['account_id'] . PHP_EOL;
            return;
        }
        if ($accessToken !== null && $accessToken->hasExpired()) {
            try {
                $apiClient->getOAuthClient()->getAccessTokenByRefreshToken($accessToken);
                echo $messagesPrefix . 'Access token was refreshed for account with id ' . $params['account_id'] . PHP_EOL;
            } catch (Exception $e) {
                echo $messagesPrefix . $e->getMessage() . PHP_EOL;
                return;
            }
            return;
        }

        if (isset($params['referer'])) {
            $apiClient->setAccountBaseDomain($params['referer']);
        }

        /**
         * Ловим обратный код
         */
        try {
            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($params['code']);
            $apiClient->setAccessToken($accessToken);

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
            echo $messagesPrefix . $e->getMessage() . PHP_EOL;
            return;
        }
        echo $messagesPrefix . 'Access token was added to account with id ' . $accountId . PHP_EOL;
        return;
    }

    public function configure(): void
    {
        $this->setDescription('Воркер для добавления access token в базу данных');
    }
}
