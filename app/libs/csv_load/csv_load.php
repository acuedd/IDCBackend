<?php
/**
 * Created by PhpStorm.
 * User: DavidRosales
 * Date: 12/11/2018
 * Time: 15:22
 */

include_once('core/global_config.php');
include_once('libs/csv_load/csv_load_model.php');
include_once("modules/sales/objects/bulk_load/bulk_load_controller.php");
//OP: d019c0ac-9cb9-11e7-93c0-286ed488ca87
class csv_load extends global_config{

    private $dir = "var/csv_load/";
    private $initChar = 64;
    private $maxChar = 26;

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
            return $this->save_csv();
        if($option == "getData")
            return $this->getData_csv();
        if($option == "process")
            return $this->process_csv();
        if($option == "extra")
            return $this->save_extra_csv();
        if($option == "delete")
            return $this->clearData();

        return response::standard(0,"Opción inválida");
    }

    private function save_csv(){
        ini_set('memory_limit', '1024M');
        set_time_limit(172800);
        $limit = $this->checkParam("limit");
        if(isset($_FILES)){
            if(isset($_FILES['iXloadCsv'])){
                if($_FILES["iXloadCsv"]["error"] == UPLOAD_ERR_OK){
                    $tmp_name = $_FILES["iXloadCsv"]["tmp_name"];
                    $arrName = explode(".",$_FILES["iXloadCsv"]["name"]);
                    $name = "load_".date("Y_m_d_H_i_s").".".$arrName[1];

                    $file = "{$this->dir}{$name}";
                    move_uploaded_file($tmp_name, $file);
                    chmod($file,0777);
                    $archi = $file;
                    $arrKey = array();
                    $arrKey["id"] = 0;
                    $arrField = array();
                    $arrField["userid"] = $_SESSION["wt"]["uid"];
                    $arrField["date"] = "NOW()";
                    $arrField["time"] = "NOW()";
                    $arrField["file"] = $file;
                    $arrField["status"] = "insert";

                    $this->sql_tableupdate("wt_csv_load",$arrKey,$arrField);
                    $idXload = db_insert_id();


                    $size = $limit;
                    $to_read = $file;
                    $done = false;
                    $part = 0;
                    if (($handle = fopen($file, "r")) !== FALSE) {
                        $header = fgets($handle);
                        while ($done == false) {
                            $locA = ftell($handle);
                            fseek($handle, $size, SEEK_CUR);
                            $tmp = fgets($handle);
                            $locB = ftell($handle);
                            $span = ($locB - $locA);
                            fseek($handle, $locA, SEEK_SET);
                            $chunk = fread($handle,$span);
                            file_put_contents($to_read.'_'.$part.'.csv',$header.$chunk);
                            $part++;
                            if (strlen($chunk) < $size) $done = true;
                        }
                        fclose($handle);
                    }

                    $file = fopen($file, "r");
                    $arrNombres = fgetcsv($file, 0, ",", "\"", "\"");
                    $arrNombreCabe = $arrNombres;

                    if(count($arrNombres) == 1){
                        $arrNombreCabe = explode(";",$arrNombreCabe[0]);
                    }
                    
                    foreach ($arrNombreCabe AS $keyHead => $valueHead){
                        $arrNombreCabe[$keyHead] = str_replace(";", "", $valueHead);
                    }
                    fclose($file);
                    $sizeFile = filesize($archi);
                    $contarRegistros = $sizeFile;
                    $arrExtra = array();
                    $arrExtra["load_id"] = $idXload;
                    $arrExtra["sheets"] = array();

                    $arrSheets =array();
                    $nameHoja = "load_".date("Y_m_d_H_i_s");
                    array_push($arrSheets,$nameHoja);

                    foreach($arrSheets AS $sheet => $nameSheet){
                        $arrExtra["sheets"][$sheet] = array();
                        $arrExtra["sheets"][$sheet]["nombre"] = $nameSheet;
                        $arrExtra["sheets"][$sheet]["headers"] = $arrNombreCabe;
                        $arrExtra["sheets"][$sheet]["rows"] = $contarRegistros;
                        unset($nameSheet);
                        unset($sheet);
                    }

                    return response::standard(1,"Archivo procesado correctamente",$arrExtra);
                }
            }
        }
        return response::standard(0,"Hubo un problema con la carga del archivo");
    }

    private function save_extra_csv(){

        $loadID = $this->checkParam("load",false,0);
        $arrExtraParam = (isset($this->arrParams["sheets"]))?$this->arrParams["sheets"]:false;
        $arrProcessRun = $this->checkParam("processRun", false, "");
        $tablasBase = $this->checkParam("tablasBase", false, "");

	    $objModel = csv_load_model::getInstance($this->arrParams);
	    $arrProcess = false;
	    if(!empty($arrProcessRun)){
		    $arrProcess = $objModel->getValidateData($arrProcessRun);
	    }
	    if ($arrProcessRun == "BalanceInquiryCsv_clear"){
            $objModel->clearBala($tablasBase);
        }
	    if($arrProcess && is_array($arrProcess)) {
		    $file = $arrProcess["path"];
		    $class = $arrProcess["class"];
		    $method = $arrProcess["method"];
		    if (file_exists($file)) {
			    include_once "{$file}";
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

    private function getData_csv(){
	    ini_set('memory_limit', '1024M');
	    set_time_limit(172800);

        $objModel = csv_load_model::getInstance($this->arrParams);
        $load = $this->checkParam("load",false,0);
        $intPar = $this->checkParam("intPar",false,0);
        $strClasse = $this->checkParam("classe",false,0);
        $strMethod = $this->checkParam("method",false,0);
        $i =$intPar;
        $nameDir =$objModel->getInfoLoad($load);
        $dirv =$nameDir["file"];
        $srtNameDir =$dirv.'_'.$i.'.csv';
        $strNaneFeli = $srtNameDir;
        if (file_exists($strNaneFeli)){
            $srtNameDir= fopen($srtNameDir, "r");
            $arrNames = fgetcsv($srtNameDir);

            foreach ($arrNames AS $keyHead => $valueHead){
                $arrNames[$keyHead] = str_replace(";", "", $valueHead);
            }

            $intLengthArrHeads = count($arrNames);
            $arrDatos = array();
            $intRow = 0;
            while (($arrDataRow = fgetcsv($srtNameDir)) !== FALSE) {
                $arrTMP = array();
                if(!empty($arrDataRow) && is_array($arrDataRow)){
                    $arrRegister = array();
                    $boolChange = false;

                    $arrDataDelimiter = array();
                    if(count($arrDataRow) == 1){
                        $arrDataDelimiter = explode(",", $arrDataRow[0]);
                    }
                    else{
                        $strArrRow = "";
                        foreach ($arrDataRow AS $keyDataRow => $valueDataRow){
                            $strArrRow .= "{$valueDataRow}";
                        }
                        $arrDataDelimiter = explode(";", $strArrRow);
                    }

                    if( count($arrDataRow) != $intLengthArrHeads ){
                        $intKeyDelimiter = 0;
                        foreach ($arrDataDelimiter AS $keyDelimiter => $valueDelimiter){
                            $valueDelimiter = str_replace(";", "", $valueDelimiter);
                            if (strpos($arrDataDelimiter[$keyDelimiter], "\"") || substr($valueDelimiter, 0, 1) == "\""){
                                if($boolChange){
                                    $arrRegister[$intKeyDelimiter] .= "{$valueDelimiter}";
                                    $boolChange = false;
                                }
                                else{
                                    $intKeyDelimiter = $keyDelimiter;
                                    if(!empty($arrRegister[$intKeyDelimiter])){
                                        $arrRegister[$intKeyDelimiter] .= "{$valueDelimiter}";
                                    }
                                    else{
                                        $arrRegister[$intKeyDelimiter] = "{$valueDelimiter}";
                                    }

                                    $boolChange = true;
                                }
                            }
                            else{
                                if($boolChange){
                                    $arrRegister[$intKeyDelimiter] .= "{$valueDelimiter}";
                                    $boolChange = false;
                                }
                                else{
                                    $intKeyDelimiter = $keyDelimiter;
                                    if(!empty($arrRegister[$intKeyDelimiter])){
                                        $arrRegister[$intKeyDelimiter] .= "{$valueDelimiter}";
                                    }
                                    else{
                                        $arrRegister[$intKeyDelimiter] = "{$valueDelimiter}";
                                    }
                                }
                            }
                        }
                    }
                    else {
                        $arrRegister = $arrDataRow;
                    }
                    foreach ($arrRegister AS $keyRegister => $valueRegister){
                        array_push($arrTMP,$valueRegister);
                    }
                }

                array_push($arrDatos, $arrTMP);
                $intRow++;
            }

            fclose($srtNameDir);
            if ($intPar == 0){
                unlink($dirv);
            }
            if(!empty($arrDatos)){
                if (class_exists($strClasse)) {
                    $objClass = new $strClasse($this->arrParams);
                    if (method_exists($objClass, $strMethod)){
                        $carga = $objClass->$strMethod($arrDatos);
                        if($carga["valido"] == 1){
                            unlink($strNaneFeli);
                            return response::standard(1,"Archivo procesado correctamente");
                        }
                        else{
                            return response::standard(0,"Archivo  no Procesado Intentar de Nuevo");
                        }
                    }
                }


            }
            else{
                return response::standard(0,"Archivo  no procesado correctamente");
            }
        }
        else{
            return response::standard(0,"Archivo  no existe");
        }
    }

    private function process_csv(){
        $load = $this->checkParam("load",false,0);
        $validate = $this->checkParam("process");
        if($load && !empty($validate)){
            $objModel = csv_load_model::getInstance($this->arrParams);
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

    private function clearData(){
        $nameFile = "";
        $name = $this->checkParam("name",false,0);
        $load_id = $this->checkParam("load_id",false,0);
        $objModel = csv_load_model::getInstance($this->arrParams);
        $fileName = $objModel->serchData($load_id);
        unlink($fileName);
        $i= 0;
        while(file_exists($fileName.'_'.$i.'.csv')) {
            $srtNameDir =$fileName.'_'.$i.'.csv';
            unlink($srtNameDir);
            $i++;
        }
        return response::standard(1,"Archivo fueron elimindos correctamente");
    }
}