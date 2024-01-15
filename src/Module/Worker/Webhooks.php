<?php

namespace Module\Worker;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Module\Worker\BaseWorker;
use Module\Config\Beanstalk as BeanstalkConfig;
use Exception;
use AmoCRM\Models\WebhookModel;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Воркер для обработки задач по выводу времени
 */
class Webhooks extends BaseWorker
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
        $account = null;

        if (!isset($params['account_id'])) {
            echo $messagesPrefix . 'account_id is required' . PHP_EOL;
            return;
        }

        try {
            $account = $accountService->findOrCreate((int) $params['account_id']);
            $accessToken = $accountService->findOrCreate((int) $params['account_id'])->amo_access_jwt;
            if ($accessToken === null) {
                echo $messagesPrefix . 'Access token is not found in account with id ' . $account->account_id . PHP_EOL;
                return;
            }
                $accessToken = json_decode((string) $accessToken, true);
                $accessToken = new AccessToken([
                    'access_token' => $accessToken['accessToken'],
                    'refresh_token' => $accessToken['refreshToken'],
                    'expires' => $accessToken['expires'],
                    'base_domain' => $accessToken['baseDomain'],
                ]);
            $apiClient->setAccessToken($accessToken);
            $apiClient->setAccountBaseDomain($accessToken->getValues()['base_domain']);

            if ($apiClient->webhooks()->get() === null) {
                $webhookModel = new WebhookModel();
                $webhookModel->setDestination($_ENV['NGROK_HOSTNAME'] . '/api/v1/amo-uni-sync');
                $webhookModel->setSettings(['add_contact', 'update_contact', 'delete_contact']);
                $apiClient->webhooks()->subscribe($webhookModel);
                echo $messagesPrefix . 'Webhook was added to account with id ' . $account->account_id . PHP_EOL;
                return;
            } elseif ($apiClient->webhooks()->get() !== null) {
                echo $messagesPrefix . 'Webhook already exists in account with id ' . $account->account_id . PHP_EOL;
                return;
            }
        } catch (Exception $e) {
            echo $messagesPrefix . 'Webhook was not added to account with id ' . $account->account_id . PHP_EOL;
            echo $messagesPrefix . $e->getMessage() . PHP_EOL;
            return;
        }
    }

    public function configure(): void
    {
        $this->setDescription('Воркер для добавления webhooks к аккаунту amoCRM');
    }
}
