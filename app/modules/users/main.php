<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 07/06/17
 * Time: 15:07
 */
global $lang, $cfg, $config;
if (strstr($_SERVER["PHP_SELF"], "/modules/"))  die ("You can't access this file directly...");

include_once("modules/users/lang/msg_".check_lang($cfg["core"]["lang"]).".php" );
include_once("modules/users/module_info.php" );
include_once("modules/users/functions.php" );

if (!isset($cfg["users"]["Save_Unencrypted_pwd"])) $cfg["users"]["Save_Unencrypted_pwd"] = false;

$config["extra_access"]["emularusuario"] = array("module" => "Site Admin", "descripcion" => $lang["CHANGE_USER_TO_TEST"]);

if (check_user_class("emularusuario") || isset($_SESSION["wt"]["originalUserToTest"])) {
	$config["admmenu"][$lang["CHANGE_USER_TO_TEST"]]["file"] = "adm_main.php?mde=users&wdw=emulate";
	$config["admmenu"][$lang["CHANGE_USER_TO_TEST"]]["class"] = "freeAccess";
	$config["admmenu"][$lang["CHANGE_USER_TO_TEST"]]["module"] = "Site Admin";
	$config["admmenu"][$lang["CHANGE_USER_TO_TEST"]]["name"] = $lang["CHANGE_USER_TO_TEST"];
	$config["admmenu"][$lang["CHANGE_USER_TO_TEST"]]["icon"] = "fa fa-neuter";
}

$config["admmenu"][$lang["ADM_USERACCESS_ADMIN_PROFILE"]]["file"] = "adm_main.php?mde=users&wdw=user_profile";
$config["admmenu"][$lang["ADM_USERACCESS_ADMIN_PROFILE"]]["class"] = "uses/user_profile";
$config["admmenu"][$lang["ADM_USERACCESS_ADMIN_PROFILE"]]["module"] = "Site Admin";
$config["admmenu"][$lang["ADM_USERACCESS_ADMIN_PROFILE"]]["name"] = $lang["ADM_USERACCESS_ADMIN_PROFILE"];
$config["admmenu"][$lang["ADM_USERACCESS_ADMIN_PROFILE"]]["icon"] = "fa fa-address-card";

$config["admmenu"][$lang["ADM_USERACCESS"]]["file"] = "adm_main.php?mde=users&wdw=user_access";
$config["admmenu"][$lang["ADM_USERACCESS"]]["class"] = "users/user_access";
$config["admmenu"][$lang["ADM_USERACCESS"]]["module"] = "Site Admin";
$config["admmenu"][$lang["ADM_USERACCESS"]]["name"] = $lang["ADM_USERACCESS"];
$config["admmenu"][$lang["ADM_USERACCESS"]]["icon"] = "fa fa-universal-access";

$config["admmenu"][$lang["ADM_USERS"]]["file"] = "adm_main.php?mde=users&wdw=users";
$config["admmenu"][$lang["ADM_USERS"]]["class"] = "users/users";
$config["admmenu"][$lang["ADM_USERS"]]["module"] = "Site Admin";
$config["admmenu"][$lang["ADM_USERS"]]["name"] = $lang["ADM_USERS"];
$config["admmenu"][$lang["ADM_USERS"]]["icon"] = "fa-users";

$config["admmenu"][$lang["USER_ROLES"]]["file"] = "adm_main.php?mde=users&wdw=user_roles";
$config["admmenu"][$lang["USER_ROLES"]]["class"] = "users/user_roles";
$config["admmenu"][$lang["USER_ROLES"]]["module"] = "Site Admin";
$config["admmenu"][$lang["USER_ROLES"]]["name"] = $lang["USER_ROLES"];
$config["admmenu"][$lang["USER_ROLES"]]["icon"] = "fa-child";

$config["admmenu"][$lang["USER_ORGANIZATION_CHART"]]["file"] = "adm_main.php?mde=users&wdw=organization_chart";
$config["admmenu"][$lang["USER_ORGANIZATION_CHART"]]["class"] = "users/organization_chart";
$config["admmenu"][$lang["USER_ORGANIZATION_CHART"]]["module"] = "Site Admin";
$config["admmenu"][$lang["USER_ORGANIZATION_CHART"]]["name"] = $lang["USER_ORGANIZATION_CHART"];
$config["admmenu"][$lang["USER_ORGANIZATION_CHART"]]["icon"] = "fa-bar-chart";


//--------------------ACCESO PARA COLOCAR PUESTO AL USUARIO----------------