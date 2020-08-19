<?php
/**
 * Created by PhpStorm.
 * User: HMLDEV-ALEX
 * Date: 3/08/2017
 * Time: 14:06
 */

include_once("core/global_config.php");
include_once("modules/users/objects/lostpass/lostpass_model.php");

class lostpass_controller extends global_config implements window_controller {

    private $strAction = "";
    private $boolUTF8 = true;
    private $boolPrintJson = false;
    private $boolSMS = false;

	/**
	 * @return bool
	 */
	public function isBoolUTF8()
	{
		return $this->boolUTF8;
	}

	/**
	 * @param bool $boolUTF8
	 */
	public function setBoolUTF8( $boolUTF8)
	{
		$this->boolUTF8 = $boolUTF8;
	}

	/**
	 * @return bool
	 */
	public function isBoolPrintJson()
	{
		return $this->boolPrintJson;
	}

	/**
	 * @param bool $boolPrintJson
	 */
	public function setBoolPrintJson( $boolPrintJson)
	{
		$this->boolPrintJson = $boolPrintJson;
	}

    function __construct($arrParams = ""){
        parent::__construct($arrParams);

        if(check_module("sms")){
            include_once "modules/sms/objects/send_sms/send_sms_model.php";
            $objSMSConfig = send_sms_model::getInstance("");
            $arrConfig = $objSMSConfig->getProveedor();
            $this->boolSMS = ($arrConfig);
        }
    }

    public function setStrAction($strAction){
        $this->strAction = $strAction;
    }

    public function main()
    {
        global $cfg;
        $this->request();
        draw_header();
        ?>
        <link rel="stylesheet" href="themes/<?php print $cfg["core"]["theme"]; ?>/css/custom_styles.css">
        <?php

        $objTPL = new Template("modules/users/objects/lostpass/views/lostpass_view.tpl");
        $objTPL->set("strAction",$this->strAction);
        $objTPL->set("USER_LOSTPASS_MSG1", $this->lang["USER_LOSTPASS_MSG1"]);
        $objTPL->set("LOGIN_NAME", $this->lang["LOGIN_NAME"]);
        print $objTPL->output();

        draw_footer();
        ?>
            <script>
                setBodyColor();
                function setBodyColor(){
                    let body = document.querySelector(`body`);
                    let sidebar = document.querySelector(`.main-sidebar`);
                    let app = document.querySelector(`.all_content`);
                    let content_wrapper = document.querySelector(`.content-wrapper`);
                    let content_color = document.querySelector(`.color-menu-text_color`);
                    let footer = document.querySelector(`.main-footer`);
                    let wrapper = document.querySelector(`.wrapper`);
                    let content = document.querySelector(`.content`);
                    let color = getComputedStyle(content_color);
                    let sidebar_style = getComputedStyle(sidebar);
                    body.style.background = sidebar_style.background;
                    content_wrapper.style.background = 'transparent';
                    footer.style.background = 'transparent';
                    content.style.background = 'transparent';
                    app.style.color = `${color.color}`;
                    wrapper.style.background = 'transparent';
                }
            </script>
        <?php
        return true;
    }

    private function request()
    {
		$this->setBoolUTF8(true);
		$this->setBoolPrintJson(true);
        $op = $this->checkParam("op");
        if($op == "send"){
            $objModel = lostpass_model::getInstance($this->arrParams);
            $arrInfoUser = $objModel->getInfoUser($this->getParam("username"));
            if($this->boolSMS && (!empty($arrInfoUser["tel_cel"]))){
                $this->arrParams["opSend"] = "s";
            }
            else if(!empty($arrInfoUser["email"])){
                $this->arrParams["opSend"] = "e";
            }
            $this->validateUser();
            die;
        }
        else if($op == "validateToken"){
            $this->validateToken();
            die;
        }
        else if($op == "savePass"){
            $this->savePass();
            die;
        }
    }

    public function validateUser()
    {
        /*
         * Params accepted for execute and return response
         * s = sms
         * e = email
         * n = notification
         * */
        $objModel = lostpass_model::getInstance($this->arrParams);
        $strUser = $this->checkParam("username");
        $strOption = $this->checkParam("opSend");
        $arrInfoUser = $objModel->getInfoUser($strUser);
	    $objToken = tokens::getInstance(true);
        $strMsg = "";

        if(is_array($arrInfoUser)){
            $objToken->setStrSessID($this->checkParam("device_id"));
            $strToken = $objToken->generate("lostpass-{$arrInfoUser["uid"]}",$arrInfoUser["uid"]);
            if (empty($strOption) || $strOption == "e"){
                if(!empty($arrInfoUser["email"])){
                    self::sendEmail($arrInfoUser["email"],$strToken);
                    $strMsg = "un correo";
                }
                else $this->addError("No hay correo electrónico");
            }
            elseif (!empty($strOption) && $strOption == "s"){
                if(check_module("sms")){
                    if(file_exists("modules/sms/objects/send_sms/send_sms_controller.php")){
                        include_once "modules/sms/objects/send_sms/send_sms_controller.php";
                        $objModelSMS = new sms_send_controller(array());
                        $intPhone = $objModel->getPhoneUser($strUser);
                        $strMsg = "Su token generado es: $strToken";
                        $arrReturn = $objModelSMS->send_sms(true, $intPhone, $strMsg);
                        $strMsg = "un sms";
                        if(empty($arrReturn["valido"])){
                            $this->addError($arrReturn["msj"]);
                        }
                    }
                    else {
                        $this->addError("El módulo de mensajes no tiene el archivo buscado, por favor contacte a soporte");
                    }
                }
                else {
                    $this->addError("El módulo de mensajes no se encuentra activo, por favor contacte a soporte");
                }

            }
            elseif (!empty($strOption) && $strOption == "n"){
                $strMsg = "una notificación push";
            }
        }
        else{
            $this->addError("Usuario inválido");

        }

        if($this->hasError()){
            return response::standard(0,$this->getErrors("string"),array(),$this->boolUTF8,$this->boolPrintJson);
        }
        else{
            return response::standard(1,"Token generado correctamente, se te acaba de enviar {$strMsg} con un token.",array(),$this->boolUTF8,$this->boolPrintJson);
        }
    }

    public function validateToken(){
        $strToken = $this->checkParam("token");
	    $strDeviceId = $this->checkParam("device_id");
        if(!empty($strToken)){
	        $objToken = tokens::getInstance();
	        $objToken->setLevelDebug(1);
			$objToken->setStrSessID($strDeviceId);
	        if($objToken->check($strToken,"",true)){
		        return response::standard(1,"El token es correcto",array(),$this->boolUTF8,$this->boolPrintJson);
	        }
	        else{
		        return response::standard(0,"El token no coincide",array(),$this->boolUTF8,$this->boolPrintJson);
	        }
        }
        else{
            return response::standard(0,"No se puede validar token",array(),$this->boolUTF8,$this->boolPrintJson);
        }
    }

    public function savePass(){
        $password = $this->checkParam("pass_1");
	    $strDeviceId = $this->checkParam("device_id");
        $pin = $this->checkParam("token");
        if(!empty($password)){
	        $objToken = tokens::getInstance();
	        $objToken->setStrSessID($strDeviceId);
            if( $objToken->check($pin,"",true) ){
            	$arrData = $objToken->getData();
	            //$objToken->clear("lostpass-{$arrData["sessionData"]}",true);
                $objModel = lostpass_model::getInstance($this->arrParams);
                $objModel->setPassword($arrData["sessionData"],$password);
                return response::standard(1,"La contraseña ha sido actualizada correctamente",array(),$this->boolUTF8,$this->boolPrintJson);
            }
            else{
                return response::standard(0,"Hubo un problema al leer el password",array(),$this->boolUTF8,$this->boolPrintJson);
            }
        }
        else{
            return response::standard(0,"Hubo un problema al leer el password",array(),$this->boolUTF8,$this->boolPrintJson);
        }
    }

    private static function sendEmail($strMail,$strPin)
    {
        $site = core_getBaseDir();
        $strAsunto = "Reinicio de contraseña";

        $objTPL = new Template("modules/users/objects/lostpass/views/mail_reset.tpl");
        $objTPL->set("site",$site);
        $objTPL->set("pin",$strPin);
        $strMensaje = $objTPL->output();

        //@mail($strMail,$strAsunto,$strMensaje,$strCabeceras);
        $objMail = new AttachMailer($strMail, $strAsunto, "");
        $objMail->setMessageHTML($strMensaje);
        $objMail->send();
    }
}