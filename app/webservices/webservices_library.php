    <?php
    global $cfg;
include_once("webservices/lang/msg_" . check_lang($cfg["core"]["lang"]) . ".php" );
/**
*
* webservices_library.php
* Tiene librerias que sirven para controlar las llamadas e inclusiones de las clases fundamentales para los webservices.
*
* DEPENDE DE main_functions.php y asume que ya esta incluida...
*
* @author   Alejandro Gudiel <agudiel@homeland.com.gt>
* @version  $Id: webservices_library.php,v 1.0 2012/06/28 14:15 $
* @access   public
*/
//**

/**
* Obtiene la informacion de una operacion en un array.  Devuelve false si la operacion es invalida o está inactiva.
*
* @param string $strOperationCode UUID de la operacion
* @return variant false si no existe o esta inactiva, array si hay informacion.
*/
function webservice_getOperationInfo($strOperationUUID) {
	$strOperationUUID_E = db_escape($strOperationUUID);
	$strQuery = "SELECT modulo, descripcion, include_path, className, activo, publica, acceso
				 FROM wt_webservices_operations
				 WHERE activo = 'Y' AND op_uuid = '{$strOperationUUID_E}'";
	$arrInfo = sqlGetValueFromKey($strQuery);
	if ($arrInfo === false) {
		return false;
	}
	else {
		return $arrInfo;
	}
}

/**
* Funcion que recorre un array tipo arbol y construye un XML analogo.  $arrArreglo se manda por referencia para ahorrar memoria.
*
* @param XMLobject $objXML Objeto XMLNode a poblar
* @param array $arrArreglo Array con los datos a insertar, puede ser de N niveles
* @param boolean $boolMayUseAtributes si puede usar atributos o no
*/
function webservice_arrayIntoXML(&$objXML, &$arrArreglo, $boolMayUseAtributes) {
	reset($arrArreglo);
	while ($arrItem = each($arrArreglo)) {
		if (is_array($arrItem["value"])) {
			// Si es un array, lo pongo como hijo y hago recurrencia
			if (is_numeric($arrItem["key"])) {
				// Si el key es numerico, pongo el nodo como "item" y el key lo pongo como un atributo o como un nodo segun el caso
				$objItem = &$objXML->children[$objXML->addChild("item")];
				if ($boolMayUseAtributes) {
					$objItem->addAttribute("nodekey", $arrItem["key"]);
				}
				else {
					$objKey = &$objItem->children[$objItem->addChild("nodekey")];
					$objKey->setInternalText($arrItem["key"]);
				}
				webservice_arrayIntoXML($objItem, $arrArreglo[$arrItem["key"]], $boolMayUseAtributes);
			}
			else {
				$objItem = &$objXML->children[$objXML->addChild($arrItem["key"])];
				webservice_arrayIntoXML($objItem, $arrArreglo[$arrItem["key"]], $boolMayUseAtributes);
			}
		}
		else {
			// Si no es un array, lo pongo como atributo o texto segun $boolMayUseAtributes
			if ($boolMayUseAtributes) {
				$strKeyName = (is_numeric($arrItem["key"]))?"item_{$arrItem["key"]}":$arrItem["key"];

				$objXML->addAttribute($strKeyName, $arrItem["value"]);
			}
			else {
				if (is_numeric($arrItem["key"])) {
					// Si el key es numerico, pongo el nodo como "item"
					$objItem = &$objXML->children[$objXML->addChild("item")];
					$objKey = &$objItem->children[$objItem->addChild("nodekey")];
					$objKey->setInternalText($arrItem["key"]);

					$objValue = &$objItem->children[$objItem->addChild("value")];
					$objValue->setInternalText($arrItem["value"]);
				}
				else {
					$objItem = &$objXML->children[$objXML->addChild($arrItem["key"])];
					$objItem->setInternalText($arrItem["value"]);
				}
			}
		}
	}
	reset($arrArreglo);
}

/**
* Desactiva dispositivos no confirmados por el usuario en su interfaz grafica.
*
*/
function webservice_deactiveNotConfirmedDevices() {
    global $cfg, $config, $lang;
	// Esto es para que el clear solo corra una vez cada hora
	$strQuery = "SELECT COUNT(lastRun) AS conteo FROM wt_webservices_last_deactivate WHERE lastRun > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
	$intRuns = sqlGetValueFromKey($strQuery);
	if ($intRuns == 0) {
        //Si la variable esta activada, voy a buscar a todos los dispositivos de usuarios que no tengan acceso y los desactivo
        if(!empty($cfg["core"]["limit_webservice_devices"])){
            $strQuery = "UPDATE wt_webservices_devices WD 
                                    LEFT JOIN wt_webservices_devices_auth WDA ON WD.userid = WDA.userid 
                         SET WD.activo = 'N' 
                         WHERE WDA.userid IS NULL";
            db_query($strQuery);            
        }
        
        $strQuery = "UPDATE wt_webservices_devices
                     SET activo = 'N', fecha_baja = NOW()
                     WHERE confirmado = 'N' AND
                           fecha_alta < DATE_SUB(NOW(), INTERVAL 12 HOUR)";
        db_query($strQuery);

        db_query("TRUNCATE wt_webservices_last_deactivate");
        db_query("INSERT INTO wt_webservices_last_deactivate (lastRun) VALUES (NOW());");    
	}
}

$intGlobalMRID = 0; // ID del log de respuestas

/**
* Verifica si el AIM ya tiene respusta para el dispositivo.
* Si hay respuesta, la devuelve.
* Si no, inicializa el registro para guardar la respuesta del webservice a un dispositivo movil.  Tambien mantiene la tabla solo con las respuestas de los ultimos 3 días.
*
* @param integer $intDeviceID ID del dispositivo
* @param string $strDeviceAIM AIM enviado por el dispositivo
*/
function webservice_check_saved_response($intDeviceID, $strDeviceAIM, $strFormato) {
	global $cfg, $config, $intGlobalPageProcessedLogID, $intGlobalMRID;

	//Housekeeping
	$intRows = sqlGetValueFromKey("SELECT COUNT(table_name) FROM wt_catalogos_last_update WHERE table_name = 'wt_webservices_mobile_responses' AND fecha = curdate()");
    if ($intRows == 0) {
        // Si NO hay corrido hoy... lo corro
        db_query("REPLACE INTO wt_catalogos_last_update VALUES ('wt_webservices_mobile_responses', curdate(), curtime())");

        db_query("DELETE FROM wt_webservices_mobile_responses WHERE fecha < DATE_SUB(NOW(), INTERVAL 3 DAY)"); // No necesita optimize porque es InnoDB
    }

    $intDeviceID = intval($intDeviceID);
    $strDeviceAIM = db_escape($strDeviceAIM);

    // Busco si ya hay respuesta
    $arrResponse = sqlGetValueFromKey("SELECT id, status, respuesta FROM wt_webservices_mobile_responses WHERE device_id = {$intDeviceID} AND device_aim = '{$strDeviceAIM}'");
    if ($arrResponse === false) {
		db_query("INSERT INTO wt_webservices_mobile_responses
				  (userid, device_id, device_aim, process_log_id, status, fecha, hora, formato)
				  VALUES
				  ({$_SESSION["wt"]["uid"]}, {$intDeviceID}, '{$strDeviceAIM}', {$intGlobalPageProcessedLogID}, 'en_proceso', curdate(), curtime(), '{$strFormato}')");
		$intGlobalMRID = db_insert_id();

		return $intGlobalMRID;
    }
	else {
		if ($arrResponse["status"] == "terminada") {
			switch ($strFormato) {
				case "csv":
					$strContentType = getFile_contentType(".csv");
					break;
				case "xmlwa":
				case "xmlno":
				case "xmlc":
					$strContentType = getFile_contentType(".xml");
					break;
				case "json":
					$strContentType = "application/json";
                    //Esto se coloco porque en el aplicaciones moviles no reconocia la respuesta como un JSON, si no como STRING
                    $arrResponse["respuesta"] = json_decode($arrResponse["respuesta"]);
                    $arrResponse["respuesta"] = json_encode($arrResponse["respuesta"]);
					break;
				case "txt":
					$strContentType = getFile_contentType(".txt");
					break;
				case "html":
					$strContentType = getFile_contentType("html");
					break;
				case "bin":
					$strContentType = getFile_contentType();
					break;
			}

			header("Content-Type: {$strContentType}");
			print $arrResponse["respuesta"];

			$intGlobalMRID = 0;

			return "stop";
		}
		else {
			// Si no ha terminado... no devuelvo nada para que asuma que no hay respuesta y vuelva a intentar esperando a que termine el proceso anterior...
			$intGlobalMRID = 0;

			return "stop";
		}
	}
}

/**
* Guarda la respuesta a un dispositivo movil para referencia futura.
*
* @param string $strResponse Respuesta
*/
function webservice_save_response($strResponse) {
	global $cfg, $config, $intGlobalPageProcessedLogID, $intGlobalMRID;

	if ($intGlobalMRID == 0) return;

	$strResponse = db_escape($strResponse);
	db_query("UPDATE wt_webservices_mobile_responses SET respuesta = '{$strResponse}', status = 'terminada' WHERE id = {$intGlobalMRID}");
}

function webservice_getAccesos($intUserID, $strCodigoSeguridad_E,$strAppName= "",$strApiVersion = ""){
    global $cfg, $config;
        
    $arrDatosUser = array();
    $arrDatosInfo = array();
    $arrEmpresasAccess = array();

    if (check_module("empresas", true, "A")){
        empresas_OnLogIn_Function();
        $arrEmpresas = explode(",",$_SESSION["wt"]["empresas"]["empresas_access"]);
        foreach($arrEmpresas AS $key => $value){
            $arrT["value"] = $value;
            $arrT["key"] = $key;

            $arrT["value"] = intval($arrT["value"]);
            $arrTMP = array();
            if($arrT["value"] > 0){
                $sql = "SELECT * from wt_empresas WHERE active ='Y' AND cod = '{$arrT["value"]}'";
                $arrInfoEmpresa = sqlGetValueFromKey($sql);

                $arrLocalidades = empresas_getLocalidadesAccess($intUserID, $arrT["value"]);
                $arrayLocal =array();
                if($arrLocalidades){
                    foreach($arrLocalidades AS $key2 => $value2){
                        $arrQ["key"] = $key;
                        $arrQ["value"] = $value2;

                        if($arrQ["key"] != "localidades_access"){
                            $arrQT = array();
                            $arrQT["id"] = $arrQ["key"];
                            $arrQT["nombre"] = $arrQ["value"];
                            array_push($arrayLocal, $arrQT);
                        }
                        unset($arrQT);
                        unset($arrQ);
                    }
                }
                unset($arrLocalidades);

                $arrTMP["cod"] = $arrT["value"];
                $arrTMP["name"] = $arrInfoEmpresa["nombre"];
                $arrTMP["nit"] = $arrInfoEmpresa["nit"];
                $arrTMP["direccion"] = trim("{$arrInfoEmpresa["direccion"]}");
                $arrTMP["localidades"] = $arrayLocal;

                if (check_module("caja_enter", true, "A")){
                    $arrDocumentos = caja_enter_get_documentos_info($arrT["value"],'contado,credito,nc,credcont,multiforma',0,false,false,false,$intUserID," AND wt_caja_enter_formas.isTarjetaCredito = 'N' AND wt_caja_enter_formas.isChequeRechazado = 'N' AND wt_caja_enter_formas.isSaldoInicial = 'N'");
                    reset($arrDocumentos);
                    if($arrDocumentos){
                        $arrTMP["formas"] = array();
                        foreach ($arrDocumentos as $key) {
                            $arrFT = array();
                            $arrFT["forma_id"] = $key["forma_id"];
                            $arrFT["documento"] = $key["documento"];
                            $arrFT["resolucion"] = $key["NumeroResolucion"];
                            $arrFT["tipo"] = $key["ResDocumento"];
                            $arrFT["serie"] = $key["serie"];
                            $arrFT["rubros"] = array();
                            $arrFT["permitido_facturar"] = array();

                            $strQueryFor = "SELECT * FROM wt_caja_enter_formas_display
                                         WHERE    forma_id = '{$key["forma_id"]}'";
                            $qTMP2 = db_query($strQueryFor);
                            if(db_num_rows($qTMP2)){
                                while ( $rTMP2 = db_fetch_assoc($qTMP2)) {
                                    array_push($arrFT["permitido_facturar"], $rTMP2["display"]);
                                    unset($rTMP2);
                                }
                                db_free_result($qTMP2);
                            }

                            $strQuery = "SELECT * FROM wt_caja_enter_rubros WHERE forma_id = '{$key["forma_id"]}' AND empresa = '{$arrTMP["cod"]}' AND active = 'Y' AND is_descuento = 'N'";
                            $qTMP3 = db_query($strQuery);
                            if(db_num_rows($qTMP3)){
                                while($rTMP3 = db_fetch_assoc($qTMP3)){
                                    $arrRU = array();
                                    $arrRU["codigo"] = $rTMP3["codigo"];
                                    $arrRU["nombre"] = $rTMP3["name"];
                                    $arrRU["tipo"] = ($rTMP3["bien_servicio"] == "b")?"bien":"servicio";
                                    array_push($arrFT["rubros"],$arrRU);
                                    unset($rTMP3);
                                }
                                db_free_result($qTMP3);
                            }

                            if(check_module("factura_electronica") && $key["pago"] != "nc"){
                                $strQuery = "SELECT * FROM wt_factura_electronica_server WHERE forma_id = '{$key["forma_id"]}' AND empresa = '{$arrT["value"]}'";
                                $qTMP = db_query($strQuery);
                                if(db_num_rows($qTMP)){
                                    $arrFT["factura_electronica"] = array();
                                    while($rTMP = db_fetch_assoc($qTMP)){
                                        $arrTFE = array();
                                        $arrTFE["nombre_impresion"] = (isset($rTMP["nombre_establecimiento"]))?$rTMP["nombre_establecimiento"]:"";
                                        $arrTFE["direccion_impresion"] = (isset($rTMP["direccion"]))?$rTMP["direccion"]:"";
                                        $arrFT["factura_electronica"][] = $arrTFE;
                                        unset($rTMP);
                                    }
                                    db_free_result($qTMP);
                                }
                            }

                            array_push($arrTMP["formas"], $arrFT);
                            unset($arrFT);
                        }
                    }
                }
                else{
                    $strFunction = "getJsonExample";
                    if(function_exists($strFunction)){
                        $strFunction($arrTMP);
                    }
                }

                $arrTMP["extra_data"] = array();
                $strFunctionData = "getExtraDataDevice";
                if(function_exists($strFunctionData)){
                    $strFunctionData($arrTMP["extra_data"], $intUserID, $strCodigoSeguridad_E, $arrTMP,$arrT,$strApiVersion);
                }

                //Get info extra data empresa
                $strQuery = "SELECT * FROM wt_empresas_extra_fields WHERE company_id = '{$arrT["value"]}'";
                $arrInfoExtra = sqlGetValueFromKey($strQuery,true);
                $arrTMPExtra = array();
                $arrTMPExtra["phone_company"] = $arrInfoExtra["phone_company"];
                $arrTMPExtra["web_page"] = $arrInfoExtra["web_page"];
                //Datos del dispositivo (lo agrego a extradata en la respuesta)
                $strQuery = "SELECT * FROM wt_webservices_devices WHERE device_udid = '{$strCodigoSeguridad_E}'";
                $arrInfoDevice = sqlGetValueFromKey($strQuery);
                $arrTMPExtra["alias"] = $arrInfoDevice["nombre_p"];

                array_push($arrTMP["extra_data"], $arrTMPExtra);

                array_push($arrEmpresasAccess,$arrTMP);

            }
            unset($arrTMP);
            unset($arrT);
        }
    }
    else{
        $strFunction = "getJsonExample";
        if(function_exists($strFunction)){
            $strFunction($arrEmpresasAccess);
        }
    }

    $arrTMP["extra_data"] = [];
    $strFunctionData = "getExtraDataDevice";
    if(function_exists($strFunctionData)){
        $strFunctionData($arrTMP["extra_data"], $intUserID, $strCodigoSeguridad_E,$strAppName, $strApiVersion);
    }
    
    $arrDatosUser["name"] = $_SESSION["wt"]["name"];
    $arrDatosUser["nombres"] = $_SESSION["wt"]["nombres"];
    $arrDatosUser["apellidos"] = $_SESSION["wt"]["apellidos"];
    $arrDatosUser["correo"] = sqlGetValueFromKey("SELECT IF(email = '',email2,email) AS email FROM wt_users WHERE uid = '{$_SESSION["wt"]["uid"]}'");

    if(isset($config["webservices"])){
        reset($config["webservices"]);
        if(is_array($config["webservices"]) && count($config["webservices"])>0){
            foreach($config["webservices"] AS $key => $value){
                $arrModules["key"] = $key;
                $arrModules["value"] = $value;
                if(is_array($arrModules["value"]) && count($arrModules["value"])){
                    foreach($arrModules["value"] AS $key2 => $value2){
                        $arrExtra["key"] = $key;
                        $arrExtra["value"] = $value;
                        $strModulo = (!empty($arrExtra["value"]["module"]))?$arrExtra["value"]["module"]:$arrModules["key"];
                        $strAccess = "{$strModulo}/{$arrExtra["value"]["page"]}";
                        if(check_user_class($strAccess)){
                            $arrDatosUser["access"]["entidades"][] = "{$arrExtra["value"]["page"]}";
                        }
                        unset($arrExtra);
                    }
                }
                unset($arrModules);
            }

            if(isset($config["accesosOperaciones"])){
                reset($config["accesosOperaciones"]);
                if(is_array($config["accesosOperaciones"]) && (count($config["accesosOperaciones"]) >0)){
                    foreach($config["accesosOperaciones"] AS $key => $value){
                        $arrOperaciones["key"] = $key;
                        $arrOperaciones["value"] = $value;
                        if(check_user_class($arrOperaciones["value"])){
                            $arrDatosUser["access"]["web_mobil"][] = $arrOperaciones["key"];
                        }
                        unset($arrOperaciones);
                    }
                }
            }
        }
    }
    
    $arrResponse["datosUser"] = $arrDatosUser;
    $arrResponse["empresa"] = $arrEmpresasAccess;
    $arrResponse["extra_data"] = $arrTMP["extra_data"];
    return $arrResponse;
}
