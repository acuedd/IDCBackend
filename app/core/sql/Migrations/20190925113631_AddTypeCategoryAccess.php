<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Phpmig\Migration\Migration;

class AddTypeCategoryAccess extends Migration
{
    protected $table;

    public function init()
    {
        $this->table = 'wt_core_extra_access';
    }
    /**
     * Do the migration
     */
    public function up()
    {
        Capsule::schema()->table("{$this->table}",function(Blueprint $table){
            $table->string('type');
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        Capsule::schema()->table("{$this->table}",function(Blueprint $table){
            $table->dropColumn('type');
        });
    }
}
