<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateTableWtCaptionsInfo extends Migration
{
    /**
     * Do the migration
     */
    protected $table;
    protected $schema;
    public function init(){
        $this->table = 'wt_caption_info';
        $this->schema = $this->get('schema');
    }
    public function up()
    {
        $this->schema->create($this->table, function(Blueprint $table){
            $table->increments('id');
            $table->string("content");
            $table->string('title');
            $table->string("caption_position");
        });
        $strQuery = "INSERT
                     INTO wt_caption_info
                     (content, title, caption_position)
                     VALUES 
                     ('¿Necesitas ayuda? | Comunícate al (+502)-3046-8139 o a | eacu@homeland.com.gt', 'Login', 'caption-login'),
                     ('¿Necesitas ayuda? | Comunícate al (+502)-3046-8139 o a | eacu@homeland.com.gt', 'Menu', 'caption-dashboard')
                     ;";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->schema->drop($this->table);
    }
}
