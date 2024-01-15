<?php

namespace Module\Worker;

use App\Services\AccountService;
use Module\Worker\BaseWorker;
use Module\Config\Beanstalk as BeanstalkConfig;
use Exception;

/**
 * Воркер для обработки задач по выводу времени
 */
class Enums extends BaseWorker
{
    /**
     * @var AccountService - сервис для работы с аккаунтами
     */
    private AccountService $accountService;

    /**
     * @var string $messagesPrefix - префикс для сообщений
     */
    private string $messagesPrefix;

    public function __construct(
        BeanstalkConfig $beanstalkConfig,
        string $tube,
        AccountService $accountService
    ) {
        parent::__construct($beanstalkConfig, $tube);
        $this->accountService = $accountService;
        $this->messagesPrefix = $tube . ': ';
    }

    /**
     * @var string $data - данные для обработки
     */
    public function process($data): void
    {
        $params = $data;
        $accountService = $this->accountService;
        $messagesPrefix = $this->messagesPrefix;

        /**
         * Проверяем наличие account_id
         */
        if (!isset($params['account_id'])) {
            echo $messagesPrefix . 'account_id is required' . PHP_EOL;
            return;
        }

        /**
         * Добавляем enum коды
         */
        try {
            $accountService->addEnumCodes((int) $params['account_id'], ['WORK']);
        } catch (Exception $e) {
            echo $messagesPrefix . $e->getMessage() . PHP_EOL;
            return;
        }

        echo $messagesPrefix . 'Enum codes were added to account with id ' . $params['account_id'] . PHP_EOL;
    }

    /**
     * Добавляем описание команды
     */
    public function configure(): void
    {
        $this->setDescription('Воркер для добавления enums к аккаунту');
    }
}
