<?php
/**
 * Created by PhpStorm.
 * User: HMLDEV-ALEX
 * Date: 5/01/2018
 * Time: 4:38 PM
 */

class app_control_model extends global_config implements window_model {

    private static $_instance;

    public function __construct($arrParams = ""){
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function getOS() {
        $strQuery = "SELECT * FROM wt_app_control_os ORDER BY os ASC";
        $qTMP = db_query($strQuery);
        $arrData = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrData[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrData;
    }

    public function getApps() {
        $strQuery = "SELECT * FROM wt_app_control_names";
        $qTMP = db_query($strQuery);
        $arrData = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrData[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrData;
    }

    public function getVersionsApp( $os,  $app) {
        $strQuery = "SELECT * FROM wt_app_control_versions WHERE id_os = '{$os}' AND id_app = '{$app}'";
        $qTMP = db_query($strQuery);
        $arrData = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $rTMP["fecha_publicado"] = date("d-m-Y",strtotime($rTMP["fecha_publicado"]));
                $rTMP["fecha_registro"] = date("d-m-Y",strtotime($rTMP["fecha_registro"]));
                $rTMP["fixes"] = $this->getFixes($rTMP["id"]);
                $rTMP["bugs"] = $this->getBugs($rTMP["id"]);
                $arrData[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrData;
    }

    private function getFixes( $version){
        $strQuery = "SELECT * FROM wt_app_control_versions_fix WHERE id_version = '{$version}'";
        $qTMP = db_query($strQuery);
        $arrData = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrData[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrData;
    }

    private function getBugs( $version){
        $strQuery = "SELECT * FROM wt_app_control_versions_bugs WHERE id_version = '{$version}'";
        $qTMP = db_query($strQuery);
        $arrData = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrData[] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrData;
    }

    public function deleteApp( $app){
        $strQuery = "DELETE FROM wt_app_control_versions WHERE id_app = '{$app}'";
        db_query($strQuery);
        $strQuery = "DELETE FROM wt_app_control_names WHERE id = '{$app}'";
        db_query($strQuery);
    }

    public function deleteVersion( $version){
        $strQuery = "DELETE FROM wt_app_control_versions WHERE id = '{$version}'";
        db_query($strQuery);
    }

    public function deleteOS( $os){
        $strQuery = "DELETE FROM wt_app_control_versions WHERE id_os = '{$os}'";
        db_query($strQuery);
        $strQuery = "DELETE FROM wt_app_control_os WHERE id = '{$os}'";
        db_query($strQuery);
    }

    public function deleteFix( $fix){
        $strQuery = "DELETE FROM wt_app_control_versions_fix WHERE id = '{$fix}'";
        db_query($strQuery);
    }

    public function deleteBug( $bug){
        $strQuery = "DELETE FROM wt_app_control_versions_bugs WHERE id = '{$bug}'";
        db_query($strQuery);
    }
}