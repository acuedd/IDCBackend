<?php
//op_uuid: 75f911ba-6a66-11e8-84ec-286ed488d291
require_once("webservices/webservices_library.php");
require_once("webservices/webservices_baseclass.php");

class webservice_check_access_udid extends webservices_baseClass {

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
        
        $strOSversion = (isset($this->arrParams["OSversion"]))?db_escape($this->arrParams["OSversion"]):"";
        $strAppversion = (isset($this->arrParams["appversion"]))?db_escape($this->arrParams["appversion"]):"";
        $strDispositivoID = (isset($this->arrParams["dispositivo_id"]))?db_escape($this->arrParams["dispositivo_id"]):"";
        $strApiVersion = (isset($this->arrParams["apiversion"]))?db_escape($this->arrParams["apiversion"]):"";
        $strOS = (isset($this->arrParams["OS"]))?db_escape($this->arrParams["OS"]):"";
        $strAppName = (isset($this -> arrParams["appname"]))?db_escape($this -> arrParams["appname"]):"";
        $strTokenGCM = (isset($this->arrParams["token_gcm"]))?db_escape($this->arrParams["token_gcm"]):"";
        
        $boolOK = true;
        
        if($boolOK){
            $boolContinue = true;
            if(!empty($this->cfg["core"]["limit_webservice_devices"]) && $this->cfg["core"]["limit_webservice_devices"]){
                if(empty($strApiVersion)){
                    $this->appendError("WEBSERVICES_ERROR004");
                    $boolContinue = false;
                    $this->darRespuestaInvalido();
                }
                else{
                    $strQuery = "SELECT     WDA.id_deviceauth, WDA.id_credencial, WDA.userid, WDA.activo, 
                                            WD.id as device_id,  WD.device_udid, WD.activo AS device_activo, WD.confirmado AS device_confirmado,
                                            WD.marca AS device_marca
                                FROM        wt_webservices_devices_auth WDA
                                                INNER JOIN wt_webservices_devices WD ON WD.id_deviceauth = WDA.id_deviceauth
                                                        AND WDA.userid = WD.userid
                                WHERE       WD.device_udid = '{$strCodigoSeguridad_E}' AND WDA.activo = 'Y'";                                                                        
                    $arrInfo = sqlGetValueFromKey($strQuery);
                    if($arrInfo){
                        $boolContinue = true;
                    }
                    else{
                        $boolContinue = false;
                        $this->appendError("WEBSERVICES_ERROR008");    
                    }
                }    
            }
            if(!$boolContinue){
                $this->appendError("WEBSERVICES_ERROR006");
                $this->darRespuestaInvalido();
            }
            else{
                $strQuery = "SELECT userid
                             FROM wt_webservices_devices
                             WHERE activo = 'Y' AND device_udid = '{$strCodigoSeguridad_E}'";
                $intUserID = sqlGetValueFromKey($strQuery);
                //codigo para forzar actualizar a todos los android
                //se comprobo que iOS si envia el OS y se dejo por default android
                $strOS = (empty($strOS))?"Android":$strOS;
                $strQuery = "SELECT COUNT(*) FROM wt_configuracion_version_app WHERE os = '{$strOS}' AND version = '{$strAppversion}'";
                $intCount = sqlGetValueFromKey($strQuery);
                if(!$intCount){
                    $this->appendError("WEBSERVICES_ERROR004");
                    $boolContinue = false;
                    $this->darRespuestaInvalido();
                }
                /****************************************************/
                if($boolContinue){
                    $arrEmpresasAccess = array();
                    if ($intUserID !== false) {
                        $strQuery = "SELECT uid FROM wt_users WHERE uid = {$intUserID} AND active = 'Y' AND retirado = 'N'";
                        $intUserID = sqlGetValueFromKey($strQuery);

                        if(!empty($_SESSION)){
                            $arrResponse = webservice_getAccesos($intUserID, $strCodigoSeguridad_E,$strApiVersion);                                        
                        }
                    }

                    if ($intUserID === false) {
                        $this->arrDataOutput["valido"] = 0;
                        $this->arrDataOutput["acessos"] = 0;
                        $this->arrDataOutput = response::standard(0,"fail",$this->arrDataOutput);
                    }
                    else {
                        db_query("UPDATE wt_webservices_devices SET 
                                    appversion = '{$strAppversion}',
                                    apiversion = '{$strApiVersion}',
                                    OS = '{$strOS}',
                                    modified_config = 'N'
                                    WHERE device_udid = '{$strCodigoSeguridad_E}'");
                        
                        $this->arrDataOutput["valido"] = 1;
                        $this->arrDataOutput["institucion"] = $this->cfg["core"]["title"];
                        $this->arrDataOutput["datosUser"] = $arrResponse["datosUser"];
                        $this->arrDataOutput["empresa"] = $arrResponse["empresa"];
                        $this->arrDataOutput = response::standard(1,"ok",$this->arrDataOutput);
                    }
                    parent::darRespuesta();
                }
            }   
        }        
    }
}