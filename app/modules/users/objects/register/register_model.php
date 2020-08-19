<?php
require_once "core/global_config.php";

class register_model extends global_config implements window_model
{

    public function __construct(&$arrParams = "")
    {
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams)
    {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function getAllActiveBanks(){
        $strQuery = "SELECT * FROM wt_bank_names WHERE bank_active = 'Y'";
        $strResult = db_query($strQuery);
        $arrAllBanks = [];
        foreach ($strResult as $bank){
            $arrAllBanks[] = $bank;
        }
        return $arrAllBanks;
    }

    public function getLoginCaption(){
        $strQuery = "SELECT * FROM wt_caption_info WHERE caption_position = 'caption-login'";
        return sqlGetValueFromKey($strQuery);
    }
}