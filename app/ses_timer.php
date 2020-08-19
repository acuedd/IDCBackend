<?php
// OJO, este script chequea/revive la sesion
include_once("core/miniMain.php");
include_once("core/xmlfunctions.php");

function returnFail($boolXML = true, $strMessage = "") {
    if ($boolXML) {
        $objXML = new XMLNode("return");
        $objXML->addAttribute("status", "fail");
        if (!empty($strMessage)) {
                $objXML->addAttribute("msg", $strMessage);
        }

        if (isset($_GET["ajax"])) {
            header("Content-Type: text/xml; charset=iso-8859-1");
            print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
        }
        print $objXML->toString(true);
    }
    else {
        header("Content-Type: text/html; charset=iso-8859-1");
        print "fail";
        if (!empty($strMessage)) print ":{$strMessage}";
    }

    die();
}

    if (isset($_GET["c"])) {
        $strCommand = user_input_delmagic($_GET["c"]);

        if ($strCommand == "gt" && $_SESSION["wt"]["logged"]) {
            $objXML = new XMLNode("return");
            $objXML->addAttribute("status", "ok");
            $objXML->addAttribute("tot", $cfg["core"]["sess_timeout"]); // en minutos

            header("Content-Type: text/xml; charset=iso-8859-1");
            print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
            print $objXML->toString(true);
        }
        else if ($strCommand == "sl") {
            if (isset($_POST["submit_login"]) && !empty($_POST["submit_login"])) {
                $_POST["login_name"] = user_input_delmagic($_POST["login_name"], true);
                $_POST["login_passwd"] = user_input_delmagic($_POST["login_passwd"], true);
                $_POST["screenInfo"] = global_function::getParam($_POST,"screenInfo","",true);
                $intUidShould = intval($_POST["nfo"]);

                do_login();

                if ($_SESSION["wt"]["logged"] && $_SESSION["wt"]["uid"] != $intUidShould) {
                    clear_login();
                }

                if ($_SESSION["wt"]["logged"]) {
                    // 20100623 AG: Esto es una copia de lo que esta en main.php, si se cambia, actualizar tambien en main.php
                    foreach($cfg["modules"] as $mid=>$enabled) {
                        if ($enabled) include_once("modules/{$mid}/main.php");
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
                        foreach($cfg['modules'] AS $key => $value){
                            $arrModule["key"] = $key;
                            if (check_module($arrModule["key"], false)) {
                                $strFunction = "{$arrModule["key"]}_OnLogIn_Function";

                                if (function_exists($strFunction)) {
                                    $strFunction();
                                }
                            }
                        }
                        reset($cfg['modules']);
                    }

                    // Para asegurar que corra bien lo de email...
                    $boolAddEmail = check_module("email");

                    $strReturn = "ok";
                }
                else {
                    $strReturn = "fail";
                }
            }
            else {
                $strReturn = "fail";
            }

            $objXML = new XMLNode("return");
            $objXML->addAttribute("status", $strReturn);

            header("Content-Type: text/xml; charset=iso-8859-1");
            print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
            print $objXML->toString(true);
        }
        else if ($strCommand == "ce") {
            $objXML = new XMLNode("return");
            $objXML->addAttribute("status", "ok");
            $objXML->addAttribute("l", ($_SESSION["wt"]["logged"])?"Y":"N");
            header("Content-Type: text/xml; charset=iso-8859-1");
            print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
            print $objXML->toString(true);
        }
        else if($strCommand == "nt" && check_module("notificaciones")){
            $objXML = new XMLNode("return");
            $objXML->addAttribute("status", "ok");
            include_once("modules/notificaciones/notificaciones_model.php");
            $arr = array();
            $objC = new notificaciones_model($arr);
            $arrNotificaciones = $objC -> notificaciones_disponibles();
            if(isset($arrNotificaciones["listado"])){
                $objXML->addAttribute("exist", "Y");
                $objXML->addAttribute("notify", count($arrNotificaciones["listado"]));
                foreach($arrNotificaciones["listado"] AS $key => $val){
                    $objData = $objXML -> children[$objXML->addChild("notify_{$key}")];
                    $objData -> addAttribute("titulo", $val[0]);
                    $objData -> addAttribute("message", $val[1]);
                    $objData -> addAttribute("type", $val[2]);
                    unset($key);
                    unset($val);
                }
            }
            else{
                $objXML->addAttribute("exist", "N");
            }
            header("Content-Type: text/xml; charset=iso-8859-1");
            print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
            print $objXML->toString(true);
        }
        else {
            returnFail();
        }
    }
    else {
        returnFail();
    }
?>