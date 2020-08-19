<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 7/02/2017
 * Time: 15:26
 */
include_once("core/global_config.php");
include_once("modules/users/objects/emulate/emulate_model.php");
include_once("modules/users/objects/emulate/emulate_view.php");
class emulate_controller extends global_config implements window_controller{

	private $boolPrintJson = false;
	private $boolUTF8 = true;
    private $strAction = "";
    public function __construct($arrParams){
        parent::__construct($arrParams);
    }

    public function setStrAction($strAction){
        $this->strAction = $strAction;
    }
	/**
	 * @param bool $boolPrintJson
	 */
	public function setBoolPrintJson( $boolPrintJson)
	{
		$this->boolPrintJson = $boolPrintJson;
	}

	/**
	 * @param bool $boolUTF8
	 */
	public function setBoolUTF8( $boolUTF8)
	{
		$this->boolUTF8 = $boolUTF8;
	}

	public function main(){

        if(!check_user_class($this->config["admmenu"][$this->lang["CHANGE_USER_TO_TEST"]]["class"]))die($this->lang["ACCESS_DENIED"]);

        if(isset($this->arrParams["op"])) {
            $option = db_escape($this->arrParams["op"]);
            if($option == "getUserAux") return $this->getUserAux();
        }

        if(isset($this->arrParams["boolReport"])){
            $objModel = emulate_model::getInstance($this->arrParams);
            $uidUserAux = $this->checkParam("IDUserAux",false,0,true);
            $boolAux = false;
            if($uidUserAux > 0){
                $boolAux = true;
                /*$arrFamily = $objModel->getFamilyUserAux($uidUserAux);*/
            }
            $objModel->report_users($boolAux, $uidUserAux);
            die;
        }

        if(isset($this->arrParams["hidUserToTest"])){
            $objModel = emulate_model::getInstance($this->arrParams);
            $objModel->emulate_user();
        }
        if(isset($this->arrParams["revertUser"]) && isset($_SESSION["wt"]["originalUserToTest"])){
            $objModel = emulate_model::getInstance($this->arrParams);
            $objModel->revert_user();
        }

        $objView = emulate_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();
    }

    public function getUserAux(){
        $objModel = emulate_model::getInstance($this->arrParams);
        $arrUserAux = $objModel->getUserAux($_SESSION["wt"]["uid"]);
        $arrResponse = array();
        if($arrUserAux){
            $arrResponse["uidAux"] = $arrUserAux;
            response::standard(1,"Usuarios auxiliar.",$arrResponse,true,true);
        }
        else{
            response::standard(1,"No es auxiliar.",$arrResponse,true, true);
        }
    }
}