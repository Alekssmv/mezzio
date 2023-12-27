<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

/**
 * Репозиторий для работы с моделью User
 */
class UserRepository
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }
    /**
     * Создает пользователя с указанным именем в БД
     * @param string $name - имя пользователя
     * @return User
     */
    public function create(string $name): User
    {
        $this->user->name = $name;
        $this->user->save();
        return $this->user;
    }
}
