<?php
/**
 * Created by PhpStorm.
 * User: NelsonMatul-DEV
 * Date: 7/05/2018
 * Time: 8:02 PM
 */
include_once("core/global_config.php");
class organization_chart_model extends global_config implements window_model{
    private static $_instance;

    public function __construct($arrParams){
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function getUsers(){
        $strQuery = "SELECT U.uid, U.nombres, U.apellidos, U.father, U.swusertype, SWU.id_usertype,U.email,SWU.color, U.name
                        FROM wt_users AS U LEFT JOIN wt_swusertypes AS SWU
                          ON U.swusertype = SWU.name
                            WHERE active = 'Y'";
        $qTMP = db_query($strQuery);
        $arrResponse = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrResponse[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrResponse;
    }

    public function getRoles(){
        $strQuery = "SELECT id_usertype, descr,color from wt_swusertypes;";
        $qTMP = db_query($strQuery);
        $arrResponse = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrResponse[$rTMP["id_usertype"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrResponse;
    }
}