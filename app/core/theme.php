<?php
if (strstr($_SERVER["PHP_SELF"], "/core/"))
	die("You can't access this file directly...");
if (!isset($cfg["core"]["theme"]))
	$cfg["core"]["theme"] = "homeland";
if (!isset($cfg["core"]["theme_interno"]))
	$cfg["core"]["theme_interno"] = "";

//ALLAN 2009-12-22
//SI ESTA DEFINIDO EL THEME INTERNO, ESTA LOGINEADO Y ESTE THEME ES DISTINTO AL NORMAL
//ENTONCES LO QUE SE HACE ES REESCRIBIR LA VARIABLE DEL THEME (PARA QUE NO SE MODIFIQUE NADA MAS)
//Y SE LLAMA EL TH_FUNCTIONS DEL THEME INTERNO Y NO DEL THEME GLOBAL
if (!empty($cfg["core"]["theme_interno"]) && $cfg["core"]["theme_interno"] != $cfg["core"]["theme"] && isset($_SESSION["wt"]["logged"]) && $_SESSION["wt"]["logged"]) {
	$cfg["core"]["theme"] = $cfg["core"]["theme_interno"];
	$cfg["core"]["theme_profile"] = $cfg["core"]["theme_interno_profile"];
	//require_once( "themes/{$cfg["core"]["theme_interno"]}/th_functions.php" );
}
//else
if (isset($_GET["chtheme"])) {
    $strTMP = user_input_delmagic($_GET["chtheme"]);
    if (file_exists("themes/{$strTMP}/th_functions.php")) {
        $_SESSION["public"]["theme"] = $strTMP;
    }
    else {
        if (isset($_SESSION["public"]["theme"])) unset($_SESSION["public"]["theme"]);
    }
}
if (isset($_GET["homeland"])) {
    unset($_SESSION["public"]["theme"]);
}

if(!empty($_SESSION["wt"]["uid"])){
    unset($_SESSION["public"]["theme"]);
}

if (isset($_SESSION["public"]["theme"])){
    $cfg["core"]["theme"] = $_SESSION["public"]["theme"];
}


require_once("themes/{$cfg["core"]["theme"]}/th_functions.php");

function draw_menu()
{
	global $config, $cfg, $lang;

	$arr_menu = $config["menu"];
	ksort($arr_menu, SORT_NUMERIC);
	reset($arr_menu);

	$arrDrawThisArray = array();
    $entry = array();
    foreach($arr_menu as $entry["key"]=>$entry["value"]) {
		if (isset($entry["value"]["moduleID"])) {
			if (!check_module($entry["value"]["moduleID"], false, $entry["value"]["type"])) {
				continue;
			}
			else {
				if (isset($entry["value"]["type"]) && $entry["value"]["type"] != "A") {
					if ($entry["value"]["type"] == "L") {
						if (!$_SESSION["wt"]["logged"])
							continue;
					} else if ($entry["value"]["type"] == "N") {
						if ($_SESSION["wt"]["logged"])
							continue;
					} else {
						continue;
					}
				}
			}
		}
		else {
			if ($entry["value"]["type"] != "A") {
				if ($entry["value"]["type"] == "L") {
					if (!$_SESSION["wt"]["logged"])
						continue;
				} else if ($entry["value"]["type"] == "N") {
					if ($_SESSION["wt"]["logged"])
						continue;
				} else {
					continue;
				}
			}
		}
		$arrDrawThisArray[$entry["key"]] = $entry["value"];
	}

	if (count($arrDrawThisArray)) {
		theme_draw_leftbox_open($lang["ADM_MENU"], "DivSideBoxes_10000");
        ?>
        <div id="DivSideBoxes_10000" style="width:100%; margin:0 auto 0; overflow:hidden;">
            <?php
            foreach($arrDrawThisArray as $entry["key"]=>$entry["value"]) {
                if (isset($entry["value"]["title"])) {
                    print theme_draw_menu_item($entry["value"]["title"], $entry["value"]["file"]);
                }
                if (isset($entry["value"]["subitems"])) {
                    reset($entry["value"]["subitems"]);
                    $subEntry = array();
                    foreach ($entry["value"]["subitems"] as $subEntry["key"]=>$subEntry["value"]) {
                        if (!($subEntry["key"] === "type")) {
                            print theme_draw_menu_item($subEntry[1]["title"], $subEntry[1]["file"], false, 15);
                        }
                    }
                }
            }
            ?>
        </div>
        <?php
		theme_draw_leftbox_close("DivSideBoxes_10000");
        ?>
		<script type="text/javascript" language="javascript">
			function fixMenuSideBox() {
				var objTMP = getDocumentLayer("DivSideBoxes_10000");
				if (objTMP) {
					var arrSize = getObjDimentions(objTMP);
					arrSbAlturaMaxima["DivSideBoxes_10000"] = arrSize["height"];
					arrSbAlturaMaxima["DivSideBoxes_10000"] = MultiploSideBoxes ( arrSbAlturaMaxima["DivSideBoxes_10000"], intSbVelocidad )+1;

                    <?php
                    if (isset($cfg["sideboxes"]["10000"])) {
                        if ($cfg["sideboxes"]["10000"] == "on") {
                            ?>
                            getDocumentLayer("DivSideBoxes_10000").style.height = (arrSbAlturaMaxima["DivSideBoxes_10000"]*1)+"px";
                            arrSbCantidad["DivSideBoxes_10000"] = arrSbAlturaMaxima["DivSideBoxes_10000"]*1;
                            <?php
                        }
                        else {
                            ?>
                            getDocumentLayer("DivSideBoxes_10000").style.height = "1px";
                            arrSbCantidad["DivSideBoxes_10000"] = 1;
                            setTimeout("DivSideBoxesOcultar('DivSideBoxes_10000', '1')", 1);
                            setTimeout("DivSideBoxesOcultar('DivSideBoxes_10000', '2')", 1);
                            <?php
			            }
                    }
                    else {
                        ?>
                        getDocumentLayer("DivSideBoxes_10000").style.height = (arrSbAlturaMaxima["DivSideBoxes_10000"]*1)+"px";
                        arrSbCantidad["DivSideBoxes_10000"] = arrSbAlturaMaxima["DivSideBoxes_10000"]*1;
                        <?php
		            }
                    ?>
                }
            }
            addLoadListener(fixMenuSideBox);
		</script>
<?php
	}

	if (check_module("email")) {
		draw_email_menu();
	}

	// DrawAdminBox
	draw_admin_menu();
}

/**
 * Devuelve el menu ordenado por grupos
 *
 * @global type $config
 * @global type $cfg
 * @global type $lang
 *
 * @param variant $arrModulesFilter filtro de modulos a considerar, util por primera vez con los universos.  Si es falso, no se filtra nada, para filtrar se espera un array
 * @return type
 */
function prepare_admin_menu_data($arrModulesFilter = false) {
	global $config, $cfg, $lang;

	if (!isset($config["modulesInfo"]["ids"])) {
	    if (is_array($config["modulesInfo"]["titles"])) {
            $config["modulesInfo"]["ids"] = array_flip($config["modulesInfo"]["titles"]);
            /*
             * // Esto a veces da un warning que cuesta encontrar... habilitar estas lineas permite recibir un debug para encontrar el modulo que tenga algun problema.
            $last_error = error_get_last();
            if ($last_error['type'] === E_WARNING) {
                $varTMP = serialize($config["modulesInfo"]["titles"]);
                core_SendScriptInfoToWebmaster("Error en theme ¿¿?? - {$varTMP}", false, "agudiel@homeland.com.gt", true);
                $varTMP = null;
                $config["modulesInfo"]["ids"] = array();
            }
            */
            $config["modulesInfo"]["ids"]["Site Admin"] = "core";
        }
        else {
            $config["modulesInfo"]["ids"] = array();
            $config["modulesInfo"]["ids"]["Site Admin"] = "core";
        }
	}

	$arrSortedArray = array();
	$arrLinks = array();

	reset($config["admmenu"]);

    $entry = array();
    foreach ($config["admmenu"] as $entry["key"]=>$entry["value"]) {
		if ($entry["key"] == "module") continue;

		if (isset($entry["value"]["class"]) && check_user_class($entry["value"]["class"]) && !empty($entry["value"]["file"])) {
			$strModule = (isset($entry["value"]["module"])) ? $entry["value"]["module"] : $entry["key"];
			if ($strModule == "Site Admin") {
				//$strModule = "_".$strModule;
				$strModule = $lang["SITE_CONFIG"];
			}

			$strModuleID = (isset($config["modulesInfo"]["ids"][$strModule])) ? $config["modulesInfo"]["ids"][$strModule] : "";

			//Evaluo si filtro o no este modulo
			if (!empty($strModuleID) && is_array($arrModulesFilter) && array_search($strModuleID, $arrModulesFilter) === false) continue;


			if (!isset($arrSortedArray[$strModule])) {
				$arrSortedArray[$strModule] = array();
			}

			//VERIFICA SI ESTA DEFINIDA LA ESTRUCTURA DE GRUPOS PARA EL MENU
			if (isset($config["admmenu"]['module'][$strModule]) && !isset($arrSortedArray[$strModule]['group'])) {
				$arrSortedArray[$strModule]['groups'] = array();
			}
			else {
				if (!isset($arrLinks[$entry["value"]["file"]])) {
					$arrSortedArray[$strModule][$entry["key"]] = $entry["value"]["file"];
					$arrLinks[$entry["value"]["file"]] = true;
				}
			}
		}
	}
	ksort($arrSortedArray);

    $arrModule = array();
    foreach ($arrSortedArray as $arrModule["key"]=>$arrModule["value"]) {
		$module = $arrModule["key"];
		$data = $arrModule["value"];

		if (isset($data["groups"])) {
			//$arrSortedArray[$module]["groups"] = $config["admmenu"]["module"][$module]["group"];
			//AQUI TENGO QUE REVISAR LINK POR LINK SI TIENE ACCESSO PARA AGREGARLO
			reset($config["admmenu"]["module"][$module]["group"]);
            $arrGrupo = array();
            foreach ($config["admmenu"]["module"][$module]["group"] as $arrGrupo["key"]=>$arrGrupo["value"]) {
				$grupoKey = $arrGrupo["key"];
				$grupo = $arrGrupo["value"];

				if (isset($grupo["elements"])) {
					if (!isset($arrSortedArray[$module]["groups"][$grupoKey]["name"])) {
						if (isset($grupo["name"])) {
							$arrSortedArray[$module]["groups"][$grupoKey]["name"] = $grupo["name"];
							if(isset($grupo["icon"]))
							    $arrSortedArray[$module]["groups"][$grupoKey]["icon"] = $grupo["icon"];
                            if(isset($grupo["new"]))
                                $arrSortedArray[$module]["groups"][$grupoKey]["new"] = $grupo["new"];
                        }
						else {
							$arrSortedArray[$module]["groups"][$grupoKey]["name"] = $grupo["elements"][0]["name"];
							if(isset($grupo["icon"]))
							    $arrSortedArray[$module]["groups"][$grupoKey]["icon"] = $grupo["elements"][0]["icon"];
                            if(isset($grupo["new"]))
                                $arrSortedArray[$module]["groups"][$grupoKey]["new"] = $grupo["new"];
						}
					}

                    $arrElement = array();
                    foreach ($grupo["elements"] as $arrElement["key"]=>$arrElement["value"]) {
						$elementKey = $arrElement["key"];
						$element = $arrElement["value"];

						if (isset($element["class"]) && check_user_class($element["class"]) && !isset($arrLinks[$element["file"]])) {
                            $arrSortedArray[$module]["groups"][$grupoKey]["elements"][$element["name"]] = $element["file"];
							$arrLinks[$element["file"]] = true;
						}
					}

					if (!isset($arrSortedArray[$module]["groups"][$grupoKey]["elements"]))
						unset($arrSortedArray[$module]["groups"][$grupoKey]);
				}
			}


		}
	}
	if(is_array($arrSortedArray)) reset($arrSortedArray);
    $arrClean = $arrSortedArray;
    //clean array
    foreach ($arrClean AS $key => $value){
        if(isset($value["groups"]) && count($value["groups"]) == 1){
            $index = key($value["groups"]);
	        if(count($value["groups"][$index]["elements"])){
	            foreach($value["groups"][$index]["elements"] AS $kk => $val){
		            $arrSortedArray[$key][$kk] = $val;
                }
            }
            unset($arrSortedArray[$key]["groups"]);
        }
    }
	if(is_array($arrSortedArray)) reset($arrSortedArray);
	//browseArray(array("Sorted"=>$arrSortedArray), true, false, true, false, false);

	return $arrSortedArray;
}

function draw_admin_menu() {
	global $config, $cfg, $lang;

	$strFunction = "draw_admin_menu_{$cfg["core"]["theme"]}";
	if (function_exists($strFunction)) {
		$strFunction();
	}
	else {
		if ($_SESSION["wt"]["logged"]) {
			$arrSortedArray = prepare_admin_menu_data();

			$admmenu = array();
			$arrMI = array();
            $arrModule = array();
            foreach ($arrSortedArray as $arrModule["key"]=>$arrModule["value"]) {
				ksort($arrSortedArray[$arrModule["key"]]);
				if (isset($config["admmenu"][$arrModule["key"]])) {
                    $entry = array();
                    foreach ($arrModule["value"] as $entry["key"]=>$entry["value"]) {
						$arrMI["link"] = $entry["value"];
						$arrMI["text"] = $entry["key"];
						$admmenu[] = $arrMI;
					}
				}
				else {
					if (!isset($arrModule['value']['groups']) && count($arrModule["value"]) == 1) {
						$strTMP = current($arrModule["value"]);
						$strTMPKey = key($arrModule["value"]);
						$strLinkText = (strlen($strTMPKey) < 20) ? $strTMPKey : $arrModule["key"];
						$arrMI["link"] = $strTMP;
						$arrMI["text"] = $strLinkText;
						$admmenu[] = $arrMI;
					}
					else {
						$arrMI["link"] = "admin_menu.php?module={$arrModule["key"]}";
						$arrMI["text"] = $arrModule["key"];
						$admmenu[] = $arrMI;
					}
				}
			}
            $strFunction = "draw_menu_horizontal_{$cfg["core"]["theme"]}";
            /*if(function_exists($strFunction)){
                return $strFunction($admmenu);
            }*/
			$_SESSION["wt"]["admin_menu_draw"] = array();
			if (count($admmenu)) {
				$_SESSION["wt"]["admin_menu_draw"] = $arrSortedArray;
				theme_draw_leftbox_open($lang["ADM_MENU_ADMIN"], false);
                $arrItem = array();
                foreach ($admmenu as $arrItem["key"]=>$arrItem["value"]) {
					print theme_draw_menu_item($arrItem["value"]["text"], $arrItem["value"]["link"]);
				}
				theme_draw_leftbox_close(false);
			}
		}
	}    
}
$arrGlobalLinksMyModuleAllModules = false;
$boolGlobalFirstAfterFrame = false;

/**
 * Dibuja el header de HTML
 *
 * @param bool $boolExportCSS, indica si el CSS se tiene que exportar en el html o si se usa un include.
 * @param bool $boolIgnoreCSS, indica si se ingnoran los CSS
 * @param bool $boolIgnoreSesExpWindow, indica si se ignora la ventana de expiracion de sesion
 * @param string $strKeywords, indica keywords
 * @param string $strPosition, indica la posición del icono de manuales, top,left,right,bottom
 * @param bool $boolManuales, indica si se mostrara el icono de manuales
 * @param bool $boolResponsive, indica si el theme es responsive design
 */
function draw_header_tag($page_name = "",$boolExportCSS = false, $boolIgnoreCSS = false,$boolIgnoreSesExpWindow = false, $strKeyWords = ""){
	global $cfg, $lang, $boolGlobalFirstAfterFrame;

	$boolJustLoggedOut = false;
	if (!$_SESSION["wt"]["logged"]) {
		$cfg["core"]["drawExternalFrame"] = false;
		if ((isset($_GET["act"]) && $_GET["act"] == "logout") || (isset($_GET["cldmd"]) && $_GET["cldmd"] == "r")) {
			$boolJustLoggedOut = true;
		}
	}
	else if ($_SESSION["wt"]["browser"]["detail"]["boolIsSafari"]) {
		$cfg["core"]["drawExternalFrame"] = false;
	}

	$boolDrawFrame = false;
	if (isset($cfg["core"]["drawExternalFrame"]) && $cfg["core"]["drawExternalFrame"]) {
		if (!isset($_SESSION["wt"]["frameDrawn"])) {
			$boolDrawFrame = true;
		}
		else {
			if (isset($_GET["clearIframeVar"])) {
				unset($_SESSION["wt"]["frameDrawn"]);
				$_SESSION["wt"]["force_page"] = $_GET["force_page"];
				$boolDrawFrame = false;
			}
		}
	}
	else {
        if (isset($_SESSION["wt"]["frameDrawn"])) unset($_SESSION["wt"]["frameDrawn"]);

		if (isset($_SESSION["wt"]["force_page"])) unset($_SESSION["wt"]["force_page"]);

		$boolDrawFrame = false;
	}
    
	if ($boolDrawFrame && !$boolExportCSS) {
		$strCurrLink = basename($_SERVER["PHP_SELF"]) . ((empty($_SERVER["QUERY_STRING"])) ? "" : "?{$_SERVER["QUERY_STRING"]}");
		if (isset($_SESSION["wt"]["force_page"])) {
			$strCurrLink = str_replace(array("*", "|"), array("?", "&"), $_SESSION["wt"]["force_page"]);
			unset($_SESSION["wt"]["force_page"]);
		}
		print "<head>\n<title>";
		print (!empty($page_name))?$page_name:$cfg["core"]["title"];
		print "</title>\n";
        print "<meta http-equiv=\"content-type\" content=\"text/html; charset={$lang["CHARSET"]}\" />\n";
        print "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\"/>";
        print "<link href=\"core/packages/awesome/css/font-awesome.min.css\" rel=\"stylesheet\" type=\"text/css\">\n";
        print "<link href=\"core/packages/bootstrap/bootstrap.min.css\" rel=\"stylesheet\" type=\"text/css\">\n";

		if (file_exists("themes/{$cfg["core"]["theme"]}/favicon.ico")) {
			print "<link rel=\"shortcut icon\" href=\"themes/{$cfg["core"]["theme"]}/favicon.ico\"/>\n";
		}
        elseif(file_exists("profiles/{$cfg["core"]["site_profile"]}/favicon.ico")){
            print "<link rel=\"shortcut icon\" href=\"profiles/{$cfg["core"]["site_profile"]}/favicon.ico\"/>\n";
        }
		else {
			print "<link rel=\"shortcut icon\" href=\"favicon.ico\"/>\n";
		}

		$_SESSION["wt"]["frameDrawn"] = true;
		$_SESSION["wt"]["doDrawFrame"] = true;
		if (strstr($strCurrLink, "IES=true") === false) {
			if (strstr($strCurrLink, "?") === false) {
				$strCurrLink .= "?IES=true";
			}
			else {
				$strCurrLink .= "&IES=true";
			}
		}
		print "</head>\n";
		$strBaseURL = core_getBaseDir();
		?>
		<frameset cols="100%" rows="100%" border="0" frameborder="0" framespacing="0">
			<frame src="<?php print "{$strBaseURL}{$strCurrLink}"; ?>" style="width:100%; height:100%;" frameborder="0"/>
		</frameset>
		</html>
		<?php
		die();
	}
	else {
        if (isset($_SESSION["wt"]["doDrawFrame"])) {
			$boolGlobalFirstAfterFrame = true;
			unset($_SESSION["wt"]["doDrawFrame"]);
		}

		print "<head>";
        print "<link rel=\"manifest\" href='/manifest.json'>";
        draw_header_code($strKeyWords);
		print "<title>";
		print (!empty($page_name))?$page_name:$cfg["core"]["title"];
		print "</title>\n";
		print "<meta http-equiv=\"content-type\" content=\"text/html; charset={$lang["CHARSET"]}\" />\n";
		print "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\"/>";
		print "<link href=\"core/packages/awesome/css/font-awesome.min.css\" rel=\"stylesheet\" type=\"text/css\">\n";
		print "<link href=\"core/packages/bootstrap/bootstrap.min.css\" rel=\"stylesheet\" type=\"text/css\">\n";
		if (!$boolIgnoreCSS) theme_draw_style($boolExportCSS);

		$boolCancelExcec = false;

		if (!$cfg["core"]["CACHE_CSS_AND_JAVA"]) {
			?>
			<script language="JavaScript" src="core/packages/jquery.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="core/packages/hml_library.js" type="text/javascript"></script>
			<script language="JavaScript" src="core/packages/bootstrap/bootstrap.min.js" type="text/javascript"></script>
			<script language="JavaScript" src="core/packages/lodash/lodash.js" type="text/javascript"></script>
			<?php
		}
		//20120307 AG: Puse el &v=2 para obligarlo a cargar el dynamicjava el 20120307 que suba un cambio en hml_library.js
		?>
		<script language="JavaScript" src="dynamicjava.php?j=1&t=<?php print $cfg["core"]["theme"];?>&n=2" type="text/javascript"></script>
		<script language="JavaScript" type="text/javascript">
			<?php
			if (isset($cfg["core"]["drawExternalFrame"]) && $cfg["core"]["drawExternalFrame"] && !$boolExportCSS) {
				// Esto sirve para el auto-reload de una ventana sin frame
				if (isset($_GET["clearIframeVar"])) {
					?>
					document.location.href = "./index.php";
					<?php
					$boolCancelExcec = true;
				}
				elseif (count($_POST) == 0) {
					$strCurrLink = basename($_SERVER["PHP_SELF"]) . ((empty($_SERVER["QUERY_STRING"])) ? "" : "?{$_SERVER["QUERY_STRING"]}");
					?>
					if (window.parent.frames != null) {
						if (window.parent.frames.length == 0 && window.name == "") {
							var strTMP = "./index.php?clearIframeVar=true&force_page=<?php print str_replace(array("?", "&"), array("*", "|"), $strCurrLink); ?>";
							document.location.href = strTMP;
						}
					}
					<?php
				}
			}
			else if (!$cfg["core"]["drawExternalFrame"] && $boolJustLoggedOut) {
				//20120201 AG: Si no debe dibujarse el external frame, busco que no este dibujada y si SI, re cargo la ventana sin el frame.
				//				Esto lo hago solo si me acabo de salir porque me podria dar inestabilidad en otros lugares
				$strCurrLink = basename($_SERVER["PHP_SELF"]) . ((empty($_SERVER["QUERY_STRING"])) ? "" : "?{$_SERVER["QUERY_STRING"]}");

				//20121002 AG: Como me acabo de salir, me aseguro de ir al lado unsecure
				if ($cfg["core"]["HTTPS"] && $cfg["core"]["HTTPS_logged"]) {
					$strURL = core_getBaseDir("N");
					$strCurrLink = "{$strURL}{$strCurrLink}";
				}
				?>
				if (window.parent.frames != null) {
					if (window.parent.frames.length == 1 && window.name == "") {
						window.parent.document.location.href = "<?php print $strCurrLink;?>";
					}
				}
				<?php
			}
			//20100921 Aqui iban las lineas de inicializacion del los RTE que se trasladaron a ./dynamicjava.php
			if (!$boolIgnoreSesExpWindow && $_SESSION["wt"]["logged"] && $cfg["core"]["sess_timeout_notification"]) {
				?>
				var boolDoSesExpTimer = <?php print ($_SESSION["wt"]["logged"] && $cfg["core"]["sess_timeout_notification"]) ? "true" : "false"; ?>;
				function setSesExpTime_UID() {
					setSesExpTime(<?php print $_SESSION["wt"]["uid"]; ?>);
				}
				addLoadListener(setSesExpTime_UID);
				<?php
				//20100921 Aqui iban las lineas de la ventanita del expiration window que se trasladaron a ./dynamicjava.php
			}
			?>
		</script>
		<?php
		//20110225 AG: Esto agrega los alerts siempre visibles en el codigo para que el usuario sepa que esta emulando a otro usuario
		if (isset($_SESSION["wt"]["originalUserToTest"])) {
			$strEmulando = sqlGetValueFromKey("SELECT realname FROM wt_users WHERE uid = {$_SESSION["wt"]["uid"]}");
			?>

			<div style="position: absolute !important; z-index:2000; min-height:6%; height: 3%; top:0; left:0;width: 100%;background-color: #C7C7C7;">

                <div class="col-lg-6">
					<h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i><?php print "{$lang["CHANGE_USER_TO_TEST_EMULANDO"]} {$strEmulando}";?></h4>
				</div>
				<div class="col-lg-6 text-right" style="min-height: 10%">
					<button type="button" class="btn btn-default" style="min-height: 10%"
							onclick="document.location.href='adm_main.php?mde=users&wdw=emulate&revertUser=<?php print $_SESSION["wt"]["originalUserToTest"]; ?>'">
						<?php print $lang["CHANGE_USER_TO_TEST_REMOVE"]; ?>
					</button>
				</div>
			</div>

			<div id="divUserEmulationFloat" class="ui-widget" style="position: fixed !important; z-index:25; top:0; left:0; width:100%;">
				<div class="ui-state-error uid-corner-all">
					<div style="height:100%; vertical-align:middle;">
						<table style=" font-family:inherit; font-size:inherit;" cellspacing="0" cellpadding="0" width="100%" border="0">
							<tr style=" font-family:inherit; font-size:inherit;">
								<td width="1%">
									<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
								</td>
								<td style="font-family:inherit; font-size:inherit; padding-left:5px;"></td>
								<td align="right" width="50%">

								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>

            <div style="height: 35px; width: 100%;" >&nbsp;</div>
			<?php
		}
		if ($boolCancelExcec) die();        
		print "</head>\n";

		//if (!empty($strDebugString)) drawDebug($strDebugString, "DebugString");

		//$config["theme_vars"]["Content_Expiration"] = 0;
	}
}

function draw_header_code($strKeyWords = "")
{
	global $lang, $cfg, $config, $boolGlobalIsLocalDev, $page_description;

	if (!isset($page_description) || empty($page_description)) $page_description = $cfg["core"]["description"];
    
    if($strKeyWords != ""){
        print "<meta name=\"keywords\" content=\"{$cfg["core"]["keywords"]},{$strKeyWords}\" />\n";
    }
    else{
        print "<meta name=\"keywords\" content=\"{$cfg["core"]["keywords"]}\" />\n";
    }
    
	echo "\n<meta http-equiv=\"content-type\" content=\"text/html; charset={$lang["CHARSET"]};\" />\n"
	. "<meta http-equiv=\"generator\" content=\"Homeland Online Communities\" />\n"
	. "<meta name=\"robots\" content=\"index, follow\" />\n"
	. "<meta name=\"author\" content=\"{$cfg["core"]["title"]}\" />\n"
	. "<meta name=\"description\" content=\"{$page_description}\" />\n";

	if (isset($config["theme_vars"]["Content_Expiration"])) {
		print "<meta http-equiv='expires' content='{$config["theme_vars"]["Content_Expiration"]}' />\n";
	}
	// Extra meta tags
	if (isset($config["theme_vars"]["metaTags"])) {
		$arrItem = array();
        foreach ($config["theme_vars"]["metaTags"] as $arrItem["key"] => $arrItem["value"]) {
			$strMeta = "<meta name=\"{$arrItem["key"]}\" ";
			$arrInfo = array();
            foreach ($arrItem["value"] as $arrInfo["key"] => $arrInfo["value"]) {
				$strMeta .= "{$arrInfo["key"]}=\"{$arrInfo["value"]}\" ";
			}
		}
		$strMeta .= "/>";
		print $strMeta;
	}

	/*Esto es para asegurar que los includes de los estilos e imagenes sea siempre la direccion del sitio
	 * Fue necesario agregarlo por el uso de $_SERVER["PATH_INFO"] para ver si venian variables despues del script (index.php/sugerencias/usuarios/)
	 */
	$boolDoNotUseBase = (isset($_SESSION["wt"]["browser"]) && $_SESSION["wt"]["browser"]["detail"]["boolIsMSIE"] && $_SESSION["wt"]["browser"]["detail"]["IEVer"] < 7);
	if (!$boolDoNotUseBase) {
		$strBaseURL = core_getBaseDir();
		print "<base href=\"{$strBaseURL}\" />\n";
	}
	// Verificacion de Google, esta deberia de venir en los metaTags
	//print "<meta name=\"verify-v1\" content=\"J9idQNFqW5ptlzE6y2vU4Ia3KhMo55Qlds6jPUS3/yU=\" />";
}

function draw_copyright()
{
	global $cfg, $time_start, $lang;

	if ($cfg["core"]["page_processed"])
		printf("<span class=\"pgprocessed\">{$lang["PAGE_PROCESSED"]}</span>", (getmicrotime() - $time_start));
	else
		echo "&nbsp;";
}

function theme_draw_style($boolExportCSS = false) {
	global $config, $cfg, $arrModuleMainGot;

    $intUserIDToLog = (isset($_SESSION["wt"]["uid"]) && $_SESSION["wt"]["uid"] > 0)?$_SESSION["wt"]["uid"]:0;
    
	if (file_exists("themes/{$cfg["core"]["theme"]}/H.ico")) {
		print "<link rel=\"shortcut icon\" href=\"themes/{$cfg["core"]["theme"]}/H.ico\"/>\n";
	}
	elseif(file_exists("profiles/{$cfg["core"]["site_profile"]}/H.ico")){
        print "<link rel=\"shortcut icon\" href=\"profiles/{$cfg["core"]["site_profile"]}/H.ico\"/>\n";
    }
	else if (!$cfg["core"]["inSecureSide"]) {
		print "<link rel=\"shortcut icon\" href=\"http://www.homeland.com.gt/logos/H.ico\"/>\n";
	}

	if (file_exists("themes/{$cfg["core"]["theme"]}/apple-touch-icon.png")) {
		print "<link rel=\"apple-touch-icon\" href=\"themes/{$cfg["core"]["theme"]}/apple-touch-icon.png\"/>\n";
	}
	else if (!$cfg["core"]["inSecureSide"]) {
		print "<link rel=\"apple-touch-icon\" href=\"http://www.homeland.com.gt/logos/apple-touch-icon.png\"/>\n";
	}

	//$boolExportCSS = ($boolExportCSS || CheckCanGzip());
	if ($boolExportCSS) {
		?>
		<style type="text/css">
			<?php
			$strURL = core_getBaseDir("N");
			if ($_SESSION["wt"]["browser"]["detail"]["boolIsMSIE"]) {
				if (file_exists("themes/{$cfg["core"]["theme"]}/custom_fonts_ie.php")) {
					readfile("{$strURL}/themes/{$cfg["core"]["theme"]}/custom_fonts_ie.php");
				}
			}
			else {
				if (file_exists("themes/{$cfg["core"]["theme"]}/custom_fonts.php")) {
					readfile("{$strURL}/themes/{$cfg["core"]["theme"]}/custom_fonts.php");
				}
			}

            readfile("themes/{$cfg["core"]["theme"]}/frames.css");
            readfile("themes/{$cfg["core"]["theme"]}/style.css");
			readfile("themes/{$cfg["core"]["theme"]}/forms.css");
			if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/frames.css")) {
                readfile("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/frames.css");
            }
            if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/style.css")) {
                readfile("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/style.css");
            }
            if(file_exists("profiles/{$cfg["core"]["site_profile"]}/frames.css")){
                readfile("profiles/{$cfg["core"]["site_profile"]}/frames.css");
            }
            if(file_exists("profiles/{$cfg["core"]["site_profile"]}/style.css")){
                readfile("profiles/{$cfg["core"]["site_profile"]}/style.css");
            }

			reset($cfg['modules']);
            $mod = array();
            foreach ($cfg['modules'] as $mod["key"] => $mod["value"]) {
				// Solo jala los modulos que estén incluidos...
				if ($mod["value"] && isset($arrModuleMainGot[$intUserIDToLog][$mod["key"]]) && $arrModuleMainGot[$intUserIDToLog][$mod["key"]] && file_exists("themes/{$cfg["core"]["theme"]}/{$mod["key"]}.css")) {
					if (isset($config["stylecss"][$mod["key"]]) && $config["stylecss"][$mod["key"]]) {
						readfile("themes/{$cfg["core"]["theme"]}/{$mod["key"]}.css");
                        if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$mod["key"]}.css")) {
                            readfile("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$mod["key"]}.css");
                        }
                        if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$mod["key"]}.css")){
                            readfile("profiles/{$cfg["core"]["site_profile"]}/{$mod["key"]}.css");
                        }
					}
				}
			}
			?>
		</style>
		<?php
	}
	else {
		if ($_SESSION["wt"]["browser"]["detail"]["boolIsMSIE"]) {
			if (file_exists("themes/{$cfg["core"]["theme"]}/custom_fonts_ie.php")) {
				print "<link href=\"themes/{$cfg["core"]["theme"]}/custom_fonts_ie.php\" rel=\"stylesheet\" type=\"text/css\">\n";
			}
		}
		else {
			if (file_exists("themes/{$cfg["core"]["theme"]}/custom_fonts.php")) {
				print "<link href=\"themes/{$cfg["core"]["theme"]}/custom_fonts.php\" rel=\"stylesheet\" type=\"text/css\">\n";
			}
		}

		if (!$cfg["core"]["CACHE_CSS_AND_JAVA"]) {
            print "<link href=\"themes/{$cfg["core"]["theme"]}/frames.css\" rel=\"stylesheet\" type=\"text/css\">\n";
            print "<link href=\"themes/{$cfg["core"]["theme"]}/style.css\" rel=\"stylesheet\" type=\"text/css\">\n";
            print "<link href=\"themes/{$cfg["core"]["theme"]}/forms.css\" rel=\"stylesheet\" type=\"text/css\">\n";
            if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/style.css")) {
                print "<link href=\"themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/style.css\" rel=\"stylesheet\" type=\"text/css\">\n";
            }
            if(file_exists("profiles/{$cfg["core"]["site_profile"]}/style.css")){
                print "<link href=\"profiles/{$cfg["core"]["site_profile"]}/style.css\" rel=\"stylesheet\" type=\"text/css\">\n";
            }
			reset($cfg['modules']);
            $mod = array();
            foreach ($cfg['modules'] as $mod["key"] => $mod["value"]) {
				// Solo jala los modulos que estén incluidos...
				if ($mod["value"] && isset($arrModuleMainGot[$intUserIDToLog][$mod["key"]]) && $arrModuleMainGot[$intUserIDToLog][$mod["key"]]) {
					if (isset($config["stylecss"][$mod["key"]]) && $config["stylecss"][$mod["key"]]) {
                        if(file_exists("themes/{$cfg["core"]["theme"]}/{$mod["key"]}.css")){
                            print "<link href=\"themes/{$cfg["core"]["theme"]}/{$mod["key"]}.css\" rel=\"stylesheet\" type=\"text/css\">\n";
                        }
                        if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$mod["key"]}.css")) {
                            print "<link href=\"themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$mod["key"]}.css\" rel=\"stylesheet\" type=\"text/css\">\n";
                        }
                        if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$mod["key"]}.css")){
                            print "<link href=\"profiles/{$cfg["core"]["site_profile"]}/{$mod["key"]}.css\" rel=\"stylesheet\" type=\"text/css\">\n";
                        }
					}
				}
			}
		}
		print "<link href=\"dynamiccss.php?j=1&t={$cfg["core"]["theme"]}\" rel=\"stylesheet\" type=\"text/css\">\n";
	}
}

function draw_users_online($boolIsLeft = true) {
	global $config, $lang, $cfg;

	if (!$_SESSION["wt"]["logged"])
		return;

	$strTMP = "SELECT COUNT(DISTINCT IF(uid=0,id,uid)) AS conteo,
					  IF(uid=0,'offline','online') AS status
			   FROM wt_online
			   GROUP BY status";

	$qTMP = db_query($strTMP);
	$int_online = 0;
	$int_offline = 0;
	while ($rTMP = db_fetch_array($qTMP)) {
		$strVar = "int_" . $rTMP["status"];
		$$strVar = $rTMP["conteo"];
	}

	$strWichToUse = ($cfg["core"]["showVisitors"]) ? "USERS_ONLINE_TEXT" : "USERS_ONLINEWOV_TEXT"; 

	if ($boolIsLeft) {
		?>
        <div id="DivSideBoxes_20000" style="width:100%;margin:0 auto 0; overflow:hidden;">
			<?php printf($lang[$strWichToUse], $int_online, "<a href=\"adm_main.php?mde=users&wdw=users&online=true\">", "</a>", $int_offline);?>
        </div>
        <?php
        /*
		<script type="text/javascript" language="javascript">
			function sideBoxesProcesarUsersOnline() {
				objTmpDIV = getDocumentLayer("DivSideBoxes_20000");
				if (objTmpDIV) {
					var arrSize = getObjDimentions(objTmpDIV);
					arrSbAlturaMaxima["DivSideBoxes_20000"] = arrSize["height"];
					//alert(alturaMaxima["DivSideBoxes_20000"]);
					arrSbAlturaMaxima["DivSideBoxes_20000"] = MultiploSideBoxes ( arrSbAlturaMaxima["DivSideBoxes_20000"], intSbVelocidad )+1;
					//getDocumentLayer("DivSideBoxes_01").style.height = (alturaMaxima["DivSideBoxes_01"]*1)+"px";
					//cantidad["DivSideBoxes_01"] = alturaMaxima["DivSideBoxes_01"]*1;
					<?php
					if (isset($cfg["sideboxes"]["20000"])) {
						if ($cfg["sideboxes"]["20000"] == "on") {
							?>
							getDocumentLayer("DivSideBoxes_20000").style.height = (arrSbAlturaMaxima["DivSideBoxes_20000"]*1)+"px";
							arrSbCantidad["DivSideBoxes_20000"] = arrSbAlturaMaxima["DivSideBoxes_20000"]*1;
							<?php
						}
						else {
							?>
							getDocumentLayer("DivSideBoxes_20000").style.height = "1px";
							arrSbCantidad["DivSideBoxes_20000"] = 1;
							setTimeout("DivSideBoxesOcultar('DivSideBoxes_20000', '1')", 1);
							setTimeout("DivSideBoxesOcultar('DivSideBoxes_20000', '2')", 1);
							<?php
						}
					}
					else {
						?>
						getDocumentLayer("DivSideBoxes_20000").style.height = (arrSbAlturaMaxima["DivSideBoxes_20000"]*1)+"px";
						arrSbCantidad["DivSideBoxes_20000"] = arrSbAlturaMaxima["DivSideBoxes_20000"]*1;
						<?php
					}
					?>
				}
			}
			addLoadListener(sideBoxesProcesarUsersOnline);
		</script>
		<?php
        */
	}
	else {
		printf($lang[$strWichToUse], $int_online, "<a href=\"adm_main.php?mde=users&wdw=users&online=true\">", "</a>", $int_offline);
	}
	db_free_result($qTMP);
}

function draw_linkPath($boolIncludeTabInfo = true, $boolReturn = false, $strDrawFunction = "") {
	global $page_name, $config, $lang;
	$strPath = "";
	$strModulo = "";
	$arrLinks = array();
	$arrLinksTMP = array();

	if (!isset($config["admmenu"][$page_name]['module']))
		return false;

	$strModulo = $config["admmenu"][$page_name]['module'];
	//drawDebug($strModulo);
	If ($boolIncludeTabInfo) {
		$arrTmp = array();
        foreach ($config["modulesInfo"] as $arrTmp["key"] => $arrTmp["value"]) {
			$arrTmp2 = array();
            foreach ($arrTmp["value"] as $arrTmp2["key"] => $arrTmp2["value"]) {
				if ($arrTmp2["value"] == $strModulo) {
					$tab = $arrTmp2["key"];
				}
			}
		}
		if (isset($config["modulesInfo"]["groups"][$tab])) {
			if ($config["modulesInfo"]["groups"][$tab][0] == "ADMON") {
				$strPath .= $lang["MENU_GROUP_ADMON"] . " &raquo ";
			}
			if ($config["modulesInfo"]["groups"][$tab][0] == "ACAD") {
				$strPath .= $lang["MENU_GROUP_ACAD"] . " &raquo ";
			}
			if ($config["modulesInfo"]["groups"][$tab][0] == "COMM") {
				$strPath .= $lang["MENU_GROUP_COMM"] . " &raquo ";
			}
			if ($config["modulesInfo"]["groups"][$tab][0] == "ORG") {
				$strPath .= $lang["MENU_GROUP_ORG"] . " &raquo ";
			}
			if ($config["modulesInfo"]["groups"][$tab][0] == "WS") {
				$strPath .= $lang["MENU_GROUP_WS"] . " &raquo ";
			}
		}
	}

	if (isset($config["admmenu"]['module'][$strModulo]["group"])) {//drawDebug($config["admmenu"]['module'][$strModulo]["group"]);
        $arrLinks = array();
        foreach ($config["admmenu"]['module'][$strModulo] as $arrLinks["key"] => $arrLinks["value"]) {
            $arrLinksTMP = array();
            foreach ($arrLinks["value"] as $arrLinksTMP["key"] => $arrLinksTMP["value"]) {
                $arrLinksTMP2 = array();
                foreach ($arrLinksTMP["value"]["elements"] as $arrLinksTMP2["key"] => $arrLinksTMP2["value"]) {
					if ($arrLinksTMP2["value"]["name"] == $page_name) {
						if ($boolIncludeTabInfo) {
							$strPath .= $config["admmenu"][$page_name]['module'] . " &raquo " . $arrLinksTMP["value"]["name"] . " &raquo <span onMouseOut='this.style.color=\"#666\"' onMouseOver='this.style.color=\"#111\"'  onclick='document.location.href=\"{$config["admmenu"][$page_name]['file']}\"' style='cursor:pointer'>" . $arrLinksTMP2["value"]["name"] . "</span>";
						}
						else {
							$strPath .= "<span onMouseOut='this.style.color=\"#666\"' onMouseOver='this.style.color=\"#111\"' onclick='document.location.href=\"admin_menu.php?module={$config["admmenu"][$page_name]['module']}\"' style='cursor:pointer;'>" . $config["admmenu"][$page_name]['module'] . "</span> &raquo " . $arrLinksTMP["value"]["name"] . " &raquo <span onMouseOut='this.style.color=\"#666\"' onMouseOver='this.style.color=\"#111\"'  onclick='document.location.href=\"{$config["admmenu"][$page_name]['file']}\"' style='cursor:pointer'>" . $arrLinksTMP2["value"]["name"] . "</span>";
						}
					}
				}
			}
		}
	}
	else {
		$strPath .= $strModulo . " / " . $page_name;
	}

	if (!$boolReturn) {
		if (!empty($strDrawFunction)) {
			$strDrawFunction($strPath);
		}
		else {
			?>
			<table align="center" width="100%" cellspacing="0" cellpadding="0" height="28px" style="vertical-align: middle;">
				<tr><td height="1px"></td></tr>
				<tr>
					<td width="2px"></td>
					<td align="center" style="height: 26px; color: #666; font-family: Verdana; font-size:11px; font-weight: normal; text-align: left; font-style: normal; padding: 5px; border: 1px solid #CCC;">
						<?php print $strPath ?>
					</td>
					<td width="2px"></td>
				</tr>
				<tr><td height="1px"></td></tr>
			</table>
			<?php
		}
	}
	else {
		return $strPath;
	}
}

function core_sortLinksInTabs($boolIncludePublic = false){
	global $cfg, $lang, $config;

	$strFunction = "core_sortLinksInTabs_ReSort_{$cfg["core"]["theme"]}";
	if (function_exists($strFunction)) {
		$strFunction();
	}

	$config["modulesInfo"]["ids"] = array_flip($config["modulesInfo"]["titles"]);
	$config["modulesInfo"]["ids"]["Site Admin"] = "core";
    
	$arrTabs = array();

	// Menu normal
	reset($config["menu"]);
    $arrItem = array();
    foreach ($config["menu"] as $arrItem["key"] => $arrItem["value"]) {
		if (isset($arrItem["value"]["moduleID"])) {
			// Pertenece a un modulo
			if (!check_module($arrItem["value"]["moduleID"], false, $arrItem["value"]["type"])) {
				continue;
			}
			else {
				if (isset($arrItem["value"]["type"]) && $arrItem["value"]["type"] != "A") {
					if ($arrItem["value"]["type"] == "L") {
						if (!$_SESSION["wt"]["logged"])
							continue;
					}
					else if ($arrItem["value"]["type"] == "N") {
						if ($_SESSION["wt"]["logged"])
							continue;
					}
					else {
						continue;
					}
				}

				reset($config["modulesInfo"]["groups"][$arrItem["value"]["moduleID"]]);
                $arrGroup = array();
                foreach ($config["modulesInfo"]["groups"][$arrItem["value"]["moduleID"]] as $arrGroup["key"] => $arrGroup["value"]) {
					if (!isset($arrTabs[$arrGroup["value"]])) {
						$arrTabs[$arrGroup["value"]] = array();
						$arrTabs[$arrGroup["value"]]["title"] = $lang["MENU_GROUP_{$arrGroup["value"]}"];
						$arrTabs[$arrGroup["value"]]["groups"] = array();
					}

					$strGroupTitle = $config["modulesInfo"]["titles"][$arrItem["value"]["moduleID"]];
					if ($strGroupTitle == "Site Admin")
						$strGroupTitle = $lang["SITE_CONFIG"];

					if (!isset($arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle])) {
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle] = array();
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["id"] = $arrItem["value"]["moduleID"];
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["title"] = $strGroupTitle;
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"] = array();
					}

					if (!isset($arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""])) {
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""] = array();
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""]["title"] = "";
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""]["links"] = array();
					}

					$arrTMP = array();
					$arrTMP["title"] = $arrItem["value"]["title"];
					$arrTMP["link"] = $arrItem["value"]["file"];

					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""]["links"][] = $arrTMP;
				}
				reset($config["modulesInfo"]["groups"][$arrItem["value"]["moduleID"]]);
			}
		}
		else {
			// Publico
			if (isset($arrItem["value"]["type"]) && $arrItem["value"]["type"] != "A") {
				if ($arrItem["value"]["type"] == "L") {
					if (!$_SESSION["wt"]["logged"])
						continue;
				}
				else if ($arrItem["value"]["type"] == "N") {
					if ($_SESSION["wt"]["logged"])
						continue;
				}
				else {
					continue;
				}
			}

			$strCoreTab = "WS";
			if (isset($config["modulesInfo"]["groups"]["core"])) {
				$strCurrent = current($config["modulesInfo"]["groups"]["core"]);
				reset($config["modulesInfo"]["groups"]["core"]);
				$strCoreTab = $strCurrent;
			}

			$strGroup = (isset($arrItem["value"]["groupID"])) ? $arrItem["value"]["groupID"] : $strCoreTab;

			if (!isset($arrTabs[$strGroup])) {
				$arrTabs[$strGroup] = array();
				$arrTabs[$strGroup]["title"] = $lang["MENU_GROUP_{$strGroup}"];
				$arrTabs[$strGroup]["groups"] = array();
			}
			if (!isset($arrTabs[$strGroup]["groups"][""])) {
				$arrTabs[$strGroup]["groups"][""] = array();
				$arrTabs[$strGroup]["groups"][""]["id"] = "";
				$arrTabs[$strGroup]["groups"][""]["title"] = $lang["MENU_MODULELESS"];
				$arrTabs[$strGroup]["groups"][""]["divisions"] = array();
			}
			if (!isset($arrTabs[$strGroup]["groups"][""]["divisions"][""])) {
				$arrTabs[$strGroup]["groups"][""]["divisions"][""] = array();
				$arrTabs[$strGroup]["groups"][""]["divisions"][""]["title"] = "";
				$arrTabs[$strGroup]["groups"][""]["divisions"][""]["links"] = array();
			}

			$arrTMP = array();
			$arrTMP["title"] = $arrItem["value"]["title"];
			$arrTMP["link"] = $arrItem["value"]["file"];

			$arrTabs[$strGroup]["groups"][""]["divisions"][""]["links"][] = $arrTMP;
		}
	}

	// Menu administrativo
	reset($config["admmenu"]);

	// Primero reviso los que NO tienen grupos definidos
    $arrItem = array();
    foreach ($config["admmenu"] as $arrItem["key"] => $arrItem["value"]) {
		if ($arrItem["key"] == "module")
			continue;
		if (!check_user_class($arrItem["value"]["class"]))
			continue;

		if (!isset($config["admmenu"]["module"][$arrItem["value"]["module"]])) {
			$strModuleID = (isset($config["modulesInfo"]["ids"][$arrItem["value"]["module"]])) ? $config["modulesInfo"]["ids"][$arrItem["value"]["module"]] : "";
			if (!check_module($strModuleID, false, "", false))
				continue;

			reset($config["modulesInfo"]["groups"][$strModuleID]);
            $arrGroup = array();
            foreach ($config["modulesInfo"]["groups"][$strModuleID] as $arrGroup["key"] => $arrGroup["value"]) {
				if (!isset($arrTabs[$arrGroup["value"]])) {
					$arrTabs[$arrGroup["value"]] = array();
					$arrTabs[$arrGroup["value"]]["title"] = $lang["MENU_GROUP_{$arrGroup["value"]}"];
					$arrTabs[$arrGroup["value"]]["groups"] = array();
				}

				$strGroupTitle = $arrItem["value"]["module"];
				if ($strGroupTitle == "Site Admin")
					$strGroupTitle = $lang["SITE_CONFIG"];

				if (!isset($arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle])) {
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle] = array();
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["id"] = $strModuleID;
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["title"] = $strGroupTitle;
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"] = array();
				}

				if (!isset($arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""])) {
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""] = array();
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""]["title"] = "";
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""]["links"] = array();
				}

				$arrTMP = array();
				$arrTMP["title"] = $arrItem["key"];
				$arrTMP["link"] = $arrItem["value"]["file"];

				$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][""]["links"][] = $arrTMP;
			}
		}
		else {
			continue;
		}
	}

	// Luego reviso los que SI tienen grupos definidos
	if (isset($config["admmenu"]["module"]) && is_array($config["admmenu"]["module"])) {
		reset($config["admmenu"]["module"]);

        $arrItem = array();
        foreach ($config["admmenu"]["module"] as $arrItem["key"] => $arrItem["value"]) {
			/*
			  20081104 AG: esto es porque hay links que se definen en el theme y si no estoy jalando ese modulo da error.
			  Por ejemplo, en el csc, el theme define unos links para "Usuarios y Familias" pero al consultar cualquier modulo
			  que no sea el de groups, da un error pues no se ha definido el modulesInfo de ese modulo.
			 */
			if (!isset($config["modulesInfo"]["ids"][$arrItem["key"]]))
				continue;

			$strModuleID = $config["modulesInfo"]["ids"][$arrItem["key"]];

			if (!check_module($strModuleID, false, "", false))
				continue;

			reset($config["modulesInfo"]["groups"][$strModuleID]);
            $arrGroup = array();
            foreach ($config["modulesInfo"]["groups"][$strModuleID] as $arrGroup["key"] => $arrGroup["value"]) {
				if (!isset($arrTabs[$arrGroup["value"]])) {
					$arrTabs[$arrGroup["value"]] = array();
					$arrTabs[$arrGroup["value"]]["title"] = $lang["MENU_GROUP_{$arrGroup["value"]}"];
					$arrTabs[$arrGroup["value"]]["groups"] = array();
				}

				$strGroupTitle = $arrItem["key"];
				if ($strGroupTitle == "Site Admin")
					$strGroupTitle = $lang["SITE_CONFIG"];

				if (!isset($arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle])) {
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle] = array();
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["id"] = $strModuleID;
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["title"] = $strGroupTitle;
					$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"] = array();
				}

				reset($arrItem["value"]["group"]);
                $arrDivision = array();
                foreach ($arrItem["value"]["group"] as $arrDivision["key"] => $arrDivision["value"]) {
					if (!isset($arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][$arrDivision["value"]["name"]])) {
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][$arrDivision["value"]["name"]] = array();
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][$arrDivision["value"]["name"]]["title"] = $arrDivision["value"]["name"];
						$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][$arrDivision["value"]["name"]]["links"] = array();
					}

					if (isset($arrDivision["value"]["elements"]) && is_array($arrDivision["value"]["elements"])) {
						reset($arrDivision["value"]["elements"]);
						$arrLink = array();
                        foreach ($arrDivision["value"]["elements"] as $arrLink["key"] => $arrLink["value"]) {
							if (!check_user_class($arrLink["value"]["class"])) {
								continue;
                            }

							$arrTMP = array();
							$arrTMP["title"] = $arrLink["value"]["name"];
							$arrTMP["link"] = $arrLink["value"]["file"];

							$arrTabs[$arrGroup["value"]]["groups"][$strGroupTitle]["divisions"][$arrDivision["value"]["name"]]["links"][] = $arrTMP;
						}
					}
				}
			}
		}
	}

	if (!$boolIncludePublic && isset($arrTabs["public"]))
		unset($arrTabs["public"]);
        
	//*
	ksort($arrTabs);
    $arrTab = array();
    foreach ($arrTabs as $arrTab["key"] => $arrTab["value"]) {
		$intTabLinks = 0;
		ksort($arrTabs[$arrTab["key"]]["groups"]);
        $arrGroup = array();
        foreach ($arrTabs[$arrTab["key"]]["groups"] as $arrGroup["key"] => $arrGroup["value"]) {
			$intGroupLinks = 0;
			$intGroupRows = 0;
            $arrDivision = array();
            foreach ($arrGroup["value"]["divisions"] as $arrDivision["key"] => $arrDivision["value"]) {
				$intDivLinks = count($arrDivision["value"]["links"]);
				if ($intDivLinks == 0) {
					unset($arrTabs[$arrTab["key"]]["groups"][$arrGroup["key"]]["divisions"][$arrDivision["key"]]);
				}
				else {
					$arrTabs[$arrTab["key"]]["groups"][$arrGroup["key"]]["divisions"][$arrDivision["key"]]["intLinks"] = $intDivLinks;
					$arrTabs[$arrTab["key"]]["groups"][$arrGroup["key"]]["divisions"][$arrDivision["key"]]["intRows"] = $intDivLinks;
					$intGroupLinks += $intDivLinks;
					$intGroupRows += ( $intDivLinks + ((empty($arrDivision["value"]["title"])) ? 1 : 2));
				}
			}
			$intDivs = count($arrTabs[$arrTab["key"]]["groups"][$arrGroup["key"]]["divisions"]);
			if ($intDivs == 0) {
				unset($arrTabs[$arrTab["key"]]["groups"][$arrGroup["key"]]);
			}
			else {
				if ($intDivs == 1)
					$intGroupRows = $intGroupLinks;
				$arrTabs[$arrTab["key"]]["groups"][$arrGroup["key"]]["intLinks"] = $intGroupLinks;
				$arrTabs[$arrTab["key"]]["groups"][$arrGroup["key"]]["intRows"] = $intGroupRows;
				$intTabLinks += $intGroupLinks;
			}
		}
		if (count($arrTabs[$arrTab["key"]]["groups"]) == 0) {
			unset($arrTabs[$arrTab["key"]]);
		}
		else {
			$arrTabs[$arrTab["key"]]["intLinks"] = $intTabLinks;
			$arrTabs[$arrTab["key"]]["intRows"] = $intTabLinks;
		}
	}
	reset($arrTabs);
    
    /*Necesitaban una forma de ordenar los grupos en el menu moderno*/
    if(isset($config["modulesInfo"]["menu_groups_order"]) && !empty($config["modulesInfo"]["menu_groups_order"])){
        $arrTMP = array();
        
        foreach($config["modulesInfo"]["menu_groups_order"] as $strTab => $arrGroup){
            if(isset($arrTabs[$strTab])){
                foreach($arrGroup as $strTitulo){
                    if(isset($arrTabs[$strTab]["groups"][$strTitulo])){
                        if(!isset($arrTMP[$strTab]))$arrTMP[$strTab] = array();
                        $arrTMP[$strTab][$strTitulo] = array();
                        $arrTMP[$strTab][$strTitulo] = $arrTabs[$strTab]["groups"][$strTitulo];
                        unset($arrTabs[$strTab]["groups"][$strTitulo]);
                    }
                }
            }
        }
        
        if(!empty($arrTMP)){
            foreach($arrTMP as $strTab => $arrGroups){
                foreach($arrGroups as $strTitulo => $arrGroup){
                    $arrTabs[$strTab]["groups"][$strTitulo] = array();
                    $arrTabs[$strTab]["groups"][$strTitulo] = $arrGroup;
                }
                reset($arrTabs[$strTab]["groups"]);
            }
            unset($arrTMP);
        }
    }
    reset($arrTabs);
	//*/

	return $arrTabs;
}

function draw_tabMenu($strMenuID, $intWidth, $boolAddHome = true) {
	global $cfg, $config, $lang;

	$strContainer = "{$strMenuID}_Container";

	$arrTabs = core_sortLinksInTabs(true);
//    print "<div style='color:white;'>";
//    drawDebug($arrTabs);
//    print "</div>";

	$boolIsChrome = $_SESSION["wt"]["browser"]["detail"]["boolIsChrome"];
	$boolIsSafari = $_SESSION["wt"]["browser"]["detail"]["boolIsSafari"];
	$boolIsIE = $_SESSION["wt"]["browser"]["detail"]["boolIsMSIE"];
	$intIEVer = $_SESSION["wt"]["browser"]["detail"]["IEVer"];

	// Variables
	$arrVars = array();
	$arrVars["boolLoadLinksOnDemand"] = false;

	$arrVars["boolOverContents"] = false;

	$arrVars["boolOpenLastTab"] = false;

	$arrVars["strTabsAlign"] = "right";

	$arrVars["strTabsFillColor"] = "#555555";
	$arrVars["strSelectedTabFillColor"] = "#666666";
	$arrVars["strOverTabFillColor"] = "#777777";

	$arrVars["strContentsFillColor"] = "#666666";

	$arrVars["strStrokeColor"] = "black";
	$arrVars["intStrokeWeight"] = 1;
	$arrVars["intAlpha"] = 100;
	$arrVars["intCurveRadius"] = 1;

	$arrVars["strTrianguloUpFill"] = "white";
	$arrVars["strTriangulosDownFill"] = "black";
	$arrVars["strTrianguloScrollFill"] = "#B4B4B4";

	$arrVars["strSepLineColor"] = "black";
	$arrVars["intSepLineAlpha"] = 30;

	$arrVars["strPicoWindowFillColor"] = "black";
	$arrVars["intPicoAlpha"] = 80;
	$arrVars["intPicoCurveRadius"] = 5;
	$arrVars["boolPicoSquareCorners"] = false;

	$arrVars["intMaxLinksTop"] = 5;
	$arrVars["intMaxRowsPico"] = 17;

	// Clases
	$arrVars["strGroupTitleClass"] = "tabMenu_GroupTitle";
	$arrVars["strGroupTitleClass2"] = "tabMenu_GroupTitle";
	$arrVars["strLinkCatClass"] = "tabMenu_LinkCat";
	$arrVars["strLinkClass"] = "tabMenu_Link";

	$arrVars["strTabTitleClass"] = "tabMenu_TabTitle";
	//$arrVars["strTabTitleClass2"] = "tabMenu_TabTitles";

	$arrVars["strMenuListIco"] = strGetCoreImageWithPath("menuListIco.png");
	//Pendiente 305 Nuevo correlativo
	$arrVars["boolShowLinksUsage"] = true;
	$arrVars["boolCompactMode"] = false;

	$strFunction = "theme_tabMenuVarsOverride_{$cfg["core"]["theme"]}";
	if (function_exists($strFunction)) {
		$strFunction($arrVars);
	}

	$strLastSelected = "";
	$arrLastScroll = "";
	if (isset($cfg["tabMenu"]) && $_SESSION["wt"]["logged"]) {
		if (isset($cfg["tabMenu"]["lastSelectedTab"]))
			$strLastSelected = $cfg["tabMenu"]["lastSelectedTab"];
		if (isset($cfg["tabMenu"]["lastScroll"]))
			$arrLastScroll = $cfg["tabMenu"]["lastScroll"];

		if (isset($cfg["tabMenu"]["boolLoadLinksOnDemand"]))
			$arrVars["boolLoadLinksOnDemand"] = ($cfg["tabMenu"]["boolLoadLinksOnDemand"] == "true");
		if (isset($cfg["tabMenu"]["boolMakeContainerGrow"]))
			$arrVars["boolOverContents"] = ($cfg["tabMenu"]["boolMakeContainerGrow"] == "false");
		if (isset($cfg["tabMenu"]["boolOpenLastTab"]))
			$arrVars["boolOpenLastTab"] = ($cfg["tabMenu"]["boolOpenLastTab"] == "true");

		if (isset($cfg["tabMenu"]["intAlpha"]))
			$arrVars["intAlpha"] = $cfg["tabMenu"]["intAlpha"];
		if (isset($cfg["tabMenu"]["intPicoAlpha"]))
			$arrVars["intPicoAlpha"] = $cfg["tabMenu"]["intPicoAlpha"];

		if (isset($cfg["tabMenu"]["intMaxLinksTop"]))
			$arrVars["intMaxLinksTop"] = $cfg["tabMenu"]["intMaxLinksTop"];

		if ($arrVars["boolOverContents"])
			$arrVars["boolOpenLastTab"] = false;
	}

	if (!file_exists($arrVars["strMenuListIco"]))
		$arrVars["strMenuListIco"] = "";
	?>
	<div id="<?php print $strContainer; ?>" style="position:relative; left:0; top:0; width:100%; height:100%; z-index:15;"></div>
	<script language="Javascript" type="text/javascript">
		var objTabMenu = new objHMLTabs("<?php print $strMenuID; ?>", "<?php print $strContainer; ?>", <?php print $intWidth; ?>);
		var objAddedTab = false;
		var objAddedGroup = false;

		objTabMenu.boolLoadLinksOnDemand = <?php print ($arrVars["boolLoadLinksOnDemand"]) ? "true" : "false"; ?>;;
		objTabMenu.boolMakeContainerGrow = <?php print ($arrVars["boolOverContents"]) ? "false" : "true"; ?>;
		objTabMenu.boolOpenLastTab = <?php print ($arrVars["boolOpenLastTab"]) ? "true" : "false"; ?>;

		objTabMenu.strTabsAlign = "<?php print $arrVars["strTabsAlign"]; ?>";

		objTabMenu.strTabsFillColor = "<?php print $arrVars["strTabsFillColor"]; ?>";
		objTabMenu.strSelectedTabFillColor = "<?php print $arrVars["strSelectedTabFillColor"]; ?>";
		objTabMenu.strOverTabFillColor = "<?php print $arrVars["strOverTabFillColor"]; ?>";

		objTabMenu.strContentsFillColor = "<?php print $arrVars["strContentsFillColor"]; ?>";

		objTabMenu.strStrokeColor = "<?php print $arrVars["strStrokeColor"]; ?>";
		objTabMenu.intStrokeWeight = <?php print $arrVars["intStrokeWeight"]; ?>;
		objTabMenu.intAlpha = <?php print $arrVars["intAlpha"]; ?>;
		objTabMenu.intCurveRadius = <?php print $arrVars["intCurveRadius"]; ?>;

		objTabMenu.strTrianguloUpFill = "<?php print $arrVars["strTrianguloUpFill"]; ?>";
		objTabMenu.strTriangulosDownFill = "<?php print $arrVars["strTriangulosDownFill"]; ?>";
		objTabMenu.strTrianguloScrollFill = "<?php print $arrVars["strTrianguloScrollFill"]; ?>";

		objTabMenu.strSepLineColor = "<?php print $arrVars["strSepLineColor"]; ?>";
		objTabMenu.intSepLineAlpha = <?php print $arrVars["intSepLineAlpha"]; ?>;

		objTabMenu.strPicoWindowFillColor = "<?php print $arrVars["strPicoWindowFillColor"]; ?>";
		objTabMenu.intPicoAlpha = <?php print $arrVars["intPicoAlpha"]; ?>;
		objTabMenu.intPicoCurveRadius = <?php print $arrVars["intPicoCurveRadius"]; ?>;
		objTabMenu.boolPicoSquareCorners = <?php print ($arrVars["boolPicoSquareCorners"]) ? "true" : "false"; ?>;

		objTabMenu.intMaxLinksTop = <?php print $arrVars["intMaxLinksTop"]; ?>;
		objTabMenu.intMaxRowsPico = <?php print $arrVars["intMaxRowsPico"]; ?>;

		objTabMenu.strGroupTitleClass = "<?php print $arrVars["strGroupTitleClass"]; ?>";
		//ludbyn
		//objTabMenu.strGroupTitleClass2 = "<?php print $arrVars["strGroupTitleClass"]; ?>";
		objTabMenu.strLinkCatClass = "<?php print $arrVars["strLinkCatClass"]; ?>";
		objTabMenu.strLinkClass = "<?php print $arrVars["strLinkClass"]; ?>";

		objTabMenu.strEmptyStatsAreaText = "<?php print $lang["MENU_EMPTYAREATEXT"]; ?>";

		objTabMenu.boolShowLinksUsage = <?php print ($arrVars["boolShowLinksUsage"]) ? "true" : "false"; ?>;
		objTabMenu.boolCompactMode = <?php print ($arrVars["boolCompactMode"]) ? "true" : "false"; ?>;

		<?php
		$strTabTitle = "<table cellspacing='0' cellpadding='2' border='0'>";
		$strTabTitle .= "<tr>";
		$strTabTitle .= "<td width='1' height='1' align='center' valign='middle' class='{$arrVars["strTabTitleClass"]}'><img width='16' height='16' src='" . strGetCoreImageWithPath("report.png") . "'></td>";
		$strTabTitle .= "</tr>";
		$strTabTitle .= "</table>";
		?>
		objAddedTab = objTabMenu.addSearchTab("SEARCH", "<?php print $strTabTitle; ?>", "<?php print $lang["MENU_SEARCH"]; ?>");
		<?php
		if (!empty($arrVars["strMenuListIco"])) {
			?>
			objTabMenu.boolLinkAsLI = true;
			objTabMenu.strLinksBullet = "<?php print $arrVars["strMenuListIco"]; ?>";
			<?php
		}
		if ($boolAddHome) {
			$strTabTitle = "<table cellspacing='0' cellpadding='2' border='0'>";
			$strTabTitle .= "<tr>";
			$strTabTitle .= "<td width='1' height='1' align='center' valign='middle'><img src='" . strGetCoreImageWithPath("ico20/tab_HOME.png") . "'></td>";
			$strTabTitle .= "</tr>";
			$strTabTitle .= "</table>";
			?>
			objAddedTab = objTabMenu.addTab("HOME", "<?php print $strTabTitle; ?>", "", "<?php print $lang["MENU_HOME"]; ?>");
			<?php
		}

		$i = 0;
        $arrTab = array();
        foreach ($arrTabs as $arrTab["key"] => $arrTab["value"]) {
			$i++;
			$strTabTitle = "<table cellspacing='0' cellpadding='2' border='0'>";
			$strTabTitle .= "<tr>";
			$strTabTitle .= "<td width='1' height='1' align='center' valign='middle'><img src='" . strGetCoreImageWithPath("ico20/tab_{$arrTab["key"]}.png") . "'></td>";
			$strTabTitle .= "<td valign='middle' class='{$arrVars["strTabTitleClass"]}' id='Menu_{$i}' onmouseover='changeClassOver(this, {$i});' onmouseout='changeClassOut(this);' nowrap>" . htmlSafePrint($arrTab["value"]["title"], false) . "</td>";
			$strTabTitle .= "</tr>";
			$strTabTitle .= "</table>";
			$intTamanio = count($arrTab["value"]["groups"]);
			?>
			objAddedTab = objTabMenu.addTab("<?php print $arrTab["key"]; ?>", "<?php print $strTabTitle; ?>", "", "<?php htmlSafePrint($arrTab["value"]["title"]); ?>","<?php print $intTamanio;?>");
			<?php
			if(!$arrVars["boolCompactMode"]){
				if ($strLastSelected == $arrTab["key"]) {
					?>
					objTabMenu.intDefaultTab = objAddedTab.intIdInParent;
					<?php
				}
				if (isset($arrLastScroll[$arrTab["key"]])) {
					?>
					objAddedTab.intInitScroll = <?php print $arrLastScroll[$arrTab["key"]]; ?>;
					<?php
				}
			}
			ksort($arrTab["value"]);
            $arrGroup = array();
            foreach ($arrTab["value"]["groups"] as $arrGroup["key"] => $arrGroup["value"]) {
				$arrGroup["value"]["title"] = str_replace("_", "", $arrGroup["value"]["title"]);
				?>
				objAddedGroup = objAddedTab.addGroup("<?php print($arrGroup["value"]["id"]); ?>", "<?php htmlSafePrint($arrGroup["value"]["title"]); ?>", "<?php htmlSafePrint($arrGroup["value"]["intLinks"]); ?>", "<?php htmlSafePrint($arrGroup["value"]["intRows"]); ?>","<?php print $intTamanio;?>");
				<?php
				// 20090213 AG: Cambios de aqui para abajo afectan tambien hmlMenu.php... debe ser todo muy parecido.
				if (!$arrVars["boolLoadLinksOnDemand"]) {
					if ($arrGroup["value"]["intRows"] <= $arrVars["intMaxLinksTop"]) {
						if(!$arrVars["boolCompactMode"]){
							?>
							objAddedGroup.arrLinks = new Array();
							<?php
							$intDivCounter = 0;
							$intRowCounter = 0;
							reset($arrGroup["value"]["divisions"]);
                            $arrDivision = array();
                            foreach ($arrGroup["value"]["divisions"] as $arrDivision["key"] => $arrDivision["value"]) {
                                if ($intRowCounter > $arrVars["intMaxLinksTop"]) break;
								?>
								objAddedGroup.arrLinks[<?php print $intDivCounter; ?>] = new Array();
								objAddedGroup.arrLinks[<?php print $intDivCounter; ?>]["title"] = "<?php print addslashes($arrDivision["key"]); ?>";
								objAddedGroup.arrLinks[<?php print $intDivCounter; ?>]["link"] = new Array();
								<?php
								if (!empty($arrDivision["key"])) {
									$intRowCounter++;
                                }
								$intLinkCounter = 0;
								reset($arrDivision["value"]["links"]);
                                $arrLink = array();
                                foreach ($arrDivision["value"]["links"] as $arrLink["key"] => $arrLink["value"]) {
                                    if ($intRowCounter > $arrVars["intMaxLinksTop"]) break;
									?>
									objAddedGroup.arrLinks[<?php print $intDivCounter; ?>]["link"][<?php print $intLinkCounter; ?>] = new Array();
									objAddedGroup.arrLinks[<?php print $intDivCounter; ?>]["link"][<?php print $intLinkCounter; ?>]["title"] = "<?php print addslashes($arrLink["value"]["title"]); ?>";
									objAddedGroup.arrLinks[<?php print $intDivCounter; ?>]["link"][<?php print $intLinkCounter; ?>]["link"] = "<?php print addslashes($arrLink["value"]["link"]); ?>";
									<?php
									$intLinkCounter++;
									$intRowCounter++;
								}
								$intDivCounter++;
							}
						}
					}
					else {
						if($arrVars["boolShowLinksUsage"]){
							$strQuery = "SELECT HLU.menuTitle, COUNT(HLUD.id) AS conteo, MAX(HLUD.fechaClick) AS lastUsage
										 FROM wt_hmlmenu_links_usage AS HLU,
												  wt_hmlmenu_links_usage_detail AS HLUD
										 WHERE HLUD.id = HLU.id AND
												   HLU.userid = {$_SESSION["wt"]["uid"]} AND
												   HLU.strModulo = '{$arrGroup["value"]["id"]}'
										 GROUP BY HLU.id
										 ORDER BY conteo DESC, lastUsage DESC, HLU.menuTitle
										 LIMIT 0, {$arrVars["intMaxLinksTop"]}";
							$qTMP = db_query($strQuery);
							if (db_num_rows($qTMP)) {
								?>
								objAddedGroup.arrLinks = new Array();
								objAddedGroup.arrLinks[0] = new Array();
								objAddedGroup.arrLinks[0]["title"] = "";
								objAddedGroup.arrLinks[0]["link"] = new Array();
								<?php
								$intLinkCounter = 0;
								while ($rTMP = db_fetch_assoc($qTMP)) {
									if (isset($config["admmenu"][$rTMP["menuTitle"]])) {
										if (check_user_class($config["admmenu"][$rTMP["menuTitle"]]["class"])) {
											?>
											objAddedGroup.arrLinks[0]["link"][<?php print $intLinkCounter; ?>] = new Array();
											objAddedGroup.arrLinks[0]["link"][<?php print $intLinkCounter; ?>]["title"] = "<?php print addslashes($rTMP["menuTitle"]); ?>";
											objAddedGroup.arrLinks[0]["link"][<?php print $intLinkCounter; ?>]["link"] = "<?php print addslashes($config["admmenu"][$rTMP["menuTitle"]]["file"]); ?>";
											<?php
											$intLinkCounter++;
										}
									}
									else {
										$boolFound = false;
										reset($config["menu"]);
                                        $arrItem = array();
                                        foreach ($config["menu"] as $arrItem["key"] => $arrItem["value"]) {
                                            if ($boolFound) break;
											if ($arrItem["value"]["type"] == "N")
												continue;

											if ($arrItem["value"]["title"] == $rTMP["menuTitle"]) {
												?>
												objAddedGroup.arrLinks[0]["link"][<?php print $intLinkCounter; ?>] = new Array();
												objAddedGroup.arrLinks[0]["link"][<?php print $intLinkCounter; ?>]["title"] = "<?php print addslashes($rTMP["menuTitle"]); ?>";
												objAddedGroup.arrLinks[0]["link"][<?php print $intLinkCounter; ?>]["link"] = "<?php print addslashes($arrItem["value"]["file"]); ?>";
												<?php
												$intLinkCounter++;
												$boolFound = true;
											}
										}
										reset($config["menu"]);
									}
								}
							}
							db_free_result($qTMP);
						}
					}
				}
			}
		}
		$strTabTitle = "<table cellspacing='0' cellpadding='2' border='0'>";
		$strTabTitle .= "<tr>";
		$strTabTitle .= "<td width='1' height='1' align='center' valign='middle'><img src='" . strGetCoreImageWithPath("ico20/tab_CONFIG.png") . "'></td>";
		$strTabTitle .= "</tr>";
		$strTabTitle .= "</table>";
		?>
		objAddedTab = objTabMenu.addOptionsTab("OPTIONS", "<?php print $strTabTitle; ?>", "<?php print $lang["MENU_OPTIONS"]; ?>");

		objAddedTab.strOptionsText = "<?php print $lang["MENU_OPTIONS"]; ?>";
		objAddedTab.strSaveButtonText = "<?php print $lang["MENU_OPTIONS_SAVE"]; ?>";
		objAddedTab.strResetButtonText = "<?php print $lang["MENU_OPTIONS_RESET"]; ?>";

		objAddedTab.strCatBehaviourText = "<?php print $lang["MENU_OPTIONS_CATBEHAVIOUR"]; ?>";
		objAddedTab.strCatBehaviour_OpenLastTabText = "<?php print $lang["MENU_OPTIONS_CATBEHAVIOUR_OPENLASTTAB"]; ?>";
		objAddedTab.strCatBehaviour_MakeContainerGrowText = "<?php print $lang["MENU_OPTIONS_CATBEHAVIOUR_MAKECONTAINERGROW"]; ?>";

		objAddedTab.strCatStylesText = "<?php print $lang["MENU_OPTIONS_STYLE"]; ?>";
		objAddedTab.strCatStyles_intAlphaText = "<?php print $lang["MENU_OPTIONS_STYLE_ALPHA"]; ?>";
		objAddedTab.strCatStyles_intPicoAlphaText = "<?php print $lang["MENU_OPTIONS_STYLE_PICOALPHA"]; ?>";

		objAddedTab.strCatPerformanceText = "<?php print $lang["MENU_OPTIONS_CATPERFORMANCE"]; ?>";
		objAddedTab.strCatPerformanceText_preLoadXML = "<?php print $lang["MENU_OPTIONS_CATPERFORMANCE_PRELOADXML"]; ?>";
		objAddedTab.strCatPerformanceText_useAJAX = "<?php print $lang["MENU_OPTIONS_CATPERFORMANCE_USEAJAXFORLINKS"]; ?>";
		objAddedTab.strCatPerformanceText_LinksOnTop = "<?php print $lang["MENU_OPTIONS_CATPERFORMANCE_LINKSONTOP"]; ?>";

		function drawMenu() {
			objTabMenu.draw();
			<?php
			if ($boolAddHome) {
				?>
				objTabMenu.arrTabs[objTabMenu.locateTabByID("HOME")].objTab.objContents.onclick = function () {
					document.location.href = "index.php";
				}
				<?php
			}
			?>
		}
		function Datos(valor) {
			getDocumentLayer("busqueda").value=valor;
		}
		//ludbyn
		/*onmouseover='changeClassOver( this )' onmouseout = 'changeClassOut(this)' */
		function changeClassOver( obj, i ){

			var count = '<?php print $i; ?>';
			//obj.className = "tabMenu_TabTitle2";
			for (j=1; j <= count; j++) {
				if (j != i){
					//obj2 =  getDocumentLayer("Menu_"+j);
					//obj2.className = "tabMenu_TabTitle";
				}
			}

		}
		function changeClassOut( obj, i ){
			//obj.className = "tabMenu_TabTitle2";
		}

		<?php
		if ($boolIsIE) {
			?>
			addLoadListener(drawMenu);
			<?php
		}
		else {
			?>
			drawMenu();
			<?php
		}
		?>
	</script>
	<?php
}

function seeAlso_encodeGet($arrGet) {
	$strGet = (is_array($arrGet) && count($arrGet))?http_build_query($arrGet):"";
	$strGet = str_replace(array("&","="), array("|","~"), $strGet);
    return $strGet;
}

function seeAlso_decodeGet($strGet) {
	$strGet = str_replace(array("|","~"),array("&","="), $strGet);
    return $strGet;
}