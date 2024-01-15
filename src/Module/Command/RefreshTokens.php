<?php

namespace Module\Command;

use AmoCRM\Client\AmoCRMApiClient;
use App\Services\AccountService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;
use League\OAuth2\Client\Token\AccessToken;

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

    public function __construct(
        AccountService $accountService,
        AmoCRMApiClient $amoCRMApiClient
    ) {
        parent::__construct();
        $this->accountService = $accountService;
        $this->amoCRMApiClient = $amoCRMApiClient;
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
        $accountService = $this->accountService;
        $amoCRMApiClient = $this->amoCRMApiClient;

        $accountIds = $accountService->getAllIds();
        foreach ($accountIds as $accountId) {
            $account = $accountService->findByAccountId($accountId);

            /**
             * Если нет токена, пропускаем
             */
            if ($account->amo_access_jwt === null) {
                echo "Аккаунт {$accountId} не имеет токенов" . PHP_EOL;
                continue;
            }

            /**
             * Обновляем токен
             */
            try {
                $accessToken = json_decode($account->amo_access_jwt, true);
                $accessToken = new AccessToken([
                    'access_token' => $accessToken['accessToken'],
                    'refresh_token' => $accessToken['refreshToken'],
                    'expires' => $accessToken['expires'],
                    'baseDomain' => $accessToken['baseDomain'],
                ]);
                $accessToken = $amoCRMApiClient->getOAuthClient()->getAccessTokenByRefreshToken($accessToken);
                $accountService->addAmoToken($accountId, $accessToken->jsonSerialize());
                echo "Токен аккаунта {$accountId} обновлен" . PHP_EOL;
                continue;
            } catch (Exception $e) {
                echo "Ошибка обновления токена аккаунта {$accountId}" . $e->getMessage() . PHP_EOL;
                continue;
            }
        }
        return Command::SUCCESS;
    }
}
