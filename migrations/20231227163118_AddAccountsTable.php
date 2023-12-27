<?php

use Phpmig\Migration\Migration;

class AddAccountsTable extends Migration
{
    /**
     * Создем таблицу accounts
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db']($container);
        $db->schema()->create('accounts', function ($table) {
            $table->integer('account_id');
            $table->string('unisender_key');
            $table->timestamps();
        });
    }

    /**
     * Удаляем таблицу accounts
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db']($container);
        $db->schema()->drop('accounts');
    }
}
