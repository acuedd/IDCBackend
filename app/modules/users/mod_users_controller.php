<?php

/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 07/06/17
 * Time: 15:52
 */
abstract class mod_users_controller extends global_config implements window_controller
{

	public function __construct($arrParams = "")
	{
		parent::__construct($arrParams);
	}

}

abstract class mod_users_model extends global_config implements window_model{

	public function userWithProfile($intProfile, $boolCount = false){
		if($boolCount){
			$strQuery = "SELECT COUNT(*) FROM wt_user_asig_profile WHERE profile_id = '{$intProfile}'";
			return sqlGetValueFromKey($strQuery);
		}
		else{
			$strQuery = "SELECT * FROM wt_user_asig_profile WHERE profile_id = '{$intProfile}'";
			$qTMP = db_query($strQuery);
			$arrUsers = array();
			if(db_num_rows($qTMP)){
				while($rTMP = db_fetch_assoc($qTMP)){
					$arrUsers[] = $rTMP["userid"];
					unset($rTMP);
				}
				db_free_result($qTMP);
			}
			return $arrUsers;
		}
	}

	public function getAccess($intProfile,$boolClean = false){
		$arrAccess = array();
		if($intProfile){
			$strQuery = "SELECT * FROM wt_user_access_perfiles_d WHERE perfil_id = '{$intProfile}'";
			$qTMP = db_query($strQuery);
			if(db_num_rows($qTMP)){
				while($rTMP = db_fetch_assoc($qTMP)){
					if($boolClean){
						$arrAccess[] = str_replace("/","",$rTMP["module"]);
					}
					else{
						$arrAccess[] = $rTMP["module"];
					}
					unset($rTMP);
				}
				db_free_result($qTMP);
			}
		}
		return $arrAccess;
	}

	public function deleteAccessUser($userid){
		return db_query("DELETE FROM wt_user_access WHERE userid = '{$userid}'");
	}

	public function assigProfile($userid,$profile){
		return db_query("REPLACE INTO wt_user_asig_profile (userid,profile_id,isCustom) VALUES ('{$userid}','{$profile}','N')");
	}

	public function asigProfileUser($userid,$profile){
		$this->removeProfile($userid);
		if($this->assigProfile($userid,$profile)){
			$this->deleteAccessUser($userid);
			$arrAcces = $this->getAccess($profile);
			foreach($arrAcces AS $val){
				$this->asiggnmentAccess($userid,$val);
				unset($val);
			}
		}
	}

	public function removeProfile($userid){
		$strQuery = "DELETE FROM wt_user_asig_profile WHERE userid = '{$userid}'";
		db_query($strQuery);
	}

	public function asiggnmentAccess($intuser,$access){
		return db_query("REPLACE INTO wt_user_access (userid,module,temporal_id) VALUES ('{$intuser}','{$access}','0')");
	}

	public function getUserProfiles($intUserID){
		$arrResponse = array();
		$strQuery = "SELECT * FROM wt_user_asig_profile WHERE userid = '{$intUserID}'";
		$qTMP = db_query($strQuery);
		while($rTMP = db_fetch_assoc($qTMP)){
			$arrResponse[$rTMP["profile_id"]] = $rTMP["profile_id"];
		}
		return $arrResponse;
	}
}