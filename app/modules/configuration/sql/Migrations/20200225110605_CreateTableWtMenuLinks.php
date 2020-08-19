<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateTableWtMenuLinks extends Migration
{
    /**
     * Do the migration
     */
    protected $table;
    protected $schema;
    public function init(){
        $this->table = 'wt_menu_links';
        $this->schema = $this->get('schema');
    }
    public function up()
    {
        $this->schema->create($this->table, function(Blueprint $table){
            $table->increments('id');
            $table->string("title");
            $table->string("icon");
            $table->boolean("blank");
            $table->string("url");
            $table->integer("order_menu");
            $table->boolean("available");
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->schema->drop($this->table);
    }
}
