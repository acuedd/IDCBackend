<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class StartXLSLoad extends Migration
{

	private $schema;
	private $arrTables;

	public function init()
	{
		$this->schema = $this->get('schema');
		$this->arrTables = [
			"wt_xls_load"=>"wt_xls_load",
			"wt_xls_load_data"=>"wt_xls_load_data",
			"wt_xls_load_data_error"=>"wt_xls_load_data_error",
			"wt_xls_load_process"=>"wt_xls_load_process",
		];
	}
    /**
     * Do the migration
     */
    public function up()
    {
	    $this->schema->create($this->arrTables["wt_xls_load"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("userid");
			$table->date("date");
			$table->time("time");
			$table->string("file",255);
			$table->enum("status",['insert','progress','success'])->default("insert");
	    });
	    $this->schema->create($this->arrTables["wt_xls_load_data"],function(Blueprint $table){
	    	$table->increments("id");
	    	$table->integer("id_sheet");
	    	$table->integer("line");
	    	$table->text("data");
	    	$table->enum("process",["Y","N"])->default("N");
	    });
	    $this->schema->create($this->arrTables["wt_xls_load_data_error"],function(Blueprint $table){
			$table->increments("id");
			$table->integer("id_sheet");
			$table->integer("line");
			$table->text("data");
	    });
	    $this->schema->create($this->arrTables["wt_xls_load_process"],function(Blueprint $table){
			$table->increments("id");
			$table->string("path",200);
			$table->string("class",20);
			$table->string("method",20);
			$table->enum("active",["Y","N"])->default("Y");
			$table->string("word_key",25);
	    });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
	    foreach($this->arrTables AS $key => $table){
		    $this->schema->dropIfExists($table);
	    }
    }
}
