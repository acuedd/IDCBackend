<?php
//op_uuid: c5427272-fd06-11e1-b6dc-b51e77f97e87
require_once("webservices/webservices_library.php");
require_once("webservices/webservices_baseclass.php");

class see_also extends webservices_baseClass {

	function __construct($strCodigoOperacion, $arrInfoOperacion) {
		parent::__construct($strCodigoOperacion, $arrInfoOperacion);

		$this->setModosPermitidos(array("wm", "w")); //Solo para aplicaciones moviles
		$this->setFormatosPermitidos(array("xmlwa", "xmlno", "json")); //Esta operacion debiera pedir la data en un formato corto estructurado
	}

	/**
	* Override para definir los parametros.
	*
	* @param mixed $arrParametros, Espero: el módulo, el nombre en el menú del link para que que consulto y los parametros get que definen el estado actual.
	*/
	public function setParametros($arrParametros) {
		$this->arrParams = $arrParametros;

		if (!isset($this->arrParams["modulo"]) || !isset($this->arrParams["linkFrom"]) || !isset($this->arrParams["gets"])) {
			$this->appendError("WEBSERVICES_ERROR003");
			return false;
		}
		else {
			return true;
		}
	}

	/**
	* Devuelvo los links relacionados al link recibido en el parámetro en un array con las posiciones:
	* title - el titulo del link
	* url - el url para hacer el link
	* get - los parametros get a re-enviar
	*
	*/
	public function darRespuesta() {
		$this->arrDataOutput["boolHasData"] = false;
		$this->arrDataOutput["links"] = array();
		
		// Incluyo el modulo del FROM para obtener todos sus seealso...
		if (check_module($this->arrParams["modulo"], false)) {
			$strLinkFrom = db_escape($this->arrParams["linkFrom"]);
			
			if (isset($this->config["admmenu"][$strLinkFrom]["see_also"]) && is_array($this->config["admmenu"][$strLinkFrom]["see_also"])) {
				//[$strModuleTo][$strNameLinkTo] = $strGetExceptions;
				reset($this->config["admmenu"][$strLinkFrom]["see_also"]);
				while ($arrModuleTo = each($this->config["admmenu"][$strLinkFrom]["see_also"])) {
					if (check_module($arrModuleTo["key"], false)) {
						while ($arrLinkTo = each($arrModuleTo["value"])) {
							if (check_user_class($this->config["admmenu"][$this->lang[$arrLinkTo["key"]]]["class"])) {
								$arrTMP = array();
								$arrTMP["title"] = $this->lang[$arrLinkTo["key"]];
								$arrTMP["url"] = $this->config["admmenu"][$this->lang[$arrLinkTo["key"]]]["file"];
								$arrTMP["get"] = seeAlso_decodeGet($this->arrParams["gets"]);
								
								if (!empty($arrLinkTo["value"])) {
									$arrTMP["get"] = "&{$arrTMP["get"]}";
									$strExceptions = $arrLinkTo["value"];
									$arrExceptions = explode(",", $strExceptions);
									while ($arrItem = each($arrExceptions)) {
										$arrReplace = explode("=", $arrItem["value"]);
										
										$arrTMP["get"] = str_replace("&{$arrReplace[0]}=", "&{$arrReplace[1]}=", $arrTMP["get"]);
									}
									$arrTMP["get"] = substr($arrTMP["get"], 1);
								}
								
								$this->arrDataOutput["links"][] = $arrTMP;			
								$this->arrDataOutput["boolHasData"] = true;
							}
						}
					}
				}
			}
		}
		
		parent::darRespuesta();
	}
}