<?php
if(isset($_GET["boolReport"])){
    include_once("core/miniMain.php");
	include_once("libs/hml_report/hml_report.php");
	
	if(!check_user_class($config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["class"])) die($lang["ADMIN_WEBSERVICE"]);
	
	header("Content-Type: text/html; charset=iso-8859-1");
	                                                         
    $strQuery = "SELECT groupString AS Grupo, op_uuid AS Codigo ,descripcion AS Descripcion, activo AS Activo FROM wt_webservices_operations WHERE 1 AND isNewMod = 'Y' ¿f? ¿o?";
    
    $arrEncabezado = array();
    $arrParametros = array();
    
    $arrEncabezado["filter"]["Grupo"] = "groupString";
    $arrEncabezado["filter"]["Descripcion"] = "descripcion";
    $arrEncabezado["filter"]["Codigo"] = "op_uuid";
    $arrEncabezado["filter"]["Activo"] = "activo";
    $arrEncabezado["sort"]["Grupo"] = "groupString";
    $arrEncabezado["sort"]["Descripcion"] = "descripcion";
    $arrEncabezado["sort"]["Codigo"] = "op_uuid";
    $arrEncabezado["sort"]["Activo"] = "activo";
    $arrEncabezado["onclick"]["all_row"]["function"] = "enviar_a";
    $arrEncabezado["onclick"]["all_row"]["params"][] = "Codigo";
    
    $arrParametros["tipo"] = "paginador";
    $arrParametros["btnExportar"] = false;
    $arrParametros["porPagina"] = "10";
 
    $strPrintRPTest = new hml_report($strQuery, $arrEncabezado, $arrParametros, "core", $lang["ADMIN_WEBSERVICE"],true);
    
    print $strPrintRPTest -> dibujarHML_RPT();
                  
    die();
}
if(isset($_GET["save"])){
    include_once('core/miniMain.php');
    if(!check_user_class($config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["class"])) die($lang["ADMIN_WEBSERVICE"]);
    //print_r($_POST);
    require_once("webservices/webservices_core/webservice_master.php");
    $objW = new admin_webservices($_POST);
    $arrReturn = $objW -> create_services();
    print json_encode($arrReturn);
    die;
}
if(isset($_GET["delete"])){
	include_once('core/miniMain.php');
	if(!check_user_class($config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["class"])) die($lang["ADMIN_WEBSERVICE"]);
	//print_r($_POST);
	require_once("webservices/webservices_core/webservice_master.php");
	$objW = new admin_webservices($_POST);
	$arrReturn = $objW->deleteService();
	print json_encode($arrReturn);
	die();
}
if(isset($_GET["add"])){
    include_once('core/miniMain.php');
    if(!check_user_class($config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["class"])) die($lang["ADMIN_WEBSERVICE"]);
	$boolEdit = check_user_class("admin");
    $intCount = isset($_GET["count"])?intval($_GET["count"]):0;
    $opt = isset($_GET["opt"])?db_escape($_GET["opt"]):0;
    if(!$intCount)die;
    if($opt == "tblParams"){
        ?>
        <tr>
            <td align="center"><?php draw_input("", "required_{$intCount}", "check-ios") ?></td>
            <td align="center"><?php draw_input("", "desc_{$intCount}", "text") ?></td>
            <td align="center"><?php draw_input("", "validate_{$intCount}", "text") ?></td>
            <td align="center"><?php draw_input("", "key_{$intCount}", "text") ?></td>
            <td align="center"><?php draw_input("", "error_{$intCount}", "text") ?></td>
            <td align="center"><?php draw_input("", "trans_{$intCount}", "text") ?></td>
            <?php
            if($boolEdit){
                ?>
                <td align="center"><i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i></td>
                <?php
            }
            ?>
        </tr>
        <?php
    }
    else{
        ?>
        <tr>
            <td align="center"><?php draw_input("", "function_{$intCount}", "text") ?></td>
            <td align="center">
                <?php draw_input("", "derived_{$intCount}", "check-ios") ?>
            </td>
            <td align="center">
                <?php
                if($boolEdit){
                    ?>
                    <i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i>
                    <?php
                }
                ?>
            </td>
        </tr>
        <?php
    }
    die;
}
include_once('core/main.php');

if(!check_user_class($config["admmenu"][$lang["ADMIN_WEBSERVICE"]]["class"])) die($lang["ACCESS_DENIED"]);

$page_name = $lang["ADMIN_WEBSERVICE"];

draw_header();
theme_draw_centerbox_open($page_name);
jquery_includeLibrary("datatables");
jquery_includeLibrary("chosen");
$strOp = "";
$strNameClass = "";
$strPathClass = "";
$strResponseClass = "";
$strModuleClass = "";
$strAccessClass = "freeAccess";
$strAllowedClass = "";
$strFormatClass = "";
$strDescripcionClass = "";
$strActiveClass = "";
$strPublicClass = "";
$strCheckConfig = "";
$arrInfo = false;
$intCountParams = 1;
$intCountFunctions = 1;
$arrParams = array();
$arrFunctions = array();
$strGet = "";
$strGroupString = "";
$boolEdit = check_user_class("admin");
if(isset($_GET["op"])){
    $strOp = db_escape($_GET["op"]);
    $strQuery = "SELECT * FROM wt_webservices_operations WHERE isNewMod = 'Y' AND op_uuid = '{$strOp}'";
    $arrInfo = sqlGetValueFromKey($strQuery);
    if(is_array($arrInfo)){
        $strNameClass = $arrInfo["class_mainClass"];
        $strPathClass = $arrInfo["path_mainClass"];
        $strResponseClass = $arrInfo["method_response"];
        $strModuleClass = $arrInfo["modulo"];
        $strAccessClass = $arrInfo["acceso"];
        $strAllowedClass = $arrInfo["allowed_format"];
        $strFormatClass = $arrInfo["format_response"];
        $strDescripcionClass = $arrInfo["descripcion"];
        $strActiveClass = $arrInfo["activo"];
        $strPublicClass = $arrInfo["publica"];
        $strCheckConfig = $arrInfo["check_config_device"];
        $strGroupString = $arrInfo["groupString"];
    }
    $intCountParams = sqlGetValueFromKey("SELECT COUNT(*) FROM wt_webservices_operations_extra_data WHERE op = '{$strOp}'");
    if(!$intCountParams)$intCountParams=1;
    $intCountFunctions = sqlGetValueFromKey("SELECT COUNT(*) FROM wt_webservices_operations_extra_function WHERE op = '{$strOp}'");
    if(!$intCountFunctions)$intCountFunctions=1;
	if(is_array($arrInfo)){
		$strGet .= "?o={$strOp}";
		$strGet .= "&m=cualquiera de estos({$strAllowedClass})";
		$strGet .= "&f=cualquiera de estos({$strFormatClass})";
		if($strPublicClass != "Y"){
			$strGet .= "&t=udid o token del servicio de login";
        }
		$strQuery = "SELECT op, required, parameter_description, method_validation, key_parameter, error_response, transform_key FROM wt_webservices_operations_extra_data WHERE op = '{$strOp}'";
		$qTMP = db_query($strQuery);
		if(db_num_rows($qTMP)){
			while($rTMP = db_fetch_assoc($qTMP)) {
				array_push($arrParams, $rTMP);
                $strGet .= (empty($strGet))? "?{$rTMP["key_parameter"]}={$rTMP["parameter_description"]}" : "&{$rTMP["key_parameter"]}={$rTMP["parameter_description"]}";
				unset($rTMP);
			}
		}

		$strQuery = "SELECT * FROM wt_webservices_operations_extra_function WHERE op = '{$strOp}'";
		$qTMP = db_query($strQuery);
		if(db_num_rows($qTMP)) {
			while ($rTMP = db_fetch_assoc($qTMP)) {
				array_push($arrFunctions, $rTMP);
			}
		}
	}
}
?>
<style>
    .fa-trash-o{
        color: #DA4F49;
        cursor: pointer;
    }
    .col-lg-3{
        min-height: 60px;
    }
    .bs-example {
        margin-right: 0;
        margin-left: 0;
        background-color: #fff;
        border-color: #ddd;
        border-width: 1px;
        border-radius: 4px 4px 0 0;
        -webkit-box-shadow: none;
        box-shadow: none;
    }
    .bs-example:after {
        position: absolute;
        top: 15px;
        left: 32px;
        font-size: 12px;
        font-weight: 700;
        color: #959595;
        text-transform: uppercase;
        letter-spacing: 1px;
        content: "Ejemplo de endPoint (Recomendación: usar Postman para una mejor visualización)";
    }
    .content-example{
        overflow-wrap: break-word;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <button type="button" id="addService" class="btn btn-warning" onclick="document.location.href='adm_webservices.php'"><i class="fa fa-plus"></i>&nbsp;&nbsp;Agregar</button>
        <button type="button" id="searchService" class="btn btn-warning" onclick="draw_rpt()"><i class="fa fa-search"></i>&nbsp;&nbsp;Buscar</button>
    </div>
    <div class="row">
        <div class="col-lg-12"><br/>
            <form id="frmWebservice">
                <input type="hidden" id="countParams" value="<?php print $intCountParams; ?>">
                <input type="hidden" id="countFuntions" value="<?php print $intCountFunctions; ?>">
                <fieldset>
                    <legend>Configuración de webservices</legend>
                    <div class="row">
                        <div class="col-lg-3 col-md-3 col-sm-5">
                            <?php draw_input("Código de operación", "txtOp", "text",$strOp) ?>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-5 bg-info">
	                        <?php draw_input("Grupo", "groupString", "text",$strGroupString) ?>
                        </div>
                    </div>
                    <div class="row ">
                        <div class="notVisits">
                            <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Nombre de la clase", "name_class", "text",$strNameClass) ?></div>
                            <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Path", "path_class", "text",$strPathClass) ?></div>
                            <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Método de respuesta", "method_response", "text",$strResponseClass) ?></div>
                        </div>
                        <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Módulo", "module", "text",$strModuleClass) ?></div>
                        <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Access", "access", "text",$strAccessClass) ?></div>
                        <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Modos permitidos", "allowed", "multiselect", $strAllowedClass, array("option"=>array("w"=>"w","am"=>"am","wm"=>"wm"))) ?></div>
                        <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Formatos de respuesta", "format_response", "multiselect", $strFormatClass, array("option"=>array("json"=>"json","html"=>"html","xmlno"=>"xmlno"))) ?></div>
                        <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Descripción", "descripcion", "text",$strDescripcionClass) ?></div>
                        <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Activo", "active", "check",$strActiveClass) ?></div>
                        <div class="col-lg-3 col-md-3 col-sm-5"><?php draw_input("Público", "public", "check",$strPublicClass) ?></div>
                        
                        <div class="col-lg-2 col-md-3 col-sm-5"><?php draw_input("Check config device", "check_config", "check",$strCheckConfig) ?></div>
                    </div>
                    <fieldset style="margin-top: 20px;">
                        <legend>Parámetros</legend>
                        <div class="row">
                            <div class="col-lg-12">
                                <div  class="table-responsive" style="height:auto; width:97%;">
                                    <table class="table table-bordered" cellspacing="0" cellpadding="0" id="tblParams" data-toggle="tooltip" title="Recuerda que el método de valicación tiene que estar en tu clase.">
                                        <thead>
                                            <tr>
                                                <th align="center">Obligatorio</th>
                                                <th align="center">Descripción</th>
                                                <th align="center">Método de validación</th>
                                                <th align="center">Key del parámetro</th>
                                                <th align="center">Error devuelto</th>
                                                <th align="center">Key transaformado</th>
	                                            <?php
	                                            if($boolEdit){
	                                                ?>
                                                    <th align="center">Eliminar</th>
                                                    <?php
	                                            }
	                                            ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            //Si existen
                                            $intCount = 0;
                                            if(is_array($arrParams)){
                                                if(count($arrParams)){
                                                    foreach($arrParams AS $key => $rTMP){
                                                    //while($rTMP = db_fetch_array($qTMP)){
                                                        ?>
                                                        <tr>
                                                            <td align="center"><?php draw_input("", "required_{$intCount}", "check-ios",$rTMP["required"]) ?></td>
                                                            <td align="center"><?php draw_input("", "desc_{$intCount}", "text",$rTMP["parameter_description"]) ?></td>
                                                            <td align="center"><?php draw_input("", "validate_{$intCount}", "text",$rTMP["method_validation"]) ?></td>
                                                            <td align="center"><?php draw_input("", "key_{$intCount}", "text",$rTMP["key_parameter"]) ?></td>
                                                            <td align="center"><?php draw_input("", "error_{$intCount}", "text",$rTMP["error_response"]) ?></td>
                                                            <td align="center"><?php draw_input("", "trans_{$intCount}", "text",$rTMP["transform_key"]) ?></td>
                                                            <?php
                                                            if($boolEdit){
                                                                ?>
                                                                <td align="center"><i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i></td>
                                                                <?php
                                                            }
                                                            ?>
                                                        </tr>
                                                        <?php
                                                        unset($rTMP);
                                                        $intCount++;
                                                    }
                                                }
                                                else{
                                                    ?>
                                                    <tr>
                                                        <td align="center"><?php draw_input("", "required_{$intCount}", "check-ios") ?></td>
                                                        <td align="center"><?php draw_input("", "desc_{$intCount}", "text") ?></td>
                                                        <td align="center"><?php draw_input("", "validate_{$intCount}", "text") ?></td>
                                                        <td align="center"><?php draw_input("", "key_{$intCount}", "text") ?></td>
                                                        <td align="center"><?php draw_input("", "error_{$intCount}", "text") ?></td>
                                                        <td align="center"><?php draw_input("", "trans_{$intCount}", "text") ?></td>
                                                        <?php
                                                        if($boolEdit){
                                                            ?>
                                                            <td align="center"><i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i></td>
                                                            <?php
                                                        }
                                                        ?>
                                                    </tr>
                                                    <?php
                                                }
                                            }
                                            else{
                                                ?>
                                                <tr>
                                                    <td align="center"><?php draw_input("", "required_{$intCount}", "check-ios") ?></td>
                                                    <td align="center"><?php draw_input("", "desc_{$intCount}", "text") ?></td>
                                                    <td align="center"><?php draw_input("", "validate_{$intCount}", "text") ?></td>
                                                    <td align="center"><?php draw_input("", "key_{$intCount}", "text") ?></td>
                                                    <td align="center"><?php draw_input("", "error_{$intCount}", "text") ?></td>
                                                    <td align="center"><?php draw_input("", "trans_{$intCount}", "text") ?></td>
	                                                <?php
	                                                if($boolEdit){
	                                                    ?>
                                                        <td align="center"><i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i></td>
                                                        <?php
	                                                }
	                                                ?>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 20px">
                            <div class="col-lg-5">
                                <button type="button" id="btnAddParam" class="btn btn-default" onclick="addLine('tblParams')">Agregar párametro</button>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset style="margin-top: 20px;">
                        <legend>Funciones a validar(se valida en setParams)</legend>
                        <div class="row">
                            <div class="col-lg-12">
                                <div  class="table-responsive" style="height:auto; width:60%;">
                                    <table class="table table-bordered" cellspacing="0" cellpadding="0" id="tblFunctions" data-toggle="tooltip" title="Si el checkbox no está activo, se buscara la función en la clase creada.">
                                            <thead>
                                                <tr>
                                                    <th align="center">Nombre de la función</th>
                                                    <th align="center">Derivada de "webservices_baseClass"</th>
	                                                <?php
	                                                if($boolEdit){
	                                                    ?>
                                                        <th align="center">Eliminar</th>
                                                        <?php
	                                                }
	                                                ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $intCountFunction = 0;
                                                if(is_array($arrInfo)){
                                                    if(count($arrFunctions)){
                                                        foreach($arrFunctions AS $key => $value ){
                                                            ?>
                                                            <tr>
                                                                <td align="center"><?php draw_input("", "function_{$intCountFunction}", "text",$value["str_function"]) ?></td>
                                                                <td align="center">
                                                                    <?php draw_input("", "derived_{$intCountFunction}", "check-ios",$value["webservices_baseClass"]) ?>
                                                                </td>
                                                                <?php
                                                                if($boolEdit){
                                                                    ?>
                                                                    <td align="center">
                                                                        <i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i>
                                                                    </td>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </tr>
                                                            <?php
                                                            unset($rTMP);
                                                            $intCountFunction++;
                                                        }
                                                        db_free_result($qTMP);
                                                    }
                                                    else{
                                                        ?>
                                                        <tr>
                                                            <td align="center"><?php draw_input("", "function_{$intCountFunction}", "text") ?></td>
                                                            <td align="center">
                                                                <?php draw_input("", "derived_{$intCountFunction}", "check-ios") ?>
                                                            </td>
                                                            <?php
                                                            if($boolEdit){
                                                                ?>
                                                                <td align="center">
                                                                    <i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i>
                                                                </td>
                                                                <?php
                                                            }
                                                            ?>
                                                        </tr>
                                                        <?php
                                                    }
                                                }
                                                else{
                                                    ?>
                                                    <tr>
                                                        <td align="center"><?php draw_input("", "function_{$intCountFunction}", "text") ?></td>
                                                        <td align="center">
                                                            <?php draw_input("", "derived_{$intCountFunction}", "check-ios") ?>
                                                        </td>
                                                        <?php
                                                        if($boolEdit){
                                                            ?>
                                                            <td align="center">
                                                                <i class="fa fa-trash-o fa-2x" onclick="delete_line(this)"></i>
                                                            </td>
                                                            <?php
                                                        }
                                                        ?>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 20px">
                            <div class="col-lg-5">
                                <button type="button" id="btnAddFunction" class="btn btn-default" onclick="addLine('tblFunctions')">Agregar función</button>
                            </div>
                        </div>
                    </fieldset>
                    <div class="row">
                        <div class="col-lg-6 col-lg-offset-3">
                            <div class="bs-example" data-example-id="simple-panel">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="content-example"><br/>
	                                        <?php
	                                        $strURL = core_getBaseDir("N");
	                                        print "{$strURL}webservice.php{$strGet}";
	                                        ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php

                            ?>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 20px">
                        <div class="col-lg-12 text-center">
                            <button type="button" id="btnSave" class="btn btn-success" onclick="saveService()">Guardar servicio</button>
                            <?php
                            if(!empty($strOp)){
                                ?>
                                <button type="button" id="btnDelete" class="btn btn-danger hide" onclick="delService();">Eliminar servicio</button>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading" style="cursor: pointer;">get sql</div>
                <div id="bodyExport" class="panel-body hidden">
                    <ul class="list-group">
                        <?php
                        if($arrInfo){
                            $strFields = ""; $strVals = "";
                            foreach($arrInfo AS $key => $value){
                                $strFields .= (!empty($strFields))?" , {$key}":" {$key}";
                                $strVals .= (!empty($strVals))?" , '{$value}'":" '{$value}'";
                            }
                            $strInsert = "INSERT INTO wt_webservices_operations ({$strFields}) VALUES({$strVals});";
                            echo '<li class="list-group-item">';
                            htmlSafePrint($strInsert);
                            echo "</li>";
                        }
                        if($arrParams){
                            foreach($arrParams AS $key => $value){
                                $strFields = ""; $strVals = "";
                                foreach($value AS $param => $desc){
                                    $strFields .= (!empty($strFields))?" , {$param}":" {$param}";
                                    $strVals .= (!empty($strVals))?" , '{$desc}'":" '{$desc}'";
                                }
                                $strInsert = "INSERT INTO wt_webservices_operations_extra_data ({$strFields}) VALUES({$strVals});";
                                echo '<li class="list-group-item">';
                                htmlSafePrint($strInsert);
                                echo "</li>";
                            }
                        }
                        if($arrFunctions){
                            foreach($arrFunctions AS $key => $value){
                                $strFields = ""; $strVals = "";
                                foreach($value AS $param => $desc){
                                    $strFields .= (!empty($strFields))?" , {$param}":" {$param}";
                                    $strVals .= (!empty($strVals))?" , '{$desc}'":" '{$desc}'";
                                }
                                $strInsert = "INSERT INTO wt_webservices_operations_extra_function ({$strFields}) VALUES({$strVals});";
                                echo '<li class="list-group-item">';
                                htmlSafePrint($strInsert);
                                echo "</li>";
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="dialogBusqueda" tabindex="-1" role="dialog" aria-labelledby="dialogBusquedaLbl">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="dialogBusquedaLbl">Busqueda</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $.extend( $.fn.dataTable.defaults, {
            "searching":false,
            "paging":   false,
            "info":     false,
            "ordering": false
        });
        $("#tblParams").dataTable({
            "language": {
                "zeroRecords": "No se encontraron resultados"
            }
        });
        $('[data-toggle="tooltip"]').tooltip();
        <?php
        if(!$boolEdit){
            ?>
            $(".notVisits").remove();
            $("input").prop("disabled",true);
            $("select").prop("disabled",true).trigger("chosen:updated");
            $("#btnAddParam").prop("disabled",true);
            $("#btnAddFunction").prop("disabled",true);
            $("#btnSave").remove();
            $("#addService").prop("disabled",true);
            $("#btnDelete").addClass("hide");
            <?php
        }
        ?>
        $("#btnDelete").removeClass("hide");
        $(".panel-heading").click(()=>{
            if($(".panel-body").hasClass("hidden")){
                $("#bodyExport").removeClass("hidden");
            }
            else{
                $("#bodyExport").addClass("hidden");
            }
        })
    });
    function enviar_a(id){
        document.location.href="adm_webservices.php?op="+id;
    }
    function draw_rpt() {
        var objD = new drawWidgets();
        $.ajax({
            type:"GET",
            url: "adm_webservices.php?boolReport=true",
            beforeSend: function(){
                objD.openLoading(true);
            },
            success: function(data){
                objD.closeLoading();
                $( "#dialogBusqueda .modal-body").html(data);
                $( "#dialogBusqueda").modal("show");
            },
            error: function(){
                objD.closeLoading();
                objD.alertDialog("Error de comunicación, por favor intente nuevamente","ERROR");
            }	
        });
    }
    function addLine(id){
        if(!id)return false;
        var intCount = 0;
        if(id === "tblParams"){
           intCount = $("#countParams").val();
        }
        else{
           intCount = $("#countFuntions").val(); 
        }
        $.ajax({
            type:"GET",
            url:"adm_webservices.php?add=true&count="+intCount+"&opt="+id,
            dataType: "html",
            success: function(data){
                $("#"+id+" tbody").append(data);
                if(id === "tblParams"){
                    intCount = (intCount * (1)) + 1;
                    $("#countParams").val(intCount);
                }
                else{
                    intCount = (intCount * (1)) + 1;
                    $("#countFuntions").val(intCount);
                }
            }
        });
    }
    function saveService(){
        var objD = new drawWidgets();
        $.ajax({
            type:"POST",
            url:"adm_webservices.php?save=true",
            data: $("#frmWebservice").serialize(),
            dataType: "JSON",
            beforeSend: function(){
                objD.openLoading(true);
            },
            success: function(data){
                objD.closeLoading();
                if(data.status === "ok"){
                    $("#txtOp").val(data.op);
                }
                objD.alertDialog(data.msj);
            },
            error: function(){
                objD.closeLoading();
                objD.alertDialog("Hubo un problema de comunicación, intente de nuevo.");
            }
        });
    }
    function delService(){
        let wd = new drawWidgets();
        let buttons = [ ];
        buttons.push({ nombre: "Si ", funcion: confirmDelete, cssClass: "btn btn-success" });
        buttons.push({ nombre: "No", funcion: "close", });
        wd.alertDialog("¿Esta seguro de eliminar el servicio?","MENSAJE DEL SISITEMA",false,false,false,buttons);
    }

    function confirmDelete(){
        let objD = new drawWidgets();
        $.ajax({
            type:"POST",
            url:"adm_webservices.php?delete=true",
            data: $("#frmWebservice").serialize(),
            dataType: "JSON",
            beforeSend: function(){
                objD.openLoading(true);
            },
            success: function(data){
                objD.closeLoading();
                objD.closeDialog();
                objD.alertDialog(data.msj,"",true);
            },
            error: function(){
                objD.closeLoading();
                objD.alertDialog("Hubo un problema de comunicación, intente de nuevo.");
            }
        });
    }
    function delete_line(obj){
        if(!obj)return false;
        if($(obj).parent().parent().parent().find("tr").length < 2){
            var objD = new drawWidgets();
            objD.alertDialog("No se puede eliminar esta fila.");
            return false;
        }
        $(obj).parent().parent().remove();
    }
</script>
<?php

theme_draw_centerbox_close();
draw_footer();

function draw_input($strName,$strNameField,$strType,$value = "",$extra = array()){
    if($strType == "text"){
        if(!empty($strName)){
            ?><b><?php print $strName; ?></b><br><?php
        }
        ?>
        <input type="text" class="form-control" name="<?php print $strNameField; ?>" id="<?php print $strNameField; ?>"
               value="<?php print $value; ?>" placeholder="<?php print isset($extra["hint"])?$extra["hint"]:""; ?>">
        <?php
    }
    else if($strType == "check"){
        ?>
        &nbsp;<br>
        <div class="divCheckbox">
            <input type="checkbox" name="<?php print $strNameField; ?>" id="<?php print $strNameField; ?>" class="chk" <?php print ($value == "Y")?"checked":""; ?>/>
            <label class="labelRadio" for="<?php print $strNameField; ?>"><?php print $strName; ?></label>
        </div>
        <?php
    }
    elseif($strType == "check-ios"){
        ?>
        <div class="slideThree" dt-active="Si" dt-not-active="No">
            <input type="checkbox" class="ios-chk" id="<?php print $strNameField; ?>" name="<?php print $strNameField; ?>" <?php print ($value == "Y")?"checked":""; ?> />
            <label for="<?php print $strNameField; ?>"></label>
        </div>
        <?php
    }
    else if($strType == "multiselect"){
        if(!isset($extra["option"]))return false;
        $arrValue = explode(",", $value);
        ?>
        <b><?php print $strName; ?></b><br>
        <select class="field_listbox" name="<?php print $strNameField; ?>[]" multiple class="chosen-select" id="<?php print $strNameField; ?>" data-placeholder="Seleccione opciones" style="width: 75%;">
            <?php 
            foreach($extra["option"] AS $key => $val){
                ?>
            <option value="<?php print $key; ?>" <?php print (in_array($key, $arrValue))?"selected":""; ?> ><?php print $val; ?></option>
                <?php
                unset($key);
                unset($val);
            }
            ?>
        </select>
        <script>
            $(document).ready(function(){
                $("#<?php print $strNameField; ?>").chosen({
                    no_results_text: "No se encontraron resultados para"
                });
                $(".chosen-choices").addClass("form-control");
            });
        </script>
        <?php
    }
}