<?php
include_once("core/global_config.php");
include_once("modules/sms/objects/sms_configuration/sms_configuration_model.php");
include_once("modules/sms/objects/sms_configuration/sms_configuration_view.php");

class sms_configuration_controller extends global_config implements window_controller
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
            if($option == 'getAllConfigurations'){
                return $this->getAllConfiguration();
            }
            if($option == 'getConfiguration'){
                return $this->getConfiguration();
            }
            if($option == 'deleteConfiguration'){
                return $this->deleteConfiguration();
            }
            if($option == 'postSmsConfiguration'){
                return $this->postConfiguration();
            }

            return false;
        }

        $objModel = sms_configuration_model::getInstance();
        $objView = sms_configuration_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();

    }

    public function postConfiguration(){
        $arrConfig = [];
        $arrConfig["id"] = $this->checkParam('id', false, '');
        $arrConfig["descripcion"] = $this->checkParam('description', false, '', true);
        $arrConfig["key_validate"] = $this->checkParam('key_validate', false, '');
        $arrConfig["key_secret"] = $this->checkParam('key_secret', false, '');
        $arrConfig["fecha_creacion"] = $this->checkParam('fecha_creacion', false, '');
        $arrConfig["active"] = $this->checkParam('active', false, '');
        $arrConfig["cod_area"] = $this->checkParam('area', false, '');
        $arrConfig["max_length"] = $this->checkParam('max_length', false, '');
        $arrConfig["url_send"] = $this->checkParam('url_send', false, '');
        $arrConfig["short_code_id"] = $this->checkParam('short_code_id', false, '');
        $arrConfig["token"] = $this->checkParam('token', false, '');
        $arrConfig["username"] = $this->checkParam('username', false, '');
        $arrConfig["password"] = $this->checkParam('password', false, '');
        $arrConfig["organization_id"] = $this->checkParam('organization_id', false, '');
        $arrKey["id"] = $this->checkParam('id', false, 0);
        if($this->sql_tableupdate('wt_sms_config', $arrKey, $arrConfig)){
            return response::standard(1, 'Guardado', [], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, 'Ocurrio un error', [], $this->boolUTF8, $this->boolPrintJson);
    }

    public function getConfiguration(){
        $intId = $this->checkParam('id', false, '');
        $objModel = sms_configuration_model::getInstance();
        $configuration = $objModel->getConfigurationBy($intId);
        if($configuration){
            return response::standard(1, 'Configuración obtenida', ["data" => $configuration], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, 'No encontrado', [], $this->boolUTF8, $this->boolPrintJson);
    }

    public function deleteConfiguration(){
        $intId = $this->checkParam('id', false, 0);
        $arrConfiguration["wt_sms_config"] = [];
        $arrConfiguration["wt_sms_config"]["id"] = $intId;
        $this->sql_deleteData($arrConfiguration);
        return response::standard(1, 'Eliminado con éxito', [], $this->boolUTF8, $this->boolPrintJson);
    }

    public function getAllConfiguration(){
        $objModel = sms_configuration_model::getInstance();
        $arrSms = $objModel->getAllConfigurationDB();
        return response::standard(1, '-', ["data" => $arrSms], $this->boolUTF8, $this->boolPrintJson);
    }

}