<?php

/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 07/06/17
 * Time: 09:28
 */
include_once("core/global_config.php");
include_once("modules/users/objects/user_access/user_access_model.php");
include_once("modules/users/objects/user_access/user_access_view.php");
include_once 'modules/users/mod_users_controller.php';
class user_access_controller extends mod_users_controller implements window_controller
{
	private $boolPrintJson = false;
	private $boolUTF8 = true;
	private $strAction = "";

	public function __construct($arrParams){
		parent::__construct($arrParams);
	}

	/**
	 * @param bool $boolPrintJson
	 */
	public function setBoolPrintJson( $boolPrintJson)
	{
		$this->boolPrintJson = $boolPrintJson;
	}

	/**
	 * @param bool $boolUTF8
	 */
	public function setBoolUTF8( $boolUTF8)
	{
		$this->boolUTF8 = $boolUTF8;
	}

	public function setStrAction($strAction)
	{
		$this->strAction = $strAction;
	}

	public function main()
	{
		$Model = user_access_model::getInstance($this->arrParams);
		$View = user_access_view::getInstance($this->arrParams);
		$View->setStrAction($this->strAction);

		if(isset($this->arrParams["boolReport"])){
			$Model->report_users();
			die();
		}

		$intUid = $this->checkParam("intuid");
		$arrModules = array();  // Array que sirve para ver que modulos puedo modificar y a cuales tiene acceso el usuario.
		$arrSortedFields = array(); // Sirve para el sort del display
		if($intUid){
			if($this->checkParam("frmAccess_save",false,false)){
				$Model->deleteAccessUser($intUid);
				foreach($this->arrParams AS $key => $value){
					$arrName = explode("_",$key);
					if(($this->checkParam("0",$arrName) == "chk") && ($this->checkParam("1",$arrName) == "access")){
						$straccess = $this->checkParam("chk_access_{$arrName[2]}_{$arrName[3]}");
						$Model->asiggnmentAccess($intUid, $straccess);
					}
				}
			}

			reset($this->config["admmenu"]);

			// Accesos del menú
			foreach($this->config["admmenu"] AS $arrTMP["key"] => $arrTMP["value"] ){
				if (!isset($arrTMP["value"]["class"])) continue;
				if ($arrTMP["value"]["class"] == "admin") continue;
				if ($arrTMP["value"]["class"] == "freeAccess") continue;

				if ($_SESSION["wt"]["class"] == "admin" || $_SESSION["wt"]["class"] == "helpdesk" ||
					($_SESSION["wt"]["class"] != "admin" &&
						isset($_SESSION["wt"]["access"][$arrTMP["value"]["class"]]) &&
						$arrTMP["value"]["class"] != "accesos" &&
						($arrTMP["value"]["class"] != "Users_Admin" ||
							($arrTMP["value"]["class"] == "Users_Admin" && isset($_SESSION["wt"]["access"]["Users_Admin_Inherit"]))
						)
					)
				)
				{
					$arrModules[$arrTMP["value"]["class"]] = false;
					$strSortKey = strtolower($arrTMP["value"]["class"]);
					$strModule = (strstr($strSortKey, "homeland") !== false)?"_Homeland Only":((isset($arrTMP["value"]["module"]))?$arrTMP["value"]["module"]:"_Extras");
					if (!isset($arrSortedFields[$strModule][$strSortKey])) {
						$arrSortedFields[$strModule][$strSortKey]["className"] = $arrTMP["value"]["class"];
						$arrSortedFields[$strModule][$strSortKey]["Links"] = array();
					}
					$arrSortedFields[$strModule][$strSortKey]["Links"][] = $arrTMP["key"];
				}
			}

			//Accesos a los Homeland tools
			if (isset($arrHMLToolsArray)) {
				reset($arrHMLToolsArray);
				while ($arrTMP = each($arrHMLToolsArray)) {
					if ($arrTMP["value"]["access"] == "admin") continue;
					if ($arrTMP["value"]["access"] == "freeAccess") continue;

					if ($_SESSION["wt"]["class"] == "admin" || $_SESSION["wt"]["class"] == "helpdesk" ||
						($_SESSION["wt"]["class"] != "admin" && isset($_SESSION["wt"]["access"][$arrTMP["value"]["access"]]) && $arrTMP["value"]["access"] != "Users_Admin_Inherit"))
					{
						$arrModules[$arrTMP["value"]["access"]] = false;

						$strSortKey = strtolower($arrTMP["value"]["access"]);
						$strModule = (strstr($strSortKey, "homeland") !== false)?"_Homeland Only":((isset($arrTMP["value"]["module"]))?$arrTMP["value"]["module"]:"_Extras");
						if (!isset($arrSortedFields[$strModule][$strSortKey])) {
							$arrSortedFields[$strModule][$strSortKey]["className"] = $arrTMP["value"]["access"];
							$arrSortedFields[$strModule][$strSortKey]["Links"] = array();
						}
						$arrSortedFields[$strModule][$strSortKey]["Links"][] = $arrTMP["value"]["name"];
					}
				}
				reset($arrHMLToolsArray);
			}

			//accesos adicionales
			if (isset($config["extra_access"])) {
				reset($config["extra_access"]);
				while ($arrTMP = each($config["extra_access"])) {

					//drawDebug($arrTMP["key"]);

					if ($arrTMP["key"] == "admin") continue;
					if ($arrTMP["key"] == "freeAccess") continue;

					if ($_SESSION["wt"]["class"] == "admin" || $_SESSION["wt"]["class"] == "helpdesk" ||
						($_SESSION["wt"]["class"] != "admin" && isset($_SESSION["wt"]["access"][$arrTMP["key"]]) && $arrTMP["key"] != "Users_Admin_Inherit"))
					{

						$arrModules[$arrTMP["key"]] = false;
						$strSortKey = strtolower($arrTMP["key"]);
						if (strstr($strSortKey, "homeland") !== false) {
							$strModule = "_Homeland Only";
							$strLink = (is_bool($arrTMP["value"]))?$arrTMP["key"]:$arrTMP["value"]["descripcion"];
						}
						else {
							if (is_bool($arrTMP["value"])) {
								$strModule = "_Extras";
								$strLink = $arrTMP["key"];
							}
							else {
								$strModule = $arrTMP["value"]["module"];
								$strLink = $arrTMP["value"]["descripcion"];
							}
						}

						if (!isset($arrSortedFields[$strModule][$strSortKey])) {
							$arrSortedFields[$strModule][$strSortKey]["className"] = $arrTMP["key"];
							$arrSortedFields[$strModule][$strSortKey]["Links"] = array();
						}

						if (!empty($strLink)) $arrSortedFields[$strModule][$strSortKey]["Links"][] = $strLink;
					}
				}
			}
			ksort($arrSortedFields);
			ksort($arrModules);
			$Model->accesPerUser($intUid,$arrModules);

			$strName = $this->checkParam("name",false,"", true);
			$strLastName = $this->checkParam("lastname",false,"",true);
			$View->setIntUid($intUid);
			$View->setStrUserName($strName . " " . $strLastName);
			$View->setArrModules($arrModules);
			$View->setArrSortedFields($arrSortedFields);
		}
		$View->draw();
	}
}