<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function create(string $name): User
    {
        $this->user->name = $name;
        $this->user->save();
        return $this->user;
    }
}
