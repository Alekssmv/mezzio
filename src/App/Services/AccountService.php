<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepository;
use App\Interfaces\Service\AccountServiceInterface;

/**
 * Сервис для работы с аккаунтами
 */
class AccountService implements AccountServiceInterface
{
    private AccountRepository $accountRepository;
    public function __construct(
        AccountRepository $accountRepository
    ) {
        $this->accountRepository = $accountRepository;
    }
    /**
     * Создает аккаунт в таблице accounts
     * @param array $data - ключ - название поля в таблице, значение - значение поля
     * @return Account
     */
    public function create(array $data): Account
    {
        return $this->accountRepository->create($data);
    }

    /**
     * Ищет аккаунт по account_id
     * @param int $accountId
     * @return Account|null
     */
    public function findOrCreate(int $accountId): Account
    {
        return $this->accountRepository->findOrCreate($accountId);
    }

    /**
     * Добавляет jwt токен в запись аккаунта
     * @param int $accountId
     * @param string $token
     * @return Account
     */
    public function addAmoToken(int $accountId, string $token): Account
    {
        return $this->accountRepository->addAmoToken($accountId, $token);
    }

    /**
     * Добавляет unisender api key в запись аккаунта
     * @param int $accountId
     * @param string $apiKey
     * @return Account
     */
    public function addUnisenderApiKey(int $accountId, string $apiKey): Account
    {
        return $this->accountRepository->addUnisenderApiKey($accountId, $apiKey);
    }

    /**
     * Ищет аккаунт по account_id
     * @param int $accountId
     * @return Account|null
     */
    public function findByAccountId(int $accountId): ?Account
    {
        return $this->accountRepository->findByAccountId($accountId);
    }
}
