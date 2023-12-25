<?php

use Phpmig\Migration\Migration;
class AddUsersTable extends Migration
{
    /**
     * Создем таблицу users
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db']($container);
        $db->schema()->create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Удаляем таблицу users
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db']($container);
        $db->schema()->drop('users');
    }
}
