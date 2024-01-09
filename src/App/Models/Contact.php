<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для работы с таблицей accounts
 */
class Contact extends Model
{
    protected $table = 'contacts';
    protected $fillable = ['id', 'email'];
}
