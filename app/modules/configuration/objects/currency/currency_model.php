<?php

include_once("core/global_config.php");

class currency_model extends global_config implements window_model
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

    public function getCurrencies($intID = 0){
        $arrReturn = [];
        $strFilter = "";
        if(!empty($intID)){
            $strFilter = "id = '{$intID}'";
        }
        $strQuery = "SELECT * FROM wt_currency WHERE 1 {$strFilter} ORDER BY pivot ASC";
        $qTMP = db_query($strQuery);
        while($rTMP = db_fetch_assoc($qTMP)){
            array_push($arrReturn, $rTMP);
        }
        return $arrReturn;
    }

    public function getCurrenciesBySymbol($strSymbol){
        $arrReturn = [];
        $strQuery = "SELECT id, rounding, symbol, decimal_digits, `name`, pivot, area_code FROM wt_currency WHERE area_code = '{$strSymbol}'";
        //$strQuery = "SELECT * FROM wt_currency WHERE symbol = '{$strSymbol}'";
        $qTMP = db_query($strQuery);
        while($rTMP = db_fetch_assoc($qTMP)){
            $arrReturn[$rTMP["area_code"]] = $rTMP;
        }
        return $arrReturn;
    }
}