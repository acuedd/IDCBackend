<?php
//op_uuid: 83ef480f-000c-11e2-8a03-a73a2d3170a0
/* Autor: Alejandro Gudiel
 * Fecha: 17-09-2012
 * Descripcion: webservice para modificar devices
 */
require_once("webservices/webservices_library.php");
require_once("webservices/webservices_baseclass.php");

class save_device_info extends webservices_baseClass {
    
    protected $boolModuleCloud;

	function __construct($strCodigoOperacion, $arrInfoOperacion) {
		parent::__construct($strCodigoOperacion, $arrInfoOperacion);

		$this->setModosPermitidos(array("w","wm")); //am,w,wm
		$this->setFormatosPermitidos(array("json")); //xmlwa,xmlno,json,html,txt
        
        $this->boolModuleCloud = check_module("cloud");
	}

	/**
	* Override para definir los parametros
	*
	* @param mixed $arrParametros, espero
	* id: el ID del dispositivo (primary key de la tabla)
	* field: el nombre del campo a actualizar
	* newval: valor a guardar
	*/
	public function setParametros($arrParametros) {
		$this->arrParams = $arrParametros;

		// Definir los parametros obligatorios, aqui tambien se puede jugar con los parametros para alguna otra cosa que sea necesaria
		if (!isset($this->arrParams["id"]) || !isset($this->arrParams["field"]) || !isset($this->arrParams["newval"])) {
			$this->appendError("WEBSERVICES_ERROR003");
			return false;
		}
		else {
			return true;
		}
	}

	/**
	* Hago los cambios solicitados, solo devuelvo "ok"
	*
	*/
	public function darRespuesta() {
        global $globalConnection;
		$boolAllOK = true;
		// Preparo el array de salida que sirve para xmlwa, xmlno, json
		$this->arrDataOutput = array();
		
		$this->arrParams["newval"] = db_escape($this->arrParams["newval"]);
		$this->arrParams["id"] = intval($this->arrParams["id"]);
		
        $intUid = (isset($this->arrParams["uid"]))?intval($this->arrParams["uid"]):$_SESSION["wt"]["uid"];
        
        $boolCloud = ($this->boolModuleCloud && (isset($this->arrParams["cloud_clientKey"])));
        if($boolCloud){
            $strClientKey =  $this->arrParams["cloud_clientKey"];
            $strDBName = cloud_getDataBaseName($strClientKey);
            $objSuperConnection = db_connect($this->config["host"], $strDBName, $this->config["local_super_user"], $this->config["local_super_password"], true, false);
            if (!$objSuperConnection){
                core_SendScriptInfoToWebmaster("Problema al conectar a base de datos remota. host: {$this->config["frontEnd_host"]}, {$this->config["frontEnd_database"]}, {$this->config["frontEnd_user"]}, cloud_configure_local_instance");
                $boolAllOK = false;
            }
            else{
                unset($globalConnection);
                $globalConnection = $objSuperConnection;
                //2014-07-30 - Para ver que modulos estan activos en el sitio que se desea hacer las modificaciones
                $intUid = intval($this->arrParams["cloud_uid"]);
            }
        }
        if(($this->cfg["core"]["limit_webservice_devices"]) && (!empty($this->arrParams["device_auth"]))){
            $arrDeviceAuth = sqlGetValueFromKey("SELECT * FROM wt_webservices_devices_auth WHERE id_deviceauth = {$this->arrParams["id"]}",false,false,true,$globalConnection);
            if($arrDeviceAuth){
                switch ($this->arrParams["field"]) {
                    case "asociar" :{
                        db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$_SESSION["wt"]["uid"]}, NOW(), 'Se asocio el device_auth: {$this->arrParams["id"]} al UID: {$intUid}','wb core','','','','')",true,$globalConnection);                            
                        db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$_SESSION["wt"]["uid"]}, NOW(), 'Se desligo el device_auth : {$this->arrParams["id"]} al UID: {$arrDeviceAuth["userid"]}','wb core','','','','')",true,$globalConnection);
                        
                        db_query("UPDATE wt_webservices_devices_auth SET userid = '0' WHERE userid = {$intUid}",true,$globalConnection);
                        db_query("UPDATE wt_webservices_devices_auth SET userid = '{$intUid}' WHERE id_deviceauth = {$this->arrParams["id"]} ",true,$globalConnection);

                        $this->arrDataOutput["ok"] = 1;
                        break;
                    }
                    case "desligado":{
                        db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$_SESSION["wt"]["uid"]}, NOW(), 'Se desligo el device_auth: {$this->arrParams["id"]} al UID: {$intUid}','wb core','','','','')",true,$globalConnection);
                        db_query("UPDATE wt_webservices_devices_auth SET userid = 0 WHERE id_deviceauth = {$this->arrParams["id"]}", true,$globalConnection);
                        $this->arrDataOutput["ok"] = 1;
                        break;
                    }
                    case "tipo":
                    case "marca":
                    case "modelo":
                    case "nombre_p":
                    case "alias":
                    case "no_telefono":
                    case "idSwiper":
                        db_query("UPDATE wt_webservices_devices_auth
                                  SET {$this->arrParams["field"]} = '{$this->arrParams["newval"]}'
                                  WHERE id_deviceauth = {$this->arrParams["id"]}",true,$globalConnection);
                        $this->arrDataOutput["ok"] = 1;
                        break;
                    case "combo_id":
                        db_query("UPDATE wt_configuracion_terminales
                                  SET {$this->arrParams["field"]} = '{$this->arrParams["newval"]}'
                                  WHERE userid = {$intUid}",true,$globalConnection);
                        $objModel = new cloud_model($this -> arrParams);
                        $objModel -> setObjSuperConection($globalConnection);
                        $objModel -> allocate_combo_to_staff($intUid,$this->arrParams["newval"]);
                        $this->arrDataOutput["ok"] = 1;
                        break;
                    default:
                        $boolAllOK = false;
                        $this->appendError("WEBSERVICES_ERROR009");
                        break;
                }   
            }
            else{
                $boolAllOK = false;
            }
        }
        else{
            $arrDevice = sqlGetValueFromKey("SELECT * FROM wt_webservices_devices WHERE id = {$this->arrParams["id"]}",false,false,true,$globalConnection);

            if($arrDevice){
                if(intval($arrDevice["userid"]) == $intUid){
                    switch ($this->arrParams["field"]) {
                        case "activo":
                            $boolActiveDevice = true;
                            if(check_module("tarjeta_credito")){
                                if(tarjeta_credito_valid_device($intUid,$this->arrParams["newval"],$globalConnection)){
                                    $boolActiveDevice = false;
                                    $boolAllOK = false;
                                    $this->appendError("TARJETA_CREDITO_DEVICE_ACTIVE");
                                }
                            }
                            if($boolActiveDevice){
                                if ($this->arrParams["newval"] == "Y") {
                                    $strTMP = "fecha_alta";
                                }
                                else {
                                    $strTMP = "fecha_baja";
                                }
                                db_query("UPDATE wt_webservices_devices 
                                          SET activo = '{$this->arrParams["newval"]}', {$strTMP} = NOW()
                                          WHERE id = {$this->arrParams["id"]}",true,$globalConnection);
                                $this->arrDataOutput["ok"] = 1;
                                $this->arrDataOutput["fecha"] = show_date(sqlGetValueFromKey("SELECT {$strTMP} FROM wt_webservices_devices WHERE id = {$this->arrParams["id"]}",false,false,true,$globalConnection), true);   
                            }
                            break;
                        case "confirmado":
                            db_query("UPDATE wt_webservices_devices 
                                      SET confirmado = '{$this->arrParams["newval"]}', fecha_confirmacion = NOW(), userid_confirma = {$_SESSION["wt"]["uid"]} 
                                      WHERE id = {$this->arrParams["id"]}",true,$globalConnection);
                            $this->arrDataOutput["ok"] = 1;
                            $this->arrDataOutput["fecha"] = show_date(sqlGetValueFromKey("SELECT fecha_confirmacion FROM wt_webservices_devices WHERE id = {$this->arrParams["id"]}"), true);
                            break;
                        case "tipo":
                        case "marca":
                        case "modelo":
                        case "nombre_p":
                        case "telefono":
                            db_query("UPDATE wt_webservices_devices
                                      SET {$this->arrParams["field"]} = '{$this->arrParams["newval"]}'
                                      WHERE id = {$this->arrParams["id"]}",true,$globalConnection);
                            $this->arrDataOutput["ok"] = 1;
                            break;
                        case "eliminado":
                            if(check_module("tarjeta_credito")){
                                $arrExist = getAssociatedDevice($this->arrParams["id"],$globalConnection);
                                if($arrExist["status"] == 'ok'){
                                    $boolAllOK = false;
                                    $this->appendError($arrExist["msj"]);
                                }
                                else{
                                    db_query("DELETE FROM wt_webservices_devices WHERE id = {$this->arrParams["id"]}",true,$globalConnection);
                                    $this->arrDataOutput["ok"] = 1;
                                }
                            }
                            else{
                                if(!db_query("DELETE FROM wt_webservices_devices WHERE id = {$this->arrParams["id"]}",true,$globalConnection)){
                                    db_query("UPDATE wt_webservices_devices
                                              SET {$this->arrParams["field"]} = '{$this->arrParams["newval"]}'
                                              WHERE id = {$this->arrParams["id"]}",true,$globalConnection);
                                }
                                $this->arrDataOutput["ok"] = 1;
                            }
                            break;
                        case "desligado":
                            $intUser = sqlGetValueFromKey("SELECT userid FROM wt_webservices_devices WHERE id = {$this->arrParams["id"]}",false,false,true,$globalConnection);
                            db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$intUser}, NOW(), 'Se desligo el deviceID: {$this->arrParams["id"]}','wb core','','','','')",true,$globalConnection);
                            db_query("UPDATE wt_webservices_devices SET userid = '0' WHERE id = {$this->arrParams["id"]}",true,$globalConnection);
                            $this->arrDataOutput["ok"] = 1;
                            break;
                        case "delete":
                            db_query("DELETE FROM wt_webservices_devices WHERE id = {$this->arrParams["id"]}",true,$globalConnection);
                            $this->arrDataOutput["ok"] = 1;
                            break;
                        default:
                            $boolAllOK = false;
                            $this->appendError("WEBSERVICES_ERROR009");
                            break;
                    }
                }
                else{
                    switch ($this->arrParams["field"]) {
                        case "asociar" :{
                            db_query("INSERT INTO wt_log (uid, date, descripcion, modulo,nombre,short_desc,access_rpt,cod) VALUES ({$_SESSION["wt"]["uid"]}, NOW(), 'Se asoció el deviceID: {$this->arrParams["id"]} al UID: {$intUid}','wb core','','','','')",true,$globalConnection);                            
                            db_query("UPDATE wt_webservices_devices SET userid = '{$intUid}', activo = 'Y', confirmado = 'Y', fecha_confirmacion = NOW(), userid_confirma = {$_SESSION["wt"]["uid"]} WHERE id = {$this->arrParams["id"]}",true,$globalConnection);                               
                            $this->arrDataOutput["ok"] = 1;
                            break;
                        }
                        case "activo":
                            $boolActiveDevice = true;
                            if(check_module("tarjeta_credito")){
                                if(tarjeta_credito_valid_device($intUid,$this->arrParams["newval"],$globalConnection)){
                                    $boolActiveDevice = false;
                                    $boolAllOK = false;
                                    $this->appendError("TARJETA_CREDITO_DEVICE_ACTIVE");
                                }
                            }
                            if($boolActiveDevice){
                                if ($this->arrParams["newval"] == "Y") {
                                    $strTMP = "fecha_alta";
                                }
                                else {
                                    $strTMP = "fecha_baja";
                                }
                                db_query("UPDATE wt_webservices_devices 
                                          SET activo = '{$this->arrParams["newval"]}', {$strTMP} = NOW()
                                          WHERE id = {$this->arrParams["id"]}",true,$globalConnection);
                                $this->arrDataOutput["ok"] = 1;
                                $this->arrDataOutput["fecha"] = show_date(sqlGetValueFromKey("SELECT {$strTMP} FROM wt_webservices_devices WHERE id = {$this->arrParams["id"]}",false,false,true,$globalConnection), true);   
                            }
                            break;
                        default:
                            $boolAllOK = false;
                            $this->appendError("WEBSERVICES_ERROR009");
                            break;
                    }
                }
            }
            else{
                $boolAllOK = false;
            }
        }
		// Lleno mi array o mi string con la respuesta segun el caso y si todo bien, devuelvo la data, si no, devuelvo un mensaje de error
		if ($boolAllOK) {
			parent::darRespuesta();
		}		else {
			$this->darRespuestaInvalido();
		}
	}
}