<?php
$strCurrSession = session_id();
if (empty($strCurrSession)) {  // Si no he revisado mi sesion, entro... esto es para evitar que el miniMain corra mas de una vez.
    date_default_timezone_set("America/Guatemala");

    include_once("core/main_functions.php");
    include_once("core/login.php");
    include_once("core/compress.php");

    if (strstr($_SERVER["PHP_SELF"], "/core/"))
        die("You can't access this file directly...");

    session_start();
    header("Cache-control: private");

    // Startup.,. Get config...
    $time_start = getmicrotime();

    $headcode = "";
    $config = array();
    $config["boolIsMiniMain"] = true;
    $config["modulesInfo"] = array();
    $config["menu"] = array();
    $config["admmenu"] = array();
    $config["local_modules"] = array();

    require_once("wt_config.php");
    if (($config["dbtype"] === "mysql") || ($config["dbtype"] === "pgsql")) {
        require_once("core/{$config["dbtype"]}.lib.php");
    }
    else {
        require_once("core/mysql.lib.php");
    }

    $globalConnection = db_connect($config["host"], $config["database"], $config["user"], $config["password"]) or die(db_error());

    // Load configuration
    $cfg = array();
    $ret = db_query("SELECT * FROM wt_config");
    if (!$ret) {
        print "<div class=\"error\">An error has ocurred while loading configuration.</div>";
    }
    else {
        while ($rowcfg = db_fetch_array($ret)) {
            $cfg[$rowcfg["id"]] = unserialize(stripslashes($rowcfg["config"]));
        }
    }
    db_free_result($ret);

    // Defaults
    //*************** OJO, PONERLOS TAMBIEN EN main.php *********************
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

	if (!isset($cfg["core"]["allow_webservice_devices"])) $cfg["core"]["allow_webservice_devices"] = false;
	if (!isset($cfg["core"]["limit_webservice_devices"])) $cfg["core"]["limit_webservice_devices"] = false;
	if (!isset($cfg["core"]["webservices_limitDevicesPerUser"])) $cfg["core"]["webservices_limitDevicesPerUser"] = false;
	if (!isset($cfg["core"]["webservice_notificationRegisterDevice"])) $cfg["core"]["webservice_notificationRegisterDevice"] = false;


	if (!isset($cfg["core"]["SSO_identityP"])) $cfg["core"]["SSO_identityP"] = false;

	if (!isset($cfg["core"]["SSO_certPath_PK"]) ||
		(isset($cfg["core"]["SSO_certPath_PK"]) && (empty($cfg["core"]["SSO_certPath_PK"]) ||
													!file_exists($cfg["core"]["SSO_certPath_PK"])))) $cfg["core"]["SSO_identityP"] = false;
	if (!isset($cfg["core"]["SSO_certPath_CERT"]) ||
		(isset($cfg["core"]["SSO_certPath_CERT"]) && (empty($cfg["core"]["SSO_certPath_CERT"]) ||
													  !file_exists($cfg["core"]["SSO_certPath_CERT"])))) $cfg["core"]["SSO_identityP"] = false;

    if (!isset($cfg["core"]["SendEmailBirthday"])) $cfg["core"]["SendEmailBirthday"] = "no_enviar";
    if (!isset($cfg["core"]["currency_label"])) $cfg["core"]["currency_label"] = "";

    //*************** OJO, PONER LOS DEFAULTS TAMBIEN EN main.php *********************
    // Check for GZip contents....
    ob_start("obOutputHandler");

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

    if (check_session_timeout(false)) {
        // Si estaba loginieado
        check_autologin(); // Limpia las variables de sesion

        if (!isset($_GET["ajax"])) {
            if (!$_SESSION["wt"]["logged"]) {
                // Notifica que la sesion expiró
                die($lang["SESS_EXPIRED"]);
            }
        }
    }

    if (!$_SESSION["wt"]["logged"]) {
        check_autologin();
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
			     Esto tambien esta en main.php
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
				if (is_array($arrTMP1) && count($arrTMP1)>0) {
                    $arrTMP2 = array();
                    foreach ($arrTMP1 as $arrTMP2["key"]=>$arrTMP2["value"]) {
                        $cfg[$rTMP["id"]][$arrTMP2["key"]] = $arrTMP2["value"];
                    }
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

    include_once("core/main_links.php");

    require_once("core/theme.php");

    $cfg["core"]["inSecureSide"] = isset($_SERVER["HTTPS"]);

    $strProfile = $cfg["core"]["site_profile"];

    if (!empty($strProfile) && is_dir("profiles/{$strProfile}")) {
        $strTextProfile = "profiles/{$strProfile}";
        include_once("{$strTextProfile}/main.php");
    }

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

    if($cfg["core"]["allow_webservice_devices"]){
        if(isset($config["webservices"])){
            reset($config["webservices"]);
            if(is_array($config["webservices"]) && count($config["webservices"])>0) {
                $arrModules = array();
                foreach ($config["webservices"] as $arrModules["key"]=>$arrModules["value"]) {
                    reset($arrModules["value"]);
                    if (is_array($arrModules["value"]) && count($arrModules["value"])) {
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
}