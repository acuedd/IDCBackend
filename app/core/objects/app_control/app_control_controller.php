<?php
/**
 * Created by PhpStorm.
 * User: Alexander Flores
 * Date: 5/01/2018
 * Time: 4:34 PM
 */

include_once("core/global_config.php");
include_once("core/objects/app_control/app_control_model.php");
class app_control_controller extends global_config implements window_controller {

    private $strAction = "";
	private $boolPrintJson = false;
	private $boolUTF8 = true;

    public function __construct($arrParams = ""){
        parent::__construct($arrParams);
    }

    public function setStrAction($strAction){
        $this->strAction = $strAction;
    }

	public function setBoolPrintJson($boolPrintJson)
	{
		$this->boolPrintJson = $boolPrintJson;
	}

	public function setBoolUTF8($boolUTF8)
	{
		$this->boolUTF8 = $boolUTF8;
	}

    public function main(){
        if(!check_user_class($this->config["admmenu"][$this->lang["APP_CONTROL"]]["class"]))die($this->lang["ACCESS_DENIED"]);

        $this->request();
        draw_header($this->lang["APP_CONTROL"]);
        theme_draw_centerbox_open();

        jquery_includeLibrary("datatables");
        jquery_includeLibrary("moment");
        jquery_includeLibrary("datetimerpicker");

        $objTPL = new Template("core/objects/app_control/view/app_control.tpl");
        $objTPL->set("strAction",$this->strAction);
        print $objTPL->output();

        theme_draw_centerbox_close();
        draw_footer();
    }

    /**
     * Metodo que recibe todos los request para este objeto
     */
    private function request(){
        $op = $this->checkParam("op");
        $objModel = app_control_model::getInstance($this->arrParams);
        if(!empty($op)){
            if($op == "init"){
                $arrOS = $objModel->getOS();
                $arrApps = $objModel->getApps();
                response::standard(1,"Datos",array("os"=>$arrOS,"apps"=>$arrApps),true,true);
            }
            else if($op == "save"){
                $intID = (int) $this->checkParam("txt_id_app",false,0,true);
                $strName = (string) $this->checkParam("txt_name_app",false,"",true);
                $strUnique = (string) $this->checkParam("txt_unique_app",false,"",true);
                $strApiKey = $this->checkParam('api_key', false, '');
                $id = $this->saveApp($intID,$strName,$strUnique, $strApiKey);
                if($id){
                    response::standard(1,"Datos guardados correctamente",array("id"=>$id),true,true);
                }
                else{
                    response::standard(0,"Hubo un problema al guardar los datos",array(),true,true);
                }
            }
            else if($op == "delete"){
                $intID = (int) $this->checkParam("id");
                $this->deleteApp($intID);
                response::standard(1,"Se elimino aplicación correctamente",array(),true,true);
            }
            else if($op == "saveOS"){
                $intOs = (int) $this->checkParam("idOs");
                $strName = (string) $this->checkParam("txtOS",false,0,true);
                $id = $this->saveOs($intOs,$strName);
                if($id){
                    response::standard(1,"Datos guardados correctamente",array("id"=>$id),true,true);
                }
                else{
                    response::standard(0,"Hubo un problema al guardar los datos",array(),true,true);
                }
            }
            else if($op == "versions"){
                $idApp = (int) $this->checkParam("idApp");
                $idOs = (int) $this->checkParam("idOs");
                $arrVersions = $this->versionsApps($idApp, $idOs);
                response::standard(1,"Datos",array("versions"=>$arrVersions),true,true);
            }
            else if($op == "saveVersion"){
                $version = (int) $this->checkParam("txtVersion",false,0,true);
                $idApp = (int) $this->checkParam("txtApp",false,0,true);
                $idOs = (int) $this->checkParam("txtOs",false,0,true);
                $strName = (string) $this->checkParam("txtName",false,0,true);
                $strDate = (string) $this->checkParam("txtDate",false,0,true);
                $strPermitted = (string) $this->checkParam("txtPermitted");
                $id = $this->saveVersion($version,$idApp,$idOs,$strName,$strDate,$strPermitted);
                if($id){
                    response::standard(1,"Datos guardados correctamente",array("id"=>$id),true,true);
                }
                else{
                    response::standard(0,"Hubo un problema al guardar los datos",array(),true,true);
                }
            }
            else if($op == "deleteVersion"){
                $intID = (int) $this->checkParam("versionID");
                $objModel->deleteVersion($intID);
                response::standard(1,"Se elimino versión correctamente",array(),true,true);
            }
            else if($op == "deleteOS"){
                $intID = (int) $this->checkParam("os");
                $objModel->deleteOS($intID);
                response::standard(1,"Se elimino sistema operativo correctamente",array(),true,true);
            }
            else if($op == "saveFix"){
                $intID = (int) $this->checkParam("txtID");
                $txtFix = (string) $this->checkParam("txtFix",false,0,true);
                $intVersion = (int) $this->checkParam("idVersion");
                $id = $this->saveFix($intID,$txtFix,$intVersion);
                if($id){
                    response::standard(1,"Datos guardados correctamente",array("id"=>$id),true,true);
                }
                else{
                    response::standard(0,"Hubo un problema al guardar los datos",array(),true,true);
                }
            }
            else if($op == "saveBug"){
                $intID = (int) $this->checkParam("txtID");
                $txtBug = (string) $this->checkParam("txtBug",false,0,true);
                $intVersion = (int) $this->checkParam("idVersion");
                $id = $this->saveBug($intID,$txtBug,$intVersion);
                if($id){
                    response::standard(1,"Datos guardados correctamente",array("id"=>$id),true,true);
                }
                else{
                    response::standard(0,"Hubo un problema al guardar los datos",array(),true,true);
                }
            }
            else if($op == "deleteFix"){
                $intID = (int) $this->checkParam("idFix");
                $objModel->deleteFix($intID);
                response::standard(1,"Eliminado correctamente",array(),true,true);
            }
            else if($op == "getNotificationTypes"){
                $this->getNotificationTypes();
            }
            else if($op == "deleteBug"){
                $intID = (int) $this->checkParam("idBug");
                $objModel->deleteBug($intID);
                response::standard(1,"Eliminado correctamente",array(),true,true);
            }
            die;
        }
    }

    private function saveApp( $id, $name, $unique, $api_key){
        $arrKey = array();
        $arrFields = array();

        $arrKey["id"] = $id;
        $arrFields["name"] = $name;
        $arrFields["name_unique"] = $unique;
        $arrFields["api_key"] = $api_key;
        $this->sql_tableupdate("wt_app_control_names",$arrKey,$arrFields);
        return ($id)?$id:db_insert_id();
    }

    private function deleteApp( $app){
        $objModel = app_control_model::getInstance($this->arrParams);
        $objModel->deleteApp($app);
    }

    private function saveOs( $os, $name){
        $arrKey = array();
        $arrFields = array();

        $arrKey["id"] = $os;
        $arrFields["os"] = $name;

        $this->sql_tableupdate("wt_app_control_os",$arrKey,$arrFields);
        return ($os)?$os:db_insert_id();
    }

    private function versionsApps( $app,  $os){
        $objModel = app_control_model::getInstance($this->arrParams);
        return $objModel->getVersionsApp($os,$app);
    }

    private function saveVersion( $version,  $app,  $os,  $name,  $date,  $permitted){
        $arrKey = array();
        $arrFields = array();

        $arrKey["id"] = $version;
        $arrFields["id_app"] = $app;
        $arrFields["id_os"] = $os;
        $arrFields["version"] = $name;
        $arrFields["publicada"] = (!empty($date))?"Y":"N";
        $arrFields["permitido"] = $permitted;
        if(!empty($date))$date = date("Y-m-d",strtotime($date));
        $arrFields["fecha_publicado"] = $date;
        if(!$version){
            $arrFields["fecha_registro"] = "NOW()";
            $arrFields["regBy"] = $_SESSION["wt"]["uid"];
        }
        $this->sql_tableupdate("wt_app_control_versions",$arrKey,$arrFields);
        return ($version)?$version:db_insert_id();
    }

    private function saveFix( $id,  $fix,  $version){
        $arrKey = array();
        $arrFields = array();

        $arrKey["id"] = $id;
        $arrFields["description"] = $fix;
        $arrFields["id_version"] = $version;

        $this->sql_tableupdate("wt_app_control_versions_fix",$arrKey,$arrFields);
        return ($id)?$id:db_insert_id();
    }

    private function saveBug( $id,  $bug,  $version){
        $arrKey = array();
        $arrFields = array();

        $arrKey["id"] = $id;
        $arrFields["description"] = $bug;
        $arrFields["id_version"] = $version;

        $this->sql_tableupdate("wt_app_control_versions_bugs",$arrKey,$arrFields);
        return ($id)?$id:db_insert_id();
    }
}