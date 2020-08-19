<?php
include_once("core/global_config.php");
include_once("modules/configuration/objects/currency/currency_model.php");
include_once("modules/configuration/objects/currency/currency_view.php");

class currency_controller extends global_config implements window_controller
{
    private $boolPrintJson = false;
    private $boolUTF8 = true;
    private $strAction = "";

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
        $this->$boolUTF8 = $boolUTF8;
    }

    public function main()
    {
        if (!check_user_class($this->config["admmenu"][$this->lang["CONFIGURATION_CURRENCY"]]["class"])) die($this->lang["ACCESS_DENIED"]);
        if($this->checkParam("op") != ""){
            $this->setBoolPrintJson(true);
            $this->setBoolUTF8(true);
            $option = $this->checkParam('op');
            if($option == "getCurrencies"){
                return $this->getCurrencies();
            }
            if($option == "save"){
                return $this->save();
            }
            if($option == "delete"){
                return $this->delete();
            }

            return;
        }

        $objModel = currency_model::getInstance();
        $arrCurrencies = $objModel->getCurrencies();
        $objView = currency_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->setArrCurrencies($arrCurrencies);
        $objView->draw();
    }

    public function getCurrencies(){
        $objModel = currency_model::getInstance();
        $arrCurrencies = $objModel->getCurrencies();
        return response::standard(1,"", array("currencies"=> $arrCurrencies),$this->boolUTF8,$this->boolPrintJson);
    }

    private function save(){
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if ($contentType === "application/json") {
            $content = trim(file_get_contents("php://input"));
            $decoded = json_decode($content, true);
            if(is_array($decoded)) {
                $arrkey = [];
                $intID = $this->getParam("area_code",$decoded,"");
                $arrkey["area_code"] = (!empty($intID)?$intID:0);
                $arrFields = [
                    "symbol" => $this->getParam("symbol",$decoded,""),
                    "name" => $this->getParam("name",$decoded,""),
                    //"area_code" => $this->getParam("area_code",$decoded,""),
                    "name_plural" => $this->getParam("name_plural",$decoded,""),
                    "created_at" => "NOW()",
                    "updated_at" => "NOW()",
                    "rounding" => $this->getParam("rate",$decoded,0),
                    "decimal_digits" => $this->getParam("reverseRate",$decoded,0),
                ];
                //debug::drawdebug($arrkey);
                //debug::drawdebug($arrFields);
                $this->setDebugLevel(1);
                $this->sql_tableupdate("wt_currency",$arrkey, $arrFields);
                //debug::drawdebug($this->getDebug());
                $objModel = currency_model::getInstance();
                $arrCurrencies = $objModel->getCurrencies();

                return response::standard(1,"Datos guardados exitosamente",["currencies"=> $arrCurrencies],$this->boolUTF8,$this->boolPrintJson);
            }
        }
        return response::standard(0,"", [],$this->boolUTF8,$this->boolPrintJson);
    }

    private function delete(){
        $intID = $this->checkParam("id");
        $arrTMP = [];
        $arrTMP["wt_currency"] = [];
        $arrTMP["wt_currency"]["id"] = $intID;
        $this->sql_deleteData($arrTMP);
        $objModel = currency_model::getInstance();
        $arrCurrencies = $objModel->getCurrencies();
        return response::standard(1,"Se ha eliminado exitosamente",["currencies"=> $arrCurrencies],$this->boolUTF8, $this->boolPrintJson);
    }
}
