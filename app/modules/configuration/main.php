<?php
if (strstr($_SERVER["PHP_SELF"], "/modules/"))  die ("You can't access this file directly...");
global $lang, $cfg;
include_once("modules/configuration/lang/msg_".check_lang($cfg["core"]["lang"]).".php" );
include_once("modules/configuration/module_info.php");
include_once("modules/configuration/functions.php");


$config["admmenu"][$lang["CONFIGURATION_CURRENCY"]]["file"] = global_function::createLink("configuration", "currency");
$config["admmenu"][$lang["CONFIGURATION_CURRENCY"]]["class"] = "config/currency";
$config["admmenu"][$lang["CONFIGURATION_CURRENCY"]]["module"] = "Site Admin";
$config["admmenu"][$lang["CONFIGURATION_CURRENCY"]]["name"] = $lang["CONFIGURATION_CURRENCY"];
$config["admmenu"][$lang["CONFIGURATION_CURRENCY"]]["icon"] = "fa fa-money";

$config["admmenu"][$lang["NOTIFICATION_ADMIN"]]["file"] = global_function::createLink("configuration", "administration");
$config["admmenu"][$lang["NOTIFICATION_ADMIN"]]["class"] = "config/notifications/administration";
$config["admmenu"][$lang["NOTIFICATION_ADMIN"]]["module"] = "Site Admin";
$config["admmenu"][$lang["NOTIFICATION_ADMIN"]]["name"] = $lang["NOTIFICATION_ADMIN"];
$config["admmenu"][$lang["NOTIFICATION_ADMIN"]]["icon"] = "fa fa-check-square";

$config["admmenu"][$lang["CONFIGURATION_COLORS"]]["file"] = global_function::createLink("configuration", "profile");
$config["admmenu"][$lang["CONFIGURATION_COLORS"]]["class"] = "config/profile";
$config["admmenu"][$lang["CONFIGURATION_COLORS"]]["module"] = "Site Admin";
$config["admmenu"][$lang["CONFIGURATION_COLORS"]]["name"] = $lang["CONFIGURATION_COLORS"];
$config["admmenu"][$lang["CONFIGURATION_COLORS"]]["icon"] = "fa fa-picture-o";

$config["admmenu"][$lang["CONFIGURATION_PUSH_NOTIFICATION"]]["file"] = global_function::createLink("configuration", "push_notifications");
$config["admmenu"][$lang["CONFIGURATION_PUSH_NOTIFICATION"]]["class"] = "config/push_notifications";
$config["admmenu"][$lang["CONFIGURATION_PUSH_NOTIFICATION"]]["module"] = "Site Admin";
$config["admmenu"][$lang["CONFIGURATION_PUSH_NOTIFICATION"]]["name"] = $lang["CONFIGURATION_PUSH_NOTIFICATION"];
$config["admmenu"][$lang["CONFIGURATION_PUSH_NOTIFICATION"]]["icon"] = "fa fa-commenting-o";