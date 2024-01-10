<?php

namespace App\Repositories;

use App\Models\Account;
use App\Interfaces\Repository\AccountRepositoryInterface;

/**
 * Репозиторий для работы с таблицей accounts
 */
class AccountRepository implements AccountRepositoryInterface
{
    /**
     * @var Account
     */
    private $account;

    public function __construct()
    {
        $this->account = new Account();
    }
    /**
     * Принимает массив данных для создания аккаунта,
     * @param array $data - ключ - название поля в таблице, значение - значение поля
     * @return void
     */
    public function create(array $data): Account
    {
        $this->account->fill($data);
        $this->account->save();
        return $this->account;
    }

    /**
     * Ищет аккаунт по account_id
     * @param int $accountId
     * @return Account|null
     */
    public function findByAccountId(int $accountId): ?Account
    {
        return $this->account->where('account_id', $accountId)->first();
    }

    /**
     * Ищет аккаунт по account_id, если не находит - создает
     * @param int $accountId
     * @return Account
     */
    public function findOrCreate(int $accountId): Account
    {
        $account = $this->findByAccountId($accountId);
        if (!$account) {
            $account = $this->create(['account_id' => $accountId]);
        }
        return $account;
    }

    /**
     * Добавляет jwt токен в запись аккаунта
     * @param int $accountId
     * @param string $token
     * @return Account
     */
    public function addAmoToken(int $accountId, string $token): Account
    {
        $account = $this->findByAccountId($accountId);
        $account->amo_access_jwt = $token;
        $account->save();
        return $account;
    }

    /**
     * Добавляет unisender_api_key в запись аккаунта
     * @param int $accountId
     * @param string $apiKey
     * @return Account
     */
    public function addUnisenderApiKey(int $accountId, string $apiKey): Account
    {
        $account = $this->findByAccountId($accountId);
        $account->unisender_api_key = $apiKey;
        $account->save();
        return $account;
    }

    /**
     * Добавляет enum_codes в запись аккаунта
     * @param int $accountId
     * @param string $enumCodes
     * @return Account
     */
    public function addEnumCodes(int $accountId, string $enumCodes): Account
    {
        $account = $this->findByAccountId($accountId);
        $account->enum_codes = $enumCodes;
        $account->save();
        return $account;
    }
}
