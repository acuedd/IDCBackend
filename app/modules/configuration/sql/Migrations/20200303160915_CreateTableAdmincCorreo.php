<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateTableAdmincCorreo extends Migration
{
    protected $table;
    protected $schema;

    public function init()
    {
        // TODO: Change the autogenerated stub
        $this->table = "wt_Profile_Configuration_Correo";
        $this->schema = $this->get('schema');
    }
    /**
     * Do the migration
     */
    public function up()
    {
        $this->schema->create($this->table, function (Blueprint $table) {
            $table->increments("id");
            $table->string("title");
            $table->string("description");
            $table->string("path");
            $table->string("position_Image");
            $table->string("link");
            $table->boolean("allow", array("0", "1"))->default("0");
            $table->timestamps();
        });
        $strQuery = "INSERT
                     INTO wt_Profile_Configuration_Correo
                     (id, `title`, description, position_Image)
                     VALUES(null,'Imagen para borde superior','Se recomienda usar imagenes con las siguientes dimensiones Width 781 pixels, Heigth 81 pixels, de forma horizontal','Borde_superior'),
                     (null,'Imagen para borde Inferior','Se recomienda usar imagenes con las siguientes dimensiones Width 781 pixels, Heigth 81 pixels, de forma horizontal','Borde_inferior');";
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