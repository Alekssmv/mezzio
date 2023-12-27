<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\User;

class UserService
{
    private UserRepository $userRepository;
    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }
    /**
     * Создает пользователя с указанным именем в БД
     * @param string $name - имя пользователя
     * @return User
     */
    public function create(string $name): User
    {
        return $this->userRepository->create($name);
    }
}
