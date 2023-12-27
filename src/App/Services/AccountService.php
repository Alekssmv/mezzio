<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepository;

/**
 * Сервис для работы с аккаунтами
 */
class AccountService
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
}
