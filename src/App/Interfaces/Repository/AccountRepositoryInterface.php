<?php

namespace App\Interfaces\Repository;

use App\Models\Account;

/**
 * Интерфейс для репозитория аккаунтов
 */
interface AccountRepositoryInterface
{
    /**
     * Принимает массив данных для создания аккаунта,
     * ключ - название поля в таблице, значение - значение поля
     * @param array $data
     * @return void
     */
    public function create(array $data);

    /**
     * Ищет аккаунт по account_id
     * @param int $accountId
     * @return Account|null
     */
    public function findByAccountId(int $accountId): ?Account;

    /**
     * Ищет аккаунт по account_id, если не находит - создает
     * @param int $accountId
     * @return Account
     */
    public function findOrCreate(int $accountId): Account;

    /**
     * Добавляет jwt токен в запись аккаунта
     * @param int $accountId
     * @param string $token - json web token
     * @return Account
     */
    public function addAmoToken(int $accountId, string $token): Account;

    /**
     * Добавляет unisender api key в запись аккаунта
     * @param int $accountId
     * @param string $apiKey
     * @return Account
     */
    public function addUnisenderApiKey(int $accountId, string $apiKey): Account;
}
