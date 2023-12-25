<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class AddUsersTable extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $capsule = $container['db']($container);

        $capsule->schema()->create('users', function ($table) {
            $table->string('name', 255);
            $table->timestamps();
        });
        
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $capsule = $container['db']($container);

        $capsule->schema()->dropIfExists('users');
    }
}
