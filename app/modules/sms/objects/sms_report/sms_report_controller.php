<?php
include_once("core/global_config.php");
include_once("modules/sms/objects/sms_report/sms_report_model.php");
include_once("modules/sms/objects/sms_report/sms_report_view.php");

class sms_report_controller extends global_config implements window_controller
{

    private $boolPrintJson = false;
    private $boolUTF8 = true;
    private $strAction = "";

    public function __construct($arrParams = "")
    {
        parent::__construct($arrParams);
    }

    public function setStrAction($strAction)
    {
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

    public function main()
    {
        if (!check_user_class($this->config["admmenu"][$this->lang["SMS_CONFIG"]]["class"])) die($this->lang["ACCESS_DENIED"]);
        if($this->checkParam("op") != ""){
            $this->setBoolPrintJson(true);
            $this->setBoolUTF8(true);
            $option = $this->checkParam('op');

            if($option == 'getAllMessages'){
                return $this->getAllMessages();
            }

            return false;
        }

        $objModel = sms_report_model::getInstance();
        $objView = sms_report_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();

    }

    public function getAllMessages(){
        $objModel = sms_report_model::getInstance();
        $messages = $objModel->getMessagesDB();
        return response::standard(1, 'Obtenido', ["data" => $messages], $this->boolUTF8, $this->boolPrintJson);
    }
}