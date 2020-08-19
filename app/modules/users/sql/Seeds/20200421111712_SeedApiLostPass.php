<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class SeedApiLostPass extends Migration
{
    protected $schema;

    public function init()
    {
        $this->schema = $this->get("schema");
    }

    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "INSERT INTO wt_webservices_operations ( op_uuid , modulo , descripcion , include_path , className , publica , acceso , activo , isNewMod , path_mainClass , class_mainClass , allowed_format , format_response , method_response , check_config_device , groupString) VALUES( 'd441a305-82f9-11ea-9a02-0242ac160002' , 'users' , '1 - Permite validar un usuario cuando se inicia el proceso de lostpass' , 'webservices/webservices_core/webservice_master.php' , 'webservice_master' , 'Y' , 'freeAccess' , 'Y' , 'Y' , 'modules/users/objects/lostpass/lostpass_controller.php' , 'lostpass_controller' , 'am' , 'json,xmlno' , 'validateUser' , 'N' , 'LostPasswd')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'd441a305-82f9-11ea-9a02-0242ac160002' , 'Y' , 'Nombre del usuario' , '' , 'username' , 'Missing username' , '');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'd441a305-82f9-11ea-9a02-0242ac160002' , 'Y' , 'Dispositivo id, puede ser imei o android id.' , '' , 'device_id' , 'Missing device_id' , '');";
        Capsule::connection()->unprepared($strQuery);

        $strQuery = "INSERT INTO wt_webservices_operations ( op_uuid , modulo , descripcion , include_path , className , publica , acceso , activo , isNewMod , path_mainClass , class_mainClass , allowed_format , format_response , method_response , check_config_device , groupString) VALUES( '3a6e2d46-82fa-11ea-9a02-0242ac160002' , 'users' , '2 - Permite la validación del token generado para el lostpass' , 'webservices/webservices_core/webservice_master.php' , 'webservice_master' , 'Y' , 'freeAccess' , 'Y' , 'Y' , 'modules/users/objects/lostpass/lostpass_controller.php' , 'lostpass_controller' , 'am' , 'json' , 'validateToken' , 'N' , 'LostPasswd')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( '3a6e2d46-82fa-11ea-9a02-0242ac160002' , 'Y' , 'Token que se envía por correo para la recuperación de contraseña' , '' , 'token' , 'Missing token' , '');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( '3a6e2d46-82fa-11ea-9a02-0242ac160002' , 'Y' , 'Id del dispositivo, puede ser imei o android id' , '' , 'device_id' , 'Missing device_id' , '');";
        Capsule::connection()->unprepared($strQuery);

        $strQuery = "INSERT INTO wt_webservices_operations ( op_uuid , modulo , descripcion , include_path , className , publica , acceso , activo , isNewMod , path_mainClass , class_mainClass , allowed_format , format_response , method_response , check_config_device , groupString) VALUES( '74614b38-82fa-11ea-9a02-0242ac160002' , 'users' , '3 - Permite actualizar el password en el proceso de lostpass' , 'webservices/webservices_core/webservice_master.php' , 'webservice_master' , 'Y' , 'freeAccess' , 'Y' , 'Y' , 'modules/users/objects/lostpass/lostpass_controller.php' , 'lostpass_controller' , 'am' , 'json' , 'savePass' , 'N' , 'LostPasswd')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( '74614b38-82fa-11ea-9a02-0242ac160002' , 'Y' , 'Password a cambiar' , '' , 'pass_1' , 'Missing password' , '');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( '74614b38-82fa-11ea-9a02-0242ac160002' , 'Y' , 'Id del dispositivo' , '' , 'device_id' , 'Missing device_id' , '');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( '74614b38-82fa-11ea-9a02-0242ac160002' , 'Y' , 'token' , '' , 'token' , 'Missing token' , '');";
        Capsule::connection()->unprepared($strQuery);

    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $strQuery = "DELETE FROM wt_webservices_operations WHERE op_uuid = 'd441a305-82f9-11ea-9a02-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "DELETE FROM wt_webservices_operations_extra_data WHERE op = 'd441a305-82f9-11ea-9a02-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);

        $strQuery = "DELETE FROM wt_webservices_operations WHERE op_uuid = '3a6e2d46-82fa-11ea-9a02-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "DELETE FROM wt_webservices_operations_extra_data WHERE op = '3a6e2d46-82fa-11ea-9a02-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);

        $strQuery = "DELETE FROM wt_webservices_operations WHERE op_uuid = '74614b38-82fa-11ea-9a02-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "DELETE FROM wt_webservices_operations_extra_data WHERE op = '74614b38-82fa-11ea-9a02-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);
    }
}
