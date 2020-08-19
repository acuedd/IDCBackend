<?php

include_once "core/global_config.php";

class push_notifications_model extends global_config implements window_model
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

    public function getListNotify($boolActive){
        $strFilter = "";
        /*if($boolActive){
            $strFilter += " WHERE notify_active = 1 ";
        }*/
        $strQuery = "SELECT * FROM wt_merchanting_notify_list";
        $this->appendDebug($strQuery);
        $rQuery = db_query($strQuery);
        $arrNotify = [];
        foreach($rQuery as $element){
            $arrNotify[] = $element;
        }
        return $arrNotify;
    }
}