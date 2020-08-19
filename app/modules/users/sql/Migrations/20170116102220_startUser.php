<?php

use Phpmig\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

class StartUser extends Migration
{
	private $arrTables = [];
	private $schema;

	public function init()
	{
		$this->schema = $this->get('schema');
		$this->arrTables = [
			"wt_user_access" => "wt_user_access",
			"wt_user_access_perfiles" => "wt_user_access_perfiles",
			"wt_user_access_perfiles_d" => "wt_user_access_perfiles_d",
			"wt_user_access_temporal" => "wt_user_access_temporal",
			"wt_user_asig_profile" => "wt_user_asig_profile",
			"wt_user_rol" => "wt_user_rol",
			"wt_user_rol_asig" => "wt_user_rol_asig",
			"wt_user_settings" => "wt_user_settings",
			"wt_user_tag_asig" => "wt_user_tag_asig",
			"wt_user_tags" => "wt_user_tags",
			"wt_users" => "wt_users",
			"wt_users_branch_rol" => "wt_users_branch_rol",
			"wt_users_role_aux" => "wt_users_role_aux",
		];
	}

	/**
     * Do the migration
     */
    public function up()
    {
	    $this->schema->create($this->arrTables["wt_user_access"], function(Blueprint $table){
		    $table->integer("userid")->default(0);
		    $table->string("module",100)->default("");
		    $table->integer("temporal_id");
		    $table->primary(["userid","module"]);
	    });
	    $this->schema->create($this->arrTables["wt_user_access_perfiles"], function(Blueprint $table){
			$table->increments("id");
			$table->string("nombre",50);
			$table->string("descripcion",200)->default("");
			$table->dateTime("last_modified")->nullable();
			$table->unique("nombre","nombre");
	    });
	    $this->schema->create($this->arrTables["wt_user_access_perfiles_d"], function(Blueprint $table){
			$table->integer("perfil_id");
			$table->increments("id");
			$table->string("module",100)->default("");
			//$table->primary(["perfil_id","id"]);
	    });
	    $this->schema->create($this->arrTables["wt_user_access_temporal"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("uid_origen");
			$table->integer("uid_destino");
			$table->dateTime("fecha_inicio");
			$table->dateTime("fecha_fin");
	    });
	    $this->schema->create($this->arrTables["wt_user_asig_profile"], function(Blueprint $table){
		    $table->increments("id");
		    $table->integer("userid");
		    $table->integer("profile_id");
		    $table->enum("isCustom",["Y","N"])->default("N");
	    });
	    $this->schema->create($this->arrTables["wt_user_rol"], function(Blueprint $table){
		    $table->increments("id");
		    $table->string("rol",50);
		    $table->string("description",255)->default("");
		    $table->string("icon",25)->nullable()->default("");
	    });
	    $this->schema->create($this->arrTables["wt_user_rol_asig"], function(Blueprint $table){
		    $table->increments("id");
		    $table->integer("userid");
		    $table->integer("rol_id");
	    });
	    $this->schema->create($this->arrTables["wt_user_settings"], function(Blueprint $table){
		    $table->integer("userid");
		    $table->string("id",20);
		    $table->longText("config");
		    $table->primary(["userid","id"]);
	    });
	    $this->schema->create($this->arrTables["wt_user_tag_asig"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("userid");
			$table->integer("tag_id");

	    });
	    $this->schema->create($this->arrTables["wt_user_tags"], function(Blueprint $table){
		    $table->increments("id");
		    $table->string("tag",75)->nullable();
		    $table->string("color",10)->nullable();
	    });
	    $this->schema->create($this->arrTables["wt_users"], function(Blueprint $table){
		    $table->increments("uid");
		    $table->dateTime("lastmodified");
		    $table->smallInteger("campus_id");
		    $table->string("name",25);
		    $table->string("password",32)->default("");
		    $table->string("uepassword",32)->nullable();
		    $table->string("swusertype",20)->default("");
			$table->enum("class",["normal","helpdesk", "admin"])->default("normal");
		    $table->string("codigocolegio", 10);
		    $table->enum("isNuevoIngreso",["Y","N"])->default("N");
		    $table->string("gradoid",10)->nullable();
		    $table->string("gradoid_nextContrato",15)->nullable();
		    $table->string("next_gradoid",10);
		    $table->string("nombres",40)->nullable();
		    $table->string("apellidos",40)->nullable();
		    $table->string("apellido_casada",40)->nullable();
		    $table->string("realname",80)->nullable();
		    $table->string("nickname",80)->nullable();
		    $table->date("nacimiento");
		    $table->enum("sex",["Male","Female"])->nullable();
		    $table->string("country",30)->default("");
		    $table->string("city",100)->nullable();
		    $table->string("nacionalidad",30)->nullable();
		    $table->integer("nacion")->default(0);
			$table->unsignedInteger("cedula_dep")->default(0);
			$table->unsignedInteger("cedula_mun")->default(0);
			$table->string("cedula_orden",4)->nullable();
			$table->string("cedula_no", 12)->nullable();
			$table->string("dpi_no", 15)->nullable()->comment("En realidad es el ID unico de un pais");
			$table->enum("extranjero", ["Y","N"])->default("N");
			$table->string("pasaporte_no",100)->nullable();
			$table->string("religion",40)->nullable();
			$table->date("fecha_ingreso");
			$table->date("fecha_retiro");
			$table->text("dir_casa");
			$table->string("tel_casa",80)->nullable();
			$table->string("tel_cel",80)->nullable();
			$table->string("hmpg_per",150)->nullable();
			$table->string("email",50)->nullable();
			$table->string("email2",50)->nullable();
			$table->string("emailInstituto",50)->nullable();
			$table->string("email_confirm",50)->nullable();
			$table->enum("public_email",["Y","PromoOnly","N"])->default("Y");
			$table->text("dir_trab");
			$table->text("dir_correspondencia");
			$table->string("tel_trab",80)->nullable();
			$table->string("fax_trab",12)->nullable();
			$table->string("profesion",50)->nullable();
			$table->date("fecha_grad")->nullable();
			$table->string("universidad",200)->nullable();
			$table->string("empresa",50)->nullable();
			$table->text("actividad_servicio")->nullable()->comment("nos dice a que se dedica la empresa");
			$table->string("puesto",50)->nullable();
			$table->string("hmpg_trab",150)->nullable();
			$table->string("especialidad", 200)->nullable();
			$table->string("estudios_post_grado", 200 )->nullable();
			$table->binary("avatar")->nullable();
			$table->mediumText("comments")->nullable();
			$table->integer("topicsposted")->default('0');
			$table->dateTime("dateregistered")->default("0000-00-00 00:00:00");
			$table->dateTime("dateactivated")->default("0000-00-00 00:00:00");
			$table->integer("uid_regby");
			$table->integer("logins")->default('0');
		    $table->dateTime("lastvisit")->default("0000-00-00 00:00:00");
		    $table->string("last_browser", 100)->nullable();
		    $table->enum("isTemp",["Y","N"])->default("N");
		    $table->date("expirationdate");
		    $table->string("token",8);
		    $table->enum("mail_confirmed",["Y","N"])->default("Y");
		    $table->enum("active",["Y","N"])->default("N");
		    $table->enum("retirado",["Y","N"])->default("N");
		    $table->string("estado_alumno",150);
		    $table->enum("allow_multi_session",["Y","N"])->default("N");
		    $table->enum("change_password",["Y","N"])->default("N");
		    $table->integer("father");
		    $table->unique("name","name");
		    $table->index(["country","name" ],"listInd");
		    $table->index(["codigocolegio" ],"codigocolegio");
		    $table->index(["active" ],"active");
		    $table->index(["swusertype","retirado", "gradoid" ],"indice01");
	    });
	    $this->schema->create($this->arrTables["wt_users_branch_rol"], function(Blueprint $table){
			$table->increments("id");
			$table->string("name_branch",50)->nullable();
	    });
	    $this->schema->create($this->arrTables["wt_users_role_aux"], function(Blueprint $table){
			$table->increments("id");
			$table->integer("id_user");
			$table->integer("id_user_role_aux");
	    });

	    $strQuery = "CREATE VIEW view_tags_user AS
					Select UTA.userid, GROUP_CONCAT(UT.tag) AS tags from
					(
								wt_users AS U
								LEFT JOIN wt_user_tag_asig AS UTA ON UTA.userid = U.uid
							)
					LEFT JOIN wt_user_tags AS UT ON UT.id = UTA.tag_id
					GROUP BY UTA.userid";
	    Capsule::connection()->unprepared($strQuery);

	    $strQuery = "CREATE VIEW rpt_users_data AS
					SELECT u.uid, swt.descr AS 'TipoUsuario', u.nombres AS Nombres, u.apellidos AS Apellidos, u.Email, IF(u.active = 'Y', 'Si', 'No' ) AS Activo, IF(u.retirado = 'Y', 'Si', 'No' ) AS Retirado
					FROM wt_users AS u RIGHT JOIN wt_swusertypes AS swt ON u.swusertype = swt.name
					WHERE 1";
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

	    Capsule::connection()->unprepared("DROP VIEW view_tags_user");
	    Capsule::connection()->unprepared("DROP VIEW IF EXISTS rpt_users_data");
    }
}
