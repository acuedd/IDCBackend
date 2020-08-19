<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class XlsSeeder extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "INSERT INTO wt_webservices_operations ( op_uuid , modulo , descripcion , include_path , className , publica , acceso , activo , isNewMod , path_mainClass , class_mainClass , allowed_format , format_response , method_response , check_config_device , groupString) VALUES( 'd019c0ac-9cb9-11e7-93c0-286ed488ca86' , 'core' , 'Webservice para utilizar el plugin de xls' , 'webservices/webservices_core/webservice_master.php' , 'webservice_master' , 'N' , 'freeAccess' , 'Y' , 'Y' , 'libs/xls_load/xls_load.php' , 'xls_load' , 'w,am' , 'json' , 'process' , 'N' , '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( id , op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( '1' , 'd019c0ac-9cb9-11e7-93c0-286ed488ca86' , 'N' , 'Option' , '' , 'opt' , '' , '')";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
