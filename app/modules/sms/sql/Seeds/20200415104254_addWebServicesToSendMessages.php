<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Phpmig\Migration\Migration;

class AddWebServicesToSendMessages extends Migration
{
    protected $tableWSOperation;
    protected $tableWSOperationData;
    public function init()
    {
        $this->tableWSOperation = "wt_webservices_operations";
        $this->tableWSOperationData = "wt_webservices_operations_extra_data";
    }
    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "INSERT INTO {$this->tableWSOperation} ( op_uuid , modulo , descripcion , include_path , className , publica , acceso , activo , isNewMod , path_mainClass , class_mainClass , allowed_format , format_response , method_response , check_config_device , groupString) VALUES( '307287dd-7dd9-11ea-81ae-0242ac140002' , 'sms' , 'Envío de mensajes' , 'webservices/webservices_core/webservice_master.php' , 'webservice_master' , 'Y' , 'freeAccess' , 'Y' , 'Y' , 'modules/sms/objects/send_sms/send_sms_controller.php' , 'sms_send_controller' , 'am' , 'json' , 'send_sms' , 'N' , '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO {$this->tableWSOperationData} ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( '307287dd-7dd9-11ea-81ae-0242ac140002' , 'N' , 'Número de telefono' , '' , 'msisdn' , 'El parametro msisdn no puede ir vacio' , '');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO {$this->tableWSOperationData} ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( '307287dd-7dd9-11ea-81ae-0242ac140002' , 'N' , 'Mensaje' , '' , 'message' , 'El parametro message no puede ir vacío' , '');";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $strQuery = "DELETE FROM {$this->tableWSOperation} WHERE op_uuid = '307287dd-7dd9-11ea-81ae-0242ac140002'";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "DELETE FROM {$this->tableWSOperationData} WHERE op = '307287dd-7dd9-11ea-81ae-0242ac140002'";
        Capsule::connection()->unprepared($strQuery);
    }
}
