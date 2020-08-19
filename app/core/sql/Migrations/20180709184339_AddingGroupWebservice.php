<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddingGroupWebservice extends Migration
{

	protected $table;

	public function init()
	{
		$this->table = 'wt_webservices_operations';
	}

    /**
     * Do the migration
     */
    public function up()
    {
    	Capsule::schema()->table("{$this->table}",function(Blueprint $table){
		    $table->string("groupString",100);
	    });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
    	Capsule::schema()->table("{$this->table}",function (Blueprint $table){
		    $table->dropColumn("groupString");
	    });
    }
}
