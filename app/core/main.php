<?php
include_once("core/main_functions.php");
include_once("core/login.php");
include_once("core/functions_core.php");
include_once("core/compress.php");
if (strstr($_SERVER["PHP_SELF"], "/core/"))
	die("You can't access this file directly...");
session_start();
header("Cache-control: private");
//VARIABLE QUE ME INDICA LOS LOGS DE TODOS LOS MODULOS EN UN SOLO REPORTE
$arrModulesLogReport = array();

// Startup.,. Get config...
$time_start = getmicrotime();

$headcode = "";
if(empty($config)) $config = array();
$config["boolIsMiniMain"] = false;
$config["modulesInfo"] = array();
$config["menu"] = array();
$config["admmenu"] = array();
$config["local_modules"] = array();

require_once("wt_config.php");
require_once 'bootstrap.php'; //load composer's packages
date_default_timezone_set($config["timezone"]);
if (($config["dbtype"] === "mysql") || ($config["dbtype"] === "mysqli") || ($config["dbtype"] === "pgsql")) {
	require_once("core/{$config["dbtype"]}.lib.php");
}
else {
	require_once("core/mysql.lib.php");
}

$globalConnection = db_connect($config["host"], $config["database"], $config["user"], $config["password"]) or die(db_error());
core_rotateInternalLogs();

// Load configuration
$cfg = array();
$ret = db_query("SELECT * FROM wt_config");
if(db_num_rows($ret)){
    while ($rowcfg = db_fetch_assoc($ret)) {
        $cfg[$rowcfg["id"]] = unserialize(stripslashes($rowcfg["config"]));
        unset($rowcfg);
    }
    db_free_result($ret);
}
else{
    print "No se pudo cargar congifuración";
    include("core/page_unavailable.php");
    die;
}
// Defaults
//*************** OJO, PONERLOS TAMBIEN EN miniMain.php *********************
$cfg["core"]["url"] = trim($cfg["core"]["url"]);
if (substr($cfg["core"]["url"], -1) !== "/")
	$cfg["core"]["url"] = "{$cfg["core"]["url"]}/";

$cfg["core"]["url_secure"] = trim($cfg["core"]["url_secure"]);
if (substr($cfg["core"]["url_secure"], -1) !== "/")
	$cfg["core"]["url_secure"] = "{$cfg["core"]["url_secure"]}/";

if (!isset($cfg["core"]["UserMayChangeName"]))
	$cfg["core"]["UserMayChangeName"] = false;
if (!isset($cfg["core"]["UserMayChangeEMail"]))
	$cfg["core"]["UserMayChangeEMail"] = true;
if (!isset($cfg["core"]["AccountRequest_type"]))
	$cfg["core"]["AccountRequest_type"] = "ext_RequestedAccount";
if (!isset($cfg["core"]["AccountRequest_type_internal"]))
	$cfg["core"]["AccountRequest_type_internal"] = false;
if (!isset($cfg["core"]["LogWithMail"]))
	$cfg["core"]["LogWithMail"] = false;
if (!isset($cfg["core"]["municipio"]))
	$cfg["core"]["municipio"] = "Guatemala";
if (!isset($cfg["core"]["images_path"]) || empty($cfg["core"]["images_path"])) {
	$cfg["core"]["images_path"] = "images";
}
else {
	$cfg["core"]["images_path"] = trim($cfg["core"]["images_path"]);
	if (substr($cfg["core"]["images_path"], -1) == "/")
		$cfg["core"]["images_path"] = substr($cfg["core"]["images_path"], 0, -1);
}
if (!isset($cfg["core"]["page_processed_LOG"]))
	$cfg["core"]["page_processed_LOG"] = true;
if (!isset($cfg["core"]["query_performance_log"]))
	$cfg["core"]["query_performance_log"] = false;
if (!isset($cfg["core"]["update_server"]))
	$cfg["core"]["update_server"] = false;
if (!isset($cfg["core"]["GZIP"]))
	$cfg["core"]["GZIP"] = true;
if (!isset($cfg["core"]["CACHE_CSS_AND_JAVA"]))
	$cfg["core"]["CACHE_CSS_AND_JAVA"] = true;
if (!isset($cfg["core"]["Show_email"]))
	$cfg["core"]["Show_email"] = false;
if (!isset($cfg["core"]["directorio_publico"]))
	$cfg["core"]["directorio_publico"] = false;
if (!isset($cfg["core"]["ocultar_directorio_interno"]))
	$cfg["core"]["ocultar_directorio_interno"] = false;
/* CContreras 20110212:  la variable site_profile nos servira para generalizar los profiles y que las funciones y otras cosas ya no dependan del THEME */
if (!isset($cfg["core"]["site_profile"]))
	$cfg["core"]["site_profile"] = "";
if (!isset($cfg["core"]["theme_profile"]) || !empty($cfg["core"]["site_profile"]))
	$cfg["core"]["theme_profile"] = "";
if (!isset($cfg["core"]["theme_interno"]))
	$cfg["core"]["theme_interno"] = "";
/* CContreras 20110212:  la variable site_profile nos servira para generalizar los profiles y que las funciones y otras cosas ya no dependan del THEME */
if (!isset($cfg["core"]["theme_interno_profile"]) || !empty($cfg["core"]["site_profile"]))
	$cfg["core"]["theme_interno_profile"] = "";
if (!isset($cfg["core"]["lostPWD"]))
	$cfg["core"]["lostPWD"] = false;
if (!isset($cfg["core"]["showVisitors"]))
	$cfg["core"]["showVisitors"] = true;
if (!isset($cfg["core"]["changePWD"]))
	$cfg["core"]["changePWD"] = true;
if (!isset($cfg["core"]["hide_homeland_users"]))
	$cfg["core"]["hide_homeland_users"] = true;
if (!isset($cfg["core"]["IPBlockOperation_public"]))
	$cfg["core"]["IPBlockOperation_public"] = "any";
if (!isset($cfg["core"]["IPBlockOperation_private"]))
	$cfg["core"]["IPBlockOperation_private"] = "any";
if ($cfg["core"]["IPBlockOperation_public"] == "reg")
	$cfg["core"]["IPBlockOperation_private"] = "reg";

if (!isset($cfg["core"]["HTTPS"]))
	$cfg["core"]["HTTPS"] = false;
if (!isset($cfg["core"]["HTTPS_logged"]))
	$cfg["core"]["HTTPS_logged"] = true;
if (!isset($cfg["core"]["sess_timeout"]))
	$cfg["core"]["sess_timeout"] = 20;
if (!isset($cfg["core"]["sess_timeout_notification"]))
	$cfg["core"]["sess_timeout_notification"] = true;
if ($cfg["core"]["sess_timeout"] > 23) {
	$cfg["core"]["sess_timeout"] = 23;
	$cfg["core"]["sess_timeout_notification"] = true;
}
if (!isset($cfg["core"]["planes"]))
	$cfg["core"]["planes"] = -1;

if (!isset($cfg["core"]["drawExternalFrame"])) $cfg["core"]["drawExternalFrame"] = true;

if (!isset($cfg["core"]["allow_webservice_devices"])) $cfg["core"]["allow_webservice_devices"] = false;
if (!isset($cfg["core"]["limit_webservice_devices"])) $cfg["core"]["limit_webservice_devices"] = false;
if (!isset($cfg["core"]["webservices_limitDevicesPerUser"])) $cfg["core"]["webservices_limitDevicesPerUser"] = false;
if (!isset($cfg["core"]["webservice_notificationRegisterDevice"])) $cfg["core"]["webservice_notificationRegisterDevice"] = false;
if (!isset($cfg["core"]["force_changed_password"])) $cfg["core"]["force_changed_password"] = false;

if (!isset($cfg["core"]["SSO_identityP"])) $cfg["core"]["SSO_identityP"] = false;

if (!isset($cfg["core"]["SSO_certPath_PK"]) ||
	(isset($cfg["core"]["SSO_certPath_PK"]) && (empty($cfg["core"]["SSO_certPath_PK"]) ||
												!file_exists($cfg["core"]["SSO_certPath_PK"])))) $cfg["core"]["SSO_identityP"] = false;
if (!isset($cfg["core"]["SSO_certPath_CERT"]) ||
	(isset($cfg["core"]["SSO_certPath_CERT"]) && (empty($cfg["core"]["SSO_certPath_CERT"]) ||
												  !file_exists($cfg["core"]["SSO_certPath_CERT"])))) $cfg["core"]["SSO_identityP"] = false;

if (!isset($cfg["core"]["SendEmailBirthday"])) $cfg["core"]["SendEmailBirthday"] = "no_enviar";
if (!isset($cfg["core"]["SendEmailBirthdayForPostal"])) $cfg["core"]["SendEmailBirthdayForPostal"] = false;
if (!isset($cfg["core"]["esconder_cedula"])) $cfg["core"]["esconder_cedula"] = true;
if (!isset($cfg["core"]["letra_default"])) $cfg["core"]["letra_default"] = false;
if (!isset($cfg["core"]["error_log"])) $cfg["core"]["error_log"] = "";
if (!isset($cfg["core"]["SEND_MAIL_SMTP"])) $cfg["core"]["SEND_MAIL_SMTP"] = "";
if (!isset($cfg["core"]["mailToSendEmail"])) $cfg["core"]["mailToSendEmail"] = "";
if (!isset($cfg["core"]["passToMailToSendEmail"])) $cfg["core"]["passToMailToSendEmail"] = "";
if (!isset($cfg["core"]["currency_label"])) $cfg["core"]["currency_label"] = "";

//*************** OJO, PONER LOS DEFAULTS TAMBIEN EN miniMain.php *********************
// Check for GZip contents....
ob_start("obOutputHandler");

// ANTES ESTABA AQUI EL HTTPS... LO PASE PARA ABAJO PARA YA TENER JALADO EL THEME Y PODER HACER LOCAL MODULES SECURE...
// 20090710 AG: El lang del CORE se jala de primero porque me sirve en las rutinas del log in y log out...
if (isset($_GET["setlang"])) {
	$_SESSION["wt"]["setlang"] = $_GET["setlang"];
}
if (isset($_SESSION["wt"]["setlang"])) {
	$cfg["core"]["lang"] = $_SESSION["wt"]["setlang"];
}
include_once("lang/msg_" . check_lang($cfg["core"]["lang"]) . ".php" );

// Inicio el log de tiempo de ejecucion
core_start_page_processed_LOG();

// ******************* Todo esto es lo de el Login, Logout, Auto Login, Session Expiration, etc. ********************
$boolOnLoginEvent = false;
if (isset($_SESSION["wt"])) {
	if ($_SESSION["wt"]["url"] != $cfg["core"]["url"]) {
		clear_login();
	}
}
else {
	clear_login();
}

if (!isset($_SESSION["wt"]["clickCount"]))
	$_SESSION["wt"]["clickCount"] = 0;
$_SESSION["wt"]["clickCount"]++;

if (check_session_timeout()) {
	// Si estaba loginieado
	//check_autologin(); // Limpia las variables de sesion
	if (!$_SESSION["wt"]["logged"]) {
		//-------------------------------------------------------------------Pendiente 294 --------------------------------------------------//
		if(isset($config["cloud"]["userUniversal"]) && $config["cloud"]["userUniversal"] && isset($_GET["cldmd"]) && $_GET["cldmd"] == "r"){
			clear_login();
		}
		else{
			// Notifica que la sesion expiró
            clear_login();
            delete_autologin();
			die($lang["SESS_EXPIRED"]);
		}
	}
}

/*if (!$_SESSION["wt"]["logged"]) {
	check_autologin();
}*/

if (isset($_GET["act"]) && $_GET["act"] == "logout") {
	clear_login();
	delete_autologin();
	//session_destroy();

	if (isset($config["cloud"]["userUniversal"]) && $config["cloud"]["userUniversal"]) {
		header("location:index.php?cldmd=r");
	}
}

if (isset($_SESSION["wt"])) {
	if ($_SESSION["wt"]["logged"] == true && $_SESSION["wt"]["uid"] == 0) {
		$strError = "Server: {$_SERVER["SERVER_NAME"]}\n Error: Logged without uid!\n Time: {$weekday} {$year}-{$month}-{$day} {$horas}:{$minutos}:{$segundos}";
		core_SendScriptInfoToWebmaster($strError);

		clear_login();
	}
}

// **************** FIN LOGIN **********************

/*
20121015 AG: Encontre que Chrome da un problema al usar SSL y sitios comprimidos.
		     Al parecer, Chrome es el UNICO browser que soporta bien el contenido comprimido
		     por SSL, de modo que es el unico browser que hace la negociacion correspondiente con el servidor para
		     solicitar contenido comprimido.  Al hacerlo, aparentemente, cierto contenido - no siempre, no todo - se
		     comprime dos veces, una con mi aplicacion de php y otra vez con el servidor, dando comportamientos inesperados
		     en la navegacion.
		     Este if deshabilita la compresion cuando es chrome en sitios SSL
		     Esto tambien esta en miniMain.php
*/
if ($_SESSION["wt"]["browser"]["detail"]["boolIsChrome"] && $cfg["core"]["HTTPS"] && isset($_SERVER["HTTPS"])) {
	$cfg["core"]["GZIP"] = false;
}

// Personal settings
if (isset($_SESSION["wt"]["logged"]) && $_SESSION["wt"]["logged"]) {
	$qTMP = db_query("SELECT * FROM wt_user_settings WHERE userid = {$_SESSION["wt"]["uid"]}");
	$intCountUserSetting = db_num_rows($qTMP);
	if($intCountUserSetting != 0){
		while ($rTMP = db_fetch_assoc($qTMP)) {
			$arrTMP1 = unserialize(stripslashes($rTMP["config"]));
            $arrTMP2 = array();
            foreach ($arrTMP1 as $arrTMP2["key"] => $arrTMP2["value"]) {
				$cfg[$rTMP["id"]][$arrTMP2["key"]] = $arrTMP2["value"];
			}
		}
	}
	db_free_result($qTMP);
	//En caso aqui se haga un override...
	include_once("lang/msg_" . check_lang($cfg["core"]["lang"]) . ".php" );
    if(check_module("idiomas")){
        idiomas_cargar_idioma($cfg["idiomas"]["idioma_id"], "" , $lang);
        $_SESSION["wt"]["idioma"] = $cfg["idiomas"]["idioma_id"];
    }
}

/*
  20090727: AG. El theme se jala antes que los modulos y sus langs.
  Esto se hace asi porque si el theme hace overrides de los langs, estos se tienen que mantener.
  Si primero defino los links en el menu y luego cambio los langs (como en zonas en el VV), los
  menus se van a definir con un string y luego voy a buscar accesos con otro string y eso causa errores.
 */
require_once("core/theme.php");

// HTTPS
$cfg["core"]["inSecureSide"] = isset($_SERVER["HTTPS"]);
if ($cfg["core"]["HTTPS"] && !$boolGlobalIsLocalDev) {
	if ($cfg["core"]["HTTPS_logged"]) {
		/*
		20121002 AG:
		Lo que quiero con esta opcion es que, si estoy loggineado, TODO sea https... si no, no importa.
		Para facilitar esto, se debe acompañar este cambio con hacer que siempre que se dibuje la funcion core_show_login_only, se salte al HTTPS
		y como en la parte publica no se cambia nada, el HTTPS se respetara y se hara login en con la sesion secure.

		Ademas, hice que al salir y quitar el frame, se salte a la parte sin https.
		*/
		if ($_SESSION["wt"]["logged"] && !$cfg["core"]["inSecureSide"]) {
			$strLink = basename($_SERVER["PHP_SELF"]);
			$strGetVars = (isset($_SERVER["QUERY_STRING"])) ? "?{$_SERVER["QUERY_STRING"]}" : "";

    		$strSite = "{$cfg["core"]["url_secure"]}{$strLink}{$strGetVars}";

    		header("Location: " . $strSite);
		}
	}
	else {
		$cfg["_https_links"]["secure"] = true;

		// Solo si el servidor soporta HTTPS
		$strHost = $_SERVER["HTTP_HOST"];
		$strLinkPath = $_SERVER["PHP_SELF"];
		$strLink = basename($strLinkPath);
		$strLinkF = str_replace(array("?", "&"), array("*", "|"), $strLink);
		$strGetVars = (isset($_SERVER["QUERY_STRING"])) ? $_SERVER["QUERY_STRING"] : "";
		$strURL = (($cfg["core"]["inSecureSide"]) ? "https://" : "http://") . $strHost . dirname($strLinkPath);

		if (substr($strURL, -1) != "/") $strURL .= "/";

		if ($strURL == $cfg["core"]["url_secure"]) {
			//Estoy en el secure
			$strTMP = substr($strLink, 0, -4);
			if ($strTMP == "local_module") {
				// Si es local module
				/*
				  Agregar
				  if (!is_array($cfg["_https_links"]["local_module"])) $cfg["_https_links"]["local_module"] = array();
				  $cfg["_https_links"]["local_module"]["custLog"] = true;

				  en el THEME para los local modules que lleven https
				 */
				if (!(isset($cfg["_https_links"][$strTMP][$_GET["lmID"]]) && $cfg["_https_links"][$strTMP][$_GET["lmID"]])) {
					// Tengo que saltar al unsecure
					if (!empty($strGetVars))
						$strGetVars = "?{$strGetVars}";
					$strSite = "{$cfg["core"]["url"]}{$strLink}{$strGetVars}";
					header("Location: " . $strSite);
					exit;
				}
			}
			else {
				if (!(isset($cfg["_https_links"][$strTMP]) && $cfg["_https_links"][$strTMP])) {
					// Tengo que saltar al unsecure
					if (!empty($strGetVars))
						$strGetVars = "?{$strGetVars}";
					$strSite = "{$cfg["core"]["url"]}{$strLink}{$strGetVars}";
					header("Location: " . $strSite);
					exit;
				}
			}
		}
		else {
			//Estoy en el unsecure
			$strTMP = substr($strLink, 0, -4);
			// Solo mantengo el post cuando me voy al secure...
			$strPost = (count($_POST)) ? serialize($_POST) : "";
			if ($strTMP == "local_module") {
				// Si es local module
				if (isset($cfg["_https_links"][$strTMP][$_GET["lmID"]]) && $cfg["_https_links"][$strTMP][$_GET["lmID"]]) {
					// Tengo que saltar al secure
					if (!empty($strGetVars))
						$strGetVars = str_replace(array("&", "="), array("~", "|"), $strGetVars);
					$strSite = "{$cfg["core"]["url_secure"]}secure.php?link={$strLinkF}&vars={$strGetVars}&post={$strPost}";
					header("Location: " . $strSite);
					exit;
				}
			}
			else {
				if (isset($cfg["_https_links"][$strTMP]) && $cfg["_https_links"][$strTMP]) {
					// Tengo que saltar al secure
					if (!empty($strGetVars))
						$strGetVars = str_replace(array("&", "="), array("~", "|"), $strGetVars);
					$strSite = "{$cfg["core"]["url_secure"]}secure.php?link={$strLinkF}&vars={$strGetVars}&post={$strPost}";
					header("Location: " . $strSite);
					exit;
				}
			}
		}
	}
}

include_once("core/main_links.php");

// 20100623 AG: Hay una copia de esto en ses_timer.php... si se cambia, actualizar tambien ses_timer.php
foreach ($cfg["modules"] as $mid => $enabled) {
	if ($enabled)
		include_once("modules/{$mid}/main.php");
}


/*
  - Primero hago Log In y verifico todo lo que haya que verificar.
  - Levanto los modulos y cada uno ejecuta sus rutinas segun si estoy Logged o no.
  - Luego reviso el evento On Login para los modulos. $boolOnLoginEvent se levanta en la funcion fill_login (en login.php)
 */

// Corro las funciones de los modulos, la unica que queda en otro lado es ForToday que está en el index.php
if ($boolOnLoginEvent) {
	// Limpio el log del menu
	db_query("DELETE FROM wt_hmlmenu_links_usage_detail WHERE fechaClick < DATE_SUB(NOW(), INTERVAL 6 MONTH)");

	db_query("DELETE wt_hmlmenu_links_usage
				  FROM wt_hmlmenu_links_usage
								LEFT JOIN wt_hmlmenu_links_usage_detail
								ON wt_hmlmenu_links_usage_detail.id = wt_hmlmenu_links_usage.id
				  WHERE wt_hmlmenu_links_usage_detail.id IS NULL");

	db_query("DELETE wt_hmlmenu_links_usage_detail
						  FROM wt_hmlmenu_links_usage_detail
										LEFT JOIN wt_hmlmenu_links_usage
										ON wt_hmlmenu_links_usage_detail.id = wt_hmlmenu_links_usage.id
						  WHERE wt_hmlmenu_links_usage.id IS NULL");

	reset($cfg['modules']);
	$arrModule = array();
    foreach ($cfg['modules'] as $arrModule["key"]=>$arrModule["value"]) {
		if ($arrModule["value"] && check_module($arrModule["key"], false)) {
			$strFunction = "{$arrModule["key"]}_OnLogIn_Function";
			if (function_exists($strFunction)) {
				$strFunction();
			}
		}
	}
	reset($cfg['modules']);
}

reset($cfg['modules']);

$arrModule = array();
foreach ($cfg['modules'] as $arrModule["key"] => $arrModule["value"]) {
	if ($arrModule["value"] && check_module($arrModule["key"], false)) {
		$strFunction = "{$arrModule["key"]}_StartupFunction";
		if (function_exists($strFunction)) {
			$strFunction();
		}
	}
	/*
	  20090203 AG Esto me va a servir para dejar todos los modules compatibles con el menu nuevo...
	  if ($boolGlobalIsLocalDev && !isset($config["modulesInfo"]["groups"][$arrModule["key"]])) {
	  drawDebug("EL MODULO {$arrModule["key"]} NO TIENE INFORMACION DE TAB", "Notificacion local");
	  }
	  // */
}
reset($cfg['modules']);


if(!empty($config["admmenu"])){
    reset($config["admmenu"]);
    $arrE = array();
    foreach ($config["admmenu"] as $arrE["key"]=>$arrE["value"]) {
        if(isset($arrE["value"]["modo"])){
            $config["accesosOperaciones"][$arrE["key"]] = $arrE["value"]["class"];
        }
        unset($arrE);
    }
    reset($config["admmenu"]);
}

//Parte de los extrafields Para los webservices
if($cfg["core"]["allow_webservice_devices"]){
    if(isset($config["webservices"])){
        if(is_array($config["webservices"]) && count($config["webservices"])>0){
            $arrModules = array();
            foreach ($config["webservices"] as $arrModules["key"]=>$arrModules["value"]) {
                if(is_array($arrModules["value"]) && count($arrModules["value"])) {
                    $arrExtra = array();
                    foreach ($arrModules["value"] as $arrExtra["key"]=>$arrExtra["value"]) {
                        $strDescripcion = (!empty($arrExtra["value"]["title"]))?$arrExtra["value"]["title"]:$arrExtra["value"]["page"];
                        $strModulo = (!empty($arrExtra["value"]["module"]))?$arrExtra["value"]["module"]:$arrModules["key"];
                        $config["extra_access"]["{$strModulo}/{$arrExtra["value"]["page"]}"] = array("module"=>"Webservices","descripcion"=>"Webservices/{$strDescripcion}");
                        //$config["extra_access"]["{$strModulo}/{$arrExtra["value"]["page"]}"] = array("module"=>$arrModules["key"],"descripcion"=>"Webservices/{$strDescripcion}");
                        unset($arrExtra);
                    }
                }
                unset($arrModules);
            }
            reset($config["webservices"]);
        }
    }
}


// Guardo estadisticas del link del menu...
if (isset($_GET["saveLinkStats"]) && isset($_GET["strModulo"]) && $_SESSION["wt"]["logged"]) {
	$strLinkTitle = db_escape(user_input_delmagic($_GET["saveLinkStats"]));
	$strModule = db_escape(user_input_delmagic($_GET["strModulo"]));

	$intID = sqlGetValueFromKey("SELECT id FROM wt_hmlmenu_links_usage WHERE userid = {$_SESSION["wt"]["uid"]} AND strModulo = '{$strModule}' AND menuTitle = '{$strLinkTitle}'");
	if (!$intID) {
		db_query("INSERT INTO wt_hmlmenu_links_usage (userid, strModulo, menuTitle) VALUES ({$_SESSION["wt"]["uid"]}, '{$strModule}', '{$strLinkTitle}')", false);
		$intID = db_insert_id();
	}
	db_query("INSERT INTO wt_hmlmenu_links_usage_detail (id, fechaClick) VALUES ({$intID}, NOW())");
}

$strProfile = $cfg["core"]["site_profile"];
if (!empty($strProfile) && is_dir("profiles/{$strProfile}")) {
	$strTextProfile = "profiles/{$strProfile}";
	include_once("{$strTextProfile}/main.php");
}
//pone los Iconos a las opciones principales
$config["admmenu"][$lang["SITE_CONFIG"]]["icon"] = "fa fa-cogs";

// User trying to login
if (isset($_POST["submit_login"]) && !empty($_POST["submit_login"])) {
	do_login();
}