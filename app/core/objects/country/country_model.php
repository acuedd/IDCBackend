<?php
require_once "core/global_config.php";

class country_model extends global_config implements window_model
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

    public function getCountry($intId = 0){
        $strFilter = "";
        if($intId){
            $strFilter .= "WHERE id = '{$intId}'";
        }
        $strQuery = "SELECT * FROM wt_paises $strFilter";
        $this->appendDebug($strQuery);
        if($strFilter){
            $rResult = db_query($strQuery);
            $arrCountry = [];
            foreach($rResult as $country){
                $arrCountry[] = $country;
            }
            return $arrCountry;
        }
        else{
            return sqlGetValueFromKey($strQuery);
        }
    }

    public function getState($intCountry){
        $strQuery = "";
    }

    public function getDepartment(){
        $strQuery = "SELECT * FROM wt_departamentos";
        $rResult = db_query($strQuery);
        $this->appendDebug($strQuery);
        $arrDepartment = [];
        foreach($rResult as $department){
            $arrDepartment[] = $department;
        }
        return $arrDepartment;
    }

    public function getCity($intIdDepartment){
        $strQuery = "SELECT * FROM wt_municipios where departamento = '{$intIdDepartment}'";
        $rResult = db_query($strQuery);
        $this->appendDebug($strQuery);
        $arrDepartment = [];
        foreach($rResult as $department){
            $arrDepartment[] = $department;
        }
        return $arrDepartment;
    }
}