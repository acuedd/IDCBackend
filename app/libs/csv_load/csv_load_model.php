<?php
/**
 * Created by PhpStorm.
 * User: Alexander Flores
 * Date: 19/09/2017
 * Time: 16:14
 */

include_once('core/global_config.php');
class csv_load_model extends global_config{

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
        return sqlGetValueFromKey("SELECT * FROM wt_csv_load WHERE id = '{$load}'");
    }

    public function getValidateData($keyword){

        return sqlGetValueFromKey("SELECT * FROM wt_csv_load_process WHERE word_key = '{$keyword}'");
    }

    public function clearBala($bace){
        $strQuery = "TRUNCATE TABLE {$bace}";
        $this->appendDebug($strQuery);
        db_query($strQuery);
    }

    public function serchData($name){
        $strQuery = "SELECT file FROM wt_csv_load WHERE id ='{$name}'";
        $this->appendDebug($strQuery);
        return sqlGetValueFromKey($strQuery);
    }
}