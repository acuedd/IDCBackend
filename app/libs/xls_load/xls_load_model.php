<?php
/**
 * Created by PhpStorm.
 * User: Alexander Flores
 * Date: 19/09/2017
 * Time: 16:14
 */

include_once('core/global_config.php');
class xls_load_model extends global_config{

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

    public function getInfoLoad($load){
        return sqlGetValueFromKey("SELECT * FROM wt_xls_load WHERE id = '{$load}'");
    }

    public function setStatusLoad($load, $status = "insert"){
        db_query("UPDATE wt_xls_load SET status = '{$status}' WHERE id = '{$load}'");
    }

    public function getInfoSheet($load,$sheet){
        return sqlGetValueFromKey("SELECT * FROM wt_xls_load_sheets WHERE id_load = '{$load}' AND sheet = '{$sheet}'");
    }

    public function getCountSheetData($id_sheet){
        return sqlGetValueFromKey("SELECT COUNT(*) FROM wt_xls_load_data WHERE id_sheet = '{$id_sheet}'");
    }

    public function getValidateData($keyword){
        return sqlGetValueFromKey("SELECT * FROM wt_xls_load_process WHERE word_key = '{$keyword}'");
    }

    public function getInfoSheets($load){
        $strQuery = "SELECT * FROM wt_xls_load_sheets WHERE id_load = '{$load}'";
        $qTMP = db_query($strQuery);
        $arrSheets = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrSheets[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrSheets;
    }

    public function getDataLineBySheet($sheet){
        $strQuery = "SELECT * FROM wt_xls_load_data WHERE id_sheet = '{$sheet}'";
        $qTMP = db_query($strQuery);
        $arrData = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrData[$rTMP["line"]] = unserialize($rTMP["data"]);
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrData;
    }

    public function setExtra($load,$sheet, $extra = ""){
        $strQuery = "UPDATE wt_xls_load_sheets SET params = '{$extra}' WHERE id_load = '{$load}' AND sheet = '{$sheet}'";
        db_query($strQuery);
    }
}