<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 05/06/18
 * Time: 10:08
 */
include_once("core/global_config.php");

class devices_model extends global_config implements window_model
{
	private static $_instance;
	public function __construct($arrParams = "")
	{
		parent::__construct($arrParams);
	}

	public static function getInstance($arrParams)
	{
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($arrParams);
		}
		return self::$_instance;
	}

	public function getUserInfo($strUserName, $strPassWord)
	{
		$strQuery = "SELECT uid, email, email2, apellidos, nombres, sex, name, active
                                           FROM wt_users
                                           WHERE name = '{$strUserName}' AND
                                                    password = md5('{$strPassWord}') AND
                                                    retirado = 'N'";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
	}

	public function validateAppVersion($strAppName, $strOS, $strAppversion)
	{
		$strQuery = "SELECT  COUNT(*) 
                         FROM    (wt_app_control_versions AS V
                                    INNER JOIN wt_app_control_os AS O
                                      ON O.id = V.id_os)
                                        INNER JOIN wt_app_control_names AS N
                                          ON N.id = V.id_app
                         WHERE   N.name_unique = '{$strAppName}' AND O.os = '{$strOS}' AND V.version = '{$strAppversion}' AND V.permitido = 'Y'";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
	}

	public function registerDevice($arrDatos)
	{
		$intID = $this->getParam("id",$arrDatos,0);
		$arrKey = array();
		$arrFields = array();
		$arrKey["id"] = $intID;
		if(empty($intID)){
			$arrFields["userid"] = $this->getParam("userid",$arrDatos,0);
			$arrFields["device_udid"] = "UUID()";
			$arrFields["activo"] = $this->getParam("activo",$arrDatos,"N");
			$arrFields["fecha_alta"] = "NOW()";
			$arrFields["id_deviceauth"] = $this->getParam("id_deviceauth",$arrDatos,0);
			$arrFields["userid_confirma"] = $this->getParam("userid_confirma",$arrDatos,0);
			$arrFields["uses"] = $this->getParam("uses",$arrDatos,0);
			$arrFields["last_use"] = "NOW()";
		}
		if($this->getParam("confirmado",$arrDatos) != ""){
			$arrFields["confirmado"] = $this->getParam("tipo",$arrDatos,"N");
		}
		$arrFields["tipo"] = $this->getParam("tipo",$arrDatos,"");
		$arrFields["marca"] = $this->getParam("marca",$arrDatos,"");
		$arrFields["modelo"] = $this->getParam("modelo",$arrDatos,"");
		$arrFields["OS"] = $this->getParam("OS",$arrDatos,"");
		$arrFields["appname"] = $this->getParam("appname",$arrDatos,"");
		$arrFields["appversion"] = $this->getParam("appversion",$arrDatos,"");
		$arrFields["token_gcm"] = $this->getParam("token_gcm",$arrDatos,"");
		$arrFields["nombre_p"] = $this->getParam("nombre_p",$arrDatos,"");
		$arrFields["osversion"] = $this->getParam("osversion",$arrDatos,"");
		$arrFields["code_device"] = $this->getParam("code_device",$arrDatos,"");
		$arrFields["apiversion"] = $this->getParam("apiversion",$arrDatos,"");
		$arrFields["telefono"] = $this->getParam("telefono",$arrDatos,"");
		$arrFields["modified_config"] = $this->getParam("modified_config",$arrDatos,"N");
		$arrFields["eliminado"] = $this->getParam("eliminado",$arrDatos,"N");

		$this->sql_tableupdate("wt_webservices_devices",$arrKey, $arrFields);
		$intPrimaryKey = db_insert_id();
		$strUDID = sqlGetArray("SELECT device_udid FROM wt_webservices_devices WHERE id = {$intPrimaryKey}");
		return $strUDID;
	}

	public function getInfoLicense($strDispositivoID, $strAppName, $intUserID)
	{
		$strQuery = "SELECT     WDA.id_deviceauth, WDA.id_credencial, WDA.userid, WDA.activo, 
                                                WD.id as device_id,  WD.device_udid, WD.activo AS device_activo, WD.confirmado AS device_confirmado
                                    FROM        wt_webservices_devices_auth WDA
                                                    LEFT JOIN wt_webservices_devices WD ON WD.id_deviceauth = WDA.id_deviceauth
                                                            AND WD.code_device = '{$strDispositivoID}'
                                                            AND WDA.userid = WD.userid
                                                            AND WD.appname = '{$strAppName}' 
                                    WHERE       WDA.userid = '{$intUserID}' AND WDA.activo = 'Y' ";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
	}

	public function deviceBYCode($strDispositivoID, $intUserID, $strAppName)
	{
		$strQuery = "SELECT *
					FROM 	wt_webservices_devices WD  
					WHERE 	WD.code_device = '{$strDispositivoID}' AND 
							WD.appname = '{$strAppName}' AND 
							WD.userid = '{$intUserID}'";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
	}

	public function countDevicesByUser($intUserID,$strAppName)
	{
		//voy a revisar si tiene mas dispositivos asociados, si tiene mas entonces no dejo relacionarlo hasta desactivar el dispositivo anterior
		$strQuery = "SELECT COUNT(*) as cuantos FROM wt_webservices_devices WHERE userid = '{$intUserID}' AND activo = 'Y' AND appname = '{$strAppName}'";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
	}

	public function countByCode($strDispositivoID, $intUserID, $strAppName)
	{
		$strQuery = "SELECT COUNT(*) as cuantos 
					FROM 	wt_webservices_devices WHERE code_device = '{$strDispositivoID}' AND 
							activo = 'Y' AND 
							appname = '{$strAppName}' AND 
							userid != '{$intUserID}'";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
	}

	public function deactivateDevices($intUserID, $intDeviceID)
	{
		$strQuery = "UPDATE wt_webservices_devices SET activo = 'N', confirmado = 'N' WHERE userid = '{$intUserID}' AND id NOT IN($intDeviceID)";
		$this->appendDebug($strQuery);
		db_query($strQuery);
	}

	public function activateDevice($intDeviceId){
		db_query("UPDATE wt_webservices_devices SET activo = 'Y', eliminado = 'N', confirmado = 'Y' WHERE id = '{$intDeviceId}'");
	}

	public function validateUdid($strCodigoSeguridad_E)
	{
		$strQuery = "SELECT userid
                     FROM wt_webservices_devices
                     WHERE activo = 'Y' AND device_udid = '{$strCodigoSeguridad_E}'";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
	}

	public function validateLicenceByUdid($strCodigoSeguridad_E)
	{
		$strQuery = "SELECT     WDA.id_deviceauth, WDA.id_credencial, WDA.userid, WDA.activo, 
                                            WD.id as device_id,  WD.device_udid, WD.activo AS device_activo, WD.confirmado AS device_confirmado,
                                            WD.marca AS device_marca
                                FROM        wt_webservices_devices_auth WDA
                                                INNER JOIN wt_webservices_devices WD ON WD.id_deviceauth = WDA.id_deviceauth
                                                        AND WDA.userid = WD.userid
                                WHERE       WD.device_udid = '{$strCodigoSeguridad_E}' AND WDA.activo = 'Y'";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
	}

	public function getCredential($Uid)
    {
	    if(check_module("credit_card")){
            $strQuery = "SELECT CR.*, AU.* FROM wt_webservices_devices_auth AS AU
                     INNER JOIN wt_tarjeta_credito_visanet_credencial AS CR
                     ON AU.id_credencial = CR.id_credencial
                     WHERE AU.userid = '{$Uid}'";
            $this->appendDebug($strQuery);
            return sqlGetValueFromKey($strQuery);
        }
        return false;
    }

    public function assignDeviceAuthToDevice($intDeviceAuth, $intDevice = 0, $strCodigoSeguridad_E = "")
    {
        if(empty($intDevice) && empty($strCodigoSeguridad_E)){
            $this->addError("No se puede actualizar sin el deviceid o el uddid");
            return false;
        }

        $arrkey = [];
        if(!empty($strCodigoSeguridad_E)){
            $arrkey["device_udid"] = $strCodigoSeguridad_E;
        }
        if(!empty($intDevice)){
            $arrkey["id"] = $intDevice;
        }
        $this->sql_tableupdate("wt_webservices_devices", $arrkey, ["id_deviceauth" =>$intDeviceAuth]);
        return true;
    }

    public function getAuthCredential($id){
	    $strQuery = "SELECT id_deviceauth, id_credencial FROM wt_webservices_devices_auth WHERE userid = '{$id}'";
	    return sqlGetValueFromKey($strQuery);
    }

    public function getCredentialAndLog($id){
	    $strQuery = "SELECT
                        DA.id_credencial,
                        DA.userid,
                        DA.id_deviceauth,
                        CR.adquirente,
                        TL.id AS 'trans_id'
                    FROM
                        wt_webservices_devices_auth AS DA
                    INNER JOIN wt_tarjeta_credito_visanet_credencial AS CR ON
                        DA.id_credencial = CR.id_credencial
                    LEFT JOIN wt_tarjeta_credito_visanet_trans_log AS TL ON
                        DA.userid = TL.userid
                    WHERE
                        DA.userid = '{$id}'
                    Group by
                        DA.id_credencial";
	    return sqlGetValueFromKey($strQuery);
    }

    public function setDeviceAuth($intId){
	    $strQuery = "UPDATE wt_webservices_devices SET id_deviceauth = 0 WHERE id_deviceauth = '{$intId}'";
	    db_query($strQuery);
    }

    public function getAppVersion($id){
	    $strQuery = "SELECT id, appversion from wt_webservices_devices where id = '{$id}'";
	    return sqlGetValueFromKey($strQuery);
    }

	public function checkUid($intUserID)
	{
		$strQuery = "SELECT uid FROM wt_users WHERE uid = {$intUserID} AND active = 'Y' AND retirado = 'N'";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
	}
}