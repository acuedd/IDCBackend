<?php
include_once("core/global_config.php");
class profile_model extends global_config implements window_model
{
    private static $_instance;

    public function __construct($arrParams = "")
    {
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams = "")
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }
    public function getAllCaptionsDB(){
        $strQuery = "Select * from wt_caption_info where id > 0";
        $allCaptions = db_query($strQuery);
        $arrAllCaptions = [];
        foreach ($allCaptions as $capti){
            $arrAllCaptions[] = $capti;
        }
        return $arrAllCaptions;
    }

    public function deleteMenuItemDB($id){
        $strQuery = "delete from wt_menu_links where id = $id";
        return db_query($strQuery);
    }

    public function getMenuItemBy($str, $id){
        $strQuery = "select * from wt_menu_links where $str = $id";
        return sqlGetValueFromKey($strQuery);
    }

    public function updateMenuOrder($id, $order){
        $strQuery = "update wt_menu_links set order_menu = $order where id = $id";
        db_query($strQuery);
        return true;
    }

    public function getMaxOrder($id = false){
        $optional = "where id != $id";
        $strQuery = "SELECT max(order_menu) 
                     from wt_menu_links ";
        if($id) $strQuery.= $optional;
        return sqlGetValueFromKey($strQuery);
    }

    public function getMenuItemsDB(){
        $strQuery = "Select * from wt_menu_links where id > 0 order by order_menu;";
        $all = db_query($strQuery);
        $arrAll = [];
        foreach($all as $menu){
            $arrAll[] = $menu;
        }
        return $arrAll;
    }

    public function getAllByType($type){
        $strQuery = "SELECT *
                     FROM wt_profile_configuration
                     WHERE `type` = '{$type}'";
        $all = db_query($strQuery);
        $arrAll = [];
        foreach($all as $profile){
            $arrAll[] = $profile;
        }
        return $arrAll;
    }
    public function getPathById($id){
        $strQuery = "SELECT PATH
                     FROM wt_profile_configuration
                     WHERE id = '{$id}'";
        return sqlGetValueFromKey($strQuery);
    }
    public function setDefault($type){
        $strQuery = "UPDATE wt_profile_configuration
                     SET color = null, path = null
                     WHERE `type` = '{$type}'";
        return db_query($strQuery);
    }
    public function setNullField($id, $param){
        $strQuery = "UPDATE wt_profile_configuration
                     SET $param = null 
                     WHERE id = '{$id}'";
        return db_query($strQuery);
    }

    public function profileConfiguration($strSpecified){
        $strQuery = "SELECT path, title FROM wt_profile_configuration WHERE specified = '{$strSpecified}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getBy($field, $data, $boolCollection = false){
        $strQuery = "SELECT *
                     FROM wt_profile_configuration
                     WHERE `{$field}` = '{$data}'";
        if($boolCollection){
            return db_query($strQuery);
        }
        return sqlGetValueFromKey($strQuery);
    }

    public function getDataInfoCorreo(){
        $strQuery = "SELECT *
                    FROM wt_Profile_Configuration_Correo";
        $all = db_query($strQuery);
        $arrAll = [];
        foreach($all as $profile){
            $arrAll[] = $profile;
        }
        return $arrAll;
    }

    public function getTitle($id){
        $strQuery = "SELECT *
                    FROM wt_Profile_Configuration_Correo
                    WHERE  id = '{$id}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getLinkImageCorreo($id)
    {
        $strQuery = "SELECT link
                    FROM wt_Profile_Configuration_Correo
                    WHERE  id = '{$id}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getPathImageCorreo($id){
        $strQuery = "SELECT path
                    FROM wt_Profile_Configuration_Correo
                    WHERE  id = '{$id}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function deletTabNew($id){
        $strQuery ="DELETE FROM wt_Profile_Configuration_Correo WHERE id= $id";
        return db_query($strQuery);
    }
    public function setNewRegistres($id, $param){
        $strQuery = "UPDATE wt_Profile_Configuration_Correo
                     SET $param = null 
                     WHERE id = '{$id}'";
        return db_query($strQuery);
    }
    public function setNullFieldCorreo($id, $param){
        $strQuery = "UPDATE wt_Profile_Configuration_Correo
                     SET $param = null 
                     WHERE id = '{$id}'";
        return db_query($strQuery);
    }
    public function updateStatusShow($id, $is){
        $strQuery = "UPDATE wt_Profile_Configuration_Correo
                     SET `allow` = {$is}
                     where id = {$id}";
        $this->appendDebug($strQuery);
        return db_query($strQuery);

    }
}
