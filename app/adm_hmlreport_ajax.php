<?php
include_once("core/main.php");

$strReportKey = (!empty($_GET["keyReport"])) ? $_GET["keyReport"] : "key_hml_report";

if(!isset($_SESSION["rpt_hml"][$strReportKey]))
    $strReportKey = "key_hml_report";

$strVentana = $_SESSION["rpt_hml"][$strReportKey]["ventana"];
$strModulo = $_SESSION["rpt_hml"][$strReportKey]["modulo"];

if($strModulo && !check_module($strModulo)) die($lang["ACCESS_DENIED"]);
if($strVentana && !check_user_class($config["admmenu"][$strVentana]["class"])) die($lang["ACCESS_DENIED"]);
/*esta variable no se setea en ningun lado por lo tanto se comento hasta saber que es lo que hace*/
//if(!isset($_SESSION["wt"]["uid"]["rpt_hml"])) die("Los parametros son incorrectos");
//------Variables de session que se setearon al instanciar el objeto del reporteador-------------------
$strQuery = $_SESSION["rpt_hml"][$strReportKey]["query"];
$arrEncabezado = $_SESSION["rpt_hml"][$strReportKey]["encabezado"];
$arrParametros = $_SESSION["rpt_hml"][$strReportKey]["parametros"];

//------Variables iniciales----------------------------------------------------------------------------
$arrPostFiltrado = array();
$arrPostOrdenamiento = array();
$arrPostHaving = array();
$boolOnlyBody = true;
$boolTheadPrint = false;
$arrCheckBoxs = false;
$strTipoPrint = "";
$intPage = 1;

//----------------------------------------Seteando Booleanos-------------------------------------------

//-------------------------------------Bool scroll-----------------------------------------------------
if(isset($_POST["boolPrimerScroll"]) && $_POST["boolPrimerScroll"] == "true"){
    $boolOnlyBody = true;
}
else if(isset($_POST["boolPrimerScroll"]) && $_POST["boolPrimerScroll"] == "false"){
    $boolOnlyBody = false;
}
//-------------------------------------Bool filter-----------------------------------------------------
if(isset($_POST["boolFilter"]) && $_POST["boolFilter"] == "true"){
    $boolOnlyBody = false;
}
else if(isset($_POST["boolFilter"]) && $_POST["boolFilter"] == "false"){
    $boolOnlyBody = true;
}
//-------------------------------------Bool print------------------------------------------------------
if(isset($_POST["boolPrint"]) && $_POST["boolPrint"] == "true"){
    $boolTheadPrint = true;
}
elseif(isset($_POST["boolPrint"]) && $_POST["boolPrint"] == "false"){
    $boolTheadPrint = false;
}
//-------------------------------------Tipo de impresion-----------------------------------------------
if(isset($_POST["strPrint"])){
    $strTipoPrint = $_POST["strPrint"];
}
//-------------------------------------Filter----------------------------------------------------------
if(isset($_POST["filter"])){
    $arrPostFiltrado = $_POST["filter"];
}
else {
    $arrPostFiltrado = array();
}
//-------------------------------------Sorter----------------------------------------------------------
if(isset($_POST["sort"])){
    $arrPostOrdenamiento = $_POST["sort"];
}
else {
    $arrPostOrdenamiento = array();
}
//-------------------------------------Having----------------------------------------------------------
if(isset($_POST["having"])){
    $arrPostHaving = $_POST["having"];
}
else {
    $arrPostHaving = array();
}
//-------------------------------------No de pagina----------------------------------------------------
if(isset($_POST['page'])){
    $intPage = $_POST['page'];
}
//-----------------------------------------------------------------------------------------------------
if(isset($_POST['chkbox'])){
    $arrCheckBoxs = $_POST['chkbox'];
}

$arrEmailData = array();
if(isset($_POST['send_email'])){
    if(isset($_POST['send_email']["para"]) && isset($_POST['send_email']["asunto"])){
        if( isset($_POST['send_email']["body"]) ){
            $_POST['send_email']["body"] = user_input_delmagic($_POST['send_email']["body"]);
        }
        if(is_array($_POST['send_email']["para"]) && count($_POST['send_email']["para"]) > 0){
            $_POST['send_email']["asunto"] = user_input_delmagic($_POST['send_email']["asunto"]);

            foreach($_POST['send_email']["para"] as $intKey => $strPara){
                $strPara = user_input_delmagic($strPara);
                if (is_valid_email($strPara)) {
                    $_POST['send_email']["para"][$intKey] = $strPara;
                }
                else{
                    unset($_POST['send_email']["para"][$intKey]);
                }
            }

            if(count($_POST['send_email']["para"]) > 0){
                $arrEmailData = $_POST['send_email'];
            }
        }
    }
}
//---------------------------------------------Inicializa----------------------------------------------
paginacionReporte($intPage, $strQuery, $boolOnlyBody, $boolTheadPrint, $strTipoPrint, $arrEncabezado, $arrParametros, $arrPostFiltrado, $arrPostOrdenamiento, $arrPostHaving, $arrCheckBoxs, $strReportKey,$arrEmailData);

//-----------------------------------------------------------------------------------------------------
//--------------------------------------------- Funciones ---------------------------------------------

function paginacionReporte($intPagina, $strQuery, $boolOnlyBody, $boolTheadPrint, $strTipoPrint, $arrEncabezado, $arrParametros, $arrPostFiltrado, $arrPostOrdenamiento, $arrPostHaving, $arrCheckBoxs, $strReportKey, $arrEmailData = array()){

    $arrDefault = array();
    $arrDefault["tipo"] = "paginador";
    $arrDefault["porPagina"] = 40;
    $arrDefault["btnAnterior"] = true;
    $arrDefault["btnSiguiente"] = true;
    $arrDefault["btnPrimero"] = true;
    $arrDefault["btnSegundo"] = true;
    $arrDefault["btnExportar"]["csv"] = true;
    $arrDefault["btnExportar"]["pdf"] = true;
    $arrDefault["btnExportar"]["excel"] = true;
    $arrDefault["btnExportar"]["html"] = true;
    $arrDefault["btnExportar"]["csv_mass"] = false;
    $arrDefault["btnExportar"]["position"] = "bottom";
    $arrDefault["btnImprimir"] = false;
    $arrDefault["btnEmail"] = false;
    $arrDefault["pdfEmailAttachment"] = false;
    $arrDefault["LogUsrBCC"] = false;
    $arrDefault["hcols"] = false;
    $arrDefault["titulo"] = "";
    $arrDefault["titulo_uppercase"] = true;
    $arrDefault["txtAnterior"] = "<";
    $arrDefault["txtSiguiente"] = ">";
    $arrDefault["txtPrimero"] = "<<";
    $arrDefault["txtUltimo"] = ">>";

    $arrParametros = array_merge($arrDefault, $arrParametros);

    if(isset($arrParametros["btnExportar"]) && is_array($arrParametros["btnExportar"])){
        $arrParametros["btnExportar"] = array_merge($arrDefault["btnExportar"], $arrParametros["btnExportar"]);
    }
    //drawDebug($arrParametros["pdfEmailAttachment"]);
    $intPaginaActual = $intPagina;
    $intPagina -= 1;
    $intPaginaInicialLimit = $intPagina * $arrParametros["porPagina"];
    $strResultado = "";
    $arrResultado = array();
    $boolOveridePrint = false;
    $strTypeOverride = "";

    //Primero tengo que verificar que si exite ese archivo, esa clase y ese metodo
    if($boolTheadPrint && isset($arrParametros["print_override"]) && isset($arrParametros["print_override"][$strTipoPrint])){
        //Verifico que esten bien seteadas todas las posiciones
        if(!empty($arrParametros["print_override"][$strTipoPrint]["path"]) && !empty($arrParametros["print_override"][$strTipoPrint]["class"]) && !empty($arrParametros["print_override"][$strTipoPrint]["method"])){
            if(file_exists($arrParametros["print_override"][$strTipoPrint]["path"])){
                include_once($arrParametros["print_override"][$strTipoPrint]["path"]);
                if(class_exists($arrParametros["print_override"][$strTipoPrint]["class"])){
                    $objReflectionClassOverride = new ReflectionClass($arrParametros["print_override"][$strTipoPrint]["class"]);
                    if(!empty($arrParametros["print_override"][$strTipoPrint]["params"])){
                        $objClassOverride = $objReflectionClassOverride->newInstanceArgs($arrParametros["print_override"][$strTipoPrint]["params"]);
                    }
                    if(method_exists($objClassOverride, $arrParametros["print_override"][$strTipoPrint]["method"])){
                        $boolOveridePrint = true;
                        $strTypeOverride = "method";
                    }
                }
            }
        }
        //Tambien lo hago para cuando son funciones y no metodos
        elseif(!empty($arrParametros["print_override"][$strTipoPrint]["path"]) && !empty($arrParametros["print_override"][$strTipoPrint]["function"])){
            if(file_exists($arrParametros["print_override"][$strTipoPrint]["path"])){
                include_once($arrParametros["print_override"][$strTipoPrint]["path"]);
                if(function_exists($arrParametros["print_override"][$strTipoPrint]["function"])){
                    $boolOveridePrint = true;
                    $strTypeOverride = "function";
                }
            }
        }
    }
    //drawDebug($strTypeOverride,"strTypeOverride");
    //Ejecuto el override ya que se sabe que entro y existe el objeto de la clase
    if($boolOveridePrint){
        $strQuery = aplicarFiltros($strQuery, (is_string($strQuery)), $arrEncabezado, $arrPostFiltrado, $arrPostOrdenamiento, $arrPostHaving, $intPaginaInicialLimit, $arrParametros["porPagina"]);
        if($strTypeOverride == "method"){
            //$objClassOverride = new $arrParametros["print_override"][$strTipoPrint]["class"]();
            $objClassOverride -> $arrParametros["print_override"][$strTipoPrint]["method"]($strQuery);
        }
        elseif($strTypeOverride == "function"){
            $arrParametros["print_override"][$strTipoPrint]["function"]($strQuery);
        }
        die;
    }
    else{
        $arrResultado = tablaReporte($strQuery, $intPaginaInicialLimit, $arrParametros["porPagina"], $intPagina, $boolOnlyBody, $boolTheadPrint, $strTipoPrint, $arrEncabezado, $arrPostFiltrado, $arrPostOrdenamiento, $arrPostHaving, $arrParametros["titulo"], $arrParametros, $strReportKey);
    }

    $strTmpTabla = $arrResultado["tabla"];

    /* ------------------------------------------------------------------------------------------- */
    /* ------------------------------------- Creando Contenido ----------------------------------- */
    /* ------------------------------------------------------------------------------------------- */
    if($arrParametros["tipo"] == "paginador" && !$boolTheadPrint){
        $intNumeroPaginaciones = $arrResultado["numPagin"];
        //---------------------- Calculando el valor inicial y final para la iteracion ------------------------
        if($arrResultado["countR"] > $arrParametros["porPagina"]){
            if ($intPaginaActual >= 7) {
                $intInicioIteracion = $intPaginaActual - 3;
                if ($intNumeroPaginaciones > $intPaginaActual + 3)
                    $intFinIteracion = $intPaginaActual + 3;
                else if ($intPaginaActual <= $intNumeroPaginaciones && $intPaginaActual > $intNumeroPaginaciones - 6) {
                    $intInicioIteracion = $intNumeroPaginaciones - 6;
                    $intFinIteracion = $intNumeroPaginaciones;
                }
                else {
                    $intFinIteracion = $intNumeroPaginaciones;
                }
            }
            else {
                $intInicioIteracion = 1;
                if ($intNumeroPaginaciones > 7)
                    $intFinIteracion = 7;
                else
                    $intFinIteracion = $intNumeroPaginaciones;
            }

            $strResultado .= "<div id='pagination_container_all-{$strReportKey}' class='pagination_container_all'>";
            $strResultado .= "<div id='pagination_container-{$strReportKey}' class='pagination_container pagination'>";
            $strResultado .= "<ul>";

            //---------------- Para habilitar el boton "Primero" ------------------
            if ($arrParametros["btnPrimero"] && $intPaginaActual > 1) {
                $strResultado .= "<li p='1' class='active'>{$arrParametros["txtPrimero"]}</li>";
            }
            else if ($arrParametros["btnPrimero"]) {
                $strResultado .= "<li p='1' class='inactive'>{$arrParametros["txtPrimero"]}</li>";
            }

            //---------------- Para habilitar el boton "anterior" ------------------
            if ($arrParametros["btnAnterior"] && $intPaginaActual > 1) {
                $intPaginaAnterior = $intPaginaActual - 1;
                $strResultado .= "<li p='$intPaginaAnterior' class='active'>{$arrParametros["txtAnterior"]}</li>";
            }
            else if ($arrParametros["btnAnterior"]) {
                $strResultado .= "<li class='inactive'>{$arrParametros["txtAnterior"]}</li>";
            }

            for ($i = $intInicioIteracion; $i <= $intFinIteracion; $i++) {

                if ($intPaginaActual == $i){
                    $strResultado .= "<li p='$i' class='active slc_page'>{$i}</li>";
                }
                else{
                    $strResultado .= "<li p='$i' class='active'>{$i}</li>";
                }
            }

            //---------------- Para habilitar el boton "siguiente" ------------------
            if ($arrParametros["btnSiguiente"] && $intPaginaActual < $intNumeroPaginaciones) {

                $intPaginaSiguiente = $intPaginaActual + 1;
                $strResultado .= "<li p='$intPaginaSiguiente' class='active'>{$arrParametros["txtSiguiente"]}</li>";

            }
            else if ($arrParametros["btnSiguiente"]) {
                $strResultado .= "<li class='inactive'>{$arrParametros["txtSiguiente"]}</li>";
            }

            //---------------- Para habilitar el boton "Ultimo" ------------------
            if ($arrParametros["btnSegundo"] && $intPaginaActual < $intNumeroPaginaciones) {
                $strResultado .= "<li p='$intNumeroPaginaciones' class='active'>{$arrParametros["txtUltimo"]}</li>";
            }
            else if ($arrParametros["btnSegundo"]) {
                $strResultado .= "<li p='$intNumeroPaginaciones' class='inactive'>{$arrParametros["txtUltimo"]}</li>";
            }

            $strIrA = "<div id='goto_container-{$strReportKey}' class='goto_container'><p><input type='text' class='input_ir_a' size='1' /><input type='button' id='go_btn-{$strReportKey}' class='botton_ir_a' value='Ir A'/></p></div>";
            $strTotalPaginacion = "<div class='num_paginas'><p class='total_num_pagin' a='{$intNumeroPaginaciones}'>Pagina <b>" . $intPaginaActual . "</b> de <b>{$intNumeroPaginaciones}</b></p></div>";
            $strResultado = $strResultado . "</ul></div>" . $strIrA . $strTotalPaginacion . "</div>";

        }
    }

    $arrResultado["paginacion"] = utf8_encode($strResultado);

    $strResultado = $strTmpTabla.$strResultado;

    if($boolOnlyBody && !$boolTheadPrint)
        $strResultado = agregarHerramientas($strResultado, $arrParametros, $strReportKey);

    $arrResultado["tabla"] = utf8_encode($strResultado);

    if(!$boolTheadPrint){

        unset($arrResultado["csv"]);
        unset($arrResultado["countR"]);
        unset($arrResultado["numPagin"]);
        print json_encode($arrResultado);

    }
    else if ($boolTheadPrint && $strTipoPrint){

        $strRptName = ($arrParametros["titulo"] != "")?str_replace(array(" ","\n","\r","\t",":","   "),"_",strip_tags($arrParametros["titulo"])):"rpt_";

        switch($strTipoPrint){
            case "html":
                draw_rpt_header($strRptName.$strTipoPrint,$strTipoPrint);
                print $strResultado;
                draw_rpt_footer(true);
            break;
            case "email":
                if(!empty($arrEmailData) && isset($arrEmailData["para"]) && isset($arrEmailData["asunto"])){
                    $arrReturn = array();
                    //!!!!!!!!!!!!!!!!CUANDO SE DEBUGEE POR AQUI RECORDAR COMENTAR ESTOS ob_start() Y ob_end_clean()!!!!!!!!!!!!!!!!
                    //!!!!!!!!!!!!!!!!PERO NO BORRARLOS PARA QUE FUNCIONE BIEN TODO!!!!!!!!!!!!!!!!
                    ob_start();
                    $arrReturn = sendReportEmail($strResultado, $strRptName, $arrEmailData["para"], $arrEmailData["asunto"], $arrParametros["pdfEmailAttachment"], $arrParametros["LogUsrBCC"]);
                    ob_end_clean();
                    print json_encode($arrReturn);
                    die;
                }
            break;
            case "html_print":
                draw_rpt_header($strRptName.$strTipoPrint,$strTipoPrint,true);
                print $strResultado;
                draw_rpt_footer(true);
            break;
            case "excel":
                draw_rpt_header($strRptName.$strTipoPrint, $strTipoPrint);
                print $strResultado;
                draw_rpt_footer(true);
            break;
            case "csv":
                draw_rpt_header($strRptName.$strTipoPrint,$strTipoPrint);
                resultadoCSV($arrResultado["csv"]);
                draw_rpt_footer();
            break;
            case "pdf":
                createPdfReport($strResultado, $strRptName);
            break;
            default:
                draw_rpt_header($strRptName.$strTipoPrint,"html");
                print $strResultado;
                draw_rpt_footer();
            break;
        }
    }
}

function tablaReporte($strQuery, $intPaginaInicialLimit, $intPorPagina, $intPagina, $boolCabeceraRPT = true, $boolTheadPrint = false,
                      $strTipoPrint, $arrEncabezado = array(), $arrPostFiltrado = array(), $arrPostOrdenamiento = array(), $arrPostHaving, $strTitulo, $arrParametros, $strReportKey){

    global $cfg;

    $boolIsString = (is_string($strQuery));

    /*------------Si es arreglo guardo los titulos por si encuentra datos ---------*/
    if(!$boolIsString){
        $arrTitulos = array();
        if(!empty($strQuery[0]) && is_array($strQuery[0])){
            foreach($strQuery[0] AS $key => $val){
                $arrTitulos[] = $key;
            }
        }
    }

    $strQuery = aplicarFiltros($strQuery, $boolIsString, $arrEncabezado, $arrPostFiltrado, $arrPostOrdenamiento, $arrPostHaving, $intPaginaInicialLimit, $intPorPagina);
    $qTMP = false;
    $intCount = 0;
    $arrCustomTmp = $strQuery;
    if($boolIsString){
        $strQuerya = ($boolTheadPrint)?$strQuery:$strQuery." LIMIT $intPaginaInicialLimit, $intPorPagina";
        $qTMP = db_query($strQuerya);
    }
    else {
        $intCount = count($strQuery);
        if(!$boolTheadPrint){
            $strQuery = array_slice($strQuery, $intPaginaInicialLimit,$intPorPagina,true);
        }
    }

    $arrResultado = array();
    $arrCSV = array();
    $intContCSV = 1;
    $strTInputs = "";
    $strTInputsO = "";
    $strTDropdownDiv = "";
    $strTDropdownArrow = "";
    $strThead = "";
    $strTheadExtra = "";
    $strTheadForH = "";
    $strTSortInputs = "";
    $strTbody = "";
    $strTheadPrint = "";
    $strTituloRpt = "";
    $strTituloRpt2 = "";
    $boolThead = true;
    $arrPosTotal = array();
    $intCantCols = 0;
    $boolTotalizador = isset($arrEncabezado["total"])?true:false;

    $boolNMR = true;
    $strClassRow0 = ($boolTheadPrint && $strTipoPrint == "pdf")?"class=\"row0\"":"class=\"row0_rpthml\"";
    $strClassRow1 = ($boolTheadPrint && $strTipoPrint == "pdf")?"class=\"row1\"":"";
    $strClassRow2 = ($boolTheadPrint && $strTipoPrint == "pdf")?"class=\"row2\"":"";
    $strRow = $strClassRow1;
    $intClaseFila = 1;
    $strImgBuscar = isset($arrEncabezado["filter"]);
    $intCountR = 0;
    $intColSpan = 0;
    $strTmpSort = "";

    //---------------Ver cantidad total de resultados para el paginador-------------------
    $intCont = 0;
    if($boolIsString){
        $qTMPNumRow = db_query($strQuery);
        $intCont = db_num_rows($qTMPNumRow);
    }
    else {
        $intCont = $intCount;
    }

    $intNumeroPaginaciones = ceil($intCont / $intPorPagina);
    $arrResultado["countR"] = $intCont;
    $arrResultado["numPagin"] = $intNumeroPaginaciones;
    $boolPaginaFinal = (($intPaginaInicialLimit+$intPorPagina) >= $intCont)?true:false;

    if($intNumeroPaginaciones > 0){
        $intTmpCont = 0;
        while($rTMP = recorrerArrayManual($qTMP, $strQuery, $boolIsString)){
            $boolNMR = false;
            $intTmpCont++;

            //-----color para la fila------
            $strBgRowColor = "";
            if(isset($arrEncabezado["row_bgcolor"])){
                $strTmpKey = key($arrEncabezado["row_bgcolor"]);
                if(isset($rTMP[$strTmpKey]) && isset($arrEncabezado["row_bgcolor"][$strTmpKey][$rTMP[$strTmpKey]])){
                    $strBgRowColor = "bgcolor=".$arrEncabezado["row_bgcolor"][$strTmpKey][$rTMP[$strTmpKey]]["bgcolor"];
                }
            }

            $strTbody .= "<tr {$strBgRowColor} >";
            $intColSpan = 0;

            if($boolThead && $boolTheadPrint){
                $strTheadPrint .="<tr>";
            }
            elseif ($boolThead && $boolCabeceraRPT){
                $strThead .= (!$arrParametros["hcols"])?"<tr id='hml_rpt_tr_filters-{$strReportKey}'>":"";
                $strTheadForH .= ($arrParametros["hcols"])?"<tr>":"";
                $strTInputs .= "<tr>";
                $strTSortInputs .= "<tr style=\"display:none\">";
            }

            $intTmpCont2 = 1;

            if(isset($rTMP) && is_array($rTMP)){
                foreach ($rTMP AS $key => $value){
                    if(isset($arrEncabezado["primaryKey"])){
                        (isset($rTMP[$arrEncabezado["primaryKey"]]))?$intPrimaryKey = $rTMP[$arrEncabezado["primaryKey"]]:$intPrimaryKey=0;
                    }

                    $strColumnId = (isset($arrEncabezado["column_id"]) && isset($arrEncabezado["column_id"][$key]))?(isset($rTMP[$arrEncabezado["column_id"][$key]])?"id='{$rTMP[$arrEncabezado["column_id"][$key]]}'":""):"";

                    $value = htmlSafePrint($value, false);

                    //-----alineacion de tipo de dato-------
                    $strTMPAlign =  ($strTipoPrint == "pdf")?"center":alineacionTipoDato($value, $key, $arrEncabezado);

                    //-----dibujado de funcion onclick-------
                    $strOnClick  = (isset($arrEncabezado["onclick"]) && !$boolTheadPrint)?funcionOnClick($key, $rTMP, $arrEncabezado):"";

                    //-----dibujado de datos en el tbody-------
                    $strValueTmp = trim($value);
                    $strTmpValue = (!empty($strValueTmp))?$value:"&nbsp;";
                    if(isset($arrParametros["crop"])){
                        $intCrop = (isset($arrParametros["crop"]["num"][$key]))?intval($arrParametros["crop"]["num"][$key]):15;
                        if(isset($arrParametros["crop"]["col"][$key]) && strlen($strTmpValue) > $intCrop){
                            $strTmpValue = substr($strTmpValue, 0, $intCrop)."...";
                        }
                    }
                    unset($strValueTmp);
                    //$strTbody .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":((isset($arrEncabezado["checkbox"]) && isset($arrEncabezado["checkbox"][$key]))?"<td $strRow  align=\"$strTMPAlign\" {$strOnClick}><input type=\"checkbox\" name=\"chkbox[{$arrEncabezado["checkbox"][$key]}_{$key}]\"  value=\"{$key}\"/></td>":"<td $strRow  align=\"$strTMPAlign\" {$strOnClick}>{$strTmpValue}</td>");
                    $boolPrintInput = ((isset($arrEncabezado["input_text"]) && isset($arrEncabezado["input_text"][$key])) && $strTipoPrint != "");
                    $boolInput = (isset($arrEncabezado["input_text"]) && isset($arrEncabezado["input_text"][$key]));

                    $boolPrintChkBox = ((isset($arrEncabezado["checkbox"]) && isset($arrEncabezado["checkbox"][$key])) && $strTipoPrint != "");
                    $boolChkBox = (isset($arrEncabezado["checkbox"]) && isset($arrEncabezado["checkbox"][$key]));

                    if(isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key])){}
                    elseif (!$boolPrintChkBox || !$boolChkBox || !$boolPrintInput || !$boolInput){

                        if(isset($arrEncabezado["input_text"]) && isset($arrEncabezado["input_text"][$key]) && $strTipoPrint == ""){
                            $strParamsInput = "";
                            if(isset($arrEncabezado["input_text_params"]) && isset($arrEncabezado["input_text_params"][$key]))$strParamsInput = $arrEncabezado["input_text_params"][$key];
                            $strInputValue = isset($rTMP[$arrEncabezado["input_text"][$key]])?$rTMP[$arrEncabezado["input_text"][$key]]:"";
                            $strTbody .= "<td {$strRow}  align=\"{$strTMPAlign}\" {$strOnClick}><input type=\"text\" id=\"{$intPrimaryKey}\" name=\"inputText[{$arrEncabezado["input_text"][$key]}][]\"  value=\"{$strInputValue}\" {$strParamsInput}/></td>";
                        }
                        else if(isset($arrEncabezado["checkbox"]) && isset($arrEncabezado["checkbox"][$key]) && $strTipoPrint == ""){
                            $strChkBoxValue = isset($rTMP[$arrEncabezado["checkbox"][$key]])?$rTMP[$arrEncabezado["checkbox"][$key]]:"";
                            $strChecked = (isset($arrEncabezado["checkbox_checked"]) && isset($arrEncabezado["checkbox_checked"][$key]))?(isset($rTMP[$arrEncabezado["checkbox_checked"][$key]])?(!empty($rTMP[$arrEncabezado["checkbox_checked"][$key]])?"checked='checked'":""):""):"";
                            $strTbody .= "<td {$strRow}  align=\"{$strTMPAlign}\" {$strColumnId} {$strOnClick}><input type=\"checkbox\" name=\"chkbox[{$arrEncabezado["checkbox"][$key]}][]\"  value=\"{$strTmpValue}\" {$strChecked}/></td>";
                        }
                        else {
                            $strTbody .= "<td {$strRow}  align=\"{$strTMPAlign}\" {$strColumnId} {$strOnClick}>{$strTmpValue}</td>";
                        }
                    }

                    //-----arreglo de datos para usar en el exportado de csv-------
                    $arrCSV[$intContCSV][] = (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":$value;

                    //-----tomando posicion de columnas para totalizadores-------
                    if($boolTotalizador && isset($arrEncabezado["total"][$key])){
                        $arrPosTotal[$intTmpCont2] = $key;
                    }

                    if($boolThead && $boolTheadPrint){
                        //-----dibujado de cabecera solo para impresion-------
                        $strTheadPrint .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<th $strClassRow0>{$key}</th>";
                        $arrCSV[0][] = (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":$key;
                    }
                    elseif ($boolThead && $boolCabeceraRPT){

                        //-----dibujado de tipo de sort-------
                        $strTMPSortKey = isset($arrEncabezado["sort"][$key])?$key."-sorter":"";
                        $strTmpSort = (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<div id=\"{$strTMPSortKey}\" class=\"asc\" ><div class=\"up-arrow\" ></div><div class=\"down-arrow\"></div></div>";
                        if(isset($arrEncabezado["sort"][$key]) && isset($arrPostOrdenamiento[$key])){
                            switch($arrPostOrdenamiento[$key]){
                                case "desc":
                                    $strTmpSort = (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<div id=\"{$strTMPSortKey}\" class=\"asc\" ><div class=\"down-arrow\"></div></div>";
                                    break;
                                case "asc":
                                    $strTmpSort = (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<div id=\"{$strTMPSortKey}\" class=\"desc\"><div class=\"up-arrow\"></div></div>";
                                    break;
                            }
                        }

                        //-----dibujado de filtros-------
                        $strTmpValue = isset($arrPostFiltrado[$key])?$arrPostFiltrado[$key]:"";
                        $strTmpValueH = isset($arrPostHaving[$key])?$arrPostHaving[$key]:"";
                        $intStrLen = strlen($key)+5;
                        $strTInputsO = isset($arrEncabezado["filter"][$key])?((isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<br><input type=\"text\" name=\"filter[{$key}]\" value=\"{$strTmpValue}\" class=\"filters field_textbox\" />"):"";
                        $strTInputsOH = isset($arrEncabezado["having"][$key])?((isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<br><input type=\"text\" name=\"having[{$key}]\" value=\"{$strTmpValueH}\" class=\"havings field_textbox\" />"):"";
                        $strTInputsOD = ($strTInputsO == "" && isset($arrEncabezado["dropdown"][$key]))?"<br><input type=\"text\" name=\"filter[{$key}]\" value=\"{$strTmpValue}\" class=\"filters\" readonly/>":"";

                        //-----dibujado de dropdown-------
                        $strTmpValue = isset($arrPostFiltrado[$key])?$arrPostFiltrado[$key]:"";
                        //$strTDropdownDiv = isset($arrEncabezado["filter"][$key])?((isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<br><input type=\"text\" name=\"filter[{$key}]\" value=\"{$strTmpValue}\" class=\"filters\" />"):"";
                        $strTDropdownArrow = "";
                        $strTDropdownDiv = "";

                        if(isset($arrEncabezado["dropdown"][$key]) && !(isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))){

                            $strNamefield = trim($key);
                            $strTDropdownArrow = "<div id=\"dropdown_{$strNamefield}\" class=\"box_cont_dropdown\"><div class=\"dropdown-arrow\">&nbsp;</div></div>";
                            $strTDropdownDiv = "<div id=\"dropdownmenu_{$strNamefield}\" class='menudropdown pop' style='color:black'>";

                            foreach($arrEncabezado["dropdown"][$key] as $key => $value){
                                $strTDropdownDiv .= "<p><label style='color:black'>{$value}</label></p>";
                            }

                            $strTDropdownDiv .= "</div>";
                        }

                        //-----dibujado de cabecera-------
                        $strTmpSort = isset($arrEncabezado["sort"][$key])?"<div class=\"box_cont\">{$strTmpSort}</div>":"";
                        //$strImgBuscar = isset($arrEncabezado["filter"][$key])?"<div class=\"contT-left\"></div>":"<div style=\"height:34px; width:5px; display:inline-block;\"></div>";
                        $strImgBuscar = "";
                        $strTraduccion="";
                        $strTitle="";
                        if(isset($arrEncabezado["new_title"]) && isset($arrEncabezado["new_title"][$key])){
                            $strTitle=$arrEncabezado["new_title"][$key];
                        }
                        else{
                            $strTitle=$key;
                        }
                        if( (isset($cfg["biblioteca"]["tooltip_reporteador"])) && $cfg["biblioteca"]["tooltip_reporteador"]){
                            if(isset($arrEncabezado["traduccion"])){
                                if(isset($arrEncabezado["traduccion"][$key])){
                                    $strTraduccion = $arrEncabezado["traduccion"][$key];
                                }
                            }

                            $strCondicion=(!empty($strTraduccion))?"<span>{$strTraduccion}</span>":"";
                            $strDivTitulo = "<div id='col_name_{$strTitle}' class=\"contT-center\"><div class='tooltips'>{$strTitle}{$strTInputsO}{$strTInputsOH}{$strTInputsOD}{$strCondicion}</div></div>";
                            $strDivTituloForH = "<div class=\"contT-center\"><div class='tooltipsH' id='divHorizontal-{$strReportKey}'>{$strTitle}{$strCondicion}</div></div>";
                        }
                        else{
                            $strDivTitulo = "<div class=\"contT-center\">{$strTitle}{$strTInputsO}{$strTInputsOH}{$strTInputsOD}</div>";
                            $strDivTituloForH = "<div class=\"contT-center\">{$strTitle}</div>";
                        }

                        if($arrParametros["hcols"]){
                            $strThead .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<div class=\"row0_rpthml_cont1\" style=\"display=inline-block; width:100%;\">{$strImgBuscar}{$strDivTitulo}{$strTmpSort}{$strTDropdownArrow}{$strTDropdownDiv}</div>";
                            $strTheadForH .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<th class=\"row0_rpthml\"><div class=\"row0_rpthml_cont\">{$strDivTituloForH}{$strTDropdownDiv}</div></th>";
                        }
                        else{
                            $strThead .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<th class=\"row0_rpthml\"><div class=\"row0_rpthml_cont\">{$strImgBuscar}{$strDivTitulo}{$strTmpSort}{$strTDropdownArrow}{$strTDropdownDiv}</div></th>";
                        }

                        //-----dibujado de sorters-------
                        $strTmpValue = isset($arrPostFiltrado[$key])?user_input_delmagic($arrPostFiltrado[$key],true):"";
                        $strTSortInputs .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<th class=\"sorter\">";
                        $strTSortInputs .= isset($arrEncabezado["sort"][$key])?((isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"<input type=\"text\" id=\"{$key}-isorter\" name=\"sort[{$key}]\" value=\"{$strTmpValue}\" class=\"sorters\"/>"):"";
                        $strTSortInputs .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?"":"</th>";
                    }
                    $intTmpCont2 += (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$key]))?0:1;
                    $intCantCols = $intTmpCont2;
                    if( !isset($arrEncabezado["hidden"][$key]) ){
                        $intColSpan++;
                    }
                }
            }

            $intContCSV++;
            if($intClaseFila == 1){
                $intClaseFila = 2;
                $strRow = $strClassRow1;
            }
            else {
                $intClaseFila = 1;
                $strRow = $strClassRow2;
            }

            $strTbody .= "</tr>";

            if($boolThead && $boolTheadPrint){
                $strTheadPrint .= "</tr>";
            }
            else if($boolThead && $boolCabeceraRPT){
                if($arrParametros["hcols"]){
                    $strThead = "<tr><th colspan='{$intColSpan}'>{$strThead}</th></tr>";
                    $strTheadForH .= "</tr>";
                }
                else{
                    $strThead .= "</tr>";
                }

                $strTInputs .="</tr>";
                $strTSortInputs .="</tr>";
            }

            $boolThead = false;
            $intCountR++;
        }
        if(isset($arrParametros["extra_header"])){
            $strTheadExtra = "<tr><td colspan='{$intColSpan}' id='extra_header-{$strReportKey}'>{$arrParametros["extra_header"]}</td></tr>";
        }

        ksort($arrCSV);
        $strTbody = "<tbody> {$strTbody} </tbody>";
    }
    else {
        $intColSpan = 0;
        if($boolThead && $boolTheadPrint){
            $strTheadPrint .="<tr>";
        }
        elseif($boolThead && $boolCabeceraRPT){
            //$strThead .="<tr>";
            $strThead .= (!$arrParametros["hcols"])?"<tr>":"";
            $strTheadForH .= ($arrParametros["hcols"])?"<tr>":"";
            $strTInputs .= "<tr>";
        }
        //----------Recordatorio------------
        //----------Ver por que entra aqui cuando se busca con array algo que contenga un numero------------
        //----------Y corregir que cuando es array no llame al db_get_fields------------

        if($boolIsString){
            $rTMP = db_get_fields($qTMP);
        }
        else{
            $rTMP = $arrTitulos;
        }
        if(isset($rTMP) && is_array($rTMP)){
            while($strTMP = each($rTMP)){
                if($boolThead && $boolTheadPrint){
                    $strTheadPrint .="<th $strClassRow0>{$strTMP["key"]}</th>";
                }
                elseif($boolThead && $boolCabeceraRPT){
                    $strTmpValue = isset($arrPostFiltrado[$strTMP["key"]])?$arrPostFiltrado[$strTMP["key"]]:"";
                    $strTmpValueH = isset($arrPostHaving[$strTMP["key"]])?$arrPostHaving[$strTMP["key"]]:"";
                    $intStrLen = strlen($strTMP["key"]);
                    $strTInputsO = isset($arrEncabezado["filter"][$strTMP["key"]])?((isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$strTMP["key"]]))?"":"<br><input type=\"text\" name=\"filter[{$strTMP["key"]}]\" value=\"{$strTmpValue}\" class=\"filters field_textbox\" size=\"{$intStrLen}\"/>"):"";
                    $strTInputsOH = isset($arrEncabezado["having"][$strTMP["key"]])?((isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$strTMP["key"]]))?"":"<br><input type=\"text\" name=\"having[{$strTMP["key"]}]\" value=\"{$strTmpValueH}\" class=\"havings field_textbox\" size=\"{$intStrLen}\"/>"):"";

                    //-----dibujado de cabecera-------
                    //$strImgBuscar = isset($arrEncabezado["filter"][$strTMP["key"]])?"<div class=\"contT-left\"></div>":"<div style=\"height:34px; width:5px; display:inline-block;\"></div>";
                    $strImgBuscar = "";
                    if(isset($arrEncabezado["new_title"]) && isset($arrEncabezado["new_title"][$strTMP["key"]])){
                        $strTitle=$arrEncabezado["new_title"][$strTMP["key"]];
                    }
                    else{
                        $strTitle=$strTMP["key"];
                    }
                    if( (isset($cfg["biblioteca"]["tooltip_reporteador"])) && $cfg["biblioteca"]["tooltip_reporteador"]){
                        if(isset($arrEncabezado["traduccion"])){
                            if(isset($arrEncabezado["traduccion"][$strTMP["key"]])){
                                $strTraduccion = $arrEncabezado["traduccion"][$strTMP["key"]];
                            }
                        }

                        $strCondicion=(!empty($strTraduccion))?"<span>{$strTraduccion}</span>":"";
                        $strDivTitulo = "<div id='col_name_{$strTitle}' class=\"contT-center\"><div class='tooltips'>{$strTitle}{$strTInputsO}{$strTInputsOH}{$strCondicion}</div></div>";
                        $strDivTituloForH = "<div class=\"contT-center\"><div class='tooltipsH' id='divHorizontal-{$strReportKey}'>{$strTitle}{$strCondicion}</div></div>";
                    }
                    else{
                         $strDivTitulo = "<div class=\"contT-center\">{$strTitle}{$strTInputsO}{$strTInputsOH}</div>";
                         $strDivTituloForH = "<div class=\"contT-center\">{$strTitle}</div>";
                    }
                    //$strThead .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$strTMP["key"]]))?"":"<th class=\"row0_rpthml\"><div class=\"row0_rpthml_cont\" >{$strImgBuscar}{$strDivTitulo}</th>";
                    if($arrParametros["hcols"]){
                        $strThead .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$strTMP["key"]]))?"":"<div class=\"row0_rpthml_cont1\" style=\"display=inline-block; width:100%;\">{$strImgBuscar}{$strDivTitulo}</div>";
                        $strTheadForH .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$strTMP["key"]]))?"":"<th class=\"row0_rpthml\"><div class=\"row0_rpthml_cont\">{$strDivTituloForH}</div></th>";
                    }
                    else{
                        $strThead .= (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$strTMP["key"]]))?"":"<th class=\"row0_rpthml\"><div class=\"row0_rpthml_cont\">{$strImgBuscar}{$strDivTitulo}{$strTmpSort}</div></th>";
                    }
                }
                $intColSpan += (isset($arrEncabezado["hidden"]) && isset($arrEncabezado["hidden"][$strTMP["key"]]))?0:1;
            }
        }
        if($boolThead && $boolTheadPrint){
            $strTheadPrint .= "</tr>";
        }
        else if($boolThead && $boolCabeceraRPT){
            //$strThead .="</tr>";
            if($arrParametros["hcols"]){
                $strThead = "<tr><th colspan='{$intColSpan}'>{$strThead}</th></tr>";
                $strTheadForH .= "</tr>";
            }
            else{
                $strThead .= "</tr>";
            }
            $strTInputs .="</tr>";
        }
        $boolThead = false;

        $strTbody = "<tbody> <tr> <td colspan=\"{$intColSpan}\" align=\"center\">No se encontraron resultados</td> </tr> </tbody>";
    }

    if($boolTheadPrint){
        $strTheadPrint = "{$strTheadPrint}";
    }
    else if($boolCabeceraRPT){
        $strThead = "{$strThead}{$strTheadForH}";
        $strTSortInputs = isset($arrEncabezado["sort"])?$strTSortInputs:"";
        $strTInputs = isset($arrEncabezado["filter"])?$strTInputs:"";
    }

    if(($boolPaginaFinal && $boolTotalizador) || $boolTheadPrint){
        $boolRows = ($boolTheadPrint && $strTipoPrint == "pdf")?true:false;
        $strQuery = (!is_string($strQuery))?$arrCustomTmp:$strQuery;
        
        if($boolTotalizador){
            $arrResTotal = agregarTotalizador($strQuery, $arrEncabezado["total"], $arrPosTotal, $intCantCols-1, $boolRows, $arrParametros);
            $strTbody .= $arrResTotal["resultado"];
            array_push($arrCSV,$arrResTotal["total_csv"]);
        }
    }

    if($boolNMR){
        $arrResultado["NMR"] = "NMR";
    }
    $strTituloRpt = "";
    $strTituloRptPdf = "";
    if($strTitulo != ""){
        if(isset($arrParametros["titulo_uppercase"]) && $arrParametros["titulo_uppercase"] == false){
              $strTitulo = $strTitulo;
        }
        else{
            $strTitulo = strtoupper($strTitulo);
        }

        if(!empty($arrParametros["pdfEmailAttachment"]) && ($strTipoPrint == "email" || $strTipoPrint == "pdf")){
            if( check_module("logistica") ){
                if(isset($arrParametros["extra_header"])){
                    $strTituloRptPdf = $arrParametros["extra_header"];
                }
                $strTituloRpt2 = "<table align='center'><thead><tr><td align=\"center\"><b>".$strTitulo."</b></td></tr></thead></table>";
            }
            else{
                $strTituloRpt = "<tr><td colspan=\"$intColSpan\" align=\"center\"><b>".$strTitulo."</b></td></tr>";
                //$strTituloRptPdf = "<table>{$strTheadExtra}</table>";
            }
        }
        else{
            $strTituloRpt = "{$strTheadExtra}<tr><td colspan=\"$intColSpan\" align=\"center\"><b>".$strTitulo."</b></td></tr>";
        }

    }

    if(!$boolCabeceraRPT || $intPagina > 1){
        $arrResultado["tabla"] = $strTbody;
    }
    else {
        $strTableWidth = isset($arrParametros["report_width"])?"style=\"width:{$arrParametros["report_width"]};\"":"";
        $strTableClassWidth = !isset($arrParametros["report_width"])?"tbl_hml_report_width":"";
        $arrResultado["tabla"] = "<div class='data'>
                                    {$strTituloRpt2}
                                    {$strTituloRptPdf}
                                    <table align='center' id=\"tbl_hml_rpt-{$strReportKey}\" name=\"tbl_hml_rpt-{$strReportKey}\" class='tbl_hml_rpt $strTableClassWidth' {$strTableWidth}>
                                        <thead id='thead_hml_rpt-{$strReportKey}'>
                                            {$strTituloRpt}
                                            {$strTheadPrint}
                                            {$strThead}
                                            {$strTSortInputs}
                                        </thead>
                                        {$strTbody}
                                    </table>
                                </div>";
    }

    $arrResultado["csv"] = $arrCSV;
    return $arrResultado;
}

function alineacionTipoDato($strValue, $strKey, $arrEncabezado){
    $strAlign = "";
    if(isset($arrEncabezado["align"][$strKey])){
        $strAlign = $arrEncabezado["align"][$strKey];
    }
    else {
        if(is_numeric($strValue))
            $strAlign = "right";
        elseif(strtotime($strValue))
            $strAlign = "center";
        else
            $strAlign = "left";
    }
    return $strAlign;
}

function agregarHerramientas($strResultado, $arrParametros, $strReportKey){
    $strHerramientasTop = "";
    $strHerramientasBottom = "";

    //-------------------Botones Barra Inferior-----------------------
    if(isset($arrParametros["btnExportar"]) && $arrParametros["btnExportar"] != false){
        $strOpcHtml = (isset($arrParametros["btnExportar"]["html"]) && $arrParametros["btnExportar"]["html"] == true)?"<p><label class=\"html\">HTML</label></p>":"";
        $strOpcPdf = (isset($arrParametros["btnExportar"]["pdf"]) && $arrParametros["btnExportar"]["pdf"] == true)?"<p><label class=\"pdf\">PDF</label></p>":"";
        $strOpcExcel = (isset($arrParametros["btnExportar"]["excel"]) && $arrParametros["btnExportar"]["excel"] == true)?"<p><label class=\"excel\">Excel</label></p>":"";
        $strOpcCsv = (isset($arrParametros["btnExportar"]["csv"]) && $arrParametros["btnExportar"]["csv"] == true)?"<p><label class=\"csv\">CSV</label></p>":"";
        $strMenu = "<div id=\"prt_frm-{$strReportKey}\" class='messagepop pop'>
                        <div class=\"contenido_titulo_menu\" >
                            <div class=\"titulo_menu\">
                                <b>Exportar en:</b>
                            </div>
                            <div class=\"cerrar_menu\">
                                <a id='close_prt_frm-{$strReportKey}' href='/'>X</a>
                            </div>
                        </div>
                        {$strOpcHtml}
                        {$strOpcPdf}
                        {$strOpcExcel}
                        {$strOpcCsv}
                    </div>";

        //-------------Boton Exportar lo ubico dependiendo el parametro "position"-----------------------------
        if($arrParametros["btnExportar"]["position"] == "bottom"){
            $strHerramientasBottom .= "<div id=\"exportar_html-{$strReportKey}\" class=\"herramientas download-icon\"><span></span>Descargar reporte{$strMenu}</div>";
        }
        elseif($arrParametros["btnExportar"]["position"] == "top"){
            $strHerramientasTop .= "<div id=\"exportar_html-{$strReportKey}\" class=\"herramientas download-icon\"><span></span>Descargar reporte{$strMenu}</div>";
        }
    }

    if(isset($arrParametros["btnImprimir"]) && $arrParametros["btnImprimir"] != false){
        $strHerramientasBottom .= "&nbsp;&nbsp;&nbsp;<div id=\"imprimir_html-{$strReportKey}\" class=\"herramientas download-icon\"><span></span>Imprimir</div>";
    }

    $strFormEmail = "<div id=\"email_frm-{$strReportKey}\" class='messagepop pop'>
                        <div class=\"contenido_titulo_menu\">
                            <div class=\"titulo_menu\" style='display:table-cell;width:80%'>
                                <b>Enviar por e-mail:</b>
                            </div>
                            <div class=\"cerrar_menu\">
                                <a id='close_email_frm-{$strReportKey}' href='/'>X</a>
                            </div>
                        </div>
                        <table id='tbl_data_email_-{$strReportKey}' width='100%'>
                            <tr>
                                <td colspan='2'>
                                    Asunto:
                                </td>
                            </tr>
                            <tr>
                                <td colspan='2'>
                                    <input type='text' class='field_textbox' name='send_email[asunto]' style='width:100%' validate='true' />
                                </td>
                            </tr>
                            <tr>
                                <td colspan='2' style='border-bottom:1px solid #EFEFEF'>
                                    E-mail:
                                </td>
                            </tr>
                            <tbody id='tb_data_email-{$strReportKey}'></tbody>
                            <tr><td colspan='2' align='center'><input type='button' class='button' value='Enviar' id='send_data_email-{$strReportKey}' /></td></tr>
                        </table>
                    </div>";

    if(isset($arrParametros["btnEmail"]) && $arrParametros["btnEmail"] != false){
        $strHerramientasBottom .= "&nbsp;&nbsp;&nbsp;<div id=\"enviar_email-{$strReportKey}\" class=\"herramientas download-icon\"><span></span>Email{$strFormEmail}</div>";
    }

    //-------------------Botones Barra Superior-----------------------
    $strHerramientasTop .= "<div id=\"refrescar_rpt-{$strReportKey}\" class=\"herramientas\" style=\"display:none;\"><div id=\"curvedarrow\"></div><div id=\"curvedarrow2\"></div></div>";

    $strHerramientasTop =  "<div id=\"herramientasT\">{$strHerramientasTop}</div>";
    $strHerramientasBottom =  "<div id=\"herramientasB\">{$strHerramientasBottom}</div>";

    if($arrParametros["tipo"] == "paginador")
        $strResultado = $strHerramientasTop."<br>".$strResultado."</br>".$strHerramientasBottom;
    elseif($arrParametros["tipo"] == "scroll")
        $strResultado = $strHerramientasTop."<br>".$strHerramientasBottom."<br>".$strResultado;
    else
        $strResultado = $strHerramientasTop."<br>".$strResultado."<br>".$strHerramientasBottom;

    $strResultado = "<div class=\"ajustar_contenido\">".$strResultado."</div>";

    return $strResultado;
}

function draw_rpt_header($strRptName, $strType, $boolOpenPrintdialog = false, $boolReturnString = false){

    global $cfg;

    $strReturn = "";

    if( $strType ) {
        $boolHeader = true;

        switch($strType){
            case "excel":
                header("Content-Type: application/vnd.ms-excel");
                header("Content-Disposition:attachment; filename={$strRptName}.xls");
            break;
            case "csv":
                header("Content-Type: text/csv");
                header("Content-Disposition:attachment; filename={$strRptName}.csv");
                $boolHeader = false;
            break;
            case "pdf":
                include_once("libs/tcpdf/tcpdf.php");
                $boolHeader = false;
            break;
            case "html":
            case "email":
                $boolHeader = true;
            break;
        }

        if($boolHeader){

            $strHtmli = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\"><html>";
            $strOpendPrintDialog = ($boolOpenPrintdialog)?"<script type='text/javascript'> window.onload = function() { window.print(); } </script>":"";
            $strHtmlf = "<body id=\"PageBody\" tabindex=\"-1\" style=\"background-color: white;\">";

            $strStyle = getStyle();

            $strReturn = $strHtmli.draw_header_tag("",true).$strStyle.$strOpendPrintDialog.$strHtmlf;

            if(!$boolReturnString){
                print $strReturn;
            }
        }
    }

    return $strReturn;

}

function getStyle(){

    global $cfg;

    $strStyle = "";

    if(isset($cfg["core"]["theme_profile"]) && file_exists("profiles/{$cfg["core"]["theme_profile"]}/style_rpt.css")){
        $strStyle = "<link rel=\"stylesheet\" href=\"profiles/{$cfg["core"]["theme_profile"]}/style_rpt.css\">";
    }
    else if(isset($cfg["core"]["site_profile"]) && file_exists("profiles/{$cfg["core"]["site_profile"]}/style_rpt.css")){
        $strStyle = "<link rel=\"stylesheet\" href=\"profiles/{$cfg["core"]["site_profile"]}/style_rpt.css\">";
    }
    else if(isset($cfg["core"]["theme"]) && file_exists("themes/{$cfg["core"]["theme"]}/style_rpt.css")){
        $strStyle = "<link rel=\"stylesheet\" href=\"themes/{$cfg["core"]["theme"]}/style_rpt.css\">";
    }
    else {
        $strStyle = "<link rel=\"stylesheet\" href=\"hml_report/style_rpt.css\">";
    }

    return $strStyle;
}

function draw_rpt_footer ($boolPrint = "", $boolReturnString = false){
    $strReturn = "";
    if(!empty($boolPrint)) {
        $boolFooter = true;
        if( $boolPrint == "pdf" )
            $boolFooter = false;
        if($boolFooter){
            $strReturn = "</body></html>";
            if(!$boolReturnString) print $strReturn;
        }
    }
    return $strReturn;
}

function resultadoCSV($arrCSV){

    $strCSV = "";

    if(isset($arrCSV) && is_array($arrCSV)){
        while($arrTMP = each($arrCSV)){

            $strTMP = "";

            while($arrTMP2 = each($arrTMP["value"])){

                if($strTMP != "")
                    $strTMP .= ",";

                $strTMP .= str_replace(","," ",$arrTMP2["value"]);

            }

            $strCSV .= $strTMP."\n";
            $strTMP = "";

        }
    }

    print $strCSV;

}

function funcionOnClick($strTMP, $rTMP, $arrEncabezado){
    $strReturn = "";

    foreach($arrEncabezado["onclick"] as $key => $arrKey){
        if($strTMP == $key || $key == "all_row"){
            if(isset($arrKey["function"]) && isset($arrKey["params"])){
                foreach($arrKey["params"] as $strParam){
                    if($strReturn != "")
                        $strReturn .= ",";
                    if(isset($rTMP[$strParam]) && ctype_digit($rTMP[$strParam])){

                        $strReturn .= isset($rTMP[$strParam])?"'".$rTMP[$strParam]."'":"";
                    }
                    elseif (isset($rTMP[$strParam])){
                        $strReturn .= isset($rTMP[$strParam])?"'".utf8_encode($rTMP[$strParam])."'":"";
                    }
                }
                $strReturn = "onclick=\"".$arrKey["function"]."({$strReturn})\" style=\"cursor:pointer;\"";
            }
        }
    }
    return $strReturn;
}

function agregarTotalizador($strQuery, $arrTotalizador, $arrPosTotal, $intCantCols, $boolRows, $arrParametros = array()){

    $strResultado = "";
    $arrResultadoCsv = array();
    $arrTotales = array();
    $arrResultado = array();
    $strRow1 = ($boolRows)?"row1":"";
    $strClassRow1 = ($boolRows)?"class=\"row1\"":"";
    $strQueryb = $strQuery;

    if(is_string($strQuery)){
        //debug::drawdebug($strQuery);
        $intPosFrom = strpos($strQuery, "FROM");
        $strTMPQuery = substr($strQuery, $intPosFrom, strlen($strQuery));
        $strCamposSum = "";

        foreach($arrTotalizador as $key => $val){
            if($val === "¿_t_?")continue;
            if($strCamposSum != "")
                $strCamposSum .= ", ";
            $strCamposSum .= "SUM($val) AS $key";
        }
        
        $strQueryb = "SELECT ".$strCamposSum." ".$strTMPQuery;
        $arrTotales = sqlGetValueFromKey($strQueryb);
        for($intPos = 1; $intPos<=$intCantCols; $intPos++){
            if(is_array($arrTotales) && (count($arrTotales)>0)){
                if($arrPosTotal[$intPos] === $arrParametros["total"]["position"]){
                    $strResultado .= "<td class=\"totales_rpt $strRow1\">Totales</td>";
                }
                else{
                    $strResultado .= isset($arrPosTotal[$intPos])?"<td class=\"totales_rpt $strRow1\">".$arrTotales[$arrPosTotal[$intPos]]."</td>":"<td $strClassRow1>&nbsp;</td>";
                }
            }
            else{
                if($arrPosTotal[$intPos] === $arrParametros["total"]["position"]){
                    $strResultado .= "<td class=\"totales_rpt $strRow1\">Totales</td>";
                }
                else{
                    $strResultado .= isset($arrPosTotal[$intPos])?"<td class=\"totales_rpt $strRow1\">".$arrTotales."</td>":"<td $strClassRow1>&nbsp;</td>";
                }
            }
            if($arrPosTotal[$intPos] !== $arrParametros["total"]["position"]){
                $arrResultadoCsv[$intPos] = isset($arrPosTotal[$intPos])?$arrTotales[$arrPosTotal[$intPos]]:" ";
            }
        }
        
        if(isset($arrParametros["total"]["position"]) && $arrParametros["total"]["position"] != "block"){
            $strResultado = "<tbody><tr>".$strResultado."</tr></tbody>";
        }
        else{
            $strResultado = "<tbody><tr><td colspan=\"$intCantCols\" class=\"row0_rpthml\" >Totales</td></tr> <tr>".$strResultado."</tr></tbody>";
        }
    }
    else if (is_array($strQuery)){

        foreach($arrTotalizador as $key => $val){
            $intTmpSum = 0;
            foreach($strQuery as $keyV => $arrVals){
                if(isset($arrVals[$key])){
                    $intTmpSum += $arrVals[$key];
                }
            }
            $arrTotales[$key] =  $intTmpSum;
        }

        for($intPos = 1; $intPos<=$intCantCols; $intPos++){
            $strResultado .= isset($arrPosTotal[$intPos])?"<td class=\"totales_rpt $strRow1\" align=\"center\">".$arrTotales[$arrPosTotal[$intPos]]."</td>":"<td $strClassRow1>&nbsp;</td>";
            $arrResultadoCsv[$intPos] = isset($arrPosTotal[$intPos])?$arrTotales[$arrPosTotal[$intPos]]:" ";
        }
        $strResultado = "<tbody><tr><td colspan=\"$intCantCols\" class=\"row0_rpthml\" >Totales</td></tr> <tr>".$strResultado."</tr></tbody>";
    }

    $arrResultado["resultado"] = $strResultado;
    $arrResultado["total_csv"] = $arrResultadoCsv;

    return $arrResultado;
}

function estilosReportePDF(){
    $strEstilo = <<<EOD
    <style type="text/css">
    .row0 {
        font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-size : 25px;
        text-align: center;
        color : #0072BC;
        background-color : white;
        font-weight : bold;
        border-bottom: 2px solid #7A94B5;
    }

    .row1 {
        font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-size : 25px;
        color : Black;
        background-color : #F0F0F0;
    }

    .row2 {
        font-family : Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-size : 25px;
        color : Black;
        background-color : #FFFFFF;
    }

    .row0_rpthml{
        background-color: black;
        color: white;
        text-align: center;
        font-size : 25px;
    }

    .totales_rpt{
        text-align: right;
    }
    </style>
EOD;

    return $strEstilo;
}
//-----------------------------------------------------------------------------------------------------
//---------------------------- Funciones para que soporte tratar con arrays ---------------------------

function filtrarArrayPorKey(&$arrCustom, $strKey, $strLike){
    foreach($arrCustom as $key => $val){

        $strKeyTMP = strtolower($val[$strKey]);
        $strLike = strtolower($strLike);

        $strCheck = (string)(strpos(trim($strKeyTMP),$strLike) !== false);
        if($strCheck == ""){
            unset($arrCustom[$key]);
        }
    }

    $arrCustom = array_values($arrCustom);
}

function recorrerArrayManual($qTMP, &$strQuery, $boolIsString){
    $objReturn;
    if($boolIsString){
        $objReturn = db_fetch_assoc($qTMP);
    }
    else {
        $arrReturn = each($strQuery);
        $objReturn = $arrReturn["value"];
    }
    return $objReturn;
}

function sendReportEmail($strResultado, $strNombreReporte, $arrPara, $strAsunto, $boolPdfAttachment = false, $strFrom = "noreply@homeland.com.gt", $strFromName = "noreply", $boolAddLogedUserAsBCC = false){

    if(!empty($strResultado) && !empty($arrPara) && is_array($arrPara) && !empty($strAsunto)){
        require_once("phpmailer/PHPMailerAutoload.php");

        $email = new PHPMailer();
        $email->From = $strFrom;
        $email->FromName = $strFromName;
        $email->Subject = $strAsunto;

        foreach ($arrPara as $strEmail) {
            $email->AddAddress($strEmail);
        }

        if($boolAddLogedUserAsBCC){
            $email->AddBCC(getEmailLogedUser());
        }

        $email->IsSMTP();
        $email->SMTPAuth = true;
        $email->Port = 25;
        $email->IsSendmail();
        $email->IsHTML(true);

        if($boolPdfAttachment){
            $strBody = isset($_POST['send_email']["body"]) ? user_input_delmagic($_POST['send_email']["body"]) : "";
            $strOrientation = empty($strBody) ? "O" : "L";
            $arrDirectorioReporte = createPdfReport($strResultado, $strNombreReporte, "F", "", $strOrientation);
            $email->AddAttachment($arrDirectorioReporte["url"].$arrDirectorioReporte["nombre"], $arrDirectorioReporte["nombre"], "base64", "application/pdf");
            $strBody = isset($_POST['send_email']["body"]) ? user_input_delmagic($_POST['send_email']["body"]) : "";
            if( empty($strBody) ){
                $email->Body = "{$strAux} Pdf adjunto ('{$strNombreReporte}')".$arrDirectorioReporte["url"].$arrDirectorioReporte["nombre"];
            }
            else{
                $email->Body = $strBody;
            }
            
        }
        else{
            $email->Body = $strResultado;
        }

        $boolSend = $email->Send();

        if($boolPdfAttachment && !empty($arrDirectorioReporte["url"]) && !empty($arrDirectorioReporte["nombre"]) && readfile($arrDirectorioReporte["url"].$arrDirectorioReporte["nombre"])){
            @unlink($arrDirectorioReporte["url"]."/".$arrDirectorioReporte["nombre"]);
        }

        if($boolSend){
            $arrReturn["status"] = "ok";
            $arrReturn["msj"] = "Email enviado";
        }
        else{
            $arrReturn["status"] = "ok";
            $arrReturn["msj"] = "Hubo un problema de comunicación, intente de nuevo";
        }

        /*$strMsjCorreoInvalido = "";
        foreach ($arrPara as $intKey => $strEmail) {
            if(!validate_email($strEmail)){
                $strMsjCorreoInvalido .= (!empty($strMsjCorreoInvalido))?", ".$strEmail:$strEmail;
                unset($arrPara[$intKey]);
            }
        }

        $strMsjCorreoInvalido = (!empty($strMsjCorreoInvalido))?"\nSe encontraron email invalidos: {$strMsjCorreoInvalido}":"";

        $strPara = "";
        if(!empty($arrPara) && is_array($arrPara) && count($arrPara) > 0){
            reset($arrPara);
            $strPara = implode(",",$arrPara);
        }

        if(!empty($strPara)){
            $strDomain = core_getBaseDomain();

            if($boolPdfAttachment){
                $arrDirectorioReporte = createPdfReport($strResultado, $strNombreReporte, "F");
                $objMail = new AttachMailer("noreply@{$strDomain}", $strPara, $strAsunto, "Pdf adjunto ('{$strNombreReporte}')");
                $objMail->attachFile($arrDirectorioReporte["url"],$arrDirectorioReporte["nombre"]);
            }
            else{
                $objMail = new AttachMailer("noreply@{$strDomain}", $strPara, $strAsunto, $strResultado);
            }

            if($objMail->send()){
                if($boolPdfAttachment && !empty($arrDirectorioReporte["url"]) && !empty($arrDirectorioReporte["nombre"]) && readfile($arrDirectorioReporte["url"]."/".$arrDirectorioReporte["nombre"])){
                    unlink($arrDirectorioReporte["url"]."/".$arrDirectorioReporte["nombre"]);
                }
                $arrReturn["status"] = "ok";
                $arrReturn["msj"] = "Email enviado {$strMsjCorreoInvalido}";
            }
            else{
                if($boolPdfAttachment && !empty($arrDirectorioReporte["url"]) && !empty($arrDirectorioReporte["nombre"]) && readfile($arrDirectorioReporte["url"]."/".$arrDirectorioReporte["nombre"])){
                    unlink($arrDirectorioReporte["url"]."/".$arrDirectorioReporte["nombre"]);
                }
                $arrReturn["status"] = "ok";
                $arrReturn["msj"] = "Hubo un problema de comunicación, intente de nuevo";
            }
        }
        else{
            $arrReturn["status"] = "ok";
            $arrReturn["msj"] = "Hubo un problema de comunicación, intente de nuevo";
        }*/

        return $arrReturn;
    }
}

function validate_email($strMail){
    $strMail = user_input_delmagic(db_escape($strMail));
    if(filter_var($strMail, FILTER_VALIDATE_EMAIL)){
        return true;
    }
    return false;
}

function is_valid_email($email){
 	if(preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email)) {
 		return true;
	} else {
 	 	return false;
 	}
}

function aplicarFiltros($strQuery, $boolIsString, $arrEncabezado, $arrPostFiltrado, $arrPostOrdenamiento, $arrPostHaving, $intPaginaInicialLimit, $intPorPagina){

    if($boolIsString){

        //--------------------??? del magdiel creo-----------------------
        if(isset($arrEncabezado["filter"])){
            foreach($arrEncabezado["filter"] as $key => $val){
                if(!is_array($val))
                    continue;

                $strToken = (isset($val["token"])) ? $val["token"] : "";
                $strNombre = (isset($val["name"])) ? $val["name"] : "";
                $strValue = (isset($val["value"])) ? $val["value"] : "";

                if(empty($strToken))
                    continue;

                if(strpos($strQuery, $strToken)){
                    $strFilters = "AND {$strNombre} {$strValue}";
                    $strQuery =  str_replace($strToken,$strFilters,$strQuery);
                }
            }
        }

        //--------------------Filtrado-----------------------
        reset($arrEncabezado);
        if(strpos($strQuery, "¿f?")){
            $strFilters = "";
            if(!empty($arrPostFiltrado)){
                $intPosTKfilter = strpos($strQuery, "¿f?");

                foreach($arrPostFiltrado as $key => $val){
                    $key = mb_detect_encoding($key,"UTF-8") ? user_input_delmagic($key,true) : $key;
                    $val = mb_detect_encoding($val,"UTF-8") ? user_input_delmagic($val,true) : $val;
                    if(is_array($arrEncabezado["filter"][$key]))
                        continue;
                    $val = str_replace("%","",db_escape(user_input_delmagic($val)));
                    if($val != ""){
                        if($strFilters != "")
                            $strFilters .= " ";
                        $val = utf8_decode($val);
                        $strFilters .= "AND {$arrEncabezado["filter"][$key]} LIKE '%{$val}%' ";
                    }
                }

                $strQuery =  str_replace("¿f?",$strFilters,$strQuery);

            }
            else {
                $strQuery =  str_replace("¿f?","",$strQuery);
            }
        }

        //--------------------Ordenamiento-----------------------
        $strDefaultOrderBy = (!empty($arrEncabezado["default_order"]))?$arrEncabezado["default_order"]:"";

        if(strpos($strQuery, "¿o?")){
            $strSorters = "";
            if(is_array($arrPostOrdenamiento)){

                $boolOrdenamiento = false;
                foreach($arrPostOrdenamiento as $key => $val){
                    $key = mb_detect_encoding($key,"UTF-8") ? user_input_delmagic($key,true) : $key;
                    $val = mb_detect_encoding($val,"UTF-8") ? user_input_delmagic($val,true) : $val;
                    if($val != "" && ($val == "asc" || $val == "desc")){
                        if($strSorters != "")
                            $strSorters .= ", ";

                        if(isset($arrEncabezado["natural_sort"][$key]) && $arrEncabezado["natural_sort"][$key]){
                            $strSorters .= "udf_NaturalSortFormat({$arrEncabezado["sort"][$key]},10,'.') {$val}";
                        }
                        else{
                            $strSorters .= "{$arrEncabezado["sort"][$key]} {$val}";
                        }

                        $boolOrdenamiento = true;
                    }
                }
                if($boolOrdenamiento)
                    $strQuery =  str_replace("¿o?","ORDER BY ".$strSorters,$strQuery);
                else
                    $strQuery =  str_replace("¿o?",$strDefaultOrderBy,$strQuery);

            }
            else {
                $strQuery =  str_replace("¿o?",$strDefaultOrderBy,$strQuery);
            }
        }

        //--------------------???????----------------------- no se quien lo puso, ni para que lo usan
        if(strpos($strQuery, "¿l?")){
            $strQuery =  str_replace("¿l?"," LIMIT {$intPaginaInicialLimit}, {$intPorPagina}",$strQuery);
        }

        //--------------------Having-----------------------
        reset($arrEncabezado);

        if(strpos($strQuery, "¿h?")){

            $strFilters = "";

            if(!empty($arrPostHaving)){

                foreach($arrPostHaving as $key => $val){

                    if(is_array($arrEncabezado["having"][$key]))
                        continue;
                    $val = str_replace("%","",db_escape(user_input_delmagic($val)));
                    if(!empty($val)){

                        $val = utf8_decode($val);

                        $strFilters .= ($strFilters == "")?"":" AND ";
                        $strFilters .= "`{$arrEncabezado["having"][$key]}` LIKE '%{$val}%' ";
                    }
                }

                $strFilters = (!empty($strFilters))?"HAVING ".$strFilters:"";

                $strQuery =  str_replace("¿h?",$strFilters,$strQuery);

            }
            else {
                $strQuery =  str_replace("¿h?","",$strQuery);
            }
        }
    }
    else {
        if(!empty($arrPostFiltrado)){
            foreach($arrPostFiltrado as $key => $val){
                $val = str_replace("%","",db_escape(user_input_delmagic($val)));
                if($val != ""){
                    filtrarArrayPorKey($strQuery, $arrEncabezado["filter"][$key], $val);
                }
            }
        }

        if(is_array($arrPostOrdenamiento)){

            $arrColToSort = array();

            foreach($arrPostOrdenamiento as $key => $val){
                foreach ($strQuery as $key2 => $fila) {
                    $arrColToSort[$key2] = $fila[$arrEncabezado["sort"][$key]];
                }
                if($val != "" && $val == "asc"){
                    array_multisort($arrColToSort,SORT_ASC,$strQuery);
                }
                elseif ($val != "" && $val == "desc") {
                    array_multisort($arrColToSort,SORT_DESC,$strQuery);
                }
            }
        }
    }

    return $strQuery;
}

function createPdfReport($strResultado, $strNombreReporte, $strOutputType = "I", $strDirectoryFile = "", $strOrientation = "O"){

    $arrReturn = array();

    if($strOutputType == "F") {
        $strSessID = session_id();
        $strDirectoryFile = empty($strDirectoryFile)?"var/tmp/{$strSessID}/":$strDirectoryFile;
        if(!file_exists($strDirectoryFile)){
            mkdir($strDirectoryFile , 0777 , true);
        }
    }
    else{
        $strDirectoryFile = "";
    }

    $strResultado .= estilosReportePDF();

    draw_rpt_header($strNombreReporte."pdf", "pdf");
    $ObjPdf = new TCPDF($strOrientation, "mm", "LETTER", false, 'ISO-8859-1', false);
    $ObjPdf->SMTPDebug = 0;
    $ObjPdf->Debug = 0;
    $strAutor = $_SESSION["wt"]["nombres"].", ".$_SESSION["wt"]["apellidos"];
    $ObjPdf->SetCreator($strAutor);
    $ObjPdf->SetAuthor($strAutor);
    $ObjPdf->SetTitle($strNombreReporte);
    $ObjPdf->SetSubject('reporte');
    $ObjPdf->SetKeywords("$strNombreReporte, HOMELAND");
    $ObjPdf->setPrintHeader(false);
    $ObjPdf->setPrintFooter(false);
    $ObjPdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    $ObjPdf->AddPage();
    /*drawDebug($strResultado);
    die();*/
    $ObjPdf->writeHTML($strResultado,true,false,true,false,"");
    $ObjPdf->Output("{$strDirectoryFile}{$strNombreReporte}.pdf", $strOutputType);

    $arrReturn["url"] = $strDirectoryFile;
    $arrReturn["nombre"] = "{$strNombreReporte}.pdf";

    return $arrReturn;
}

function getEmailLogedUser(){
    $strQuery = "SELECT email FROM wt_users WHERE uid = {$_SESSION["wt"]["uid"]}";
    $strEmail = sqlGetValueFromKey($strQuery);
    return $strEmail;
}