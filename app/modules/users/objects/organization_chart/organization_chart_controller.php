<?php
/**
 * Created by PhpStorm.
 * User: NelsonMatul-DEV
 * Date: 7/05/2018
 * Time: 8:02 PM
 */
include_once("core/global_config.php");
include_once("modules/users/objects/organization_chart/organization_chart_model.php");
include_once("modules/users/objects/organization_chart/organization_chart_view.php");
class organization_chart_controller extends global_config implements window_controller{

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
        if(!check_user_class($this->config["admmenu"][$this->lang["USER_ORGANIZATION_CHART"]]["class"]))die($this->lang["ACCESS_DENIED"]);
        if(isset($this->arrParams["op"])){
            $option = db_escape($this->arrParams["op"]);
            if($option == "getOrganizationChart") return $this->getOrganizationChart();
            if($option == "getRoles") return $this->getRoles();
        }

        $objView = organization_chart_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();
    }

    public function getOrganizationChart(){
        $objModel = organization_chart_model::getInstance($this->arrParams);
        $arrResponse = array();
        $arrResponse["users"] = $objModel->getUsers();
        response::standard(1,"Usuarios obtenidos correctamente",$arrResponse,true,true);
    }

    public function getRoles(){
        $objModel = organization_chart_model::getInstance($this->arrParams);
        $arrResponse = array();
        $arrResponse["roles"] = $objModel->getRoles();
        response::standard(1,"Roles obtenidos correctamente.",$arrResponse,true,true);
    }
}