<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для работы с таблицей tokens
 */
class Token extends Model
{
    protected $table = 'tokens';
    protected $fillable = ['account_id', 'json_token'];
}
