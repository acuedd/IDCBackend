<?php
/**
*
* webservice.php
* El entrypoint de las llamadas a nuestros webservices.
*
* DEPENDE DEL core y lo incluye
*
* @author   Alejandro Gudiel <agudiel@homeland.com.gt>
* @version  $Id: webservice.php,v 1.0 2012/06/28 14:15 $
* @access   public
*/


/**
* DEVELOPER NOTES
* - Falta soporte para parametros tipo $_FILE
* - Falta usar langs para los mensajes de error de baseclass y las dos operaciones ya definidas en el core
*/

/**
* * Servicios del core pendiente:
* - Una operacion para que me devuelva los IDs, titulos de modulos y descripciones de las operaciones a las que mi usuario tiene acceso.  Tomar en cuenta admin y helpdesk.
*/

include_once("core/main.php");
require_once("core/xmlfunctions.php");
require_once("webservices/webservices_library.php");

function local_invalid_operation($strDebugMessage = "") {
	global $boolGlobalIsLocalDev;
	if ($boolGlobalIsLocalDev) {
		die("{$strDebugMessage}, en produccion dara un error 404 - Not Found");
	}
	else {
		$strProtocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		header($strProtocol . " 400 Bad request");
		die();
	}
}

$strCodigoOperacion = (isset($_REQUEST["o"]))?user_input_delmagic($_REQUEST["o"]):false; // codigo de operacion - Obligatorio
$strFormatoRespuesta = (isset($_REQUEST["f"]))?user_input_delmagic($_REQUEST["f"]):"json"; //formato de salida - Default es json
$strModoOperacion = (isset($_REQUEST["m"]))?user_input_delmagic($_REQUEST["m"]):false; // modo de operacion - Obligatorio
$strSecurityToken = (isset($_REQUEST["t"]) && !empty($_REQUEST["t"]))?user_input_delmagic($_REQUEST["t"]):session_id(); // token de seguridad - Obligatorio solo para modo am ya que un session id jamas sera un UDID
$strQry = (isset($_REQUEST["qry"]))?user_input_delmagic($_REQUEST["qry"]):false; // Para ver si se quiere hacer un query de los formatos o modos de operacion validos

if ($strCodigoOperacion === false || $strFormatoRespuesta === false || $strModoOperacion === false || $strSecurityToken === false) {
	local_invalid_operation("Faltan parametros");
}
else {
	// Recibo los parametros de operacion en un array de GET o POST  (mejor si son en POST para que funcione con friendly URL)
	$arrParametros = $_REQUEST;

	// Quito los parametros de la operacion en si
	if (isset($arrParametros["o"])) unset($arrParametros["o"]);
	if (isset($arrParametros["f"])) unset($arrParametros["f"]);
	if (isset($arrParametros["m"])) unset($arrParametros["m"]);
	if (isset($arrParametros["t"])) unset($arrParametros["t"]);
	if (isset($arrParametros["qry"])) unset($arrParametros["qry"]);

	utf8_decode_array($arrParametros);

	$arrInfo = webservice_getOperationInfo($strCodigoOperacion);
	if ($arrInfo === false) {
		local_invalid_operation("Operacion Invalida");
	}
	else {
		/*
		Le tengo que mandar un "A" al checkmodule para que ignore la variable de configuracion que dice si el modulo es visible en parte publica o privada.
		La razon de esto es que aqui no he hecho log in, el log in se hace mas adelante y en este punto se valida si la operacion es publica o privada.
		*/
		if (!check_module($arrInfo["modulo"], true, "A")) local_invalid_operation("Modulo invalido");
		if (!file_exists($arrInfo["include_path"])) local_invalid_operation("Path no encontrado");

		// Estas variables se leen de mi base de datos... ¿Valdra la pena validar más por seguridad?

		include_once($arrInfo["include_path"]);
		$strClassName = $arrInfo["className"];

        $arrInfo["page"] = (!empty($arrParametros["page"]))?$arrParametros["page"]:"";
		$objWebservice = new $strClassName($strCodigoOperacion, $arrInfo);

		if (!$objWebservice->setFormatoRespuesta($strFormatoRespuesta)) {
			die($objWebservice->getError());
		}

		if (!$objWebservice->setModoOperacion($strModoOperacion)) {
			$objWebservice->darRespuestaInvalido();
		}
		else {
			if ($arrInfo["publica"] == "N" && (!$objWebservice->boolValidarCodigo($strSecurityToken) || !$objWebservice->boolValidarAcceso())) {
				$objWebservice->darRespuestaInvalido();
			}
			else {
				if ($strQry) {
					// Puedo solicitar a la operacion que me indique los formatos disponibles y modos de operacion válidos.
					if ($strFormatoRespuesta == "txt" || $strFormatoRespuesta == "html" || $strFormatoRespuesta == "bin") {
						die("Formato inválido para qry");
					}
					else {
						if ($strQry == "formatos_validos") {
							$objWebservice->darRespuestaFormatosValidos();
						}
						else if ($strQry == "modos_validos") {
							$objWebservice->darRespuestaModosValidos();
						}
						else {
							local_invalid_operation("Qry invalido de webservice");
						}
					}
				}
				else {
					$varReturn = "not am";
					if ($strModoOperacion == "am" && isset($arrParametros["aim"])) {
						$varReturn = webservice_check_saved_response($objWebservice->getDeviceID(), $arrParametros["aim"], $strFormatoRespuesta);
					}
					if ($varReturn !== "stop") {
						if ($objWebservice->setParametros($arrParametros)) {
							$objWebservice->darRespuesta();
						}
						else {
							$objWebservice->darRespuestaInvalido();
						}
					}
				}
			}
		}
	}
}