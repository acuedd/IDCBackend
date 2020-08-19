<?php
/**
 * Created by PhpStorm.
 * User: nelsonrodriguez
 * Date: 13/04/2020
 * Time: 12:30
 */
if (strstr($_SERVER["PHP_SELF"], "/modules/"))  die ("You can't access this file directly...");
include_once("modules/sms/lang/msg_".check_lang($cfg["core"]["lang"]).".php" );
include_once("modules/sms/functions.php" );
include_once("modules/sms/module_info.php" );
$config["stylecss"]["reportes"] = true;
if (!isset($cfg["sms"]["Remitente"])) $cfg["sms"]["Remitente"] = "HML:";

$config["admmenu"][$lang["SMS_CONFIG"]]["file"] = global_function::createLink('sms', 'sms_configuration');
$config["admmenu"][$lang["SMS_CONFIG"]]["class"] = "sms/configurar";
$config["admmenu"][$lang["SMS_CONFIG"]]["module"] = $lang["SMS_TITLE"];
$config["admmenu"][$lang["SMS_CONFIG"]]["name"] = $lang["SMS_CONFIG"];
$config["admmenu"][$lang["SMS_CONFIG"]]["icon"] = "fa fa-sliders";

$config["admmenu"][$lang["SMS_REPORT"]]["file"] = global_function::createLink('sms', 'sms_report');
$config["admmenu"][$lang["SMS_REPORT"]]["class"] = "sms/reporte";
$config["admmenu"][$lang["SMS_REPORT"]]["module"] = $lang["SMS_TITLE"];
$config["admmenu"][$lang["SMS_REPORT"]]["name"] = $lang["SMS_REPORT"];
$config["admmenu"][$lang["SMS_REPORT"]]["icon"] = "fa fa-list-alt";

$config["admmenu"][$lang["SMS_TITLE"]] = array();
$config["admmenu"][$lang["SMS_TITLE"]]["icon"] = "fa fa-commenting-o";

$menuCont = 0;
$config["admmenu"][$lang["SMS_TITLE"]]['group'][$menuCont] = array();
$config["admmenu"][$lang["SMS_TITLE"]]['group'][$menuCont]['name'] = "Administración";
$config["admmenu"][$lang["SMS_TITLE"]]['group'][$menuCont]['elements'][] = &$config["admmenu"][$lang["SMS_CONFIG"]];
$menuCont++;
$config["admmenu"][$lang["SMS_TITLE"]]['group'][$menuCont] = array();
$config["admmenu"][$lang["SMS_TITLE"]]['group'][$menuCont]['name'] = "Reportes";
$config["admmenu"][$lang["SMS_TITLE"]]['group'][$menuCont]['elements'][] = &$config["admmenu"][$lang["SMS_REPORT"]];

