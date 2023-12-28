<?php

require __DIR__ . '/../phpmig.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAccountsTable extends Migration
{
    /**
     * Создем таблицу accounts
     */
    public function up()
    {
        Capsule::schema()->create('accounts', function (Blueprint $table) {
            $table->integer('account_id')->primary();
            $table->string('unisender_api_key')->unique()->nullable();
            $table->text('amo_access_jwt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Удаляем таблицу accounts
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('accounts');
    }
}
