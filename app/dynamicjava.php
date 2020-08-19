<?php
/**
 * dynamicjava.php (2010-sept-21)
 * (c) by Alejandro Gudiel
 * All Rights Reserved
 * License does not permit use by third parties
 *
 * La idea de este archivo es tener un include de javascript que
 * baje comprimido, se pueda cachear y pueda ser modificado por php.
 *
 * OJO que no puede llevar variables de GET ni POST ni de SESSION por el cache...
**/

include_once("core/miniMain.php"); // Este ya jala el theme

// EXPERIMENTAL
$boolGlobalIsJS = true;

// Verifico fechas de modificacion
$intMaxLastModified = 0;
$intLastModified = 0;

$arrFiles = array();
if ($cfg["core"]["CACHE_CSS_AND_JAVA"]) {
    // Defino los archivos
    if (file_exists("core/packages/jquery.min.js")) $arrFiles[] = "core/packages/jquery.min.js";
    if (file_exists("core/packages/hml_library.js")) $arrFiles[] = "core/packages/hml_library.js";
    if (file_exists("core/packages/bootstrap/bootstrap.min.js")) $arrFiles[] = "core/packages/bootstrap/bootstrap.min.js";
    if (file_exists("core/packages/lodash/lodash.js")) $arrFiles[] = "core/packages/lodash/lodash.js";

    $arrFile = array();
    foreach ($arrFiles as $arrFile["key"] => $arrFile["value"]) {
        $intLastModified = filemtime($arrFile["value"]);
        if ($intLastModified > $intMaxLastModified) $intMaxLastModified = $intLastModified;
    }
}

// Para ver la fecha de modificacion del archivo de php que esta procesando...
$intLastModifiedThis = filemtime(__FILE__);
if ($intLastModifiedThis > $intMaxLastModified) $intMaxLastModified = $intLastModifiedThis;

header("Pragma: private");

$intHoras = 1;
$expires = 60*60*$intHoras; // El tiempo de expiracion en segundos

$arrApacheHeaders = apache_request_headers();
if (isset($arrApacheHeaders["If-Modified-Since"])) {
    // Si esta el parametro If-Modified-Since es porque estoy validando una fecha
    $intIfModifiedSince = strtotime($arrApacheHeaders["If-Modified-Since"]); //Combierto esta fecha a timestamp
    if ($intIfModifiedSince >= $intMaxLastModified) {
        // Si el cache tiene un archivo que no ha cambiado segun el parametro de la fecha de modificacion, devuelvo un 304 not modified
        $strProtocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        header("Cache-Control: max-age=".$expires);
        header("Expires: " . gmdate("D, d M Y H:i:s", time()+$expires) . " GMT");
        header($strProtocol . " 304 Not Modified");
        die();
    }
}

// Si los archivos cambiaron desde la ultima descarga, los bajo de nuevo
$strLastModified = gmdate("D, d M Y H:i:s", $intMaxLastModified);
header("Cache-Control: max-age=".$expires);
header("Last-Modified: " . $strLastModified . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", time()+$expires) . " GMT");
header("Content-Type: text/javascript");

reset($arrFiles);
$arrFile = array();
foreach ($arrFiles as $arrFile["key"] => $arrFile["value"]) {
    readfile($arrFile["value"]);
    print "\n";
    print "\n";
}

// Lineas para inicializar los rich text editors y los fonts especializados
?>

var intSesExpTimer = 0;
var intSesExpTotalTime = 0;
var intSesExpFailCounter = 0;
var objSesExpMiniWindow = false;
function SesCheckExpiration(intUID) {
if (!boolDoSesExpTimer) return false;
clearTimeout(intSesExpTimer);

$.ajax({
url : "ses_timer.php?ajax=1&c=ce",
type :"GET",
success: function(data){
intSesExpFailCounter = 0;

var objResponse = xmlToJson(data);
if (objResponse.return.attributes.status == "ok") {

if (objResponse.return.attributes.l == "N") {
showSesExpirationMiniWindow(intUID);
}
else {
var objFunTMP = function(){ SesCheckExpiration(intUID); };
intSesExpTimer = setTimeout(objFunTMP, intSesExpTotalTime);
}
}
},
error: function(){
intSesExpFailCounter++;
if (intSesExpFailCounter < 3) {
var objFunTMP = function(){ SesCheckExpiration(intUID); };
setTimeout(objFunTMP, 500);
}
}
});
}

function showSesExpirationMiniWindow(intUID) {

var strTitle = "<?php print $lang["SESS_EXPIRED_SHRT"];?>";
var strName = "<?php echo $lang["LOGIN_NAME"]?>";
const strPass = "<?php echo $lang["LOGIN_PASSWD"]?>";
const strAutoLogin = "<?php print $lang["LOGIN_AUTOLOGIN"]?>";

var frmLogin = `

<div class="modal fade" id="alertSession" tabindex="-1" role="dialog" aria-labelledby="alertSessionLbl">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close"  onclick="document.location.href = 'index.php?act=logout'" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="alertSessionLbl">MENSAJE DEL SISTEMA</h4>
            </div>
            <div class="modal-body">
                <p class="bg-default" id="lblSessExpirationLabel">`+strTitle+`</p>
                <form name="login_form" id="login_form">
                    <input type="hidden" name="submit_login" value="1">
                    <input type='hidden' name='nfo' value='`+intUID+`'>
                    <div class="form-group">
                        <label for="login_name">`+strName+`</label>
                        <input type="email" class="form-control" id="login_name" name="login_name">
                    </div>
                    <div class="form-group">
                        <label for="login_passwd">`+strPass+`</label>
                        <input type="password" class="form-control" id="login_passwd" name="login_passwd">
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name='login_auto' value='1'> `+strAutoLogin+`
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="document.location.href = 'index.php?act=logout'">Cerrar</button>
                <button type="button" class="btn btn-success" onclick="sendSesExpInf(`+intUID+`,$('#alertSession'));">Ingresar</button>
            </div>
        </div>
    </div>
</div>
`;
$('body').append(frmLogin);
$("#alertSession").modal("show");

$('#alertSession').on('hidden.bs.modal', function (e) {
$('#alertSession').remove();
});
intSesExpFailCounter = 0;
}

function sendSesExpInf(intUID,obj) {
if(!obj)obj = false;


$.ajax({
url :"ses_timer.php?ajax=1&c=sl",
type : "POST",
data : $("#login_form").serialize(),
success: function(data){
var objResponse = xmlToJson(data);
if(objResponse.return.attributes.status == 'ok'){
var objFunTMP = function() { SesCheckExpiration(intUID);};
intSesExpTimer = setTimeout(objFunTMP, intSesExpTotalTime);
if(obj){
obj.modal("hide");
}
}
else{
$("#lblSessExpirationLabel").removeClass("bg-default");
$("#lblSessExpirationLabel").addClass("bg-danger");
$("#lblSessExpirationLabel").html("<?php print $lang["ERROR_23"];?>");
}
},
error: function(){
intSesExpFailCounter++;
if (intSesExpFailCounter < 3) {
var objFunTMP = function() { sendSesExpInf(intUID);};
setTimeout(objFunTMP, 500);
}
}
});

return false;
}

function setSesExpTime(intUID) {
if (!boolDoSesExpTimer) return false;

<?php
$boolForceAJAXMode = true;
$intTotTime = $cfg["core"]["sess_timeout"]; // En minutos
if ($boolForceAJAXMode || $intTotTime < 10) {
    // Esta forma es mas exacta en cuanto a tiempo pero le suma unos 300ms de carga a cada click...
    ?>
    $.ajax({
    url : "ses_timer.php?ajax=1&c=gt",
    type: "GET",
    success: function(data){
    intSesExpFailCounter = 0;

    var objResponse = xmlToJson(data);

    if(objResponse.return.attributes.status == "ok"){
    var intTot = objResponse.return.attributes.tot;
    intSesExpTotalTime = (intTot*60000) + 1000;
    var objFunTMP = function() { SesCheckExpiration(intUID);};
    intSesExpTimer = setTimeout(objFunTMP, intSesExpTotalTime);
    }
    },
    error: function(){
    intSesExpFailCounter++;
    if (intSesExpFailCounter < 3) {
    var objFunTMP = function() { setSesExpTime(intUID);};
    setTimeout(objFunTMP, 500);
    }
    }
    });
    <?php
}
else {
    //$intFirstTimeout = ($intTotTime - 3) * 60000; // 3 minutos menos que el tiempo normal
    $intTotTime = ($intTotTime * 60000) + 1000; // Tiempo para los ajax que verifican la conexion...
    $intFirstTimeout = $intTotTime;
    ?>
    intFirstTimeout = <?php print $intFirstTimeout;?>;
    intSesExpTotalTime = <?php print $intTotTime;?>;
    var objFunTMP = function() { SesCheckExpiration(intUID);};
    intSesExpTimer = setTimeout(objFunTMP, intFirstTimeout);
    <?php
}
?>
}