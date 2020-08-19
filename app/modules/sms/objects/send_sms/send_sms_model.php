<?php

class send_sms_model extends global_config implements window_model
{
    private static $_instance;

    public function __construct($arrParams = "")
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

    public function getProveedor()
    {
        return sqlGetValueFromKey("SELECT descripcion FROM wt_sms_config WHERE active = 'Y'");
    }

}