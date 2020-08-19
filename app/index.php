<?php
include_once("core/main.php");
global $lang, $config, $cfg;
$index_page = true;
$page_name = $lang["HOME_TITLE"];
$boolDebugPerformance = false;
if(isset($_GET["paisID"]) && isset($_GET["changePaisByUser"]) && check_module("idiomas",false,"",false)){
    $intPais = intval($_GET["paisID"]);
    if($intPais != 0){
        $_SESSION["wt"]["idioma"] = $intPais;
        $arrTMP = array();
        $arrTMP["idioma_id"] = $intPais;
        $strSerializesIdioma = serialize($arrTMP);
        $strQuery = "REPLACE INTO wt_user_settings (userid, id, config) VALUES ('{$_SESSION["wt"]["uid"]}', 'idiomas', '{$strSerializesIdioma}')";
        db_query($strQuery);
        ?>
        <script>
            document.location.href='index.php';
        </script>
        <?php
    }
}

if (!isset($_SESSION["wt"]["idioma"])) {
    $_SESSION["wt"]["idioma"] = 1;   
}
  
if (!$_SESSION["wt"]["logged"]) {
    if (isset($_SERVER["PATH_INFO"])) {
        // Si hay / despues de la direccion: index.php/texto1/texto2 por ejemplo... traduzco estos a GETS...
        $arrVars = explode("/", $_SERVER["PATH_INFO"]);
        
        if (count($arrVars) > 1) {
            $arrVars[1] = str_replace(array(".html",".htm"), "", $arrVars[1]);
            if ($arrVars[1] == "login") {
                $_GET["login"] = "true";
            }
            else {
                $_GET["page"] = $arrVars[1];
            }
        }
    }

    if ((isset($cfg["core"]["show_login_only"]) && $cfg["core"]["show_login_only"] && !isset($_GET["forceNormal"])) ||
        (isset($_GET["login"]) && $_GET["login"] == "true")) { 
        (isset($_GET["redir"]))?$strRedirectAfterLogin = "redir=".$_GET["redir"]:$strRedirectAfterLogin = false;
        core_show_login_only("index.php",$strRedirectAfterLogin);
    }
    else{
        if (function_exists("draw_public_page")) { 
            draw_public_page();
        }
        else {
            if (isset($_GET['page'])) {
                $strPage = $_GET['page'];
            }
            else {
                $strPage = "";
            }
            draw_header();
            if (function_exists("draw_info_page")) {
                draw_info_page($strPage);
            }
            else {
                print "&nbsp;";
            }
            draw_footer();
        }
    }
}
else {
    if ($boolOnLoginEvent) {
        if(isset($_GET["redir"]) && $_GET["redir"] != ""){
            header("location: {$_GET["redir"]}");
            die();
        }
        if($cfg["core"]["force_changed_password"]){
            $boolChangePassword = sqlGetValueFromKey("SELECT change_password FROM wt_users WHERE uid = '{$_SESSION["wt"]["uid"]}'");
            if($boolChangePassword == 'N'){
                //Redireccionar a mi cuenta
                header("Location: index.php");
                die();
            }
        }

        $intMeses = date_getDifferenceInMonths($_SESSION["wt"]["lastvisit"], date("Y-m-d"));
        if ($intMeses >= 3) {
            //Redireccionar a mi cuenta
            header("Location: index.php");
            die();
        }
    }

    draw_header();
    core_show_AccountsToBeActivated();

    reset($cfg['modules']);

    // Cambio el orden de las alertas
    $arrFunctions = array();
    $arrTabSort = array();

    $arrTabSort["ACAD"] = 3;
    $arrTabSort["ADMON"] = 1;
    $arrTabSort["MONEY"] = 2;
    $arrTabSort["COMM"] = 4;
    $arrTabSort["ORG"] = 5;
    $arrTabSort["REG"] = 6;
    $arrTabSort["TOOL"] = 7;
    $arrTabSort["ROOT"] = 9;
    $arrTabSort["WS"] = 8;

    $arrModule = array();
    foreach ($cfg['modules'] as $arrModule["key"] => $arrModule["value"]) {
        if (check_module($arrModule["key"], false)) {
            $strFunction = "Show_{$arrModule["key"]}_ForToday";
            if (function_exists($strFunction)) {
                if (isset($config["modulesInfo"]["groups"][$arrModule["key"]])) {
                    $intMin = 1000;
                    reset($config["modulesInfo"]["groups"][$arrModule["key"]]);
                    $arrItem = array();
                    foreach ($config["modulesInfo"]["groups"][$arrModule["key"]] as $arrItem["key"] => $arrItem["value"]) {
                        if ($arrTabSort[$arrItem["value"]] < $intMin) {
                            $intMin = $arrTabSort[$arrItem["value"]];
                        }
                    }
                    reset($config["modulesInfo"]["groups"][$arrModule["key"]]);
                }
                else {
                    $intMin = 6;
                }
                $arrFunctions[$intMin][] = $strFunction;
            }
        }
    }
    reset($cfg['modules']);

    if ($boolDebugPerformance) $intLastCheckpoint = getmicrotime();

    //drawDebug($arrFunctions, "antes");
    ksort($arrFunctions);
    //drawDebug($arrFunctions, "despues");
    $arrTab = array();
    foreach ($arrFunctions as $arrTab["key"] => $arrTab["value"]) {
        $arrFunction = array();
        foreach ($arrTab["value"] as $arrFunction["key"] => $arrFunction["value"]) {
            $strFunction = $arrFunction["value"];
            $strFunction();

            if ($boolDebugPerformance) {
				$intCurrentTime = getmicrotime();
				$intCurrDelta = $intCurrentTime-$intLastCheckpoint;
				if ($intCurrDelta >= 0.1) {
					db_query("INSERT INTO wt_log (uid, date, descripcion) VALUES (0, NOW(), 'for today ran - {$strFunction}: {$intCurrDelta}s')");
				}
				$intLastCheckpoint = getmicrotime();
            }

        }
    }

    if(!empty($config['local_modules'])){
	    reset($config['local_modules']);
	    $arrModule = array();
	    foreach ($config['local_modules'] as $arrModule["key"] => $arrModule["value"]) {
		    $strFunction = "Show_{$arrModule["key"]}_ForToday";
		    if (function_exists($strFunction)) {
			    $strFunction();
		    }
	    }
	    reset($config['local_modules']);
    }
    
    print "&nbsp;";
    draw_footer();
}
?>