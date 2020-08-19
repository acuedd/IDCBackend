<?php

/* 
 * Autor: Alexander Flores
 * Fecha: 20/03/2015
 * Version: 1.0
 * Descripcion: webservice para enviar correo por algun inconveniente que tenga el usaurio
 * OP: dd0b2b32-cf4f-11e4-95f7-286ed488b5d1
 */

require_once("webservices/webservices_library.php");
require_once("webservices/webservices_baseclass.php");

class support extends webservices_baseClass{
    
    private $strOS = "";
    private $strObs = "";
    private $strOpt = "";
    private $intDeviceId = "";
    private $strDispositvo = "movil";
    private $boolOk = true;
    private $nombreContacto = "";
    private $telefonoContacto = "";
    
    function __construct($strCodigoOperacion, $arrInfoOperacion) {
        parent::__construct($strCodigoOperacion, $arrInfoOperacion);
        $this->setModosPermitidos(array("w", "am")); //am,w,wm
        $this->setFormatosPermitidos(array("json","xmlno")); //xmlwa,xmlno,json
    }
    
    public function setParametros($arrParametros) {
        $this -> arrParams = $arrParametros;
        
        if(!isset($this -> arrParams["obs"])){
            $this -> appendError("Falta observaciones");
        }
        if(!isset($this -> arrParams["OS"])){
            $this -> appendError("Falta sistema operativo");
        }
        if(!isset($this -> arrParams["opt"])){
            $this -> appendError("Falta opcion de soporte");
        }
        
        $strError = $this ->getError();
        if(!empty($strError)){
            return false;
        }
        
        return true;
    }
    
    public function darRespuesta() {
        $this -> intDeviceId = $this -> getDeviceID();
        $this -> strOS = user_input_delmagic(db_escape($this -> arrParams["OS"]));
        $this -> strOpt = user_input_delmagic(db_escape($this -> arrParams["opt"]));
        $this -> strObs = $this -> arrParams["obs"];
        $this -> strObs = str_replace("\n", "<br>", $this -> strObs);
        $this -> nombreContacto = $this->checkParam("nombre_contacto");
        $this -> telefonoContacto = $this->checkParam("no_contacto");
        
        if($this -> sendMailSupport()){
            $this -> arrDataOutput["valido"] = 1;
            $this -> arrDataOutput["razon"] = "Su solicitud ha sido enviada exitosamente, en unos momentos un ejecutivo se comunicara con usted.";
        }
        else{
            $this -> arrDataOutput["valido"] = 0;
            $this -> arrDataOutput["razon"] = "Hubo un problema de comunicación, intente de nuevo";
        }
        parent::darRespuesta();
    }
    
    public function getNameEmpresa(){
        return sqlGetValueFromKey("SELECT nombre FROM wt_empresas WHERE active ='Y'");
    }
    
    public function sendMailSupport(){
        $strEmpresa = $this -> getNameEmpresa();
        $strTo = "techdesk@homeland.com.gt";
        //OJO: se hizo pensando en que se haria diferente el correo dependiendo la solicitud
        if(check_module("credit_card") && $this -> strOpt == "tarjeta"){
            $strTo = $this -> cfg["credit_card"]["support"];
        }
        $strAsunto = "Soporte desde móvil - {$strEmpresa}";
        $strMessage = $this -> htmlToSend($strEmpresa);
        if($this -> boolOk && !empty($strTo)){
            $objMail = new AttachMailer($strTo, $strAsunto, $strMessage);
            $objMail->send();
            //@mail($strTo,$strAsunto,$strMessage,$strHeaders)
            $this -> boolOk = true;
        }
        else{
            $this -> boolOk = false;
        }
        return $this -> boolOk;
        
    }
    
    public function htmlToSend($strEmpresa){
        $strHtml = "";
        if(check_module("credit_card") && $this -> strOpt == "tarjeta"){
            $strHtml = credit_card_html_support($this -> intDeviceId, $this -> strObs,$this -> strOS, $strEmpresa, "movil",$this->nombreContacto, $this->telefonoContacto);
        }
        
        if(empty($strHtml)){
            $strHtml .= <<<EOD
                <table cellspacing="2" cellpadding="5">
                    <tr>
                        <td><b>Comercio</b></td>
                        <td>{$strEmpresa}</td>
                    </tr>
                    <tr>
                        <td><b>Dispositivo</b></td>
                        <td>{$this -> strDispositvo}</td>
                    </tr>
                    <tr>
                        <td><b>Sistema Operativo</b></td>
                        <td>{$this -> strOS}</td>
                    </tr>
                    <tr>
                        <td><b>Nombre del contacto</b></td>
                        <td>{$this->nombreContacto}</td>
                    </tr>
                    <tr>
                        <td><b>Telefono de contacto</b></td>
                        <td>{$this->telefonoContacto}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <b>Problema o sugerencia</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">{$this -> strObs}</td>
                    </tr>
                </table>
EOD;
        }
        return $strHtml;
    }
}
