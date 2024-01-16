<?php

namespace Module\Command;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Module\Config\Beanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use Pheanstalk\Pheanstalk;
use Module\Config\Beanstalk as BeanstalkConfig;

/**
 * Команда для обновления токенов всех аккаунтов
 */
class RefreshTokens extends Command
{
    /**
     * @var string $defaultName - название команды
     */
    protected static $defaultName = 'app:refresh-tokens';

    /**
     * @var AccountService $accountService - сервис для работы с аккаунтами
     */
    private AccountService $accountService;

    /**
     * @var AmoCRMApiClient $amoCRMApiClient - клиент для работы с amoCRM
     */
    private AmoCRMApiClient $amoCRMApiClient;

    /**
     * @var Pheanstalk $pheanstalk - клиент для работы с очередью
     */
    private Pheanstalk $pheanstalk;

    public function __construct(
        AccountService $accountService,
        BeanstalkConfig $beanstalkConfig
    ) {
        parent::__construct();
        $this->accountService = $accountService;
        $this->pheanstalk = $beanstalkConfig->getConnection();
    }

    /**
     * Конфигурирование команды
     */
    protected function configure(): void
    {
        $this->setDescription('Обновляет токены всех аккаунтов');
    }

    /**
     * Выполнение команды, обновление токенов всех аккаунтов
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var AccountService $accountService - сервис для работы с аккаунтами
         */
        $accountService = $this->accountService;

        /**
         * @var Pheanstalk $pheanstalk - клиент для работы с очередью
         */
        $pheanstalk = $this->pheanstalk;

        $accountIds = $accountService->getAllIds();

        foreach ($accountIds as $accountId) {
            $pheanstalk->useTube('token')->put(json_encode([
                'account_id' => $accountId,
                'force_refresh' => true,
            ]));
        }
        return Command::SUCCESS;
    }
}
