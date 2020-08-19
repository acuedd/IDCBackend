<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 15/02/2017
 * Time: 09:46
 */
include_once("core/global_config.php");
include_once("modules/users/objects/user_profile/user_profile_model.php");
include_once("modules/users/objects/user_profile/user_profile_view.php");
class user_profile_controller extends global_config implements window_controller {

	private $boolPrintJson = false;
	private $boolUTF8 = true;
    private $strAction = "";
    public function __construct($arrParams){
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

    public function main()
    {
        if(!check_user_class($this->config["admmenu"][$this->lang["ADM_USERACCESS_ADMIN_PROFILE"]]["class"]))die($this->lang["ACCESS_DENIED"]);

        if(isset($this->arrParams["op"])){
            $this->setBoolUTF8(true);
            $this->setBoolPrintJson(true);
            $option = db_escape($this->arrParams["op"]);
            if($option == "access"){
                $arrTMP["access"] = $this->getAccess();
                $arrTMP["categories"] = $this->getCategories();
                $arrTMP["categoriesMovil"] = $this->getCategoriesMovil();
                return response::standard(1,"accesos",$arrTMP,$this->boolUTF8,$this->boolPrintJson);
            }
            if($option == "profiles"){
                $arrTMP["profiles"] = $this->getProfiles();
                return response::standard(1,"perfiles",$arrTMP,$this->boolUTF8,$this->boolPrintJson);
            }
            if($option == "detail"){
                return $this->getDetailProfile();
            }
            if($option == "save"){
                return $this->saveProfile();
            }
            if($option == "delete"){
                return $this->deleteProfile();
            }
            if($option == "getCategories"){
                return $this->getCategories();
            }
            return;
        }

        $objView = user_profile_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();
    }

    public function getProfiles(){
        $objModel = user_profile_model::getInstance($this->arrParams);
        return $objModel->getProfiles();
    }

    public function getAccess(){
        $arrAccess = array();
        foreach($this->config["admmenu"] AS $key => $val){
            if (!isset($val["class"])) continue;
            if ($val["class"] == "admin") continue;
            if ($val["class"] == "freeAccess") continue;

            if(!isset($arrAccess[$val["module"]]))
                $arrAccess[$val["module"]] = array();

            $arrTMP = array();
            $arrTMP["class"] = $val["class"];
            $arrTMP["clean"] = str_replace("/","",$val["class"]);
            $arrTMP["description"] = $val["name"];
            array_push($arrAccess[$val["module"]],$arrTMP);

            unset($val);
            unset($key);
        }

        foreach($this->config["extra_access"] AS $key => $val){
            if ($key == "admin") continue;
            if ($key == "freeAccess") continue;


            if(!isset($arrAccess[$val["module"]]))
                $arrAccess[$val["module"]] = array();

            $arrTMP = array();
            $arrTMP["class"] = $key;
            $arrTMP["clean"] = str_replace("/","",$key);
            $arrTMP["description"] = $val["descripcion"];
            array_push($arrAccess[$val["module"]],$arrTMP);

            unset($val);
            unset($key);
        }
        return $arrAccess;
    }

    public function getDetailProfile(){
        $intProfile = $this->checkParam("id",false,0);
        if($intProfile){
            $objModel = user_profile_model::getInstance($this->arrParams);
            $arrTMP = array();
            $arrTMP["detail"] = $objModel->getProfile($intProfile);
            $arrTMP["access"] = $objModel->getAccess($intProfile,true);
            if(check_module("sales")){
                $arrTMP["categories"] = $objModel->getAccessCategory($intProfile);
            }
            return response::standard(1, "Perfil de acceso", $arrTMP, $this->boolUTF8, $this->boolPrintJson);
        }

        return response::standard(0, "Perfil de acceso inválido", array(), $this->boolUTF8, $this->boolPrintJson);
    }

    public function saveProfile(){
        if(empty($this->arrParams["access"])){
            return response::standard(0, "No existen accesos seleccionado", array(), $this->boolUTF8, $this->boolPrintJson);
        }


        $objModel = user_profile_model::getInstance($this->arrParams);
        $arrKey = array();
        $arrValues = array();

        $arrKey["id"] = $this->checkParam("sltProfile",false,0);
        $arrValues["nombre"] = $this->checkParam("name",false,"",true);
        $arrValues["descripcion"] = $this->checkParam("desc",false,"",true);
        $arrValues["last_modified"] = "NOW()";

        if(!$objModel->validateNameProfile($arrValues["nombre"]) || $arrKey["id"]){
            if($objModel->sql_tableupdate("wt_user_access_perfiles",$arrKey,$arrValues)){
                $intProfile = ($arrKey["id"])?$arrKey["id"]:db_insert_id();


                $this->saveCategoriesByProfileAccess($intProfile);


                $objModel->deleteAccessProfile($intProfile);

                $arrUsers = $objModel->userWithProfile($intProfile);

                //Elimino los accesos para los usuarios y elimino duplicidad en asginacion
                if(!empty($this->arrParams["access"]) && count($this->arrParams["access"]) > 0){
                    foreach($arrUsers AS $val){
                        $objModel->deleteAndAssiggProfileUser($val,$intProfile);
                        unset($val);
                    }
                }

                foreach($this->arrParams["access"] AS $val){
                    $strAccess = db_escape($val);
                    $objModel->saveAccessProfile($intProfile, $strAccess);
                    foreach($arrUsers AS $userid){
                        $objModel->asiggnmentAccess($userid,$strAccess);
                        unset($userid);
                    }
                    unset($val);
                }

                return response::standard(1, "Datos guardados exitosamente", array(), $this->boolUTF8, $this->boolPrintJson);
            }

            return response::standard(0, "Error al guardar Perfil", array(), $this->boolUTF8, $this->boolPrintJson);
        }

        return response::standard(0, "El nombre del perfil ya existe", array(), $this->boolUTF8, $this->boolPrintJson);
    }

    public function saveCategoriesByProfileAccess($intProfile)
    {
        if(!empty($intProfile)){
            if(check_module("sales")){
                $objModel = user_profile_model::getInstance($this->arrParams);
                $objModel->deleteCategoriesExistByProfile($intProfile);
                if(!empty($this->arrParams["category"])){
                    foreach ($this->arrParams["category"] AS $key => $value){
                        $arrKey = array();
                        $arrKey["id"] = 0;
                        $arrValue = array();
                        $arrValue["id_profile"] = $intProfile;
                        $arrValue["id_category"] = $value;
                        $arrValue["type"] = "product";
                        $this->sql_tableupdate("wt_user_access_categories" , $arrKey, $arrValue);
                    }
                }
                if(!empty($this->arrParams["categoryMovil"])){
                    foreach ($this->arrParams["categoryMovil"] AS $key => $value){
                        $arrKey = array();
                        $arrKey["id"] = 0;
                        $arrValue = array();
                        $arrValue["id_profile"] = $intProfile;
                        $arrValue["id_category"] = $value;
                        $arrValue["type"] = "movil";
                        $this->sql_tableupdate("wt_user_access_categories" , $arrKey, $arrValue);
                    }
                }
            }
        }
    }

    public function deleteProfile(){
        $intProfile = $this->checkParam("id");
        if($intProfile){
            $objModel = user_profile_model::getInstance($this->arrParams);
            if(!$objModel->userWithProfile($intProfile,true)){
                if($objModel->deleteProfile($intProfile)){
                    $objModel->deleteCategoriesByProfileID($intProfile);
                    return response::standard(1, "Perfil eliminado correctamente", array(), $this->boolUTF8, $this->boolPrintJson);
                }

                return response::standard(0, "Error al eliminar perfil", array(), $this->boolUTF8, $this->boolPrintJson);
            }

            return response::standard(0,"No se puede eliminar perfil porque hay usuarios asignados a este.",array(),true,true);
        }

        return response::standard(0, "Error al eliminar Perfil", array(), $this->boolUTF8, $this->boolPrintJson);
    }

    public function getCategories()
    {
        if(check_module("sales")){
            $objModel = user_profile_model::getInstance($this->arrParams);
            $arrCategories = array();
            $arrCategories["categories"] = $objModel->getCategories();
            if(!empty($arrCategories["categories"]))
                return $arrCategories["categories"];
        }
        
        return [];
    }

    public function getCategoriesMovil()
    {
        if(check_module("sales")){
            include_once("modules/sales/objects/proposal/proposal_model.php");
            $objModel = proposal_model::getInstance(array());
            $arrCategosMovil = $objModel->getPlanCategories();
            $arrCategos = [];
            foreach($arrCategosMovil AS $key => $value){
                if(!empty($value["access"])){
                    $arrCategos[$value["access"]]["category_code"] = $value["access"];
                    $arrCategos[$value["access"]]["category_name"] = $value["category_name"];
                    $arrCategos[$value["access"]]["id_category"] = $value["id_category"];
                    $arrCategos[$value["access"]]["type"] = "movil";
                }
            }
            return $arrCategos;
        }

        return [];
    }

    public function getSWUserTypes()
    {
        $objModel = user_profile_model::getInstance($this->arrParams);
        return $objModel->getSWUserTypes();
    }
}