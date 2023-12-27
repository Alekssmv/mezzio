<?php

namespace App\Repositories;

use App\Models\Token;

/**
 * Репозиторий для работы с таблицей accounts
 */
class TokenRepository
{
    /**
     * @var Token
     */
    private $token;

    public function __construct()
    {
        $this->token = new Token();
    }
    /**
     * Принимает массив данных для создания токена,
     * @param array $data - ключ - название поля в таблице, значение - значение поля
     * @return void
     */
    public function create(array $data): Token
    {
        $this->token->fill($data);
        $this->token->save();
        return $this->token;
    }

    public function getToken(int $accountId): Token
    {
        return $this->token->where('account_id', $accountId)->firstOrFail();
    }
}
