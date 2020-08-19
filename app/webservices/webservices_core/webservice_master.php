<?php
/**
 * Webservice master, aqui pasaran todos los servicios creados por adm_webservices
 *
 * @author Alexander Flores
 */
require_once("webservices/webservices_baseclass.php");
class webservice_master extends webservices_baseClass{
    
    private $objAdmin = false;    
    function __construct($strCodigoOperacion, $arrInfoOperacion) {
        parent::__construct($strCodigoOperacion, $arrInfoOperacion);
        //obtengo formatos permitidos
        $arr = array();
        $this->objAdmin = new admin_webservices($arr,$strCodigoOperacion);
        $this->setModosPermitidos($this->objAdmin->getModosPermitidos());
        $this->setFormatosPermitidos($this->objAdmin->getFormatResponse());
    }
    
    public function setParametros($arrParametros) {
        $this->arrParams = $arrParametros;
	    $this->arrParams["user"] = [];
	    $this->arrParams["api"] = [];
	    $this->arrParams["user"]["token"] = $this->getCodigoSeguridad();
	    $this->arrParams["user"]["deviceid"] = $this->getDeviceID();
	    $this->arrParams["user"]["userid"] = $this->getUserID();
	    $this->arrParams["api"]["mode"] = $this->getModoOperacion();
	    $this->arrParams["api"]["opcode"] = $this->getCodigoOperacion();

        //Valido si se quiere ver que la configuracion del dispositivo ha cambiado
        $arrTMP = $this->objAdmin->get_info_class();
        if($arrTMP["check_config_device"] == "Y"){
            if(!$this->check_config_device())return false;
        }
        
        /**
         * Si se desean tener las bondades o funciones del webservices, entonces se envia la clase como tal a la clase del proceso
         * Esto es ya para un uso avanzado de los webservices (o para quien se anime y no se pierda en código XD)
         */
        if(method_exists($this->objAdmin->objClass, "setObjWebservices")){
            $this->objAdmin->objClass->setObjWebservices($this);
        }

        $arrReturn = $this->objAdmin->check_params_class($this->arrParams);
        /**
         * Si la clase viene derivada de un global_config entonces se válida para setear los parámetros
         */
        if(method_exists($this->objAdmin->objClass,"setArrParams")){
            $this->objAdmin->objClass->setArrParams($this->arrParams);
        }

        //reviso si existen funciones para validar
        $arrTMP = $this->objAdmin->get_extra_function();
        if(count($arrTMP)){
            $_return = NULL;
            foreach($arrTMP AS $val){
                $strFunction = $val["nombre"];
                if($val["local"] == "Y"){
                    if(method_exists($this, $strFunction)){
                        $_return = $this->$strFunction();
                    }
                }
                else{
                    $_return = $this->objAdmin->execute_function($strFunction);
                }

                if(is_array($_return)){
                    if($_return["status"] == "fail"){
                        $this ->appendError($_return["msj"]);
                        return false;
                    }
                }
                elseif(is_bool($_return)){
                    if(!$_return){
                        return false;
                    }
                }
                else{
                    $this ->appendError("Respuesta del método \"{$strFunction}\" es incorrecto");
                    return false;
                }
                unset($val);
            }
        }

        if($arrReturn["status"] == "fail"){
            $this->appendError($arrReturn["msj"]);
            return false;
        }
        return true;
    }
    
    public function darRespuesta() {
        $response = $this->objAdmin->response_class($this->arrParams);
        if(is_array($response)){
            $this->arrDataOutput = $response;
            parent::darRespuesta();
        }
        elseif(is_string($response)){
            $this->strContentOutput = $response;
            parent::darRespuesta();
        }
        else{
            $this->appendError("WEBSERVICES_DATA_NOT_READY");
            $this->darRespuestaInvalido();
        }
    }
}

class admin_webservices extends global_config{

    private $strOpCod = "";
    private $strPathMaster = "webservices/webservices_core/webservice_master.php";
    private $strNameClassMaster = "webservice_master";
    private $response = false;
    
    public $objClass = false;
    private $arrInfoClass = array();
    function __construct(&$arrParams = "", $strOp = "") {
        parent::__construct($arrParams);
        $this->strOpCod = $this->clean_var($strOp);
        if(!empty($this->strOpCod)){
            $this->setClass();
        }
    }    
    public function setObjConection($objRemoteConection = false) {
        parent::setObjConection($objRemoteConection);
    }
    /**
     * Valida campos necesarios para crear servicio
     * @return boolean retorna false en caso de haber un error
     */
    private function validate_fields_service(){
        if(empty($this->arrParams["module"])){
            $this->addError($this->lang["WEBSERVICES_ERROR012"]);
        }
        if(empty($this->arrParams["descripcion"])){
            $this->addError($this->lang["WEBSERVICES_ERROR013"]);
        }
        if(empty($this->arrParams["path_class"])){
            $this->addError($this->lang["WEBSERVICES_ERROR014"]);
        }
        elseif(!file_exists($this->arrParams["path_class"])){
            $this->addError($this->lang["WEBSERVICES_ERROR015"]);
        }
        if(empty($this->arrParams["name_class"])){
            $this->addError($this->lang["WEBSERVICES_ERROR016"]);
        }
        if(empty($this->arrParams["allowed"])){
            $this->addError($this->lang["WEBSERVICES_ERROR017"]);
        }
        if(empty($this->arrParams["format_response"])){
            $this->addError($this->lang["WEBSERVICES_ERROR018"]);
        }
        if(empty($this->arrParams["method_response"])){
            $this->addError($this->lang["WEBSERVICES_ERROR019"]);
        }
        foreach($this->arrParams AS $key => $val){
            $arrKey = explode("_", $key);
            if(isset($arrKey[1]) && ($arrKey[0] == "desc" || $arrKey[0] == "key")){
                $intCampo = $arrKey[1] + 1;
                if($arrKey[0] == "desc" && empty($val)){
                    $this->addError("La descripción del parámetro No. {$intCampo} no puedes estar vacio");
                }
                if($arrKey[0] == "key" && empty($val)){
                    $this->addError("El key del parámetro No. {$intCampo} no puedes estar vacio");
                }
            }
            unset($key);
            unset($val);
        }
        if($this ->hasError()){
            $strMsj = $this->getErrors("string","<br>");
            $this->response = response::standard(0,$strMsj);
            return false;
        }
        $this->validate_fields_extra();
        return true;
    }
    /**
     * Valida campos extras para el servicio
     */
    private function validate_fields_extra(){
        $this->arrParams["access"] = isset($this->arrParams["access"])?$this->arrParams["access"]:"freeAccess";
        $this->arrParams["active"] = isset($this->arrParams["active"])?"Y":"N";
        $this->arrParams["public"] = isset($this->arrParams["public"])?"Y":"N";
        $this->arrParams["check_config"] = isset($this->arrParams["check_config"])?"Y":"N";
    }
    /**
     * Guarda el servicio
     * @return bolean true si se guardo o false si hubo un error
     */
    private function save_service(){
        $arrKey = array();
        $arrKey["op_uuid"] = $this->strOpCod;
        $arrFields = array();
        $arrFields["modulo"] = $this->clean_var($this->arrParams["module"]);
        $arrFields["descripcion"] = $this->clean_var($this->arrParams["descripcion"]);
        $arrFields["include_path"] = $this->strPathMaster;
        $arrFields["className"] = $this->strNameClassMaster;
        $arrFields["publica"] = $this->clean_var($this->arrParams["public"]);
        $arrFields["acceso"] = $this->clean_var($this->arrParams["access"]);
        $arrFields["activo"] = $this->clean_var($this->arrParams["active"]);
        $arrFields["isNewMod"] = "Y";
        $arrFields["path_mainClass"] = $this->clean_var($this->arrParams["path_class"]);
        $arrFields["class_mainClass"] = $this->clean_var($this->arrParams["name_class"]);
        $arrFields["allowed_format"] = implode(",", $this->arrParams["allowed"]);
        $arrFields["format_response"] = implode(",", $this->arrParams["format_response"]);
        $arrFields["method_response"] = $this->clean_var($this->arrParams["method_response"]);
        $arrFields["check_config_device"] = $this->arrParams["check_config"];
        $arrFields["groupString"] = $this->clean_var($this->arrParams["groupString"]);

        return $this->sql_tableupdate("wt_webservices_operations", $arrKey, $arrFields);
    }
    /**
     * Guarda parametros del servicio
     */
    private function save_fields_service(){
        $arrTMP = array();
        $arrTMP["wt_webservices_operations_extra_data"] = array();
        $arrTMP["wt_webservices_operations_extra_data"]["op"] = $this->strOpCod;
        $this->sql_deleteData($arrTMP);
        foreach($this->arrParams AS $key => $val){
            $arrKey = explode("_", $key);
            if(isset($arrKey[1]) && ($arrKey[0] == "desc")){
                $intCampo = $arrKey[1];
                $arrKey = array();
                $arrKey["id"] = 0;
                $arrFields = array();
                $arrFields["op"] = $this->strOpCod;
                $arrFields["required"] = isset($this->arrParams["required_{$intCampo}"])?"Y":"N";
                $arrFields["parameter_description"] = isset($this->arrParams["desc_{$intCampo}"])?$this->clean_var($this->arrParams["desc_{$intCampo}"]):"";
                $arrFields["method_validation"] = isset($this->arrParams["validate_{$intCampo}"])?$this->clean_var($this->arrParams["validate_{$intCampo}"]):"";
                $arrFields["key_parameter"] = isset($this->arrParams["key_{$intCampo}"])?$this->clean_var($this->arrParams["key_{$intCampo}"]):"";
                $arrFields["error_response"] = isset($this->arrParams["error_{$intCampo}"])?$this->clean_var($this->arrParams["error_{$intCampo}"]):"";
                $arrFields["transform_key"] = isset($this->arrParams["trans_{$intCampo}"])?$this->clean_var($this->arrParams["trans_{$intCampo}"]):"";
                
                $this->sql_tableupdate("wt_webservices_operations_extra_data", $arrKey, $arrFields);
            }
            unset($key);
            unset($val);
        }
    }
    /**
     * Guarda funciones extra para validar
     */
    private function save_functions_services(){
        $arrTMP = array();
        $arrTMP["wt_webservices_operations_extra_function"] = array();
        $arrTMP["wt_webservices_operations_extra_function"]["op"] = $this->strOpCod;
        $this->sql_deleteData($arrTMP);
        foreach($this->arrParams AS $key => $val){
            $arrKey = explode("_", $key);
            if(isset($arrKey[1]) && ($arrKey[0] == "function")){
                $intCampo = $arrKey[1];
                if(empty($this->arrParams["function_{$intCampo}"]))continue;
                $arrKey = array();
                $arrKey["id"] = 0;
                $arrFields = array();
                $arrFields["op"] = $this->strOpCod;
                $arrFields["webservices_baseClass"] = isset($this->arrParams["derived_{$intCampo}"])?"Y":"N";
                $arrFields["str_function"] = isset($this->arrParams["function_{$intCampo}"])?$this->clean_var($this->arrParams["function_{$intCampo}"]):"";
                
                $this->sql_tableupdate("wt_webservices_operations_extra_function", $arrKey, $arrFields);
            }
            unset($key);
            unset($val);
        }
    }
    /**
     * limpia el arreglo de tildes o cualquier carracter extraño y retorna
     * @return array retorna la respuesta del servicio
     */
    private function get_return(){
        /*
        if(is_array($this->response))
            utf8_encode_array($this->response);
        */
        return $this->response;
    }
    /**
     * setea el codigo de operacion del servicio
     * @param string $strOp uuid de servicio
     */
    private function setOperationCode(){
        if(empty($this->arrParams["txtOp"])){
            $this->strOpCod = sqlGetValueFromKey("SELECT UUID()");
        }
        else{
            $this->strOpCod = $this->clean_var($this->arrParams["txtOp"]);
        }
    }
    /**
     * limpia la variable segun el tipo
     * @param string $str variable a limpiar
     * @return string variable limpia
     */
    private function clean_var($str){
        if(is_array($str)){
	        return $str;
        }
        else{
	        $str = trim($str);
	        if(is_string($str))
		        $str = user_input_delmagic(db_escape($str),true);
	        elseif(is_int($str))
		        $str = intval($str);
	        elseif(is_float($str))
		        $str = floatval($str);
	        return $str;
        }
    }
    /**
     * Trae toda la información del servicio e instancia la clase principal
     */
    private function setClass(){
        $this->arrInfoClass = sqlGetValueFromKey("SELECT * FROM wt_webservices_operations WHERE op_uuid = '{$this->strOpCod}'");
        if(file_exists($this->arrInfoClass["path_mainClass"])){
            include_once($this->arrInfoClass["path_mainClass"]);
            if(class_exists($this->arrInfoClass["class_mainClass"])){
                $this->objClass = new $this->arrInfoClass["class_mainClass"]();
            }
        }
    }
    /**
     * Guarda el servicio
     * @return array la respuesta del servicio
     */
    public function create_services(){
        if($this->validate_fields_service()){
            $this->setOperationCode();
            if($this->save_service()){
                $this->save_fields_service();
                $this->save_functions_services();
                $arrTMP = array();
                $arrTMP["op"] = $this->strOpCod;
                $this->response = response::standard(1,"Servicio guardado corretamente",$arrTMP);
            }
            else{
                $this->response = response::standard(0,$this->lang["WEBSERVICES_ERROR020"]);
            }
        }
        return $this->get_return();
    }
    /**
     * Obtiene los modos permitidos del servicio
     * @return array 
     */
    public function getModosPermitidos(){
        $strModes = $this->arrInfoClass["allowed_format"];
        $arr = explode(",", $strModes);
        return $arr;
    }
    /**
     * Obtiene los formatos de respuesta del servicio
     * @return array
     */
    public function getFormatResponse(){
        $strFormats = $this->arrInfoClass["format_response"];
        $arr = explode(",", $strFormats);
        return $arr;
    }
    /**
     * Revisa los parametros de la clase
     * @param array $arrParams Parametros a validar
     * @return array retorna ok o fail con su mensaje
     */
    public function check_params_class(&$arrParams){
        $this->response = response::standard(1,"Parámetros correctos");
        if($this->objClass){
            $strQuery = "SELECT * FROM wt_webservices_operations_extra_data WHERE op = '{$this->strOpCod}'";
            $qTMP = db_query($strQuery);
            if(db_num_rows($qTMP)){
                while($rTMP = db_fetch_array($qTMP)){
                    //Reviso los parametros requeridos
                    if($rTMP["required"] == "Y"){
                        if(empty($arrParams[$rTMP["key_parameter"]])){
                            $error = (!empty($rTMP["error_response"]))?$rTMP["error_response"]:"Falta o viene vacío parámetro '{$rTMP["key_parameter"]} - {$rTMP["parameter_description"]}'";
                            $this->response = response::standard(0,$error,false,false);
                            break;
                        }
                    }
                    if(isset($arrParams[$rTMP["key_parameter"]])){
                        $this->clean_var($arrParams[$rTMP["key_parameter"]]);
                        //Si tiene metodo de validacion lo valido con el metodo
                        if(!empty($rTMP["method_validation"])){
                            $strMethod = $rTMP["method_validation"];
                            if(method_exists($this->objClass, $strMethod)){
                                $boolTMP = $this->objClass->$strMethod($arrParams[$rTMP["key_parameter"]]);
                                if(empty($boolTMP)){
                                    if($this->objClass->hasError()){
                                        $error = (!empty($rTMP["error_response"]))?$rTMP["error_response"]:"'{$rTMP["key_parameter"]} - {$this->objClass->getErrors('string',", ")}";
                                    }
                                    else{
                                        $error = (!empty($rTMP["error_response"]))?$rTMP["error_response"]:"'{$rTMP["key_parameter"]} - {$rTMP["parameter_description"]}' es incorrecto";
                                    }
                                    $this->response = response::standard(0,$error,false,false);
                                    break;
                                }
                            }
                            else{
                                $this->response = response::standard(0,"No existe método de validación para el parámetro '{$rTMP["key_parameter"]} - {$rTMP["parameter_description"]}'",false,false);
                                break;
                            }
                        }
                        
                        //Transformo el key si asi lo pidiera
                        if(!empty($rTMP["transform_key"])){
                            $arrParams[$rTMP["transform_key"]] = $arrParams[$rTMP["key_parameter"]];
                            unset($arrParams[$rTMP["key_parameter"]]);
                        }
                    }
                    else{                        
                        $arrParams[$rTMP["key_parameter"]] = "";
                        //Transformo el key si asi lo pidiera
                        if(!empty($rTMP["transform_key"])){
                            $arrParams[$rTMP["transform_key"]] = "";
                            unset($arrParams[$rTMP["key_parameter"]]);
                        }
                    }
                    unset($rTMP);
                }
                db_free_result($qTMP);
            }
            else{
                $this->response = response::standard(0,$this->lang["WEBSERVICES_ERROR021"],false,false);
            }
        }
        else{
            $this->response = response::standard(0,$this->lang["WEBSERVICES_ERROR022"],false,false);
        }
        return $this->response;
    }
    /**
     * 
     * @param array $arrParams parametros
     * @return array retorna la respuesda de la clase asignada
     */
    public function response_class($arrParams){
        $strMethodResponse = $this->arrInfoClass["method_response"];
        if(!empty($strMethodResponse)){
            if(method_exists($this->objClass, $strMethodResponse)){
                $this->response = $this->objClass->$strMethodResponse($arrParams);
            }
            else{
                $this->response = response::standard(0,$this->lang["WEBSERVICES_ERROR023"]);
            }
        }
        else{
            $this->response = response::standard(0,$this->lang["WEBSERVICES_ERROR023"]);
        }
        return $this->get_return();
    }
    /**
     * vaiable donde se almacena la informacion de la clase
     * @return array retorna la informacion de la clase
     */
    public function get_info_class(){
        return $this->arrInfoClass;
    }
    
    public function get_extra_function(){
        $arrTMP = array();
        $strQuery = "SELECT * FROM wt_webservices_operations_extra_function WHERE op = '{$this->strOpCod}'";
        $qTMP = db_query($strQuery,true,$this->objConection);
        if(db_num_rows($qTMP)){
            $intCount = 0;
            while($rTMP = db_fetch_array($qTMP)){
                $arrTMP[$intCount]["nombre"] = $rTMP["str_function"];
                $arrTMP[$intCount]["local"] = $rTMP["webservices_baseClass"];
                $intCount++;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrTMP;
    }
    
    public function execute_function($strFunction){
        $this->response = response::standard(0,"método de validación no exite");
        if($this->objClass){
            if(method_exists($this->objClass, $strFunction)){
                return $this->objClass->$strFunction();
            }
        }
        return $this->get_return();
    }

    public function deleteService()
    {
	    $this->setOperationCode();
	    $this->sql_deleteData(array(
			"wt_webservices_operations" => array("op_uuid" => $this->strOpCod),
		    "wt_webservices_operations_extra_data" => array("op" => $this->strOpCod),
		    "wt_webservices_operations_extra_function" => array("op" => $this->strOpCod),
	    ));
	    $this->response = response::standard(1,"Se eliminó el servicio correctamente");
	    return $this->get_return();
    }
}
