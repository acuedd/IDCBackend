<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class StartUp extends Migration
{
	private $arrTables = [];
	private $schema;

	public function init()
	{
		$this->schema = $this->get('schema');
		$this->arrTables = [
			/*"migrations" => "migrations",*/
			"wt_app_control_names" => "wt_app_control_names",
			"wt_app_control_os" => "wt_app_control_os",
			"wt_app_control_versions" => "wt_app_control_versions",
			"wt_app_control_versions_bugs" => "wt_app_control_versions_bugs",
			"wt_app_control_versions_fix" => "wt_app_control_versions_fix",
			"wt_cache_misc" => "wt_cache_misc",
			"wt_catalogos_last_update" => "wt_catalogos_last_update",
			"wt_config" => "wt_config",
			"wt_departamentos" => "wt_departamentos",
			"wt_error_code" => "wt_error_code",
			"wt_hml_config_vars" => "wt_hml_config_vars",
			"wt_hmlmenu_links_usage" => "wt_hmlmenu_links_usage",
			"wt_hmlmenu_links_usage_detail" => "wt_hmlmenu_links_usage_detail",
			"wt_log" => "wt_log",
			"wt_log_detail" => "wt_log_detail",
			"wt_log_visitas" => "wt_log_visitas",
			"wt_municipios" => "wt_municipios",
			"wt_online" => "wt_online",
			"wt_page_processed_log" => "wt_page_processed_log",
			"wt_paises" => "wt_paises",
			"wt_queries_log" => "wt_queries_log",
			"wt_swusertypes" => "wt_swusertypes",
			"wt_swusertypes_users_st" => "wt_swusertypes_users_st",
			"wt_tokens" => "wt_tokens",
			"wt_updates" => "wt_updates",
			"wt_updates_client_details" => "wt_updates_client_details",
			"wt_updates_server_status" => "wt_updates_server_status",
			"wt_webservices_devices" => "wt_webservices_devices",
			"wt_webservices_devices_auth" => "wt_webservices_devices_auth",
			"wt_webservices_last_deactivate" => "wt_webservices_last_deactivate",
			"wt_webservices_mobile_responses" => "wt_webservices_mobile_responses",
			"wt_webservices_operations" => "wt_webservices_operations",
			"wt_webservices_operations_extra_data" => "wt_webservices_operations_extra_data",
			"wt_webservices_operations_extra_function" => "wt_webservices_operations_extra_function",
		];
	}

    /**
     * Do the migration
     */
    public function up()
    {
	    /*$this->schema->create($this->arrTables["migrations"], function(Blueprint $table){
		    $table->string("version",255);
	    });*/

	    $this->schema->create($this->arrTables["wt_app_control_names"], function(Blueprint $table){
		    $table->increments('id');
		    $table->string("name",30);
		    $table->string("name_unique",100);
	    });
	    $this->schema->create($this->arrTables["wt_app_control_os"], function(Blueprint $table){
		    $table->increments('id');
		    $table->string("os",30);
	    });
	    $this->schema->create($this->arrTables["wt_app_control_versions"], function(Blueprint $table){
		    $table->increments('id');
		    $table->integer('id_app');
		    $table->integer('id_os');
		    $table->string("version",30);
		    $table->enum("publicada",array("Y","N"))->default("N");
		    $table->enum("permitido",array("Y","N"))->default("N");
		    $table->date("fecha_registro");
		    $table->date("fecha_publicado");
		    $table->integer('regBy');
	    });
	    $this->schema->create($this->arrTables["wt_app_control_versions_bugs"], function(Blueprint $table){
		    $table->increments('id');
		    $table->string("description",255);
		    $table->integer("id_version");
	    });
	    $this->schema->create($this->arrTables["wt_app_control_versions_fix"], function(Blueprint $table){
		    $table->increments('id');
		    $table->string("description",255);
		    $table->integer("id_version");
	    });
	    $this->schema->create($this->arrTables["wt_cache_misc"], function(Blueprint $table){
		    $table->string("sessionid",40)->default("");
		    $table->string("cacheName",50)->default("");
		    $table->text("cacheString");
		    $table->dateTime("dateTimeRegistered")->nullable();
		    $table->smallInteger("duration_segs");
		    $table->primary(['sessionid', 'cacheName']);
	    });
	    $this->schema->create($this->arrTables["wt_catalogos_last_update"], function(Blueprint $table){
		    $table->string("table_name",100)->primary();
		    $table->date("fecha")->default("0000-00-00");
		    $table->time("hora")->default("00:00:00");
	    });
	    $this->schema->create($this->arrTables["wt_config"], function(Blueprint $table){
		    $table->string("id",20)->default("")->primary();
		    $table->longText("config");
	    });
		$this->schema->create($this->arrTables["wt_departamentos"], function(Blueprint $table){
			$table->increments("id");
			$table->unsignedInteger("codigo_clasificacion");
			$table->string("nombre",100)->default("");
			$table->string("orden_cedula",4)->nullable();
			$table->enum("active",["Y","N"])->default("Y");
			$table->index(['orden_cedula', 'active'],"indice01");
	    });
		$this->schema->create($this->arrTables["wt_error_code"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("code_family")->default("0");
			$table->string("description_family",25)->default("");
			$table->string("error_code",5)->default("");
			$table->string("lang_key",100)->default("");
			$table->index("code_family", "code_family");
	    });
		$this->schema->create($this->arrTables["wt_hml_config_vars"], function(Blueprint $table){
			$table->increments("var_id");
			$table->integer("pais_id");
			$table->string("module_name",60);
			$table->string("var",100)->default("");
			$table->string("value",100)->default("");
	    });

		$this->schema->create($this->arrTables["wt_hmlmenu_links_usage"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("userid")->default("0");
			$table->string("strModulo",150)->default("");
			$table->string("menuTitle",150)->default("");
			$table->unique(["userid","strModulo","menuTitle"],"userid");
			$table->index(["userid","menuTitle"],"userid_2");
	    });
		$this->schema->create($this->arrTables["wt_hmlmenu_links_usage_detail"], function(Blueprint $table){
			$table->unsignedInteger("id");
			$table->dateTime("fechaClick")->nullable();
	    });
		$this->schema->create($this->arrTables["wt_log"], function(Blueprint $table){
			$table->increments("ID");
			$table->string("uid",50)->default("");
			$table->string("swusertype",20)->default("");
			$table->dateTime("date")->default("0000-00-00 00:00:00");
			$table->string("descripcion",250);
			$table->string("modulo",100);
			$table->string("nombre",150);
			$table->string("short_desc",150);
			$table->string("access_rpt",150);
			$table->string("cod",30)->comment("key de una dato importante cuando es especifica la descripcion");
			$table->index(["modulo","nombre","access_rpt"],"modulo");
			$table->index(["date"],"indice01");
	    });
		$this->schema->create($this->arrTables["wt_log_detail"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("logID");
			$table->string("short_name",150);
			$table->string("tabla_nombre",100);
			$table->string("tabla_campo", 75);
			$table->text("campo_value");
			$table->string("tabla_key",150);
			$table->string("key_value",150);
			$table->index(["logID","tabla_nombre","tabla_campo"],"logID");
	    });
		$this->schema->create($this->arrTables["wt_log_visitas"], function(Blueprint $table){
			$table->increments("id");
			$table->string("sessid",40);
			$table->string("from_ip",15);
			$table->dateTime("fecha");
			$table->enum("logged",["Y","N"])->default("N");
			$table->integer("uid")->default(0);
			$table->dateTime("fecha_out")->default("0000-00-00 00:00:00");
			$table->index(["uid"],"uid");
			$table->index(["sessid","fecha"],"indice01");
			$table->index(["fecha"], "indice02");
	    });
		$this->schema->create($this->arrTables["wt_municipios"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("departamento")->default("0");
			$table->string("nombre",100)->default("");
			$table->enum("active",["Y","N"])->default("Y");
	    });
		$this->schema->create($this->arrTables["wt_online"], function(Blueprint $table){
			$table->string("id",40)->default("")->primary();
			$table->dateTime("hora")->nullable();
			$table->integer("uid")->default("0");
	    });
		$this->schema->create($this->arrTables["wt_page_processed_log"], function(Blueprint $table){
			$table->increments("id");
			$table->enum("isSecure",["Y","N"])->default("N");
			$table->unsignedInteger("uid");
			$table->unsignedInteger("uid_emulatedby");
			$table->string("self",250);
			$table->string("qry",250);
			$table->text("qry_post");
			$table->string("ip");
			$table->date("fecha");
			$table->time("hora");
			$table->decimal("processed",17,13);
			$table->index(["uid","fecha"], "uid");
			$table->index(["fecha","uid"], "fecha");
			$table->index(["self"], "indice03");
	    });
		$this->schema->create($this->arrTables["wt_paises"], function(Blueprint $table){
			$table->increments("id");
			$table->string("nombre",150)->default("");
			$table->enum("active",["Y","N"])->default("Y");
			$table->enum("isLocalDefault",["Y","N"])->default("N");
			$table->index(["nombre","active"], "indice01");
	    });
		$this->schema->create($this->arrTables["wt_queries_log"], function(Blueprint $table){
			$table->integer("uid");
			$table->string("sessid",40);
			$table->unsignedInteger("clickCounter");
			$table->dateTime("fecha");
			$table->text("strQuery");
			$table->text("strBackTrace");
			$table->decimal("processed",17,15);
	    });
		$this->schema->create($this->arrTables["wt_swusertypes"], function(Blueprint $table){
			$table->increments("id_usertype");
			$table->string("name",20)->default("_");
			$table->string("descr",50)->nullable();
			$table->string("father",20)->default("");
			$table->string("color",20)->default("");
			$table->integer("order");
			$table->integer("idbranch")->nullable();
			$table->string("role_auxiliar",50)->nullable();
			$table->unique("name", "name");
	    });
		$this->schema->create($this->arrTables["wt_swusertypes_users_st"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("userid");
			$table->enum("subtype",['padre','ext_exalumno'])->nullable();
			$table->enum("isMain",["Y","N"])->default("N");
			$table->unique(["userid","subtype"],"indice01");
	    });
		$this->schema->create($this->arrTables["wt_tokens"], function(Blueprint $table){
			$table->string("sessionid",40)->default("");
			$table->string("tokenName",50)->default("");
			$table->string("tokenString",32)->default("");
			$table->primary(["sessionid","tokenName"]);
	    });
		$this->schema->create($this->arrTables["wt_updates"], function(Blueprint $table){
			$table->increments("id");
			$table->dateTime("fecha");
			$table->string("filename",255)->default("");
			$table->string("modulo",255)->default("");
			$table->enum("hasMain",["Y","N"])->default("N");
			$table->enum("hasMySQL",["Y","N"])->default("N");
			$table->enum("hasCode",["Y","N"])->default("N");
			$table->enum("update_type",["C","S"])->default("C");
			$table->integer("userid");
			$table->enum("rdy_to_delete",["Y","N"])->default("N");
			$table->index(["fecha","update_type"],"fecha");
	    });
		$this->schema->create($this->arrTables["wt_updates_client_details"], function(Blueprint $table){
			$table->integer("updateid");
			$table->string("filename",255)->default("");
			$table->enum("status",["pend","ok","fail"])->default("pend");
			$table->primary(["updateid","filename"]);
	    });
		$this->schema->create($this->arrTables["wt_updates_server_status"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("updateid");
			$table->string("website",255);
			$table->string("remote_ip",255);
			$table->dateTime("fecha");
			$table->enum("status",["pend","ok","fail"])->default("pend");
			$table->index("status","status");
	    });
		$this->schema->create($this->arrTables["wt_webservices_devices"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("id_deviceauth");
			$table->integer("userid");
			$table->string("device_udid", 60)->comment("UDID del device");
			$table->enum("activo",["Y","N"])->default("Y");
			$table->enum("eliminado",["Y","N"])->default("N");
			$table->dateTime("fecha_alta")->nullable()->comment("Fecha en que se da de alta al dispositivo");
			$table->dateTime("fecha_baja")->nullable()->comment("Fecha en que se da de baja al dispositivo");
			$table->enum("confirmado",["Y","N"])->default("N")->comment("Para indicar que el dispositivo ya se confirmo en el sitio principal");
			$table->dateTime("fecha_confirmacion")->nullable()->comment("Fecha en que se confirmo el dispositivo");
			$table->integer("userid_confirma")->comment("Usuario que lo confirmo");
			$table->string("nombre_p",50)->comment("Nombre personalizado por el usuario");
			$table->dateTime("last_use")->comment("Ultima vez que este dispositivo se conectó al sitio");
			$table->integer("uses")->comment("Conteo de consultas por medio de este dispositivo");
			$table->string("tipo",100)->nullable()->comment("Tipo de dispositivo");
			$table->string("marca")->nullable()->comment("Marca de dispositivo");
			$table->string("modelo",100)->nullable()->comment("Modelo de dispositivo");
			$table->string("telefono",50)->nullable();
			$table->string("osversion",20);
			$table->string("appversion",20);
			$table->string("code_device",50);
			$table->string("apiversion",20);
			$table->string("OS",30);
			$table->string("appname",100);
			$table->enum("modified_config",["Y","N"])->default("N");
			$table->string("token_gcm",255)->default("");
			$table->index(["activo","userid","device_udid"]);
			$table->index(["activo","device_udid","userid"]);
			$table->index(["confirmado","fecha_alta"]);
			$table->index(["tipo", "marca"]);
			$table->index(["marca","modelo"]);
			$table->index(["device_udid","userid"]);
			$table->index(["userid","device_udid"]);
			$table->index(["id_deviceauth"]);

	    });
		$this->schema->create($this->arrTables["wt_webservices_devices_auth"], function(Blueprint $table){
			$table->increments("id_deviceauth");
			$table->integer("id_credencial")->comment("id de la credencial para tarjeta de credito, table wt_tarjeta_credito_visanet_credencial");
			$table->string("no_telefono",50);
			$table->integer("userid");
			$table->dateTime("fecha_alta")->nullable();
			$table->dateTime("fecha_baja")->nullable();
			$table->enum("activo",["Y","N"])->default("Y");
			$table->enum("removed",["Y","N"])->default("N");
			$table->string("alias",100);
			$table->string("modelo",100);
			$table->string("marca",100);
			$table->string("tipo",100);
			$table->integer("idSwiper")->default(0);
			$table->string("no_facturacion",50);
			$table->integer("modifying_user")->default(0);
			$table->date("modification_date")->nullable();
			$table->time("modification_time")->nullable();
			$table->index("id_deviceauth","credencial");
	    });
		$this->schema->create($this->arrTables["wt_webservices_last_deactivate"], function(Blueprint $table){
			$table->dateTime("lastRun")->nullable()->comment("Ultima fecha en que corrio la funcion para desactivar dispositivos no activos");
	    });
		$this->schema->create($this->arrTables["wt_webservices_mobile_responses"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("userid");
			$table->integer("device_id");
			$table->string("device_aim",10)->default("");
			$table->integer("process_log_id");
			$table->enum("status",["en_proceso","terminada"])->nullable();
			$table->date("fecha")->nullable();
			$table->time("hora")->nullable();
			$table->string("formato")->nullable();
			$table->mediumText("respuesta");
			$table->index(["device_id","device_aim"]);
			$table->index(["fecha"]);
	    });
		$this->schema->create($this->arrTables["wt_webservices_operations"], function(Blueprint $table){
			$table->string("op_uuid",36)->comment("UUID de la operacion")->primary();
			$table->string("modulo",20)->default('')->comment("Modulo al que pertenece");
			$table->string("descripcion",200)->default('')->comment("Descripcion de operacion");
			$table->string("include_path",250)->default('')->comment("Path a la libreria desde root");
			$table->string("className",250)->default('')->comment("Nombre de la clase");
			$table->enum("publica",["Y","N"])->default("N")->comment("Para definir si la operacion es publica y no le importa el token de seguridad");
			$table->string("acceso",100)->default('admin')->comment("Acceso que debe tener el usuario para utilizar la operacion");
			$table->enum("activo",["Y","N"])->default("Y")->comment("Para desactivar el servicio");
			$table->enum("isNewMod",["Y","N"])->default("Y")->comment("Si corre con el webservice master");
			$table->string("path_mainClass",250)->default('')->comment("path de la clase a la que hara referencia");
			$table->string("class_mainClass",250)->default('')->comment("nombre de la clase que hara referencia");
			$table->string("allowed_format",100)->comment("'w','wm','am' -> formatos permitidos para el webservice");
			$table->string("format_response",100)->comment("'json','html','xmlno' -> formatos permitidos de repuestas para el webservice");
			$table->string("method_response",250)->comment("metodo que llamara para dar la respuesta");
			$table->enum("check_config_device",["Y","N"])->default("N");
			$table->index(["modulo"]);
			$table->index(["activo","modulo"]);
			$table->index(["activo","op_uuid"]);
			$table->index(["activo","acceso"]);
	    });
		$this->schema->create($this->arrTables["wt_webservices_operations_extra_data"], function(Blueprint $table){
			$table->increments("id");
			$table->string("op",36);
			$table->enum("required",["Y","N"])->default("Y");
			$table->string("parameter_description",250)->default("");
			$table->string("method_validation",250)->default("");
			$table->string("key_parameter",30)->default("");
			$table->string("error_response",250)->default("");
			$table->string("transform_key",250)->default("");
	    });
	    $this->schema->create($this->arrTables["wt_webservices_operations_extra_function"], function(Blueprint $table){
		    $table->increments("id");
		    $table->string("op",36)->default("");
		    $table->string("str_function",60)->default("");
		    $table->enum("webservices_baseClass",["Y","N"])->default("N");
	    });

	    $strQuery = "CREATE TRIGGER gen_error_code BEFORE INSERT ON `{$this->arrTables["wt_error_code"]}` FOR EACH ROW
		            BEGIN
                        SET @cod_family = LPAD(NEW.code_family, 2, '0');
						SET @error_id = LPAD(NEW.id, 4,'0');
						SET NEW.error_code = CONCAT(@cod_family, @error_id);                       		               
		            END";
	    Capsule::connection()->unprepared($strQuery);

	    $strQuery = "CREATE VIEW view_page_processed_log
					AS
					SELECT id, isSecure, uid, uid_emulatedby, self, qry AS qry_get, qry_post, ip, fecha, hora, processed
					FROM wt_page_processed_log
					WHERE self NOT IN('dynamiccss.php','dynamicjava.php','jqueryloader.php','ses_timer.php','ttfrender.php') AND
					      self NOT LIKE '%.jpg' AND
					      (self NOT LIKE 'adm_noticias.php' AND qry NOT LIKE '%imgid%')";
	    Capsule::connection()->unprepared($strQuery);

	    $strQuery = "DROP FUNCTION IF EXISTS `udf_FirstNumberPos`";
	    Capsule::connection()->unprepared($strQuery);

	    $strQuery = "CREATE FUNCTION `udf_FirstNumberPos` (`instring` varchar(4000)) 
						RETURNS int
						LANGUAGE SQL
						DETERMINISTIC
						NO SQL
						SQL SECURITY INVOKER
						BEGIN
						    DECLARE position int;
						    DECLARE tmp_position int;
						    SET position = 5000;
						    SET tmp_position = LOCATE('0', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF; 
						    SET tmp_position = LOCATE('1', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF;
						    SET tmp_position = LOCATE('2', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF;
						    SET tmp_position = LOCATE('3', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF;
						    SET tmp_position = LOCATE('4', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF;
						    SET tmp_position = LOCATE('5', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF;
						    SET tmp_position = LOCATE('6', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF;
						    SET tmp_position = LOCATE('7', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF;
						    SET tmp_position = LOCATE('8', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF;
						    SET tmp_position = LOCATE('9', instring); IF (tmp_position > 0 AND tmp_position < position) THEN SET position = tmp_position; END IF;
						
						    IF (position = 5000) THEN RETURN 0; END IF;
						    RETURN position;
						END";
	    Capsule::connection()->unprepared($strQuery);

	    $strQuery = "DROP FUNCTION IF EXISTS `udf_NaturalSortFormat`";
	    Capsule::connection()->unprepared($strQuery);
	    $strQuery = "CREATE FUNCTION `udf_NaturalSortFormat` (`instring` varchar(4000), `numberLength` int, `sameOrderChars` char(50)) 
						RETURNS varchar(4000)
						LANGUAGE SQL
						DETERMINISTIC
						NO SQL
						SQL SECURITY INVOKER
						BEGIN
						    DECLARE sortString varchar(4000);
						    DECLARE numStartIndex int;
						    DECLARE numEndIndex int;
						    DECLARE padLength int;
						    DECLARE totalPadLength int;
						    DECLARE i int;
						    DECLARE sameOrderCharsLen int;
						
						    SET totalPadLength = 0;
						    SET instring = TRIM(instring);
						    SET sortString = instring;
						    SET numStartIndex = udf_FirstNumberPos(instring);
						    SET numEndIndex = 0;
						    SET i = 1;
						    SET sameOrderCharsLen = LENGTH(sameOrderChars);
						
						    WHILE (i <= sameOrderCharsLen) DO
						        SET sortString = REPLACE(sortString, SUBSTRING(sameOrderChars, i, 1), ' ');
						        SET i = i + 1;
						    END WHILE;
						
						    WHILE (numStartIndex <> 0) DO
						        SET numStartIndex = numStartIndex + numEndIndex;
						        SET numEndIndex = numStartIndex;
						
						        WHILE (udf_FirstNumberPos(SUBSTRING(instring, numEndIndex, 1)) = 1) DO
						            SET numEndIndex = numEndIndex + 1;
						        END WHILE;
						
						        SET numEndIndex = numEndIndex - 1;
						
						        SET padLength = numberLength - (numEndIndex + 1 - numStartIndex);
						
						        IF padLength < 0 THEN
						            SET padLength = 0;
						        END IF;
						
						        SET sortString = INSERT(sortString, numStartIndex + totalPadLength, 0, REPEAT('0', padLength));
						
						        SET totalPadLength = totalPadLength + padLength;
						        SET numStartIndex = udf_FirstNumberPos(RIGHT(instring, LENGTH(instring) - numEndIndex));
						    END WHILE;
						
						    RETURN sortString;
						END";
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

	    Capsule::connection()->unprepared("DROP VIEW IF EXISTS view_page_processed_log");
	    Capsule::connection()->unprepared("DROP FUNCTION IF EXISTS udf_NaturalSortFormat");
	    Capsule::connection()->unprepared("DROP FUNCTION IF EXISTS udf_FirstNumberPos");
    }
}
