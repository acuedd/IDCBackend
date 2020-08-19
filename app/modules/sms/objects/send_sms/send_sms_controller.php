<?php
/**
 * Created by PhpStorm.
 * User: nelsonrodriguez
 * Date: 13/04/2020
 * Time: 12:41
 */
require_once("core/global_config.php");
require_once("webservices/webservices_library.php");
require_once("webservices/webservices_baseclass.php");
include_once "modules/sms/objects/send_sms/send_sms_model.php";

class sms_send_controller extends global_config implements window_controller
{
    private $boolTC = false;
    private $empresa = 0;
    private $strLocalidad = "";
    private $strAuditNumber = "";
    private $strMoneda = "Q. ";
    private $sinMonto = "";
    private $intTelefono = 0;
    private $strMensaje = "";
    private $strOpt = "";
    private $boolPrintJson = false;
    private $boolUTF8 = true;
    private $strAction = "";

    public function __construct($arrParams = array())
    {
        //parent::__construct($strCodigoOperacion, $arrInfoOperacion);
        parent::__construct($arrParams);
        /*$this->setModosPermitidos(array("w", "am","wm")); //am,w,wm
        $this->setFormatosPermitidos(array("json","xmlno","xmlwa")); //xmlwa,xmlno,json*/
        $this -> boolTC = check_module("credit_card");
    }

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
        if(!empty($this->arrParams["op"])){
            $this->setBoolPrintJson(true);
            $this->setBoolUTF8(true);
        }
        // TODO: Implement main() method.
    }

    /**
     * @param string $strMensaje
     */
    public function setStrMensaje($strMensaje)
    {
        $this->strMensaje = $strMensaje;
    }

    public function send_sms($boolRemitente = false, $intPhone = false, $strMessage = "")
    {
        $this->setStrMensaje($this->getParam("message", false, $strMessage));
        $boolsender = $this->getParam("sender",false, "");
        $boolRemitente = ($boolsender == "true")?true:$boolRemitente;
        $objModel = send_sms_model::getInstance($this->arrParams);
        $strProveedor = $objModel->getProveedor();
        $objSMS = new startSMS($strProveedor);
        if(is_bool($boolRemitente) && $boolRemitente){
            $objSMS->setRemitente("{$this -> cfg["core"]["title"]}:");
        }
        if(empty($this->intTelefono)) {
            if(!empty($this->arrParams["msisdn"])){
                $this->intTelefono = $this->getParam("msisdn",false,0);
            }
            elseif(!empty($intPhone)){
                $this->intTelefono = $intPhone;
            }
        }
        $objSMS->sendSMS($this->intTelefono, $this->strMensaje);
        if($objSMS->hasError()){
            return response::standard(0,$objSMS->getErrors("string"),false, $this->boolUTF8, $this->boolPrintJson);
        }
        else{
            return response::standard(1,$objSMS->strResponse, false, $this->boolUTF8, $this->boolPrintJson);
        }
    }


}