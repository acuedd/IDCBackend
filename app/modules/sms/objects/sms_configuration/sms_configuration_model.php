<?php
include_once "core/global_config.php";

class sms_configuration_model extends global_config implements window_model
{
    private static $_instance;

    public function __construct(&$arrParams = "")
    {
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams = "")
    {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function getConfigurationBy($id){
        $strQuery = "SELECT * FROM wt_sms_config WHERE id = $id";
        return sqlGetValueFromKey($strQuery);
    }

    public function getAllConfigurationDB(){
        $strQuery = "SELECT * FROM wt_sms_config WHERE id > 0;";
        $arrConfiguration = db_query($strQuery);
        $arrAll = [];
        if($arrConfiguration){
            foreach($arrConfiguration as $configuration){
                $arrAll[] = $configuration;
            }
        }
        return $arrAll;
    }

}