<?php

namespace App\Repositories;

use App\Models\Account;
use App\Interfaces\Repository\AccountRepositoryInterface;

/**
 * Репозиторий для работы с аккаунтами
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
     * ключ - название поля в таблице, значение - значение поля
     * @param array $data
     * @return void
     */
    public function create(array $data)
    {
        $this->account->fill($data);
        $this->account->save();
    }
    return $this->account;
}
