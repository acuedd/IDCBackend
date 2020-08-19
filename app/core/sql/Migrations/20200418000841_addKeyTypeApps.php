<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class AddKeyTypeApps extends Migration
{
    /**
     * Do the migration
     */
    protected $table;
    public function up()
    {
        $this->table = "wt_app_control_names";
        $strQuery = "ALTER TABLE $this->table
                     ADD COLUMN IF NOT EXISTS api_key VARCHAR(255)";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->table = "wt_app_control_names";
        $strQuery = "ALTER TABLE $this->table
                     DROP COLUMN IF EXISTS notification_type,
                     DROP COLUMN IF EXISTS api_key";
        Capsule::connection()->unprepared($strQuery);
    }
}
