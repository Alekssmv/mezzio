<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для работы с таблицей accounts
 */
class Account extends Model
{
    protected $table = 'accounts';
    protected $fillable = ['account_id', 'unisender_key'];
}
