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

    public function __construct(
        BeanstalkConfig $beanstalkConfig,
        string $tube,
        AccountService $accountService,
        AmoCRMApiClient $apiClient
    ) {
        parent::__construct($beanstalkConfig, $tube);
        $this->accountService = $accountService;
        $this->apiClient = $apiClient;
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
            echo 'Токен аккаунта с id ' . $params['account_id'] . ' не найден или имеет неверный формат' . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            return;
        }

        /**
         * Если токен есть и он не истек, то возвращаем ответ success
         */
        if ($accessToken !== null && !$accessToken->hasExpired()) {
            echo 'Токен аккаунта с id ' . $params['account_id'] . ' есть и не истек' . PHP_EOL;
            return;
        }
        if ($accessToken !== null && $accessToken->hasExpired()) {
            $apiClient->getOAuthClient()->getAccessTokenByRefreshToken($accessToken);
            echo 'Токен аккаунта с id ' . $params['account_id'] . ' обновлен' . PHP_EOL;
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
            echo $e->getMessage() . PHP_EOL;
            return;
        }
        echo 'Токен аккаунта с id ' . $accountId . ' добавлен' . PHP_EOL;
        return;
    }

    public function configure(): void
    {
        $this->setDescription('Воркер для добавления access token в базу данных');
    }
}
