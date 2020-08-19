<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 07/06/17
 * Time: 09:28
 */
require_once 'core/login.php';
require_once 'modules/users/mod_users_controller.php';

class user_access_model extends mod_users_model implements window_model
{
	private static $_instance;

	public function __construct($arrParams){
		parent::__construct($arrParams);
	}

	public static function getInstance($arrParams)
	{
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($arrParams);
		}
		return self::$_instance;
	}

	public function report_users(){
		if (!check_user_class("emularusuario") && !isset($_SESSION["wt"]["originalUserToTest"]))
			die($this->lang["ACCESS_DENIED"]);

		include_once("libs/hml_report/hml_report.php");
		header("Content-Type: text/html; charset=iso-8859-1");

		$strQuery = "SELECT  U.uid, U.nombres AS Nombres, U.apellidos AS Apellidos, S.descr AS Tipo
                 FROM    wt_users AS U, wt_swusertypes AS S
                 WHERE   S.name = U.swusertype AND
                         U.active = 'Y' AND
                         U.retirado = 'N' AND
                         U.class <> 'admin' AND
                         U.class <> 'helpdesk' AND
                         U.swusertype <> 'ext_homeland' ¿f? ¿o?";

		$arrEncabezado = array();
		$arrParametros = array();

		$arrEncabezado["filter"]["Nombres"] = "U.nombres";
		$arrEncabezado["filter"]["Apellidos"] = "U.apellidos";
		$arrEncabezado["filter"]["Tipo"] = "S.descr";
		$arrEncabezado["sort"]["Nombres"] = "U.nombres";
		$arrEncabezado["sort"]["Apellidos"] = "U.apellidos";
		$arrEncabezado["sort"]["Tipo"] = "S.descr";
		$arrEncabezado["hidden"]["uid"] = "uid";

		$arrEncabezado["onclick"]["all_row"]["function"] = "setUser";
		$arrEncabezado["onclick"]["all_row"]["params"][] = "uid";
		$arrEncabezado["onclick"]["all_row"]["params"][] = "Nombres";
		$arrEncabezado["onclick"]["all_row"]["params"][] = "Apellidos";

		$arrEncabezado["align"]["Nombres"] = "right";
		$arrEncabezado["align"]["Apellidos"] = "right";
		$arrEncabezado["align"]["Tipo"] = "right";

		$arrParametros["tipo"] = "paginador";
		$arrParametros["btnExportar"] = false;
		$arrParametros["porPagina"] = "5";

		$objPrintRPTest = new hml_report($strQuery, $arrEncabezado, $arrParametros,false,false,true);
		print $objPrintRPTest->dibujarHML_RPT();
	}

	public function accesPerUser($intUID, &$arrModules){

		$strTMP = "SELECT module FROM wt_user_access WHERE userid = {$intUID}";
		$qTMP = db_query($strTMP);
		while ($rTMP = db_fetch_array($qTMP)){
			$arrModules[$rTMP["module"]] = true;
		}
		db_free_result($qTMP);
	}
}