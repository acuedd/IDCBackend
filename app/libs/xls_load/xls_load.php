<?php
/**
 * Created by PhpStorm.
 * User: HMLDEV-ALEX
 * Date: 14/09/2017
 * Time: 15:22
 */

include_once('core/global_config.php');
include_once('libs/xls_load/xls_load_model.php');
include_once('libs/simplexlsx/simplexlsx.class.php');

//OP: d019c0ac-9cb9-11e7-93c0-286ed488ca86
class xls_load extends global_config{

    private $dir = "var/xls_load/";
    private $initChar = 64;
    private $maxChar = 26;
    private $rowProcess = 100;

    function __construct($arrParams = ""){
        parent::__construct($arrParams);
        $this->validateDir();
    }

    private function validateDir(){
        if(!is_dir($this->dir))
            mkdir($this->dir,0777);
    }

    private function getColumn($i){
        $str = "";
        if($i <= $this->maxChar){
            $str .= chr($i+$this->initChar);
        }
        else{
            $k = intval($i / $this->maxChar);
            $j = $i - ($this->maxChar * $k);
            if($j == 0){
                $j = $this->maxChar;
                $k--;
            }
            $str = $this->getColumn($k);
            $str .= $this->getColumn($j);
        }
        return $str;
    }

    public function process(){
        $option = $this->checkParam("opt");
        if($option == "save")
            return $this->save_xls();
        if($option == "delete")
            return $this->delete_xls();
        if($option == "getData")
            return $this->getData_xls();
        if($option == "validateData")
            return $this->validate_xls();
        if($option == "process")
            return $this->process_xls();
        if($option == "extra")
            return $this->save_extra_xls();

        return response::standard(0,"Opción inválida");
    }

    private function save_xls(){
        if(isset($_FILES)){
            if(isset($_FILES['iXload'])){
                if($_FILES["iXload"]["error"] == UPLOAD_ERR_OK){
                    $tmp_name = $_FILES["iXload"]["tmp_name"];
                    $arrName = explode(".",$_FILES["iXload"]["name"]);

                    $name = "load_".date("Y_m_d_H_i_s").".".$arrName[1];
                    $file = "{$this->dir}{$name}";
                    move_uploaded_file($tmp_name, $file);
                    chmod($file,0777);

                    $arrKey = array();
                    $arrKey["id"] = 0;
                    $arrField = array();
                    $arrField["userid"] = $_SESSION["wt"]["uid"];
                    $arrField["date"] = "NOW()";
                    $arrField["time"] = "NOW()";
                    $arrField["file"] = $file;
                    $arrField["status"] = "insert";

                    $this->sql_tableupdate("wt_xls_load",$arrKey,$arrField);
                    $idXload = db_insert_id();


                    $xlsx = SimpleXLSX::parse($file);
                    $arrSheets = $xlsx->sheetNames();

                    $arrExtra = array();
                    $arrExtra["load_id"] = $idXload;
                    $arrExtra["sheets"] = array();

                    foreach($arrSheets AS $sheet => $nameSheet){
                        $arrKey = array();
                        $arrField = array();
                        $arrDimension = $xlsx->dimension($sheet);
                        $arrHeaders = array();
                        for($i = 1;$i <= $arrDimension[0];$i++){
                            $strHeader = utf8_decode(@$xlsx->getCell($sheet,$this->getColumn($i)."1"));
                            $arrHeaders[] = $strHeader;
                        }

                        $arrKey["id"] = 0;
                        $arrField["id_load"] = $idXload;
                        $arrField["sheet"] = $sheet;
                        $arrField["name_sheet"] = $nameSheet;
                        $arrField["headers"] = serialize($arrHeaders);
                        $arrField["col_sheet"] = $arrDimension[0];
                        $arrField["row_sheet"] = $arrDimension[1] - 1;
                        $arrField["process"] = "N";

                        $this->sql_tableupdate("wt_xls_load_sheets",$arrKey,$arrField);

                        $arrExtra["sheets"][$sheet] = array();
                        $arrExtra["sheets"][$sheet]["nombre"] = $nameSheet;
                        $arrExtra["sheets"][$sheet]["headers"] = $arrHeaders;
                        $arrExtra["sheets"][$sheet]["rows"] = $arrDimension[1] - 1;
                        unset($nameSheet);
                        unset($sheet);
                    }
                    return response::standard(1,"Archivo procesado correctamente",$arrExtra);
                }
            }
        }
        return response::standard(0,"Hubo un problema con la carga del archivo");
    }

    private function save_extra_xls(){
        $loadID = $this->checkParam("load",false,0);
        $arrExtraParam = (isset($this->arrParams["sheets"]))?$this->arrParams["sheets"]:false;
        $arrProcessRun = $this->checkParam("processRun", false, "");
	    $objModel = xls_load_model::getInstance($this->arrParams);
	    $arrProcess = false;
	    if(!empty($arrProcessRun)){
		    $arrProcess = $objModel->getValidateData($arrProcessRun);
	    }

	    if($arrProcess && is_array($arrProcess)) {
		    $file = $arrProcess["path"];
		    $class = $arrProcess["class"];
		    $method = $arrProcess["method"];
		    if (file_exists($file)) {
			    include_once($file);
			    if (class_exists($class)) {
				    $objClass = new $class($this->arrParams);
				    if (method_exists($objClass, $method)) {
					    $objClass->$method();
				    }
			    }
		    }
	    }

        if(is_array($arrExtraParam) && $loadID){
            foreach($arrExtraParam AS $key => $val){
                $objModel->setExtra($loadID,$key,$val);
                unset($val);
                unset($key);
            }
        }

        if($this->hasError()){
	        return response::standard(0,$this->getErrors("string"));
        }
        else{
	        return response::standard(1,"Datos extras guardados correctamente");
        }
    }

    private function delete_xls(){
        $id_load = $this->checkParam("load",false,0);
        if($id_load){
            $arr = array();
            $arr["wt_xls_load"] = array();
            $arr["wt_xls_load"]["id"] = $id_load;
            $this->sql_deleteData($arr);

            $arr = array();
            $arr["wt_xls_load_sheets"] = array();
            $arr["wt_xls_load_sheets"]["id_load"] = $id_load;
            $this->sql_deleteData($arr);

            return response::standard(1,"Datos eliminados correctamente");
        }
        else{
            return response::standard(0,"No se puede eliminar la carga");
        }
    }

    private function getData_xls(){
	    ini_set('memory_limit', '1024M');
	    set_time_limit(172800);
        $load = $this->checkParam("load",false,0);
        $sheet = $this->checkParam("sheet",false,0);
        $rows = $this->checkParam("rows",false,0);
	    $boolProcessWhileUpload = $this->checkParam("processWhileUpload");
	    $objModel = xls_load_model::getInstance($this->arrParams);
	    $arrProcess = false;
		if(!empty($boolProcessWhileUpload)){
			$arrProcess = $objModel->getValidateData($boolProcessWhileUpload);
		}

        if($load && $sheet && $rows){
            $info = $objModel->getInfoLoad($load);
            if(is_array($info)){
                $objModel->setStatusLoad($load,"progress");

                $infoSheet = $objModel->getInfoSheet($load,$sheet);

                if(is_array($infoSheet)){
                    $countSheet = $objModel->getCountSheetData($infoSheet["id"]);
                    if($infoSheet["row_sheet"] == $countSheet){
                        $objModel->setStatusLoad($load,"success");
                        return response::standard(1,"La carga finalizo");
                    }
                    else{
                        $init = 2;
                        if($countSheet){
                            $init = $countSheet + 2;
                            $rows += $countSheet + 1;
                        }
                        else
                            $rows ++;
                        $file = $info["file"];

                        $initMicro = getmicrotime();
                        $xlsx = SimpleXLSX::parse($file);
                        for($i = $init ; $i <= $rows ; $i++){
                            $arrColums = array();
                            for($j = 1 ; $j <= $infoSheet["col_sheet"] ; $j++){
                                $strColum = utf8_decode(@$xlsx->getCell($sheet,$this->getColumn($j).$i));
                                $arrColums[] = trim($strColum);
                            }

                            if(count($arrColums)){
	                            if($arrProcess && is_array($arrProcess)) {
		                            $file = $arrProcess["path"];
		                            $class = $arrProcess["class"];
		                            $method = $arrProcess["method"];
		                            if (file_exists($file)) {
			                            include_once($file);
			                            if (class_exists($class)) {
				                            $objClass = new $class($this->arrParams);
				                            if (method_exists($objClass, $method)) {
				                            	$arrInfo = [
													"line" => $i,
						                            "sheet" => $infoSheet,
						                            "init" => $init
					                            ];
					                            $objClass->$method($arrInfo, $arrColums);
				                            }
			                            }
		                            }
	                            }
                            }

	                        $arrKey = array();
	                        $arrKey["id"] = 0;
	                        $arrField = array();
	                        $arrField["id_sheet"] = $infoSheet["id"];
	                        $arrField["line"] = $i;
	                        $arrField["data"] = serialize($arrColums);
	                        $arrField["process"] = "N";
	                        $this->sql_tableupdate('wt_xls_load_data',$arrKey,$arrField);
                        }
                        $endMicro = getmicrotime();
                        $timeEnd = $initMicro - $endMicro;
                        return response::standard(1,"Se proceso archivo");
                    }
                }
                else return response::standard(0,"No hay información sobre la hoja a procesar");

            }
            else
                return response::standard(0,"No hay información de la carga");
        }
        else
            return response::standard(0,"No se pueden procesar los datos");
    }

    private function validate_xls(){
        $load = $this->checkParam("load",false,0);
        $validate = $this->checkParam("validate");
        if($load && !empty($validate)){
            $objModel = xls_load_model::getInstance($this->arrParams);
            $arrValidate = $objModel->getValidateData($validate);
            if(is_array($arrValidate)){
                $file = $arrValidate["path"];
                $class = $arrValidate["class"];
                $method = $arrValidate["method"];
                $boolOk = false;
                if(file_exists($file)){
                    include_once($file);
                    if(class_exists($class)){
                        $objClass = new $class($this->arrParams);
                        if(method_exists($objClass,$method)){
                            $boolOk = true;

                            $arrData = array();
                            $arrSheets = $objModel->getInfoSheets($load);
                            foreach($arrSheets AS $val){
                                $header = unserialize($val["headers"]);
                                $arrLines = $objModel->getDataLineBySheet($val["id"]);
                                $arrData[$val["id"]] = array();
                                /**
                                 * Falta agregar parametros extras
                                 */
                                $arrData[$val["id"]]["load"] = $load;
                                $arrData[$val["id"]]["name"] = $val["name_sheet"];
                                $arrData[$val["id"]]["sheet"] = $val["sheet"];
                                $arrData[$val["id"]]["params"] = $val["params"];
                                $arrData[$val["id"]]["lineas"] = array();
                                if(is_array($arrLines)){
                                    foreach($arrLines AS $line => $data){
                                        foreach($data AS $r => $value){
                                            $arrData[$val["id"]]["lineas"][$line][$header[$r]] = $value;
                                            unset($value);
                                            unset($r);
                                        }
                                        unset($data);
                                        unset($line);
                                    }
                                }
                                unset($val);
                            }
                            $strError = $objClass->$method($arrData);
                            if(empty($strError)){
                                return response::standard(1,"Datos procesados correctamente");
                            }
                            else{
                                return response::standard(0,$strError);
                            }
                        }
                    }
                }

                if(!$boolOk)
                    return response::standard(0,"Validación mal configurada, contacte a soporte");
            }
            else
                return response::standard(0,"Validación mal configurada, contacte a soporte");
        }
        else
            return response::standard(0,"Parámetros inválidos, contace a soporte");
    }

    private function process_xls(){
        $load = $this->checkParam("load",false,0);
        $validate = $this->checkParam("process");
        if($load && !empty($validate)){
            $objModel = xls_load_model::getInstance($this->arrParams);
            $arrValidate = $objModel->getValidateData($validate);
            if(is_array($arrValidate)){
                $file = $arrValidate["path"];
                $class = $arrValidate["class"];
                $method = $arrValidate["method"];
                $boolOk = false;
                if(file_exists($file)) {
                    include_once($file);
                    if (class_exists($class)) {
                        $objClass = new $class($this->arrParams);
                        if (method_exists($objClass, $method)){
                            $boolOk = true;
                            $arrResponse = $objClass->$method($load);
                        }
                    }
                }

                if(!$boolOk)
                    return response::standard(0,"Procesamiento mal configurada, contacte a soporte");
                else{
                    if(isset($arrResponse))
                        return $arrResponse;
                    else
                        return response::standard(1,"Datos procesados correctamente");
                }
            }
            else
                return response::standard(0,"Validación mal configurada, contacte a soporte");
        }
        else
            return response::standard(0,"Parámetros inválidos, contace a soporte");
    }
}