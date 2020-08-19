<?php
// Información para agrupar el módulo
// Core
$config["modulesInfo"]["titles"]["core"] = "Site Admin";
$config["modulesInfo"]["groups"]["core"][] = "WS";

// Universo
$config["modulesInfo"]["universe"]["uWEB"][] = "core";//uWEB, uINST, uFIN, uNC

// Links at main box
$config["menu"][100] = array("title" => $lang["HOME_TITLE"], "file" => "index.php", "type" => "L"); //, "moduleID"=>"core" forzo que sea huerfano

$strType = "L";
if ($cfg["core"]["ocultar_directorio_interno"]) {
	$strType = (check_user_class("showDirInterno")) ? "L" : "NEVER";
}
elseif ($cfg["core"]["directorio_publico"]) {
	$strType = "A";
}

if (isset($_SESSION["wt"]) && $_SESSION["wt"]["logged"] &&
		isset($cfg["core"]["AccountRequest"]) && $cfg["core"]["AccountRequest"] &&
		$_SESSION["wt"]["swusertype"] == $cfg["core"]["AccountRequest_type"] &&
		!$cfg["core"]["AccountRequest_type_internal"]) {
	$config["menu"][10000]["type"] = "NEVER";
}
// Links at admmenu
$config["admmenu"][$lang["ADM_INFO"]] = array("file" => "adm_main.php?mde=core&wdw=info", "class" => "admin", "module" => "Site Admin","icon"=>"fa fa-info-circle");
$config["admmenu"][$lang["ADM_WEBADMIN"]] = array("file" => "admin.php", "class" => "admin", "module" => "Site Admin", "icon"=>"fa fa-wrench");

$config["extra_access"]['Users_Admin_Inherit'] = array("module" => "Core", "descripcion" => $lang["Users_Admin_Inherit_DESCRIPTION"]);
$config["extra_access"]["Disconnect_Accounts"] = array("module" => "Core", "descripcion" => $lang["Disconnect_Accounts_DESCRIPTION"]);
$config["extra_access"]["accesos/profiles/asign"] = array("module" => "Site Admin", "descripcion" => $lang["accesos/profiles/asign_DESCRIPTION"]);
if ($cfg["core"]["ocultar_directorio_interno"]) {
	$config["extra_access"]["showDirInterno"] = array("module" => "Core", "descripcion" => $lang["LIST_USERS_TITLE"]);
}

$config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["file"] = "adm_webservices.php";
$config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["class"] = "core/webservices";
$config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["module"] = "Site Admin";
$config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["name"] = $lang["ADMIN_WEBSERVICE"];
$config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["icon"] = "fa fa-bolt";

$config["admmenu"][$lang["APP_CONTROL"]]["file"] = "adm_main.php?mde=core&wdw=app_control";
$config["admmenu"][$lang["APP_CONTROL"]]["class"] = "core/app_control";
$config["admmenu"][$lang["APP_CONTROL"]]["module"] = "Site Admin";
$config["admmenu"][$lang["APP_CONTROL"]]["name"] = $lang["APP_CONTROL"];
$config["admmenu"][$lang["APP_CONTROL"]]["icon"] = "fa fa-code-fork";

if(!empty($cfg["core"]["error_log"])){
    $config["admmenu"][$lang["ERROR_LOG"]]["file"] = "ver_error_log.php";
    $config["admmenu"][$lang["ERROR_LOG"]]["class"] = "core/error_log";
    $config["admmenu"][$lang["ERROR_LOG"]]["module"] = "Site Admin";
    $config["admmenu"][$lang["ERROR_LOG"]]["name"] = $lang["ERROR_LOG"];
}