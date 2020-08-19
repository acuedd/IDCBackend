<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateTableWtProfileConfiguration extends Migration
{
    protected $table;
    protected $schema;
    public function init(){
        $this->table = 'wt_profile_configuration';
        $this->schema = $this->get('schema');
    }
    public function up()
    {
        $this->schema->create($this->table, function(Blueprint $table){
            $table->increments('id');
            $table->string("type");
            $table->string("specified");
            $table->string("path");
            $table->string("color");
            $table->string("title");
            $table->string("description");
        });
        $strQuery = "INSERT
                     INTO wt_profile_configuration
                     (id, `type`, specified, path, color, title, description)
                     VALUES (null, 'image', 'img-start', 'var/configuration/theme/eintein_genius.png', '', 'Imagen de inicio', 'Recurso para imagen en inicio'),
                     (null, 'image', 'img-menu-exp', 'themes/geniusAdminLTE/images/theme_image_principal.png', '', 'Imagen de menu maximizado', 'Imagen para menu'),
                     (null, 'image', 'img-menu-min', 'themes/geniusAdminLTE/images/theme_image_principal_mini.png', '', 'Imagen de menu minimizado', 'Imagen para menu'),
                     (null, 'image', 'img-start-bkg', '', '', 'Imagen de fondo en inicio', 'Imagen de fondo en pantalla de login'),
                     (null, 'color', 'color-menu', '', '#1c2637', 'Color de menu', 'perfil'),
                     (null, 'color', 'color-menu-elements', '', '#2c3b41', 'Color de fondo en menu', 'Color de elementos activos en menu'),
                     (null, 'color', 'color-deg', '', '#', 'Color extra de degradado', 'Color para degradado opcinal'),
                     (null, 'color', 'color-menu-text', '', '#fff', 'Color de texto en menu', 'Color de texto en menu'),
                     (null, 'color', 'color-menu-active', '', '#1e282c', 'Color de elemento activo en menu', 'Color de un elemento en menu'),
                     (null, 'color', 'color-title', '', '#2e4e78', 'Color de titulo', 'Color de fondo de titulo'),
                     (null, 'color', 'color-title-text', '', '#', 'Color de texto en titulo', 'Color de texto'),
                     (null, 'color', 'color-start-bkg', '', '#1e282c', 'Color de fondo en inicio', 'Color de fondo en inicio');";
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
