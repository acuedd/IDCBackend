<?php
//op_uuid: fd000fb1-c6ef-11e1-bd02-bc845d4852c1
require_once("webservices/webservices_library.php");
require_once("webservices/webservices_baseclass.php");

class webservice_validar_udid extends webservices_baseClass {

	function __construct($strCodigoOperacion, $arrInfoOperacion) {
		parent::__construct($strCodigoOperacion, $arrInfoOperacion);

		$this->setModosPermitidos(array("am")); //Solo para aplicaciones moviles
		$this->setFormatosPermitidos(array("xmlwa", "xmlno", "json")); //Esta operacion debiera pedir la data en un formato corto estructurado
	}

	/**
	* Override para definir los parametros
	*
	* @param mixed $arrParametros, Espero que los parametros sean:
	* $this->arrParams["udid"] = "xxxx";
	*/
	public function setParametros($arrParametros) {
		$this->arrParams = $arrParametros;

		if (!isset($this->arrParams["udid"])) {
			$this->appendError("WEBSERVICES_ERROR003");
			return false;
		}
		else {
			return true;
		}
	}

	/**
	* Valido si el udid (codigo del dispositivo) que me envian es valido o no.
	* 
	*/
	public function darRespuesta() {
		webservice_deactiveNotConfirmedDevices();

		$strCodigoSeguridad = $this->arrParams["udid"];

		$strCodigoSeguridad_E = db_escape($strCodigoSeguridad);		
        $strQuery = "SELECT userid
					 FROM wt_webservices_devices
					 WHERE activo = 'Y' AND device_udid = '{$strCodigoSeguridad_E}'";
		$intUserID = sqlGetValueFromKey($strQuery);
		if ($intUserID !== false) {
			$strQuery = "SELECT uid FROM wt_users WHERE uid = {$intUserID} AND active = 'Y' AND retirado = 'N'";
			$intUserID = sqlGetValueFromKey($strQuery);
		}

		if ($intUserID === false) {
			$this->arrDataOutput["valido"] = 0;
		}
		else {
			$this->arrDataOutput["valido"] = 1;
		}

		parent::darRespuesta();
	}
}