<?php
require_once "core/global_config.php";
require_once "modules/users/objects/register/register_view.php";
require_once "modules/users/objects/register/register_model.php";

class register_controller extends global_config implements window_controller
{
    private $strAction = "";
    private $boolUTF8 = true;
    private $boolPrintJson = false;
    private $siteKey = "6LfBqvMUAAAAADu6YLw6_Y-aSaepe1zkjpoH5SPw";
    private $shkey = "6LfBqvMUAAAAAI87ooO9gF_FSWxCZ6t-F-4spDdx";
    public function setStrAction($strAction)
    {
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
        if($this->checkParam("op") != ""){
            $this->setBoolUTF8(true);
            $this->setBoolPrintJson(true);

            $option = $this->checkParam("op");
            if($option == "getTown"){
                return $this->getTown();
            }
            if($option == "login"){
                return $this->registerUser();
            }
            if($option == "getAllBanks"){
                return $this->getAllBanks();
            }
            if($option == "getAllDepartments"){
                return $this->returnDepartment();
            }
            return false;
        }
        /*if($this->checkParam("login") != ""){
            $this->setBoolPrintJson(true);
            $this->setBoolUTF8(true);
            $option = $this->checkParam('login');
            if($option){
                return $this->registerUser();
            }

            return false;
        }*/
        $objView = new register_view($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->setSiteKey($this->siteKey);
        $objView->draw();
    }

    public function returnDepartment(){
        include_once("core/objects/country/country_model.php");
        $objCountry = new country_model();
        $arrDepartments = $objCountry->getDepartment();
        $arrCountry = $objCountry->getCountry(72);
        return response::standard(1, 'Departamentos obtenidos', ["data" => $arrDepartments, "country" => $arrCountry], $this->boolUTF8, $this->boolPrintJson);
    }

    public function getTown(){
        include_once("core/objects/country/country_model.php");
        $intId = $this->checkParam('id', false, 0);
        if($intId){
            $objCountry = new country_model();
            $arrTowns = $objCountry->getCity($intId);
            return response::standard(1, 'Municipios obtenidos', ["data" => $arrTowns], $this->boolUTF8, $this->boolPrintJson);
        }
    }

    public function getAllBanks(){
        $model = new register_model();
        $banks = $model->getAllActiveBanks();
        return response::standard(1, 'Bancos obtenidos', ["data" => $banks], $this->boolUTF8, $this->boolPrintJson);
    }

    public function registerUser(){
        $secret = $this->shkey;
        $response = $this->checkParam('g-recaptcha-response');
        $ip = $_SERVER['REMOTE_ADDR'];

        $dav = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$response."&remoteip=".$ip);

        $res = json_decode($dav,true);

        if($res['success']) {
            include_once("modules/users/objects/user_merchanting/user_merchanting_controller.php");
            $objMerchanting = new user_merchanting_controller($this->arrParams);
            $objMerchanting->setBoolUTF8(false);
            $response = $objMerchanting->registerCommerce();
            return response::standard($response["valido"], $response["razon"], [], $this->boolUTF8, $this->boolPrintJson);
        }
        else{
            return response::standard(0, 'Ocurrio un error al validar ReCaptcha.', [], $this->boolUTF8, $this->boolPrintJson);
        }
    }
}