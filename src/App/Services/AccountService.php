<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepository;

class AccountService
{
    private AccountRepository $accountRepository;
    public function __construct(
        AccountRepository $accountRepository
    ) {
        $this->accountRepository = $accountRepository;
    }
    /**
     * Создает пользователя с указанным именем в БД
     * @param string $name - имя пользователя
     * @return Account
     */
    public function create(array $data): Account
    {
        return $this->accountRepository->create($data);
    }
}
