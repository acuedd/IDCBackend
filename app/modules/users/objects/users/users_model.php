<?php

/**<
 * Created by PhpStorm.
 * User: alexf
 * Date: 16/02/2017
 * Time: 12:18
 */
include_once("modules/users/objects/user_profile/user_profile_model.php");
class users_model extends user_profile_model  implements window_model{

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

    public function deleteBeforeChilds($userid){
        return db_query("UPDATE father");
    }

    public function deleteFatherAsignUser($userid){
        return db_query("UPDATE wt_users SET father = '' WHERE uid = '{$userid}'");
    }

    public function deleteChildAsignUser($userid){
        return db_query("UPDATE wt_users SET father = '' WHERE father = '{$userid}'");
    }

    public function getRol($intRol){
        $strQuery = "SELECT id_usertype, name, father FROM wt_swusertypes WHERE id_usertype = '{$intRol}'";
        $qTMP = db_query($strQuery);
        $arrRoles = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrRoles[$rTMP["id_usertype"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrRoles;
    }

    public function getChildByRol($nameSelectedRol){
        $strQuery = "SELECT id_usertype, name, father FROM wt_swusertypes WHERE father = '{$nameSelectedRol}';";
        $qTMP = db_query($strQuery);
        $arrChilds = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrChilds[$rTMP["id_usertype"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrChilds;
    }

    public function getUserCointidityRol($strRol,$strUser){
        $strFilter = "";
        if($strRol) $strFilter .= " AND swusertype = '{$strRol}'";
        if(!empty($strUser)) $strFilter .= " AND (nombres LIKE '%{$strUser}%' OR apellidos LIKE '%{$strUser}%')";

        $strQuery = "SELECT uid,nombres,apellidos,swusertype FROM wt_users WHERE  1 {$strFilter}";
        $qTMP = db_query($strQuery);
        $arrUsers = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrUsers[$rTMP["uid"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrUsers;
    }

    public function getUsersReport(){
        $strQuery = "SELECT
                        U.name,
                        U.nombres,
                        U.apellidos,
                        U.tel_cel,
                        U.email,
                        U.sex,
                        U.active,
                        U.father,
                        U.allow_multi_session,
                        R.name AS rol_name,
                        AP.profile_id,
                        VWT.tags,
                        UAP.nombre AS profile_access,
                        U.uid,
                        U.active,
                        DATE_FORMAT(U.lastvisit,'%d/%m/%Y') AS lastvisit,
                        U.logins AS logs
                    FROM
                        (
                            (
                                (
                                    (
                                        wt_users AS U
                                        LEFT JOIN wt_user_rol_asig AS RA ON RA.userid = U.uid
                                    )
                                    LEFT JOIN wt_swusertypes AS R ON U.swusertype = R.name
                                )
                                LEFT JOIN wt_user_asig_profile AS AP ON AP.userid = U.uid
                            )
                            LEFT JOIN view_tags_user AS VWT ON VWT.userid = U.uid
                        )
                    LEFT JOIN wt_user_access_perfiles AS UAP ON UAP.id = AP.profile_id
                    GROUP BY
                        uid";
        $arrData = array();
        $intCount = 0;
        $qTMP = db_query($strQuery);
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                if($rTMP["active"] == "Y"){
                    $rTMP["active"] = "Si";
                }
                else{
                    $rTMP["active"] = "No";
                }
                $arrData[$intCount] = $rTMP;
                $intCount++;
            }
            db_free_result($qTMP);
        }
        return $arrData;
    }

    public function getRolById($swUserType){
        return sqlGetValueFromKey("SELECT name FROM wt_swusertypes WHERE id_usertype = '{$swUserType}'");
    }

    public function getRolIdByName($swUserType){
        return sqlGetValueFromKey("SELECT id_usertype FROM wt_swusertypes WHERE name = '{$swUserType}'");
    }

    /**
     * @param $userid
     * @param string $term
     * @param string $tipo
     * @param string $strName este esta para buscar por dpi
     * @return array
     */

    public function getUsers($userid, $term = "" , $tipo = "", $strName = ""){
        $strFilter = "";
        if($userid) $strFilter .= " AND U.uid = '{$userid}'";

        if(!empty($term)){
            $strFilter .= " AND (U.nombres LIKE '%{$term}%' OR U.apellidos LIKE '%{$term}%')";
            $strFilter .= " AND U.name LIKE '%{$term}%'";
        }

        if(!empty($tipo))
            $strFilter .= " AND R.rol = '{$tipo}'";
        if(!empty($strName)){
            $strFilter .= " AND U.name LIKE '%{$strName}%'
                            OR U.nombres LIKE '%{$strName}%'
                            OR U.realname LIKE '%{$strName}%'
                            OR U.email LIKE '%{$strName}%'";
        }

        $strQuery = "SELECT
                            U.uid,
                            U.name,
                            U.nombres,
                            U.apellidos,
                            U.tel_cel,
                            U.email,
                            U.sex,
                            U.active,
                            U.father,
                            U.allow_multi_session,
                            R.name AS rol_name,
                            R.id_usertype AS rol_id,
                            AP.profile_id
                        FROM
                            (
                                (
                                    wt_users AS U
                                )
                                LEFT JOIN wt_swusertypes AS R ON U.swusertype = R.name
                            )
                        LEFT JOIN wt_user_asig_profile AS AP ON AP.userid = U.uid
                        WHERE
                            1 {$strFilter}
                     GROUP BY uid";
        $qTMP = db_query($strQuery);
        $arrUsers = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
	            $rTMP["hijos"] = array();
                $strChilds = $this->getChild($rTMP["uid"]);
                $arrTMP = explode(",",$strChilds);
				foreach($arrTMP AS $key => $value){
					$userChild = $this->getUserInfo($value);
					if(is_array($userChild))  array_push($rTMP["hijos"], $userChild);
					unset($key);unset($value);
				}

	            $intFather = $rTMP["father"];
	            $rTMP["father"] = $this->getUserInfo($intFather);


	            $strQuery = "SELECT T.id, T.tag, T.color
							FROM wt_user_tags T
								INNER JOIN wt_user_tag_asig TA
									ON T.id = TA.tag_id
							WHERE userid = '{$rTMP["uid"]}'";
	            $qTMP2 = db_query($strQuery);
	            $rTMP["tags"] = array();
	            while($rTMP2 = db_fetch_assoc($qTMP2)){
		            array_push($rTMP["tags"],$rTMP2);
		            unset($rTMP2);
	            }
	            $arrUsers[$rTMP["uid"]] = $rTMP;
	            $this->getUserInfo($rTMP["uid"]);

                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrUsers;
    }

    public function getUserInfo($intUser){
		$strQuery = "SELECT uid, name, nombres, apellidos, swusertype, father FROM wt_users WHERE uid = '$intUser'";
		return sqlGetValueFromKey($strQuery,true);
    }

    public function getChild($userid){
        return sqlGetValueFromKey("SELECT GROUP_CONCAT(uid) FROM wt_users WHERE father = '{$userid}'");
    }

    public function getRoles($rol = 0){
        $strFilter = "";
        if($rol) $strFilter = " AND id_usertype = '{$rol}'";

        $strQuery = "SELECT name, father, id_usertype FROM wt_swusertypes WHERE 1 {$strFilter}";
        $qTMP = db_query($strQuery);

        $arrRoles = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrRoles[$rTMP["id_usertype"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrRoles;
    }

    public function getTags($tag = 0, $term = ""){
        $strFilter = "";
        if($tag) $strFilter = " AND id = '{$tag}'";
        if(!empty($term)) $strFilter .= " AND tag  LIKE'%{$term}%'";

        $strQuery = "SELECT * FROM wt_user_tags WHERE 1 {$strFilter}";
        $qTMP = db_query($strQuery);

        $arrTags = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrTags[$rTMP["id"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrTags;
    }

    public function userExist($userid){
        return sqlGetValueFromKey("SELECT COUNT(*) FROM wt_users WHERE uid = '{$userid}'");
    }

    public function nameExiste($name){
        return sqlGetValueFromKey("SELECT COUNT(*) FROM wt_users WHERE name = '{$name}'");
    }

    public function asigRol($userid,$rolid){
        $strQuery = "INSERT INTO wt_user_rol_asig (userid,rol_id) VALUES ('{$userid}','{$rolid}')";
        return db_query($strQuery);
    }

    public function reAsignRol($userid,$swUserType){
        $strQuery = "UPDATE wt_user_rol_asig SET rol_id = '{$swUserType}' WHERE userid = '{$userid}'";
        return db_query($strQuery);
    }

    public function deleteRol($userid){
        $strQuery = "DELETE FROM wt_user_rol_asig WHERE userid = '{$userid}'";
        return db_query($strQuery);
    }

    public function asigTags($userid,$tagid){
        $strQuery = "REPLACE INTO wt_user_tag_asig (userid,tag_id) VALUES ('{$userid}','{$tagid}')";
        return db_query($strQuery);
    }

    public function deleteTags($userid){
        $strQuery = "DELETE FROM wt_user_tag_asig WHERE userid = '{$userid}'";
        return db_query($strQuery);
    }

    public function getPositionRol($strRol){
        $strQuery = "SELECT father FROM wt_swusertypes WHERE name = '{$strRol}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getUsersFather($intUsers,$strFatherRole,$uidEach){
        $strQuery = "SELECT uid FROM wt_users WHERE swusertype = '{$strFatherRole}' AND uid = '$uidEach'";
        return sqlGetValueFromKey($strQuery);
    }

    public function asigFather($userid,$usersFather){
        $strQuery = "UPDATE wt_users SET father = '{$usersFather}' WHERE uid = '{$userid}'";
        return db_query($strQuery);
    }

    public function asigChild($userid,$child){
        $strQuery = "UPDATE wt_users SET father = '{$userid}' WHERE uid = '{$child}'";
        return db_query($strQuery);
    }

    public function getUserExistRolAsig($userid){
        $strQuery = "SELECT COUNT(*) FROM wt_user_rol_asig WHERE userid = '{$userid}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function UpdateRolAsig($userid,$swUserType){
        $strQuery = "UPDATE wt_user_rol_asig SET rol_id = '{$swUserType}' WHERE userid = '{$userid}'";
        return db_query($strQuery);
    }

    public function getExistUserRol($userid){
        $strQuery = "SELECT COUNT(*) FROM wt_user_rol_asig WHERE userid = '{$userid}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getUsersOnline(){
    	$arrResponse = array();
	    $qTMP = db_query("SELECT distinct O.uid,
    						    U.name
    				     FROM wt_online O
    						      LEFT OUTER JOIN wt_users U
    					      ON ( O.uid = U.uid )
    				     WHERE O.uid > 0
    				     ORDER BY U.name");
	    while($rTMP = db_fetch_assoc($qTMP)){
			array_push($arrResponse, $rTMP);
	    }
	    return $arrResponse;
    }

    public function getUserByName($strName){
        $strQuery = "SELECT uid, name, swusertype, nombres, apellidos FROM wt_users WHERE name = '{$strName}' ";
        $this->appendDebug($strQuery);
        return sqlGetValueFromKey($strQuery);
    }

    public function updatePassword($intUserIDm, $strPass)
    {
	    $strAdd = "";
	    if(!empty($this->cfg["users"]["Save_Unencrypted_pwd"])){
		    $strAdd = ", uepassword = '{$strPass}' ";
	    }

    	$strQuery = "UPDATE wt_users SET password = MD5('{$strPass}') {$strAdd} WHERE uid = '{$intUserIDm}' ";
	    $this->appendDebug($strQuery);
	    db_query($strQuery);
    }

    public function getUsersSendNotification($intUser = 0)
    {
        $arrResponse = array();
        $strFiler = " U.active = 'Y' ";
        if(!empty($intUser)){
            $strFiler .= " AND U.`name` IN('{$intUser}' ";
        }

        $strQuery = "SELECT 
                            U.`name`,
                            U.sex,
                            U.email,
                            U.tel_cel
                        FROM 
                            wt_users U
                            WHERE 1
                            {$strFiler}";
        $this->appendDebug($strQuery);
        $qTMP = db_query($strQuery);
        while($rTMP = db_fetch_assoc($qTMP)){
            array_push($arrResponse, $rTMP);
        }
        return $arrResponse;
    }

    public function getAllUserInfo($id){
        $strQuery = "SELECT * FROM wt_users where uid = $id";
        return sqlGetValueFromKey($strQuery);
    }

    public function getInfoUserByName($strUserName)
    {
        $strQuery = "SELECT `name`, tel_cel, email WHERE `name` = '{$strUserName}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getUserContact($intId){
        $strQuery = "SELECT US.uid, US.email, US.tel_cel, UD.token_gcm 
                    FROM wt_users AS US
                    LEFT JOIN wt_webservices_devices AS UD
                    ON UD.userid = US.uid
                    WHERE uid = $intId";
        return sqlGetValueFromKey($strQuery);
    }

    public function getUserIdByName($strName){
        $strQuery = "SELECT uid FROM wt_users WHERE `name` = '{$strName}'";
        $this->appendDebug($strQuery);
        return sqlGetValueFromKey($strQuery);
    }

    public function getAllUsers($strId = ''){
        $strFilter = "";
        if(!empty($strId)){
            $strFilter .= "WHERE name IN ({$strId}) AND active = 'Y'";
        }
        else{
            $strFilter .= "WHERE uid > 0 AND active = 'Y'";
        }

        $strQuery = "SELECT uid, `name`, email, tel_cel, country, mail_confirmed FROM wt_users $strFilter";
        $allUsers = db_query($strQuery);
        $arrData = [];
        foreach($allUsers as $user){
            $arrData[] = $user;
        }
        return $arrData;
    }
}