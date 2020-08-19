<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class CsvSeeder extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "INSERT INTO wt_webservices_operations ( op_uuid , modulo , descripcion , include_path , className , publica , acceso , activo , isNewMod , path_mainClass , class_mainClass , allowed_format , format_response , method_response , check_config_device , groupString) VALUES( 'd019c0ac-9cb9-11e7-93c0-286ed488ca87' , 'core' , 'Webservice para utilizar el plugin de csv' , 'webservices/webservices_core/webservice_master.php' , 'webservice_master' , 'N' , 'freeAccess' , 'Y' , 'Y' , 'libs/csv_load/csv_load.php' , 'csv_load' , 'w,am' , 'json' , 'process' , 'N' , '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'd019c0ac-9cb9-11e7-93c0-286ed488ca87' , 'N' , 'Option' , '' , 'opt' , '' , '')";
        Capsule::connection()->unprepared($strQuery);

        $strQuery = "INSERT INTO wt_csv_load_process VALUES ('1', 'modules/sales/objects/bulk_load/bulk_load_controller.php', 'bulk_load_controller', 'process_BalanceInquiryCsv', 'Y', 'BalanceInquiryCsv_process')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_csv_load_process VALUES ('2', 'modules/sales/objects/bulk_load/bulk_load_controller.php', 'bulk_load_controller', 'validate_BalanceInquiryCsv', 'Y', 'BalanceInquiryCsv_validate')";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        Capsule::connection()->unprepared("TRUNCATE TABLE wt_csv_load_process");
    }
}
