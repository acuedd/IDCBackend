<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 05/06/18
 * Time: 10:09
 */
include_once("core/global_config.php");
include_once "core/objects/devices/devices_model.php";

class devices_controller extends global_config implements window_controller
{
	private $boolPrintJson = false;
	private $boolUTF8 = true;
	private $strAction = "";
	public function __construct($arrParams = array()){
		parent::__construct($arrParams);
	}

	public function setStrAction( $strAction)
	{
		$this->strAction = $strAction;
	}

	/**
	 * @param bool $boolPrintJson
	 */
	public function setBoolPrintJson( $boolPrintJson)
	{
		$this->boolPrintJson = $boolPrintJson;
	}

	/**
	 * @param bool $boolUTF8
	 */
	public function setBoolUTF8( $boolUTF8)
	{
		$this->boolUTF8 = $boolUTF8;
	}

	public function main()
	{
		// TODO: Implement main() method.
	}

	/**
	 *
	 */
	public function registerDevice()
	{
		$strUserName = $this->checkParam("username");
		$strPassWord = $this->checkParam("password");

		//Parametros opcionales
		$strTipo = $this->checkParam("tipo");
		$strMarca = $this->checkParam("marca");
		$strModelo = $this->checkParam("modelo");
		$strOSversion = $this->checkParam("OSversion");
		$strAppversion = $this->checkParam("appversion");
		$strDispositivoID = $this->checkParam("dispositivo_id");
		$strApiVersion = $this->checkParam("apiversion");
		$strOS = $this->checkParam("OS");
		$strAppName = $this->checkParam("appname");
		$strTokenGCM = $this->checkParam("token_gcm");
		$strPhoneNumber = $this->checkParam("phoneNumber");

		$objModel = devices_model::getInstance($this->arrParams);
		$objModel->setDebugLevel(0);
		$strFunction = "external_validation_{$this->cfg["core"]["site_profile"]}";
		if(function_exists($strFunction)){
			$strFunction($strUserName, $strPassWord);
		}
		$arrUserInfo = $objModel->getUserInfo($strUserName,$strPassWord);
		if (!$arrUserInfo) {
			$this->addError("WEBSERVICES_ERROR005");
			return response::standard(0,$this->getErrors("string"),false,true);
		}
		else if($arrUserInfo["active"] == "N"){
			$this->addError(str_replace("%s",$this->cfg["core"]["title"],$this->lang["WEBSERVICES_ERROR024"]));
			return response::standard(0,$this->getErrors("string"),false,true);
		}
		else{
			$intCount = $objModel->validateAppVersion($strAppName, $strOS, $strAppversion);
			if(!$intCount){
				$this->addError("WEBSERVICES_ERROR004");
				return response::standard(0,$this->getErrors("string"),false,true);
			}
			else{
				$booContinue = true;
				webservice_deactiveNotConfirmedDevices();
				$strUDID = "";
				$intUserID = $arrUserInfo["uid"];
				$arrEmails = array();

				if (!empty($arrUserInfo["email"]) && core_validateEmailAddress($arrUserInfo["email"])) $arrEmails[] = $arrUserInfo["email"];
				if (!empty($arrUserInfo["email2"]) && core_validateEmailAddress($arrUserInfo["email2"])) $arrEmails[] = $arrUserInfo["email2"];

				if($this->getParam("limit_webservice_devices",$this->cfg["core"])){
					if(empty($strApiVersion)){
						$this->addError("WEBSERVICES_ERROR004");
						$booContinue = false;
					}
					else{
						$arrInfo = $objModel->getInfoLicense($strDispositivoID,$strAppName, $intUserID);
						if($arrInfo){
							//Reviso si existe ya el dispositivo registrado
							if(!empty($arrInfo["device_udid"])){
								if($arrInfo["device_activo"] =="N"){
									/*Este cambio es solo para las pruebas de james*/

									/*db_query("UPDATE wt_webservices_devices SET activo = 'N' WHERE userid = '{$intUserID}'");
									db_query("UPDATE wt_webservices_devices SET activo = 'Y' WHERE id = '{$arrInfo["device_id"]}'");
									$strUDID = $arrInfo["device_udid"];*/

									$booContinue = false;
									$this->addError("WEBSERVICES_ERROR001");
								}
								else{
									$strUDID = $arrInfo["device_udid"];
									$objModel->registerDevice(array(
										"id" => $arrInfo["device_id"],
										"marca" => $strMarca,
										"modelo" => $strModelo,
										"osversion" => $strOSversion,
										"appversion" => $strAppversion,
										"code_device" => $strDispositivoID,
										"apiversion" => $strApiVersion,
										"OS" => $strOS,
										"appname" => $strAppName,
										"modified_config" => "N",
										"token_gcm" => $strTokenGCM,
										"telefono" => $strPhoneNumber
									));
								}
							}
							else{
								$intCount = $objModel->countDevicesByUser($intUserID,$strAppName);
								if($intCount>0){
									$booContinue = false;
									$this->addError("WEBSERVICES_ERROR002");
								}
								else{
									$strUDID = $objModel->registerDevice(array(
										"userid" => $intUserID,
										"id_deviceauth" => $arrInfo["id_deviceauth"],
										"activo" => "Y",
										"tipo" => $strTipo,
										"marca" => $strMarca,
										"modelo" => $strModelo,
										"osversion" => $strOSversion,
										"appversion" => $strAppversion,
										"code_device" => $strDispositivoID,
										"apiversion" => $strApiVersion,
										"confirmado" => "Y",
										"OS" => $strOS,
										"appname" => $strAppName,
										"token_gcm" => $strTokenGCM,
										"telefono" => $strPhoneNumber
									));
								}
							}
						}
						else{
							$booContinue = false;
							$this->addError("WEBSERVICES_ERROR007");
						}
					}
				}
				else{
					if($this->cfg["core"]["webservices_limitDevicesPerUser"]){
						$intCountDeviceCode = $objModel->countByCode($strDispositivoID,$intUserID,$strAppName);
						if($intCountDeviceCode > 0){
							$this->addError("El dispositivo pertenece a otro usuario, se debe desactivar para poder utilizarse");
							$booContinue = false;
						}
						else{
							$arrInfo = $objModel->deviceBYCode($strDispositivoID, $intUserID, $strAppName);
							if($arrInfo){
								$objModel->deactivateDevices($intUserID, $arrInfo["id"]);
								if($arrInfo["activo"] != "Y"){
									$objModel->activateDevice($arrInfo["id"]);
									$strUDID = $arrInfo["device_udid"];
								}
								else{
									$strUDID = $arrInfo["device_udid"];
									$objModel->registerDevice(array(
										"id" => $arrInfo["id"],
										"marca" => $strMarca,
										"modelo" => $strModelo,
										"osversion" => $strOSversion,
										"appversion" => $strAppversion,
										"code_device" => $strDispositivoID,
										"apiversion" => $strApiVersion,
										"OS" => $strOS,
										"appname" => $strAppName,
										"modified_config" => "N",
										"token_gcm" => $strTokenGCM,
										"telefono" => $strPhoneNumber,
										"eliminado" => "N"
									));
								}
							}
							else{
								$intCount = $objModel->countDevicesByUser($intUserID,$strAppName);
								if($intCount>0){
									$booContinue = false;
									$this->addError("WEBSERVICES_ERROR002");
								}
								else{
									$strUDID = $objModel->registerDevice(array(
										"userid" => $intUserID,
										"activo" => "Y",
										"tipo" => $strTipo,
										"marca" => $strMarca,
										"modelo" => $strModelo,
										"osversion" => $strOSversion,
										"appversion" => $strAppversion,
										"code_device" => $strDispositivoID,
										"apiversion" => $strApiVersion,
										"confirmado" => "Y",
										"OS" => $strOS,
										"appname" => $strAppName,
										"token_gcm" => $strTokenGCM,
										"telefono" => $strPhoneNumber,
										"confirmado" => "Y"
									));
								}
							}
						}
					}
					else{
						$arrInfo = $objModel->deviceBYCode($strDispositivoID, $intUserID, $strAppName);
						if($arrInfo){
							if($arrInfo["activo"] != "Y"){
								$objModel->activateDevice($arrInfo["id"]);
							}
							$strUDID = $arrInfo["device_udid"];
							$this->sql_tableupdate("wt_webservices_devices", ["id"=>$arrInfo["id"]], ["token_gcm" =>$strTokenGCM]);
						}
						else{
							$strUDID = $objModel->registerDevice(array(
								"userid" => $intUserID,
								"activo" => "Y",
								"tipo" => $strTipo,
								"marca" => $strMarca,
								"modelo" => $strModelo,
								"osversion" => $strOSversion,
								"appversion" => $strAppversion,
								"code_device" => $strDispositivoID,
								"apiversion" => $strApiVersion,
								"confirmado" => "Y",
								"OS" => $strOS,
								"appname" => $strAppName,
								"token_gcm" => $strTokenGCM,
								"telefono" => $strPhoneNumber
							));
						}
					}
				}

				if($booContinue && (!empty($strUDID))){
					$strDestination = "";
					if (count($arrEmails)) $strDestination = implode(", ", $arrEmails);
					$boolSendNotification = $this->getParam("webservice_notificationRegisterDevice",$this->cfg["core"]);
					$strEmailText = "";
					if (!empty($strDestination) && $boolSendNotification) {
						$strNombre = "{$arrUserInfo["nombres"]} {$arrUserInfo["apellidos"]}";
						$strGender = $arrUserInfo["sex"];

						$this->webservice_sendRegistrationNotificacionEMail($strDestination, $strNombre, $strGender);
						$strEmailText = "  Se ha enviado notificacion a: {$strDestination}";
					}
					fill_login($intUserID);
					/*Dependiendo de que modulos estén activos */
					$arrResponse = webservice_getAccesos($intUserID, $strUDID, $strAppName,$strApiVersion);
					$this->arrDataOutput["udid"] = $strUDID;
					$this->arrDataOutput["institucion"] = $this->cfg["core"]["title"];
					$this->arrDataOutput["datosUser"] = $arrResponse["datosUser"];
					$this->arrDataOutput["empresa"] = $arrResponse["empresa"];
					$this->arrDataOutput["extra_data"] = $arrResponse["extra_data"];
					return response::standard(1,"Dispositivo registrado satisfactoriamente.{$strEmailText}",$this->arrDataOutput);

				}
				else{
					return response::standard(0,$this->getErrors("string"),false,true);
				}
			}
		}
	}

	public function checkUUDID()
	{
		$objModel = devices_model::getInstance($this->arrParams);
		webservice_deactiveNotConfirmedDevices();
		$strCodigoSeguridad_E = $this->checkParam("udid");

		$strAppversion = $this->checkParam("appversion");
		$strApiVersion = $this->checkParam("apiversion");
		$strOS = $this->checkParam("OS");
		$strAppName = $this->checkParam("appname");

		$intCount = $objModel->validateAppVersion($strAppName, $strOS,$strAppversion);
		if(!$intCount){
			$this->addError("WEBSERVICES_ERROR004");
			return response::standard(0,$this->getErrors("string"),false,true);
		}

		if(!$this->hasError()){
			$boolContinue = true;
			if($this->cfg["core"]["limit_webservice_devices"]){
				if(empty($strApiVersion)){
					$this->addError("WEBSERVICES_ERROR004");
					return response::standard(0,$this->getErrors("string"),false,true);
				}
				else{
					$arrInfo = $objModel->validateLicenceByUdid($strCodigoSeguridad_E);
					if($arrInfo){
						$boolContinue = true;
					}
					else{
						$boolContinue = false;
						$this->addError("WEBSERVICES_ERROR008");
					}
				}
			}

			if(!$boolContinue){
				$this->addError("WEBSERVICES_ERROR006");
				return response::standard(0,$this->getErrors("string"),false,true);
			}
			else{
				$intUserID = $objModel->validateUdid($strCodigoSeguridad_E);
				if($boolContinue){
					$arrEmpresasAccess = array();
					if ($intUserID !== false) {
						$intUserID = $objModel->checkUid($intUserID);

						if(!empty($_SESSION)){
							$arrResponse = webservice_getAccesos($intUserID, $strCodigoSeguridad_E,$strApiVersion);
						}
					}
					else{
						$this->addError("ERROR_17");
					}

					$arrTMP = array();
					if ($intUserID === false){
						$arrTMP["valido"] = 0;
						$arrTMP["acessos"] = 0;
						return response::standard(0,$this->getErrors("string"),$arrTMP);
					}
					else {
						$this->sql_tableupdate("wt_webservices_devices",array("device_udid"=>$strCodigoSeguridad_E), array(
							"appversion" => $strAppversion,
							"apiversion" => $strApiVersion,
							"OS"         => $strOS,
							"modified_config" => "N"
						));

						$arrTMP["valido"] = 1;
						$arrTMP["institucion"] = $this->cfg["core"]["title"];
						$arrTMP["datosUser"] = $arrResponse["datosUser"];
						$arrTMP["empresa"] = $arrResponse["empresa"];
						return response::standard(1,"ok",$arrTMP);
					}
				}
			}
		}
	}
    /**
     * Envia un mensaje de notificacion de activacion de dispositivo a un usuario registrado
     *
     * @param string $strDestination Direcciones de correo a donde enviar la notificacion
     * @param string $strUserName Nombre del usuario
     * @param string $strGender Sexo del usuario
     */
	private function webservice_sendRegistrationNotificacionEMail($strDestination, $strUserName, $strGender) {
        global $cfg, $lang;

        // Envio correo de notificacion al usuario
        $strBaseURL = core_getBaseDir();
        $strDomain = core_getBaseDomain();

        $strHeaders  = "MIME-Version: 1.0" . "\r\n";
        $strHeaders .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
        $strHeaders .= "From: noreply@{$strDomain}\r\n";

        $strTo = "{$strDestination}". "\r\n";
        $strSubject = sprintf($lang["WEBSERVICES_ACTIVATED_SUBJECT"], $cfg["core"]["title"]);
        $strMessage = sprintf($lang["WEBSERVICES_ACTIVATED_BODY"], (($strGender == "Male")?"o":"a"), $strUserName, $strBaseURL, $cfg["core"]["title"], $cfg["core"]["title"]);

        $objMail = new AttachMailer($strTo, $strSubject,"");
        $objMail->setHeader($strHeaders);
        $objMail->setMessageHTML($strMessage);
        $objMail->send();
    }
}