<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class AddTriggerDeactivateUser extends Migration
{
	private $schema;

	public function init()
	{
		$this->schema = $this->get('schema');
	}

    /**
     * Do the migration
     */
    public function up()
    {
	    $strQuery = "CREATE TRIGGER update_users_active BEFORE UPDATE ON wt_users
	                FOR EACH ROW BEGIN	                
	                    IF (NEW.active = 'N') THEN
	                        SET NEW.fecha_retiro = NOW();
	                        SET NEW.retirado = 'Y';
                        ELSEIF (NEW.active = 'Y') THEN
                            SET NEW.fecha_ingreso = NOW();
	                        SET NEW.retirado = 'N';     
	                    END IF;	                   	                     	                  
	                END;";
	    Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
	    $strQuery = "DROP TRIGGER update_users_active";
	    Capsule::connection()->unprepared($strQuery);
    }
}
