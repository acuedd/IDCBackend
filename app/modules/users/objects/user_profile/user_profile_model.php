<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 15/02/2017
 * Time: 09:55
 */
include_once("core/global_config.php");
include_once 'modules/users/mod_users_controller.php';
class user_profile_model extends mod_users_model implements window_model{
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

    public function getProfiles(){
        $arrProfiles = array();
        $strQuery = "SELECT * FROM wt_user_access_perfiles";
        $qTMP = db_query($strQuery);
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $intId = $rTMP["id"] ;
                $arrProfiles[$intId] = array();
                unset($rTMP["id"]);
                $arrProfiles[$intId] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrProfiles;
    }

    public function getSWUserTypes()
    {
        $strQuery = "SELECT name, descr, id_usertype FROM wt_swusertypes";
        $this->appendDebug($strQuery);
        $arrReturn = [];
        $qTMP = db_query($strQuery);
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrReturn[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrReturn;
    }

    public function getProfile($intProfile){
        $strQuery = "SELECT * FROM wt_user_access_perfiles WHERE id = '{$intProfile}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function deleteAccessProfile($intProfile){
        return db_query("DELETE FROM wt_user_access_perfiles_d WHERE perfil_id = '{$intProfile}'");
    }

    public function saveAccessProfile($intProfile, $access)
    {
        return db_query("INSERT INTO wt_user_access_perfiles_d (perfil_id,module) VALUES ('{$intProfile}','{$access}')");
    }

    public function deleteProfile($intProfile){
        $strQuery = "DELETE FROM wt_user_access_perfiles_d WHERE perfil_id = '{$intProfile}'";
        if(db_query($strQuery)){
            $strQuery = "DELETE FROM wt_user_access_perfiles WHERE id = '{$intProfile}'";
            if(db_query($strQuery)){
                return true;
            }
        }
        return false;
    }

    public function validateNameProfile($strName){
        $strName = trim($strName);
        return sqlGetValueFromKey("SELECT COUNT(*) FROM wt_user_access_perfiles WHERE nombre = '{$strName}'");
    }

    public function deleteAndAssiggProfileUser($userid,$profile){
        $strQuery = "DELETE FROM wt_user_asig_profile WHERE userid = '{$userid}'";
        db_query($strQuery);

        $strQuery = "INSERT INTO wt_user_asig_profile (userid,profile_id,isCustom) VALUES ('{$userid}','{$profile}','N')";
        db_query($strQuery);

        $strQuery = "DELETE FROM wt_user_access WHERE userid = '{$userid}'";
        db_query($strQuery);
    }

    public function getCategories()
    {
        //$strQuery = "SELECT * FROM wt_sales_mobil_plan_category";
        $strQuery = "SELECT id_product AS id_category, name AS category_name, access AS category_code FROM wt_sales_category";
        $this->appendDebug($strQuery);
        $qTMP = db_query($strQuery);
        $arrDatos = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $strCategoryCode = strtolower($rTMP["category_code"]);
                $arrDatos[$strCategoryCode] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrDatos;
    }

    public function deleteCategoriesExistByProfile($intProfile)
    {
        return db_query("DELETE FROM wt_user_access_categories WHERE id_profile = '{$intProfile}'");
    }

    public function getAccessCategory($intProfile = 0)
    {
        $strFilter = "";
        if(!empty($intProfile)){
            $strFilter .= " AND AC.id_profile = '{$intProfile}'";
        }

        $strQuery = "SELECT AC.id, AC.id_profile, AC.id_category, EA.module, EA.window, EA.description, AC.type FROM
                        wt_user_access_categories AS AC
                        LEFT JOIN wt_core_extra_access AS EA
                        ON AC.id_category = EA.window AND EA.type = AC.type 
                         WHERE 1 {$strFilter}";
        $this->appendDebug($strQuery);
        $qTMP = db_query($strQuery);
        $arrReturn = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrReturn[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        //debug::drawdebug($this->getDebug());
        return $arrReturn;
    }

    public function getProfileByName($strName){
        $strQuery = "SELECT * FROM wt_user_access_perfiles WHERE nombre = '{$strName}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function deleteCategoriesByProfileID($intProfile)
    {
        return db_query("DELETE FROM wt_user_access_categories WHERE id_profile = '{$intProfile}'");
    }
}