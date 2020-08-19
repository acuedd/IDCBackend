<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class CreateTableInstallModuleSMS extends Migration
{
    private $arrTables = [];
    private $schema;

    public function init()
    {
        $this->schema = $this->get('schema');
        $this->arrTables = [
            "wt_sms_config" => "wt_sms_config",
            "wt_sms_error_log" => "wt_sms_error_log",
            "wt_sms_mensajes" => "wt_sms_mensajes",
        ];
    }

    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "CREATE TABLE `wt_sms_config` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `descripcion` varchar(100) NOT NULL,
                      `key_validate` varchar(50) NOT NULL,
                      `key_secret` varchar(50) NOT NULL,
                      `fecha_creacion` date NOT NULL,
                      `active` enum('Y','N') NOT NULL,
                      `cod_area` varchar(10) NOT NULL DEFAULT '502',
                      `max_length` int(11) NOT NULL DEFAULT 145,
                      `url_send` varchar(255) NOT NULL,
                      `short_code_id` varchar(255) NOT NULL,
                      `token` varchar(255) NOT NULL,
                      `username` varchar(255) NOT NULL,
                      `password` varchar(255) NOT NULL,
                      `organization_id` varchar(255) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        Capsule::connection()->unprepared($strQuery);

        $strQuery = "CREATE TABLE `wt_sms_error_log` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `mensaje_id` int(11) NOT NULL,
                      `fecha` datetime NOT NULL,
                      `descripcion` varchar(200) NOT NULL,
                      `usuario` int(11) NOT NULL,
                      `proveedor` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        Capsule::connection()->unprepared($strQuery);

        $strQuery = "CREATE TABLE `wt_sms_mensajes` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `fecha` date NOT NULL,
                      `hora` time NOT NULL,
                      `destino` varchar(15) NOT NULL,
                      `mensaje` varchar(500) NOT NULL,
                      `usuario` int(11) NOT NULL,
                      `proveedor` int(11) NOT NULL,
                      `status` enum('ok','fail','process') NOT NULL DEFAULT 'process',
                      `ref` int(11) NOT NULL DEFAULT 0,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        foreach($this->arrTables AS $key => $table){
            $this->schema->drop($table);
        }
    }
}
