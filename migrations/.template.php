<?= "<?php ";?>

require __DIR__ . '/../phpmig.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class <?= $className ?> extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        Capsule::schema()->dropIfExists('users');
    }
}