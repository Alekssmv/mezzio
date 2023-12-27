<?php

use Phpmig\Migration\Migration;

class AddTokensTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $db = $container['db']($container);
        $db->schema()->create('tokens', function ($table) {
            $table->integer('account_id');
            $table->string('json_token');
            $table->timestamps();
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $db = $container['db']($container);
        $db->schema()->drop('tokens');
    }
}
