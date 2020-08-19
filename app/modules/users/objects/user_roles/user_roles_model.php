<?php
/**
 * Created by PhpStorm.
 * User: NelsonMatul
 * Date: 26/09/2017
 * Time: 14:57
 */

include_once("modules/users/objects/user_profile/user_profile_model.php");
class users_roles_model extends user_profile_model  implements window_model
{

    private static $_instance;

    public function __construct($arrParams)
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

    public function getChildsTreeFam($father){
        $strQuery = "SELECT name, id_usertype, father FROM wt_swusertypes WHERE father = '{$father}' ";
        $qTMP = db_query($strQuery);
        $arrRoles = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrRoles[$rTMP["name"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrRoles;
    }

    public function nameExiste($name){
        return sqlGetValueFromKey("SELECT COUNT(*) FROM wt_swusertypes WHERE name LIKE '%{$name}%' ");
    }

    public function insertNewRol($name,$descr, $color){
        $strQuery = "INSERT INTO wt_swusertypes (name, descr, color) VALUES ('{$name}', '{$descr}', '{$color}');";
        return db_query($strQuery);
    }

    public function getRoles(){
        $strQuery = "SELECT name, descr, father, color, role_auxiliar, BR.name_branch AS branch, BR.id AS branchId FROM wt_swusertypes AS UT LEFT JOIN wt_users_branch_rol AS BR ON UT.idbranch = BR.id";
        $qTMP = db_query($strQuery);
        $arrRoles = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrA = array();
                $arrRoles[$rTMP["name"]] = $rTMP;
                unset($rTMP);
                /*array_push($arrTMP, $rTMP["name"]);
                array_push($arrTMP, $rTMP["father"]);
				array_push($arrRoles, $arrTMP);*/
            }
            db_free_result($qTMP);
        }
        /*return $arrRoles;*/
        return $arrRoles;

    }

    public function getTreeFamily($strName){
        $strQuery = "SELECT * FROM wt_swusertypes WHERE father = '{$strName}'";
        return db_query($strQuery);
    }

    function getRols($strRol = "", &$arrRols){
        if(empty($strRol)){
            $strQuery = "SELECT * FROM wt_swusertypes WHERE father = ''";
            $qTMP = db_query($strQuery);
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrRols[$rTMP["name"]] = array();
                $this->getRols( $rTMP["name"]);
            }
        }
        else{
            $strQuery = "SELECT * FROM wt_swusertypes WHERE father = '{$strRol}'";
            return db_query($strQuery);
        }

    }

    public function updateNameRol($newName,$nameAfected,$color,$intBranch,$auxRole){
        $strQuery = "UPDATE wt_swusertypes SET descr = '{$newName}', color = '{$color}', idbranch = '{$intBranch}', role_auxiliar = '{$auxRole}' WHERE name = '{$nameAfected}'";
        return db_query($strQuery);
    }

    public function updateFather($newName,$beforeName){
        $strQuery = "UPDATE wt_swusertypes SET father = '{$newName}' WHERE father = '{$beforeName}'";
        return db_query($strQuery);
    }

    public function updateFatherRol($strChild,$father){
        $strQuery = "UPDATE wt_swusertypes SET father = '{$father}' WHERE name = '{$strChild}'";
        return db_query($strQuery);
    }

    public function updateRoleAllUser($newName,$beforeName){
        $strQuery = "UPDATE wt_users SET swusertype = '{$newName}' WHERE swusertype = '{$beforeName}'";
        return db_query($strQuery);
    }

    public function eliminatedRol($toEliminated){
        $strQuery = "DELETE FROM wt_swusertypes WHERE name = '{$toEliminated}'";
        return db_query($strQuery);
    }

    public function removeFather($name){
        $strQuery = "UPDATE wt_swusertypes SET father = '', role_auxiliar = '' WHERE name = '{$name}'";
        return db_query($strQuery);
    }

    public function removeFatherUser($name){
        $strQuery = "UPDATE wt_users SET  father = '0' WHERE swusertype = '{$name}'";
        return db_query($strQuery);
    }

    public function nameExist($name){
        $strQuery = "SELECT COUNT(*) FROM wt_swusertypes WHERE name LIKE '%{$name}%' ";
        return sqlGetValueFromKey($strQuery);
    }

    public function getChilds($name){
        $strQuery = "SELECT COUNT(*) FROM wt_swusertypes WHERE father = '{$name}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getFather($name){
        $strQuery = "SELECT father FROM wt_swusertypes WHERE name = '{$name}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function insertBranchRol($name_branch){
        $strQuery = "INSERT INTO wt_users_branch_rol SET name_branch = '{$name_branch}'";
        return db_query($strQuery);
    }

    public function getCountUserExist($toDelete){
        $strQuery = "SELECT COUNT(*) FROM wt_users WHERE swusertype = '{$toDelete}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function isAuxiliar($strKeyName)
    {
		$strQuery = "SELECT DISTINCT role_auxiliar FROM wt_swusertypes WHERE role_auxiliar = '{$strKeyName}'";
		return sqlGetValueFromKey($strQuery);
    }

    public function getInfoByField($strField, $strTerm){
		$strQuery = "SELECT * FROM wt_swusertypes WHERE {$strField} = '{$strTerm}'	";
		$this->appendDebug($strQuery);
		return sqlGetValueFromKey($strQuery);
    }
}