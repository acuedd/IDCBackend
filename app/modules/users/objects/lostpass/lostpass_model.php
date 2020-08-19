<?php
/**
 * Created by PhpStorm.
 * User: HMLDEV-ALEX
 * Date: 3/08/2017
 * Time: 14:24
 */

class lostpass_model extends global_config implements window_model{

    private static $_instance;

    function __construct($arrParams = ""){
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function getInfoUser($user){
        $strQuery = "SELECT uid,name,email, tel_cel FROM wt_users WHERE name = '{$user}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function setPassword($uid,$password){

        $strSet = " password = MD5('{$password}')";
        if(!empty($this->cfg["users"]["Save_Unencrypted_pwd"])){
            $strSet .= ", uepassword = '{$password}'";
        }
        $strQuery = "UPDATE wt_users SET {$strSet} WHERE uid = '{$uid}'";
        db_query($strQuery);
    }

    public function getPhoneUser($user)
    {
        $strQuery = "SELECT tel_cel FROM wt_users WHERE name = '{$user}'";
        return sqlGetValueFromKey($strQuery);
    }
}