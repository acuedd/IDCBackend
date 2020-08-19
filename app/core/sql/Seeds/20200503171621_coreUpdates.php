<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class CoreUpdates extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $strQuery = "INSERT INTO wt_swusertypes (name,descr) VALUES ('ext_homeland','Homeland')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_users (uid,name,password,class,swusertype,realname,nickname,apellidos,nombres,email,country,sex,active,dateregistered,dateactivated)
                     VALUES('1', 'HomelandWM', '790dc6a8eb5d85f41815e01dd9ecebf4', 'admin', 'ext_homeland', 'Homeland, Webmaster','Webmaster Homeland', 'Homeland', 'Webmaster', 'webmaster@homeland.com.gt', 'Guatemala', 'Male', 'Y', now(),now())";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "UPDATE wt_users SET class = 'helpdesk' WHERE swusertype= 'ext_homeland' AND class <> 'admin' AND retirado = 'N' AND email LIKE '%homeland%'";
        Capsule::connection()->unprepared($strQuery);
        #20170526
        $strQuery = "update wt_users SET password = md5('Sg!aTh4tCh.') WHERE uid = 1";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = 'REPLACE INTO wt_config VALUES (\'core\', \'a:59:{s:5:"title";s:8:"Homeland";s:20:"Save_Unencrypted_pwd";b:0;s:23:"Unencrypted_swusertypes";s:0:"";s:10:"Show_email";b:0;s:6:"slogan";s:0:"";s:11:"LogWithMail";b:0;s:13:"update_server";b:0;s:4:"GZIP";b:1;s:18:"CACHE_CSS_AND_JAVA";b:1;s:3:"url";s:17:"http://localhost/";s:5:"HTTPS";b:0;s:10:"url_secure";s:18:"https://localhost/";s:8:"keywords";s:18:"php,mysql,homeland";s:11:"description";s:8:"Homeland";s:17:"drawExternalFrame";b:1;s:15:"show_news_first";b:0;s:15:"show_login_only";i:1;s:19:"allow_multi_session";b:0;s:23:"IPBlockOperation_public";s:3:"any";s:24:"IPBlockOperation_private";s:3:"any";s:23:"use_nickname_in_welcome";b:0;s:14:"page_processed";b:0;s:18:"page_processed_LOG";b:1;s:21:"query_performance_log";b:0;s:19:"hide_homeland_users";b:1;s:14:"auto_user_name";b:0;s:18:"directorio_publico";b:0;s:26:"ocultar_directorio_interno";b:0;s:18:"show_personal_info";b:0;s:13:"birthdayAlert";b:1;s:17:"UserMayChangeName";b:1;s:7:"lostPWD";b:0;s:12:"showVisitors";b:1;s:9:"changePWD";b:1;s:18:"UserMayChangeEMail";b:1;s:17:"find_user_by_code";b:0;s:9:"logreport";b:1;s:10:"visits_log";b:1;s:12:"visits_graph";b:0;s:10:"name_admin";s:9:"Webmaster";s:10:"mail_admin";s:25:"webmaster@homeland.com.gt";s:12:"sess_timeout";s:2:"20";s:25:"sess_timeout_notification";b:1;s:4:"lang";s:3:"esp";s:5:"theme";s:7:"default";s:12:"site_profile";s:0:"";s:13:"theme_interno";s:0:"";s:11:"images_path";s:6:"images";s:11:"date_format";s:6:"fmtEUR";s:9:"municipio";s:9:"Guatemala";s:9:"tempUsers";b:0;s:14:"AccountRequest";b:0;s:19:"AccountRequest_type";s:20:"ext_RequestedAccount";s:28:"AccountRequest_type_internal";b:0;s:21:"AccountRequest_manual";b:0;s:20:"AccountRequest_email";s:29:"webmaster@homeland-online.com";s:7:"avatars";b:1;s:17:"avatars_adminonly";b:1;s:13:"sendmail_qtde";s:3:"300";}\')';
        Capsule::connection()->unprepared($strQuery);
        #20170607
        $strQuery = 'REPLACE INTO wt_config VALUES ("modules", "a:1:{s:5:\"users\";s:2:\"on\";}")';
        Capsule::connection()->unprepared($strQuery);
        #20170601
        $strQuery = "INSERT INTO wt_swusertypes (name,descr) VALUES ('admon','Administracion')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_swusertypes (name,descr) VALUES ('staff','Staff')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations (op_uuid, modulo, descripcion, include_path, className, publica, acceso, activo)
                     VALUES ('c5427272-fd06-11e1-b6dc-b51e77f97e87', 'core', 'Devuelve el see also de un link', 'webservices/webservices_core/see_also.php', 'see_also', 'N', 'freeAccess', 'Y')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations (op_uuid, modulo, descripcion, include_path, className, publica, acceso, activo)
                     VALUES ('83ef480f-000c-11e2-8a03-a73a2d3170a0', 'core', 'Webservice para actualizar los datos de un dispositivo.  Solo se usa desde myaccount.php', 'webservices/webservices_core/save_device_info.php', 'save_device_info', 'N', 'freeAccess', 'Y')";
        Capsule::connection()->unprepared($strQuery);
        #20180607
        $strQuery = "INSERT INTO wt_webservices_operations ( op_uuid, modulo, descripcion, include_path, className, publica, acceso, activo, isNewMod, path_mainClass, class_mainClass, allowed_format, format_response, method_response, check_config_device) 
                     VALUES ('42be2e78-69d3-11e8-84ec-286ed488d291', 'core', 'Registra un dispositivo móvil', 'webservices/webservices_core/webservice_master.php', 'webservice_master', 'Y', 'freeAccess', 'Y', 'Y', 'core/objects/devices/devices_controller.php', 'devices_controller', 'w,wm,am', 'json,xmlno', 'registerDevice', 'N')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "DELETE FROM wt_webservices_operations_extra_data WHERE op = '42be2e78-69d3-11e8-84ec-286ed488d291'";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'Y', 'nombre del usuario', '', 'username', 'WEBSERVICES_ERROR003', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'Y', 'password del usuario', '', 'password', 'WEBSERVICES_ERROR003', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'N', 'si es smartphone o tablet', '', 'tipo', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'N', 'la marca del device', '', 'marca', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'N', 'modelo del device', '', 'modelo', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'N', 'versión del sistema operativo', '', 'OSversion', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'Y', 'la versión del app', '', 'appversion', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'Y', 'codigo unico de dispositivo', '', 'dispositivo_id', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'N', 'version de api a utilizar', '', 'apiversion', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'Y', 'nombre del aplicativo', '', 'appname', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'N', 'token para google notifications', '', 'token_gcm', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'N', 'numero de telefono', '', 'phoneNumber', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '42be2e78-69d3-11e8-84ec-286ed488d291', 'Y', 'nombre del sistema operativo', '', 'OS', '', '')";
        Capsule::connection()->unprepared($strQuery);

        $strQuery = "INSERT INTO wt_webservices_operations ( op_uuid, modulo, descripcion, include_path, className, publica, acceso, activo, isNewMod, path_mainClass, class_mainClass, allowed_format, format_response, method_response, check_config_device) VALUES ( '75f911ba-6a66-11e8-84ec-286ed488d291', 'core', 'Check udid', 'webservices/webservices_core/webservice_master.php', 'webservice_master', 'Y', 'freeAccess', 'Y', 'Y', 'core/objects/devices/devices_controller.php', 'devices_controller', 'w,wm,am', 'json,xmlno', 'checkUUDID', 'N')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "DELETE FROM wt_webservices_operations_extra_data WHERE op = '75f911ba-6a66-11e8-84ec-286ed488d291'";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '75f911ba-6a66-11e8-84ec-286ed488d291', 'Y', 'Udid del device', '', 'udid', 'WEBSERVICES_ERROR003', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '75f911ba-6a66-11e8-84ec-286ed488d291', 'N', 'la versión del app', '', 'appversion', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '75f911ba-6a66-11e8-84ec-286ed488d291', 'N', 'version de api a utilizar', '', 'apiversion', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '75f911ba-6a66-11e8-84ec-286ed488d291', 'N', 'nombre del sistema operativo', '', 'OS', '', '')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_webservices_operations_extra_data ( op, required, parameter_description, method_validation, key_parameter, error_response, transform_key) VALUES ( '75f911ba-6a66-11e8-84ec-286ed488d291', 'N', 'nombre del aplicativo', '', 'appname', '', '')";
        Capsule::connection()->unprepared($strQuery);

        #20150122
        $strQuery = "TRUNCATE TABLE wt_error_code";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('1', '3', 'credit_card', '03001', 'TARJETA_CREDITO_DEVICE_ACTIVE')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('2', '2', 'credit_card', '02001', 'VISANET_ERR001')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('3', '2', 'credit_card', '02012', 'VISANET_ERR002')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('4', '2', 'credit_card', '02013', 'VISANET_ERR003')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('5', '2', 'credit_card', '02014', 'VISANET_ERR004')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('6', '2', 'credit_card', '02015', 'VISANET_ERR005')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('7', '2', 'credit_card', '02016', 'VISANET_ERR006')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('8', '2', 'credit_card', '02017', 'VISANET_ERR007')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('9', '2', 'credit_card', '02018', 'VISANET_ERR008')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('10', '2', 'credit_card', '02019', 'VISANET_ERR009')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('11', '2', 'credit_card', '02020', 'VISANET_ERR010')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('12', '2', 'credit_card', '02021', 'VISANET_ERR011')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('13', '2', 'credit_card', '02022', 'VISANET_ERR012')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('14', '2', 'credit_card', '02011', 'VISANET_ERR013')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('15', '2', 'credit_card', '02010', 'VISANET_ERR014')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('16', '2', 'credit_card', '02006', 'VISANET_ERR015')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('17', '2', 'credit_card', '02034', 'VISANET_ERR016')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('18', '2', 'credit_card', '02035', 'VISANET_ERR017')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('19', '2', 'credit_card', '02036', 'VISANET_ERR018')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('20', '2', 'credit_card', '02002', 'VISANET_ERR019')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('21', '2', 'credit_card', '02003', 'VISANET_ERR020')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('22', '2', 'credit_card', '02004', 'VISANET_ERR021')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('23', '2', 'credit_card', '02005', 'VISANET_ERR022')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('24', '2', 'credit_card', '02007', 'VISANET_ERR024')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('25', '2', 'credit_card', '02008', 'VISANET_ERR025')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('26', '2', 'credit_card', '02009', 'VISANET_ERR026')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('27', '2', 'credit_card', '02024', 'VISANET_ERR027')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('28', '2', 'credit_card', '02025', 'VISANET_ERR028')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('29', '2', 'credit_card', '02026', 'VISANET_ERR029')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('30', '2', 'credit_card', '02027', 'VISANET_ERR030')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('31', '2', 'credit_card', '02040', 'VISANET_ERR031')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('32', '2', 'credit_card', '02028', 'VISANET_ERR032')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('33', '2', 'credit_card', '02029', 'VISANET_ERR033')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('34', '2', 'credit_card', '02030', 'VISANET_ERR034')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('35', '2', 'credit_card', '02031', 'VISANET_ERR035')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('36', '2', 'credit_card', '02032', 'VISANET_ERR036')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('37', '2', 'credit_card', '02033', 'VISANET_ERR037')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('38', '2', 'credit_card', '02037', 'VISANET_ERR038')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('39', '2', 'credit_card', '02038', 'VISANET_ERR039')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('40', '2', 'credit_card', '02039', 'VISANET_ERR040')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('41', '2', 'credit_card', '02041', 'VISANET_ERR041')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('42', '2', 'credit_card', '02042', 'VISANET_ERR042')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('43', '1', 'core', '01001', 'WEBSERVICES_ERROR001')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('44', '1', 'core', '01002', 'WEBSERVICES_ERROR002')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('45', '1', 'core', '01003', 'WEBSERVICES_ERROR003')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('46', '1', 'core', '01004', 'WEBSERVICES_ERROR004')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('47', '1', 'core', '01005', 'WEBSERVICES_ERROR005')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('48', '1', 'core', '01006', 'WEBSERVICES_ERROR006')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('49', '1', 'core', '01007', 'WEBSERVICES_ERROR007')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('50', '1', 'core', '01008', 'WEBSERVICES_ERROR008')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('51', '1', 'core', '01009', 'WEBSERVICES_ERROR009')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "INSERT INTO wt_error_code VALUES ('52', '1', 'core', '01010', 'WEBSERVICES_ERROR010')";
        Capsule::connection()->unprepared($strQuery);
        $strQuery = "REPLACE INTO wt_config VALUES ('core', 'a:59:{s:5:\"title\";s:8:\"Homeland\";s:20:\"Save_Unencrypted_pwd\";b:0;s:23:\"Unencrypted_swusertypes\";s:0:\"\";s:10:\"Show_email\";b:0;s:6:\"slogan\";s:0:\"\";s:11:\"LogWithMail\";b:0;s:13:\"update_server\";b:0;s:4:\"GZIP\";b:1;s:18:\"CACHE_CSS_AND_JAVA\";b:1;s:3:\"url\";s:17:\"http://localhost/\";s:5:\"HTTPS\";b:0;s:10:\"url_secure\";s:18:\"https://localhost/\";s:8:\"keywords\";s:18:\"php,mysql,homeland\";s:11:\"description\";s:8:\"Homeland\";s:17:\"drawExternalFrame\";b:1;s:15:\"show_news_first\";b:0;s:15:\"show_login_only\";i:1;s:19:\"allow_multi_session\";b:0;s:23:\"IPBlockOperation_public\";s:3:\"any\";s:24:\"IPBlockOperation_private\";s:3:\"any\";s:23:\"use_nickname_in_welcome\";b:0;s:14:\"page_processed\";b:0;s:18:\"page_processed_LOG\";b:1;s:21:\"query_performance_log\";b:0;s:19:\"hide_homeland_users\";b:1;s:14:\"auto_user_name\";b:0;s:18:\"directorio_publico\";b:0;s:26:\"ocultar_directorio_interno\";b:0;s:18:\"show_personal_info\";b:0;s:13:\"birthdayAlert\";b:1;s:17:\"UserMayChangeName\";b:1;s:7:\"lostPWD\";b:0;s:12:\"showVisitors\";b:1;s:9:\"changePWD\";b:1;s:18:\"UserMayChangeEMail\";b:1;s:17:\"find_user_by_code\";b:0;s:9:\"logreport\";b:1;s:10:\"visits_log\";b:1;s:12:\"visits_graph\";b:0;s:10:\"name_admin\";s:9:\"Webmaster\";s:10:\"mail_admin\";s:25:\"webmaster@homeland.com.gt\";s:12:\"sess_timeout\";s:2:\"20\";s:25:\"sess_timeout_notification\";b:1;s:4:\"lang\";s:3:\"esp\";s:5:\"theme\";s:14:\"geniusAdminLTE\";s:12:\"site_profile\";s:0:\"\";s:13:\"theme_interno\";s:0:\"\";s:11:\"images_path\";s:6:\"images\";s:11:\"date_format\";s:6:\"fmtEUR\";s:9:\"municipio\";s:9:\"Guatemala\";s:9:\"tempUsers\";b:0;s:14:\"AccountRequest\";b:0;s:19:\"AccountRequest_type\";s:20:\"ext_RequestedAccount\";s:28:\"AccountRequest_type_internal\";b:0;s:21:\"AccountRequest_manual\";b:0;s:20:\"AccountRequest_email\";s:29:\"webmaster@homeland-online.com\";s:7:\"avatars\";b:1;s:17:\"avatars_adminonly\";b:1;s:13:\"sendmail_qtde\";s:3:\"300\";}')";
        Capsule::connection()->unprepared($strQuery);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        Capsule::connection()->unprepared("TRUNCATE TABLE wt_webservices_operations_extra_data");
        Capsule::connection()->unprepared("TRUNCATE TABLE wt_webservices_operations");
        Capsule::connection()->unprepared("TRUNCATE TABLE wt_error_code");
    }
}
