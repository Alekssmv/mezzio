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
}
