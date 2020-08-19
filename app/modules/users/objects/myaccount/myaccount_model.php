<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 8/02/2017
 * Time: 09:06
 */
include_once("core/global_config.php");
class myaccount_model extends global_config implements window_model {

    private static $_instance;

    public function __construct($arrParams){
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function getUsers($intUid = 0,$arrSelect = "", $arrFilter = false, $boolMultiple = false){
        $strFilter = "";
        if(!empty($intUid))$strFilter .= " AND uid = '{$intUid}'";
	    $strSelect = "";
        if(is_array($arrSelect)){
			foreach($arrSelect AS $field){
				$strSelect .= (empty($strSelect))?$field:",{$field}";
			}
		}
		else{
			$strSelect = "*";
		}
		if(is_array($arrFilter)){
        	foreach($arrFilter AS $key => $value){
        		$operator = $this->getParam("operator",$value,"=");
        		$strTerm = $this->getParam("term",$value,"=");
		        $strFilter .= " AND {$key} {$operator} '{$strTerm}'";
	        }
		}

        $arrUsers = array();
        $strQuery = "SELECT {$strSelect} FROM wt_users WHERE 1 {$strFilter}";
        $this->appendDebug($strQuery);
        $qTMP = db_query($strQuery);
        $intRows = db_num_rows($qTMP);
        if($intRows > 1  || $boolMultiple){
            while($rTMP = db_fetch_assoc($qTMP)){
                if(isset($rTMP["avatar"]))unset($rTMP["avatar"]);
                $arrUsers[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        else if($intRows == 1){
            $arrUsers = db_fetch_assoc($qTMP);
            if(isset($arrUsers["avatar"]))unset($arrUsers["avatar"]);
            db_free_result($qTMP);
        }
        return $arrUsers;
    }

    public function getPaises(){
        $arrPaises = array();
        $strQuery = "SELECT * FROM wt_paises WHERE active = 'Y'";
        $qTMP = db_query($strQuery);
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrTMP = array();
                $arrTMP["nombre"] = $rTMP["nombre"];
                $arrTMP["default"] = $rTMP["isLocalDefault"];
                array_push($arrPaises,$arrTMP);
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrPaises;
    }

    public function getDevices($intUid = 0,$strActive = ""){
        $strFilter = "";
        if(!empty($strActive))$strFilter .= " AND activo = '{$strActive}'";
        if(!empty($intUid))$strFilter .= " AND userid = '{$intUid}'";

        $arrDevices = array();

        /*device_udid,*/
	    $strQuery = "SELECT id, nombre_p, activo, fecha_alta, fecha_baja, last_use, uses, tipo, marca, modelo, telefono, 
							osversion
					FROM wt_webservices_devices WHERE 1 {$strFilter} AND eliminado = 'N'";
        $qTMP = db_query($strQuery);
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                if($rTMP["last_use"] != '0000-00-00 00:00:00' && $rTMP["last_use"] != NULL){
                    $rTMP["last_use"] = date("H:i - d/m/Y",strtotime($rTMP["last_use"]));
                }
                else{
                    $rTMP["last_use"] = "";
                }
                $arrDevices[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrDevices;
    }

    public function save_avatar($intUid,$avatar){
        $strQuery = "UPDATE wt_users SET avatar = '{$avatar}' WHERE uid = '{$intUid}'";
        return db_query($strQuery);
    }

    public function getContentAvatar($intUid){
        $strQuery = "SELECT avatar FROM wt_users WHERE uid = '{$intUid}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getRoleByUID($userID){
        $strQuery = "SELECT swusertype FROM wt_users WHERE uid = '{$userID}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getInfoRoleByUID($userID){
        $strQuery = "SELECT SWU.id_usertype, SWU.name, SWU.descr, SWU.father, SWU.idbranch FROM wt_users AS U LEFT JOIN wt_swusertypes AS SWU ON U.swusertype = SWU.name WHERE U.uid = '{$userID}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getChildAndRoleAuxByRol($nameSelectedRol){
        $arrResponse = array();
        $strQueryRoleAux = "SELECT role_auxiliar FROM wt_swusertypes WHERE name = '{$nameSelectedRol}'";
        $strRoleAux = sqlGetValueFromKey($strQueryRoleAux);
        $arrResponse["roleAux"] = sqlGetValueFromKey("SELECT id_usertype, name, descr, father FROM wt_swusertypes WHERE name = '{$strRoleAux}'");
        $strQuery = "SELECT id_usertype, name, descr, father FROM wt_swusertypes WHERE father = '{$nameSelectedRol}'";
        $qTMP = db_query($strQuery);
        $arrChilds = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                array_push($arrChilds, $rTMP);
            	//$arrChilds[$rTMP["id_usertype"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        $arrResponse["rolesChild"] = $arrChilds;
        return $arrResponse;
    }

    public function getUsersByRol($nameRol){
        $strQuery = "SELECT uid, nombres, apellidos, swusertype, father FROM wt_users WHERE swusertype = '{$nameRol}' AND active = 'Y'";
        $qTMP = db_query($strQuery);
        $arrResponse = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrResponse[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrResponse;
    }

    public function getInfoUserFamilyById($uid){
        $strQuery = "SELECT uid, swusertype, nombres, apellidos, father FROM wt_users WHERE father = '{$uid}'";
        $qTMP = db_query($strQuery);
        $arrResponse = array();
        if(db_num_rows($qTMP)){
            while ($rTMP = db_fetch_assoc($qTMP)){
                array_push($arrResponse, $rTMP);
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrResponse;
    }

    public function deleteUserFatherByUID($userID){
        $strQuery = "UPDATE wt_users SET father = 0 WHERE uid = '$userID'";
        return db_query($strQuery);
    }

    public function getIDUserAuxByFather($uid){
        $strQuery = "SELECT id_user_role_aux FROM wt_users_role_aux WHERE id_user = '{$uid}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getUserAuxByUserID($intIDUserFather){
        $strQuery = "SELECT nombres, apellidos, uid FROM wt_users WHERE uid = '{$intIDUserFather}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function deleteUserAuxByUserFather($idUserFatherAux){
        $strQuery = "DELETE FROM wt_users_role_aux WHERE id_user_role_aux = '{$idUserFatherAux}'";
        return db_query($strQuery);
    }

    public function getUserAux(){
        $strQuery = "SELECT role_auxiliar FROM wt_swusertypes WHERE role_auxiliar IS NOT NULL";
        $qTMP = db_query($strQuery);
        $arrResponse = array();
        if(db_num_rows($qTMP)){
            while ($rTMP = db_fetch_assoc($qTMP)){
                $arrResponse[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrResponse;
    }

    public function getInfoFamilyByRolAux($userID){
        $strQuery = "SELECT id, id_user, id_user_role_aux FROM wt_users_role_aux WHERE id_user_role_aux = '{$userID}'";
        $qTMP = db_query($strQuery);
        $arrResponse = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrResponse[$rTMP['id']] = $this->getInfoFather($rTMP["id_user"]);
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrResponse;
    }

    public function getInfoFather($userID){
        $strQuery = "SELECT uid, swusertype, nombres, apellidos, father FROM wt_users WHERE uid = '{$userID}'";
        $qTMP = db_query($strQuery);
        $arrResponse = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrResponse["father"] = $rTMP;
                $arrResponse["childs"] = $this->getInfoUserFamilyById($userID);
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrResponse;
    }

    public function getUserAuxMyFather($swusertype){
        $strQuery = "SELECT role_auxiliar FROM wt_swusertypes WHERE name = '{$swusertype}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getMyUserAux($uid){
        $strQuery = "SELECT id_user_role_aux FROM wt_users_role_aux WHERE id_user = '{$uid}'";
        return sqlGetValueFromKey($strQuery);
    }
}