<?php
require __DIR__ . '/../phpmig.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Добавление столбца enum_codes в таблицу accounts
 */
class UpdateAccountsTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        Capsule::schema()->table('accounts', function (Blueprint $table) {
            $table->string('enum_codes')->after('amo_access_jwt')->nullable();
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        Capsule::schema()->table('accounts', function (Blueprint $table) {
            $table->dropColumn('enum_codes');
        });
    }
}