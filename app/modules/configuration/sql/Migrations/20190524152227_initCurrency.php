<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class InitCurrency extends Migration
{
    private $strTable = "wt_currency";
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
        $this->schema->create($this->strTable, function(Blueprint $table){
            $table->increments("id");
            $table->string("symbol");
            $table->string("name");
            $table->decimal("decimal_digits",18,10)->default(0);
            $table->decimal("rounding",18,10)->default(0);
            $table->string("area_code");
            $table->string("name_plural");
            $table->enum("pivot", array("Y", "N"))->default("N");
            $table->timestamps();
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->schema->drop($this->strTable);
    }
}
