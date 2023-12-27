<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Token;
use App\Repositories\AccountRepository;
use App\Repositories\TokenRepository;

/**
 * Сервис для работы с аккаунтами
 */
class TokenService
{
    private TokenRepository $tokenRepository;
    public function __construct(
        TokenRepository $tokenRepository
    ) {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Создает токен в таблице tokens
     * @param array $data - ключ - название поля в таблице, значение - значение поля
     * @return Token
     */
    public function create(array $data): Token
    {
        return $this->tokenRepository->create($data);
    }

    public function getToken(int $accountId): Token
    {
        return $this->tokenRepository->getToken($accountId);
    }
}
