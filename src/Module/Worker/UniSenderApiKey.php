<?php

namespace Module\Worker;

use App\Services\AccountService;
use Module\Worker\BaseWorker;
use Module\Config\Beanstalk as BeanstalkConfig;
use Exception;

/**
 * Воркер для обработки задач по выводу времени
 */
class UniSenderApiKey extends BaseWorker
{
    /**
     * @var AccountService - сервис для работы с аккаунтами
     */
    private AccountService $accountService;

    public function __construct(
        BeanstalkConfig $beanstalkConfig,
        string $tube,
        AccountService $accountService
    ) {
        parent::__construct($beanstalkConfig, $tube);
        $this->accountService = $accountService;
    }

    /**
     * @var string $data - данные для обработки
     */
    public function process($data): void
    {
        $params = $data;

        if (!isset($params['unisender_key']) && !isset($params['account_id'])) {
            echo 'unisender_key and account_id are required' . PHP_EOL;
            return;
        }

        try {
            $account = $this->accountService->findOrCreate((int) $params['account_id']);
            $account = $this->accountService->addUnisenderApiKey((int) $params['account_id'], $params['unisender_key']);
        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            return;
        }

        echo 'Unisender api key was added to account with id ' . $account->account_id . PHP_EOL;
    }

    public function configure(): void
    {
        $this->setDescription('Воркер для добавления unisender api key в базу данных');
    }
}
