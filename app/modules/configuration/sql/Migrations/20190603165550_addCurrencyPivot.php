<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Phpmig\Migration\Migration;

class AddCurrencyPivot extends Migration
{
    protected $table;

    public function init()
    {
        // TODO: Change the autogenerated stub
        $this->table = "wt_currency";
    }
    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "INSERT INTO {$this->table}(symbol, name, decimal_digits,rounding,area_code, name_plural, pivot,created_at, updated_at)
                        VALUES('GTQ', 'Guatemalan Quetzal',1.0,1.0,'GTQ','Guatemalan quetzals','Y',NOW(), NOW());";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $strQuery = "TRUNCATE {$this->table};";
        Capsule::connection()->unprepared($strQuery);
    }
}
