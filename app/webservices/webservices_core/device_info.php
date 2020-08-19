<?php
/**
 * Description of device_info
 *
 * @author Alexander
 */
include_once("core/global_config.php");
class device_info extends global_config {
    
    private $boolCloud = false;
    private $intId = 0;
    private $strField = "";
    private $strNewVal = "";
    private $intUID = 0;
    private $boolAllOK = true;
    private $_response = array();
    
    private $intDeviceAuth = "";
    
    public function __construct(&$arrParams = "") {
        parent::__construct($arrParams);
        $this->boolCloud = check_module("cloud");
    }
    
    private function get_id_device($t,$objConection){
        return sqlGetValueFromKey("SELECT id FROM wt_webservices_devices WHERE device_udid = '{$t}'",false,false,true,$objConection);
    }
    
    public function save_device_info(){
        global $globalConnection;
        $this->intUID = (isset($this->arrParams["uid"]))?intval($this->arrParams["uid"]):$_SESSION["wt"]["uid"];
        $this->boolCloud = ($this->boolCloud && (isset($this->arrParams["cloud_clientKey"])));
        
        $this->strField = $this->checkParam("field");
        $this->strNewVal = $this->checkParam("newval");
        
        $this->intDeviceAuth = $this->checkParam("device_auth");
        
        //Reviso si viende desde cloud
        if($this->boolCloud){
            $strDBName = cloud_getDataBaseName($this->arrParams["cloud_clientKey"]);
            $objSuperConnection = db_connect($this->config["host"], $strDBName, $this->config["local_super_user"], $this->config["local_super_password"], true, false);
            if (!$objSuperConnection){
                core_SendScriptInfoToWebmaster("Problema al conectar a base de datos remota. host: {$this->config["frontEnd_host"]}, {$this->config["frontEnd_database"]}, {$this->config["frontEnd_user"]}, cloud_configure_local_instance");
                $this->boolAllOK = false;
            }
            else{
                unset($globalConnection);
                $globalConnection = $objSuperConnection;
                //2014-07-30 - Para ver que modulos estan activos en el sitio que se desea hacer las modificaciones
                $this->intUID = intval($this->arrParams["cloud_uid"]);
            }
        }
        
        //Limpio parametros
        if(!empty($this->arrParams["udid"])){
            $this->intId = $this->get_id_device($this->arrParams["udid"],$globalConnection);
        }
        else{
            $this->intId = intval($this->arrParams["id"]);
        }
        
        if(($this->cfg["core"]["limit_webservice_devices"]) && (!empty($this->intDeviceAuth))){
            $arrDeviceAuth = sqlGetValueFromKey("SELECT * FROM wt_webservices_devices_auth WHERE id_deviceauth = {$this->intId}",false,false,true,$globalConnection);
            if($arrDeviceAuth){
                switch ($this->arrParams["field"]) {
                    case "asociar" :{
                        db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$_SESSION["wt"]["uid"]}, NOW(), 'Se asocio el device_auth: {$this->intId} al UID: {$this->intUID}','wb core','','','','')",true,$globalConnection);                            
                        db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$_SESSION["wt"]["uid"]}, NOW(), 'Se desligo el device_auth : {$this->intId} al UID: {$arrDeviceAuth["userid"]}','wb core','','','','')",true,$globalConnection);
                        
                        db_query("UPDATE wt_webservices_devices_auth SET userid = '0' WHERE userid = {$this->intUID}",true,$globalConnection);
                        db_query("UPDATE wt_webservices_devices_auth SET userid = '{$this->intUID}' WHERE id_deviceauth = {$this->intId} ",true,$globalConnection);

                        $this->_response["ok"] = 1;
                        break;
                    }
                    case "desligado":{
                        db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$_SESSION["wt"]["uid"]}, NOW(), 'Se desligo el device_auth: {$this->intId} al UID: {$this->intUID}','wb core','','','','')",true,$globalConnection);
                        db_query("UPDATE wt_webservices_devices_auth SET userid = 0 WHERE id_deviceauth = {$this->intId}", true,$globalConnection);
                        $this->_response["ok"] = 1;
                        break;
                    }
                    case "tipo":
                    case "marca":
                    case "modelo":
                    case "nombre_p":
                    case "alias":
                    case "no_telefono":
                    case "no_facturacion":
                    case "idSwiper":
                        db_query("UPDATE wt_webservices_devices_auth
                                  SET {$this->strField} = '{$this->strNewVal}'
                                  WHERE id_deviceauth = {$this->intId}",true,$globalConnection);
                        $this->_response["ok"] = 1;
                        break;
                    case "combo_id":
                        db_query("UPDATE wt_configuracion_terminales
                                  SET {$this->strField} = '{$this->strNewVal}'
                                  WHERE userid = {$this->intUID}",true,$globalConnection);
                        $objModel = new cloud_model($this -> arrParams);
                        $objModel -> setObjSuperConection($globalConnection);
                        $objModel -> allocate_combo_to_staff($this->intUID,$this->strNewVal);
                        $this->_response["ok"] = 1;
                        break;
                    default:
                        $this->boolAllOK = false;
                        $this->addError("WEBSERVICES_ERROR009");
                        break;
                }   
            }
            else{
                $this->boolAllOK = false;
            }
        }
        else{
            $arrDevice = sqlGetValueFromKey("SELECT * FROM wt_webservices_devices WHERE id = {$this->intId}",false,false,true,$globalConnection);

            if($arrDevice){
                if(intval($arrDevice["userid"]) == $this->intUID){
                    switch ($this->strField) {
                        case "activo":
                            $boolActiveDevice = true;
                            if(check_module("tarjeta_credito")){
                                if(tarjeta_credito_valid_device($this->intUID,$this->arrParams["newval"],$globalConnection)){
                                    $boolActiveDevice = false;
                                    $this->boolAllOK = false;
                                    $this->addError("TARJETA_CREDITO_DEVICE_ACTIVE");
                                }
                            }
                            if($boolActiveDevice){
                                if ($this->strNewVal == "Y") {
                                    $strTMP = "fecha_alta";
                                }
                                else {
                                    $strTMP = "fecha_baja";
                                }
                                db_query("UPDATE wt_webservices_devices 
                                          SET activo = '{$this->strNewVal}', {$strTMP} = NOW()
                                          WHERE id = {$this->intId}",true,$globalConnection);
                                $this->_response["ok"] = 1;
                                $this->_response["fecha"] = show_date(sqlGetValueFromKey("SELECT {$strTMP} FROM wt_webservices_devices WHERE id = {$this->intId}",false,false,true,$globalConnection), true);   
                            }
                            break;
                        case "confirmado":
                            db_query("UPDATE wt_webservices_devices 
                                      SET confirmado = '{$this->strNewVal}', fecha_confirmacion = NOW(), userid_confirma = {$_SESSION["wt"]["uid"]} 
                                      WHERE id = {$this->intId}",true,$globalConnection);
                            $this->_response["ok"] = 1;
                            $this->_response["fecha"] = show_date(sqlGetValueFromKey("SELECT fecha_confirmacion FROM wt_webservices_devices WHERE id = {$this->intId}"), true);
                            break;
                        case "tipo":
                        case "marca":
                        case "modelo":
                        case "nombre_p":
                        case "telefono":
                            db_query("UPDATE wt_webservices_devices
                                      SET {$this->strField} = '{$this->strNewVal}'
                                      WHERE id = {$this->intId}",true,$globalConnection);
                            $this->_response["ok"] = 1;
                            break;
                        case "eliminado":
                            if(check_module("tarjeta_credito")){
                                $arrExist = getAssociatedDevice($this->intId,$globalConnection);
                                if($arrExist["status"] == 'ok'){
                                    $this->boolAllOK = false;
                                    $this->addError($arrExist["msj"]);
                                }
                                else{
                                    db_query("DELETE FROM wt_webservices_devices WHERE id = {$this->intId}",true,$globalConnection);
                                    $this->_response["ok"] = 1;
                                }
                            }
                            else{
                                if(!db_query("DELETE FROM wt_webservices_devices WHERE id = {$this->intId}",true,$globalConnection)){
                                    db_query("UPDATE wt_webservices_devices
                                              SET {$this->strField} = '{$this->strNewVal}'
                                              WHERE id = {$this->intId}",true,$globalConnection);
                                }
                                $this->_response["ok"] = 1;
                            }
                            break;
                        case "desligado":
                            $intUser = sqlGetValueFromKey("SELECT userid FROM wt_webservices_devices WHERE id = {$this->intId}",false,false,true,$globalConnection);
                            db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$intUser}, NOW(), 'Se desligo el deviceID: {$this->intId}','wb core','','','','')",true,$globalConnection);
                            db_query("UPDATE wt_webservices_devices SET userid = '0' WHERE id = {$this->intId}",true,$globalConnection);
                            $this->_response["ok"] = 1;
                            break;
                        case "delete":
                            db_query("DELETE FROM wt_webservices_devices WHERE id = {$this->intId}",true,$globalConnection);
                            $this->_response["ok"] = 1;
                            break;
                        default:
                            $this->boolAllOK = false;
                            $this->addError("WEBSERVICES_ERROR009");
                            break;
                    }
                }
                else{
                    switch ($this->strField) {
                        case "asociar" :{
                            db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$_SESSION["wt"]["uid"]}, NOW(), 'Se asoció el deviceID: {$this->intId} al UID: {$this->intUID}','wb core','','','','')",true,$globalConnection);
                            db_query("UPDATE wt_webservices_devices SET userid = '{$this->intUID}', activo = 'Y', confirmado = 'Y', fecha_confirmacion = NOW(), userid_confirma = {$_SESSION["wt"]["uid"]} WHERE id = {$this->intId}",true,$globalConnection);
                            $this->_response["ok"] = 1;
                            break;
                        }
                        case "activo":
                            $boolActiveDevice = true;
                            if(check_module("tarjeta_credito")){
                                if(tarjeta_credito_valid_device($this->intUID,$this->strNewVal,$globalConnection)){
                                    $boolActiveDevice = false;
                                    $this->boolAllOK = false;
                                    $this->addError("TARJETA_CREDITO_DEVICE_ACTIVE");
                                }
                            }
                            if($boolActiveDevice){
                                if ($this->strNewVal == "Y") {
                                    $strTMP = "fecha_alta";
                                }
                                else {
                                    $strTMP = "fecha_baja";
                                }
                                db_query("UPDATE wt_webservices_devices 
                                          SET activo = '{$this->strNewVal}', {$strTMP} = NOW()
                                          WHERE id = {$this->intId}",true,$globalConnection);
                                $this->_response["ok"] = 1;
                                $this->_response["fecha"] = show_date(sqlGetValueFromKey("SELECT {$strTMP} FROM wt_webservices_devices WHERE id = {$this->intId}",false,false,true,$globalConnection), true);   
                            }
                            break;
                        default:
                            $this->boolAllOK = false;
                            $this->addError("WEBSERVICES_ERROR009");
                            break;
                    }
                }
            }
            else{
                $this->boolAllOK = false;
                $this->addError("No se encuentra dispositivo");
            }
        }
        
        // Lleno mi array o mi string con la respuesta segun el caso y si todo bien, devuelvo la data, si no, devuelvo un mensaje de error
		if ($this->boolAllOK) {
			return response::standard(1,"Datos guardados correctamente",$this->_response);
		}else {
			return response::standard(0,$this->getErrors("string"));
		}
    }
}
