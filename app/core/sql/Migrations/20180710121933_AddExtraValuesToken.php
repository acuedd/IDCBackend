<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddExtraValuesToken extends Migration
{
	protected $table;

	public function init()
	{
		$this->table = 'wt_tokens';
	}
    /**
     * Do the migration
     */
    public function up()
    {
	    Capsule::schema()->table("{$this->table}",function(Blueprint $table){
		    $table->string("sessionData",100);
		    $table->timestamp("created_at");
	    });

	    $strQuery = "CREATE TRIGGER created_at_token BEFORE INSERT ON `{$this->table}` FOR EACH ROW
		            BEGIN
		                SET NEW.created_at = NOW();                       		               
		            END";
	    Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
	    $strQuery = "DROP TRIGGER IF EXISTS  `created_at_token`";
	    Capsule::connection()->unprepared($strQuery);

	    Capsule::schema()->table("{$this->table}",function (Blueprint $table){
		    $table->dropColumn("sessionData");
		    $table->dropColumn("created_at");
	    });
    }
}
