<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для работы с таблицей users
 */
class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name'];
}
