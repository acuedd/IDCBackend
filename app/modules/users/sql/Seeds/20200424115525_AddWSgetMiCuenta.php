<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class AddWSgetMiCuenta extends Migration
{
    private $schema;
    public function init()
    {
        $this->schema = $this->get('schema');
    }

    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "INSERT INTO wt_webservices_operations ( op_uuid , modulo , descripcion , include_path , className , publica , acceso , activo , isNewMod , path_mainClass , class_mainClass , allowed_format , format_response , method_response , check_config_device , groupString) VALUES( '46dd40c9-8654-11ea-9a71-0242ac160002' , 'users' , 'Devuelve la información del usuario para el apartado de mi cuenta' , 'webservices/webservices_core/webservice_master.php' , 'webservice_master' , 'N' , 'freeAccess' , 'Y' , 'Y' , 'modules/users/objects/myaccount/myaccount_controller.php' , 'myaccount_controller' , 'am' , 'json' , 'getMyAccount' , 'N' , 'Mi cuenta')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( '46dd40c9-8654-11ea-9a71-0242ac160002' , 'N' , 'Token del usuario' , '' , 'udid' , '' , '');";
        Capsule::connection()->unprepared($strQuery);


        $strQuery = "INSERT INTO wt_webservices_operations ( op_uuid , modulo , descripcion , include_path , className , publica , acceso , activo , isNewMod , path_mainClass , class_mainClass , allowed_format , format_response , method_response , check_config_device , groupString) VALUES( 'c064ea42-8655-11ea-9a71-0242ac160002' , 'users' , 'Permite modificar información del usuario para' , 'webservices/webservices_core/webservice_master.php' , 'webservice_master' , 'N' , 'freeAccess' , 'Y' , 'Y' , 'modules/users/objects/myaccount/myaccount_controller.php' , 'myaccount_controller' , 'am' , 'json' , 'save_user' , 'N' , 'Mi cuenta')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'c064ea42-8655-11ea-9a71-0242ac160002' , 'Y' , 'Id del usuario' , '' , 'userid' , 'Missing userid' , 'uid');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'c064ea42-8655-11ea-9a71-0242ac160002' , 'Y' , 'Nombre del usuario' , '' , 'nameUser' , '' , 'iNombres');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'c064ea42-8655-11ea-9a71-0242ac160002' , 'Y' , 'Apellido del usuario' , '' , 'lastnameUser' , '' , 'iApellidos');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'c064ea42-8655-11ea-9a71-0242ac160002' , 'Y' , 'nickname' , '' , 'nickanemUser' , '' , 'iUsual');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'c064ea42-8655-11ea-9a71-0242ac160002' , 'Y' , 'email' , '' , 'emailUser' , '' , 'iCorreo');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'c064ea42-8655-11ea-9a71-0242ac160002' , 'Y' , 'sexo' , '' , 'sexUser' , '' , 'sSexo');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'c064ea42-8655-11ea-9a71-0242ac160002' , 'Y' , 'pais' , '' , 'countryUser' , '' , 'sPais');";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op , required , parameter_description , method_validation , key_parameter , error_response , transform_key) VALUES( 'c064ea42-8655-11ea-9a71-0242ac160002' , 'Y' , 'celular del usuario' , '' , 'celphoneUser' , '' , 'iCelular');";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $strQuery = "DELETE FROM wt_webservices_operations_extra_data WHERE op = '46dd40c9-8654-11ea-9a71-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "DELETE FROM wt_webservices_operations_extra_data WHERE op = '46dd40c9-8654-11ea-9a71-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);

        $strQuery = "DELETE FROM wt_webservices_operations_extra_data WHERE op = 'c064ea42-8655-11ea-9a71-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "DELETE FROM wt_webservices_operations_extra_data WHERE op = 'c064ea42-8655-11ea-9a71-0242ac160002'";
        Capsule::connection()->unprepared($strQuery);
    }
}
