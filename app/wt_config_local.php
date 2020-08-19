<?php

use Dotenv\Dotenv;

date_default_timezone_set("America/Guatemala");
header('Content-Type: text/html; charset=ISO-8859-1');
require_once "core/objects/configCore/configCore.php";

//Es cronjob, quiere decir que para probarlos necesito una base de datos conectada directamente.
$boolCronjob = false;

// Database configuration
configCore::setConfig("is_LocalDev",true);

$wt_config_version = "1.2";

if ((array_key_exists("CLIENTNAME", $_SERVER) || array_key_exists("SHELL", $_SERVER) || (isset($_SERVER["COMPUTERNAME"]) && ($_SERVER["COMPUTERNAME"] == "HMLADM-GUDIEL"))) && !array_key_exists("HTTP_USER_AGENT", $_SERVER)) {
	configCore::setConfig("is_LocalDev",false);
}

if ($_SERVER["PHP_SELF"] == "main_cronjob.php") {
	configCore::setConfig("is_LocalDev",false);
}

configCore::setConfig("isDEBUG",false);
if (isset($_GET["DBGSESSID"]) || isset($_SESSION["DBGSESSID"])) {
	configCore::setConfig("is_LocalDev",false);
	configCore::setConfig("isDEBUG",true);
    $_SESSION["DBGSESSID"] = $_GET["DBGSESSID"];
}
configCore::getConfig($config);

if(isset($_GET["notas_de_version"]) && $config["is_LocalDev"] == true){

    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <html>
        <body onresize="return window.dialogWidth;" id="PageBody"  style="padding: 130px 20px 0px 0px" >
            <table align="center">
                <tr>
                    <td style="font-size: 20px; font-weight: bold; text-align: center; padding: 15px;">
                        Schoolworld, Wt_config version <?php print $wt_config_version ?>
                    </td>
                </tr>
                <tr>
                    <td>1. Arreglado el boton para conectar a bases de datos locales, luego de cambiar contraseña</td>
                </tr>
                <tr>
                    <td>2. Agregada herramienta para correr dumps desde el wt_config, aun esta en fase Alfa... (No soporta dumps con la utilizacion de variables para MySQL)</td>
                </tr>
                <tr>
                    <td>3. Agregados fondos de pantalla que responden conforme al estado de animo del developer. (XD)</td>
                </tr>
                <tr>
                    <td>4. Se agregan las mejoras del uso de la variable $config</td>
                </tr>
            </table>
        </body>
    </html>
    <?php

    die();
}

if(isset($_GET["dropDatabase"]) && $config["is_LocalDev"] == true){

    $strIp = (isset($_POST["ip"]))?$_POST["ip"]:"";
    $strUser = (isset($_POST["user"]))?$_POST["user"]:"";
    $strPass = (isset($_POST["pass"]))?$_POST["pass"]:"";
    $strNameDB = (isset($_POST["dbName"]))?$_POST["dbName"]:"";
    $strPath = (isset($_POST["dbPath"]))?$_POST["dbPath"]:"";

    //arroba para evitar el error de josue, lo que pasa es que el no tiene instalado mysql y por eso da error.
    if(!@mysqli_connect($strIp, $strUser, $strPass)){
        ?>
        <span style="color: red;">Get out of here! Mysql will explode !!! Unable to connect to Mysql ...</span>
        <?php
    }
    else{

        $strQuery = "DROP DATABASE {$strNameDB}";
        if(mysqli_query($strQuery)){
            print "Base de datos eliminada con exito! <br>";
        }
        else{
            print "Error al eliminar base de datos, talvez no exista... <br>";
        };

        mysqli_close();
    }

    die();
}

if(isset($_GET["createAndUseDB"]) && $config["is_LocalDev"] == true){

    $strIp = (isset($_POST["ip"]))?$_POST["ip"]:"";
    $strUser = (isset($_POST["user"]))?$_POST["user"]:"";
    $strPass = (isset($_POST["pass"]))?$_POST["pass"]:"";
    $strNameDB = (isset($_POST["dbName"]))?$_POST["dbName"]:"";
    $strPath = (isset($_POST["dbPath"]))?$_POST["dbPath"]:"";

    //arroba para evitar el error de josue, lo que pasa es que el no tiene instalado mysql y por eso da error.
    if(!@mysqli_connect($strIp, $strUser, $strPass)){
        ?>
        <span style="color: red;">Get out of here! Mysql will explode !!! Unable to connect to Mysql ...</span>
        <?php
    }
    else{

        $strQuery = "CREATE DATABASE {$strNameDB}";
        if(mysqli_query($strQuery)){
            print "Base de datos creada con exito!<br>";
        }
        else{
            print "Error al crear base de datos! <br>";
        };

        mysqli_close();
    }

    die();
}

if(isset($_GET["runDBrunFiles"]) && $config["is_LocalDev"] == true){

    $strIp = (isset($_POST["ip"]))?$_POST["ip"]:"";
    $strUser = (isset($_POST["user"]))?$_POST["user"]:"";
    $strPass = (isset($_POST["pass"]))?$_POST["pass"]:"";
    $strNameDB = (isset($_POST["dbName"]))?$_POST["dbName"]:"";
    $strPath = (isset($_POST["dbPath"]))?$_POST["dbPath"]:"";
    $strPath = trim($strPath);

    if($strPath != "" && $strNameDB != ""){
        //arroba para evitar el error de josue, lo que pasa es que el no tiene instalado mysql y por eso da error.
        if(!@mysqli_connect($strIp, $strUser, $strPass)){
            ?>
            <span style="color: red;">Get out of here! Mysql will explode !!! Unable to connect to Mysql ...</span>
            <?php
        }
        else{

            set_time_limit(90000);

            $strErrosLog = "";

            $strQuery = "USE {$strNameDB}";
            mysqli_query($strQuery);

            $strTMPPath = substr($strPath, -1);
            $strPath = ($strTMPPath == "/" || $strTMPPath == "\\")?$strPath:$strPath."/";

            $directorio = opendir($strPath); //ruta actual
            while ($archivo = readdir($directorio)){
                if(!is_dir($archivo)){
                    if($archivo != ".." || $archivo != "."){
                        $tmpPath = $strPath.$archivo;
                        $strBigQuery = "";
                        $sourceFile = fopen($tmpPath, "r") or $strErrosLog = "Error al abrir {$archivo}";


                        //Leo línea a línea hasta recorrer todo el archivo
                        $boolMultilineCommentIni = false;
                        while($linea = fgets($sourceFile)) {

                            $strIniLinea = substr(trim($linea), 0, 2);

                            //si la linea va vacia (por si acaso)
                            if($strIniLinea == "")continue;

                            //Detecto comentarios de esta manera: -- comentario
                            if($strIniLinea == "--")continue;

                            //Detecto comentarios de esta manera: /* comentario */;
                            $strFinLineaComentarioI = substr(trim($linea), -3, 3);
                            if($strIniLinea == "/*" && $strFinLineaComentarioI == "*/;")continue;

                            //Detecto comentarios de esta manera: /* comentario */
                            $strFinLineaComentarioII = substr(trim($linea), -2, 2);
                            if($strIniLinea == "/*" && $strFinLineaComentarioII == "*/")continue;

                            //Detecto comentarios multilinea

                            if($boolMultilineCommentIni){
                                //detecto el final del comentario
                                if($strFinLineaComentarioI == "*/" || $strFinLineaComentarioII == "*/;"){
                                    $boolMultilineCommentIni = false;
                                }
                                continue;
                            }

                            if($strIniLinea == "/*"){
                                $boolMultilineCommentIni = true;
                                continue;
                            }
                            print $linea."<br>";

                            //Aqui deberian quedarme solo los querys
                            $strBigQuery .= $linea;
                        }
                        fclose($sourceFile);

                        if(!mysqli_query($strBigQuery)){
                            print mysqli_error();
                            //$strErrosLog .= "Error al procesar {$archivo}<br>";
                        };
                    }
                }
            }
            print $strErrosLog;
            print "<br>Todos los archivos han sido procesados...";
            mysqli_close();
        }
    }
    die();
}

if(isset($_GET["getDatabasesLocalDev"]) && $config["is_LocalDev"] == true){

    $strIp = (isset($_POST["ip"]))?$_POST["ip"]:"";
    $strUser = (isset($_POST["user"]))?$_POST["user"]:"";
    $strPass = (isset($_POST["pass"]))?$_POST["pass"]:"";

    $strQuery = "SHOW DATABASES";

    //arroba para evitar el error de josue, lo que pasa es que el no tiene instalado mysql y por eso da error.
    $link = @mysqli_connect($strIp, $strUser, $strPass);
    if(mysqli_connect_errno()){
        ?>
        <span style="color: red;">Get out of here! Mysql will explode !!! Unable to connect to Mysql ...</span>
        <?php
    }
    else{
        $qTMP = mysqli_query($link, $strQuery);
        $arrDataBases = array();
        if(mysqli_num_rows($qTMP)){
            while ($rTMP = mysqli_fetch_assoc($qTMP)) {
                $strValue = $rTMP["Database"];
                $arrDataBases[$rTMP["Database"]] = $strValue;
            }
            mysqli_free_result($qTMP);
            mysqli_close($link);
        }
        ksort($arrDataBases);
        ?>
        <div style="margin: 10px 10px 25px 10px;">
            <a style="color: blue; cursor: pointer;" onclick="displayRunDump()">
                ¡Quiero restaurar un Dump (Beta)!
            </a>
            <div id="runDumpContent" style="color: black; text-align: left; margin-top: 20px; display: none;" class="runDumpContent">
                <b>¿Como funciona?</b><br>
                <table style="margin-top: 5px;" align="center">
                    <tr>
                        <td style="font-style: italic;">
                            Muchos archivos .sql -> Procesados por MySQL
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; padding-bottom: 10px; color: red;">
                            ¿Increible no?
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #08007A;">
                            Nombre para base de datos (Se sobreescribe si ya existe)
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input id="runDBinputName" class="form-control" type="text" value="db_" style="width: 100%;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 10px; color: #08007A;">
                            Path de carpeta contenedora (archivos .sql)
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input id="runDBinputPath" class="form-control" type="text" value="" style="width: 100%;">
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; padding: 10px;">
                            <input type="button" class="btn btn-danger" value="A por todas!" style="cursor: pointer;" onclick="goRunDBfiles()">
                        </td>
                    </tr>
                    <tr>
                        <td id="runDBconsole"></td>
                    </tr>
                    <tr>
                        <td id="runDBconsoleLoading">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            <input id="daleButton" onclick="goRunDB()" type="button" value="Enhorabuena! Menudo premio el que te habeis ganado! clic aqui para reclamarlo..." style="display: none; cursor: pointer;">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <input type="hidden" name="selectIP" value="<?php print $strIp ?>">
        <input type="hidden" name="selectUser" value="<?php print $strUser ?>">
        <input type="hidden" name="selectPassword" value="<?php print $strPass ?>">
        <input type="hidden" id="siteIDalternative" name="siteIDalternative" value="">

        <select name="siteID" id="siteID" class="form-control" onchange="document.frmSelectSite.submit();">
            <option value="">Elija un sitio para trabajar...
            <optgroup label="Bases de datos disponibles">
                <?php
                while ($arrTMP = each($arrDataBases)) {
                    $strDatabase = str_replace("db_","",$arrTMP["key"]);
                    $strDatabase = ucwords($strDatabase);
                    ?>
                    <option value="<?php print $arrTMP["key"]; ?>"><?php print $strDatabase; ?>
                    <?php
                }
                reset($arrDataBases);
                ?>
            </optgroup>
        </select>
        <?php
    }

    die();
}

configCore::setDbType("mysql");
configCore::setPrefix("wt");
configCore::getConfig($config);

$boolIsLocalDev = (isset($config["is_LocalDev"]) && $config["is_LocalDev"]);

if ($boolIsLocalDev && isset($_GET["homeland"])){
    if(isset($_SESSION["siteID"])){
	    unset($_SESSION["siteID"]);
	    unset($_SESSION["siteLocalDevHostDB"]);
	    unset($_SESSION["siteLocalDevUserDB"]);
	    unset($_SESSION["siteLocalDevPassDB"]);
    }
	if(file_exists(__DIR__."/wt_config.yml")){
		unlink(__DIR__.'/wt_config.yml');
	}
}

if ($boolIsLocalDev && $boolCronjob == false) {

    if(isset($_POST["siteIDalternative"]) && $_POST["siteIDalternative"] != ""){
        $_POST["siteID"] = $_POST["siteIDalternative"];
    }

    $dotenv = Dotenv::create(__DIR__);
    $dotenv->load();
    $strReady = getenv("HML_LOCALREADY");
    if ((!isset($_SESSION["siteID"]) && !isset($_SESSION["siteLocalDevHostDB"]) && !isset($_SESSION["siteLocalDevUserDB"]) && !isset($_SESSION["siteLocalDevPassDB"])) && empty($strReady)  ) {

	    configCore::setHost("");
	    configCore::setUser("");
	    configCore::setPass("");
	    configCore::getConfig($config);
		if (isset($_POST["siteID"])) {

            //aqui los defaults tambien
            $strHost = (isset($_POST["selectIP"]) && $_POST["selectIP"] != "")?$_POST["selectIP"]:"127.0.0.1";
            $strUser = (isset($_POST["selectUser"]) && $_POST["selectUser"] != "")?$_POST["selectUser"]:"root";
            $strPass = (isset($_POST["selectPassword"]))?$_POST["selectPassword"]:"homeland";
            $strDatabase = (isset($_POST["siteID"]) && $_POST["siteID"] != "")?$_POST["siteID"]:"";

			configCore::setHost("{$strHost}");
			configCore::setUser("{$strUser}");
			configCore::setPass("{$strPass}");
			configCore::setDatabase("{$strDatabase}");
			configCore::setConfig("justContinue",true);
			configCore::getConfig($config);

            $_SESSION["siteID"] = $_POST["siteID"];
            $_SESSION["siteLocalDevHostDB"] = $config["host"];
            $_SESSION["siteLocalDevUserDB"] = $config["user"];
			$_SESSION["siteLocalDevPassDB"] = $config["password"];
			configCore::createENV($config);
		}
		else {
            //para backgrounds
            $arrBackgrounds = array();
            /*$directorio = opendir("wt_config_background_NO_SUBIR"); //ruta actual
            while ($archivo = readdir($directorio)){
                if(!is_dir($archivo)){
                    if($archivo != ".." || $archivo != "."){
                        $arrNamePartsBackground = explode(".",$archivo);
                        $arrImageExtension = array_pop($arrNamePartsBackground);

                        if($arrImageExtension == "jpg" || $arrImageExtension == "JPG" || $arrImageExtension == "gif" || $arrImageExtension == "GIF" ||
                           $arrImageExtension == "png" || $arrImageExtension == "PNG"){
                               $arrBackgrounds[] = $archivo;
                        }
                        else{
                            continue;
                        }
                    }
                }
            }*/

            //$imgBackgroundSelected = array_rand($arrBackgrounds);
            // xD
            $intRand = rand(1, 1536);
            $imgBackgroundSelected = "http://replygif.net/i/{$intRand}.gif";
            // xD
			?>
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
			<html>
                <head>
                    <!-- Latest compiled and minified CSS -->
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

                    <!-- Optional theme -->
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
                    <style type="text/css">
                        .bs-callout-info {
                            border-left-color: #1b809e;
                        }
                        .bs-callout {
                            padding: 20px;
                            margin: 20px 0;
                            border: 1px solid #eee;
                            border-left-width: 5px;
                            border-radius: 3px;
                        }
                    </style>
                </head>
				<body onresize="return window.dialogWidth;" id="PageBody"  style="padding: 130px 20px 0px 0px;background-color: white;" >
					<table border="0" align="right" class="bg-info contentTable" width="550px">
						<tr>
							<td>
                                <div style="text-align: center; font-size: 20px; padding: 20px; color: black;">
                                    Bienvenido Maestro Del Universo
                                </div>
                                <div style="text-align: center; padding-bottom: 10px; color: blue;">
                                    Selecciona la IP del servidor MySQL
                                </div>
                                <table align="center" style="width: 100%;">
                                    <tr>
                                        <td style="vertical-align: top; padding-left: 20px;">
                                            <input type="radio" id="127.0.0.1" name="selectIP" class="form-control" data-pass="homeland" data-user="root" value="127.0.0.1" onclick="changePassOptions(this, false, false)">
                                        </td>
                                        <td style="padding-right: 20px;">
                                            <label for="127.0.0.1">127.0.0.1</label>
                                        </td>
                                        <td style="vertical-align: top; padding-left: 20px;">
                                            <input type="radio" name="selectIP"  class="form-control" id="10.1.1.30" data-pass="homeland" data-user="root" value="10.1.1.30" onclick="changePassOptions(this, false, false)">
                                        </td>
                                        <td>
                                            <label for="10.1.1.30">10.1.1.30</label>
                                        </td>
                                        <td style="vertical-align: top; padding-left: 20px;">
                                            <input type="radio" name="selectIP" class="form-control" id="OtherIP" data-pass="" data-user="root" value="" onclick="changePassOptions(this, true, false)">
                                        </td>
                                        <td>
                                            <label for="OtherIP">Otro</label>
                                        </td>
                                    </tr>
                                    <tr id="conectConfig" style="display: none;">
                                        <td colspan="6" style="padding-top: 20px;">
                                            <table align="center">
                                                <tr>
                                                    <td style="padding-right: 10px;">
                                                        IP
                                                    </td>
                                                    <td>
                                                        <input id="ipDBinput" type="text" class="form-control" value="<?php print $config["host"] ?>" onkeyup="changePassOptions(false, true, true)">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-right: 10px;">
                                                        User
                                                    </td>
                                                    <td>
                                                        <input id="ipDBuser" type="text" class="form-control" value="<?php print $config["user"] ?>" onkeyup="changePassOptions(false, true, true)">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-right: 10px;">
                                                        Password para DB
                                                    </td>
                                                    <td>
                                                        <input id="passDBinput" type="text" class="form-control" value="<?php print $config["password"] ?>"  onkeyup="changePassOptions(false, true, true)">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" style="text-align: center; padding-top: 10px; text-align: center;">
                                                        <input id="buttonConect" type="button" class="btn btn-primary" value="Conectar" onclick="changePassOptions(false, true, false)" style="display: none">
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <script type="text/javascript">
                                    function changePassOptions(Obj, displayConectButton, noTryConect){

                                        document.getElementById("conectConfig").style.display="table-row";

                                        if(displayConectButton == true){
                                            console.log("test");
                                            document.getElementById("buttonConect").style.display="inline";
                                            /*document.getElementById("ipDBinput").readOnly=false;
                                            document.getElementById("ipDBuser").readOnly=false;
                                            document.getElementById("passDBinput").readOnly=false;*/
                                        }
                                        else{
                                            document.getElementById("buttonConect").style.display="none";
                                            /*document.getElementById("ipDBinput").readOnly=true;
                                            document.getElementById("ipDBuser").readOnly=true;
                                            document.getElementById("passDBinput").readOnly=true;*/
                                        }

                                        if(noTryConect == false){

                                            var strIp = "";
                                            var strUser = "";
                                            var strPass = "";

                                            if(!Obj){
                                                strIp = document.getElementById("ipDBinput").value;
                                                strUser = document.getElementById("ipDBuser").value;
                                                strPass = document.getElementById("passDBinput").value;
                                            }
                                            else{

                                                strIp = Obj.value;
                                                strUser = Obj.dataset.user;
                                                strPass = Obj.dataset.pass;

                                                document.getElementById("ipDBinput").value = strIp;
                                                document.getElementById("ipDBuser").value = strUser;
                                                document.getElementById("passDBinput").value = strPass;
                                            }

                                            if(strIp != "" && strUser != ""){
                                                var http = new XMLHttpRequest();
                                                var url = "wt_config_local.php?homeland&getDatabasesLocalDev=true";
                                                var params = "ip="+strIp+"&pass="+strPass+"&user="+strUser;
                                                http.open("POST", url, true);

                                                //Send the proper header information along with the request
                                                http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                                                http.setRequestHeader("Content-length", params.length);
                                                http.setRequestHeader("Connection", "close");

                                                http.onreadystatechange = function() {//Call a function when the state changes.
                                                    if(http.readyState == 4 && http.status == 200) {
                                                        document.getElementById("frmSelectSite").innerHTML = http.responseText;
                                                    }
                                                }
                                                http.send(params);
                                            }
                                            else{
                                                document.getElementById("frmSelectSite").innerHTML = "";
                                            }
                                        }

                                    }

                                    function displayRunDump(){
                                        if(document.getElementById("runDumpContent").style.display == "none"){
                                            document.getElementById("runDumpContent").style.display = "block";
                                        }
                                        else{
                                            document.getElementById("runDumpContent").style.display = "none";
                                        }
                                    }

                                    var intervalLoadingConsole = "";

                                    function displayLoadingConsole(boolIni, strTexto){

                                        if(!strTexto){
                                            strTexto = "";
                                        }

                                        if(boolIni == true){
                                            var countPoints = 0;
                                            document.getElementById("runDBconsoleLoading").innerHTML = strTexto;

                                            intervalLoadingConsole = setInterval(function(){
                                                if(countPoints >= 6){
                                                    document.getElementById("runDBconsoleLoading").innerHTML = strTexto;
                                                    countPoints = 0;
                                                }
                                                document.getElementById("runDBconsoleLoading").innerHTML += ".";
                                                countPoints++;
                                            }, 500);
                                        }
                                        else{
                                            document.getElementById("runDBconsoleLoading").innerHTML = "";
                                            clearInterval(intervalLoadingConsole);
                                        }
                                    }

                                    function goRunDBfiles(){

                                        document.getElementById("runDBconsole").innerHTML = "";

                                        var runDBInputName = document.getElementById("runDBinputName").value;
                                        var runDBInputPath = document.getElementById("runDBinputPath").value;

                                        if(runDBInputName == "" || runDBInputName == "db_"){
                                            alert("Debes ingresar un nombre para la base de datos...");
                                            return false;
                                        }

                                        if(runDBInputPath == ""){
                                            alert("Debes ingresar el Path de la carpeta contenedora...");
                                            return false;
                                        }

                                        var strIp = "";
                                        var strUser = "";
                                        var strPass = "";

                                        strIp = document.getElementById("ipDBinput").value;
                                        strUser = document.getElementById("ipDBuser").value;
                                        strPass = document.getElementById("passDBinput").value;


                                        if(strIp != "" && strPass != "" && strUser != ""){
                                            var http = new XMLHttpRequest();
                                            var url = "wt_config.php?homeland&dropDatabase=true";
                                            var params = "ip="+strIp+"&pass="+strPass+"&user="+strUser+"&dbName="+runDBInputName+"&dbPath="+runDBInputPath;
                                            http.open("POST", url, true);

                                            //Send the proper header information along with the request
                                            http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                                            http.setRequestHeader("Content-length", params.length);
                                            http.setRequestHeader("Connection", "close");

                                            http.onreadystatechange = function() {//Call a function when the state changes.
                                                if(http.readyState == 4 && http.status == 200) {
                                                    displayLoadingConsole(false);

                                                    //si termino de eliminar la db
                                                    var httpCreate = new XMLHttpRequest();
                                                    var url = "wt_config.php?homeland&createAndUseDB=true";
                                                    var params = "ip="+strIp+"&pass="+strPass+"&user="+strUser+"&dbName="+runDBInputName+"&dbPath="+runDBInputPath;
                                                    httpCreate.open("POST", url, true);

                                                    //Send the proper header information along with the request
                                                    httpCreate.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                                                    httpCreate.setRequestHeader("Content-length", params.length);
                                                    httpCreate.setRequestHeader("Connection", "close");

                                                    httpCreate.onreadystatechange = function() {//Call a function when the state changes.
                                                        if(httpCreate.readyState == 4 && httpCreate.status == 200) {
                                                            displayLoadingConsole(false);
                                                            //si termino de crear
                                                            var httpRunFiles = new XMLHttpRequest();
                                                            var url = "wt_config.php?homeland&runDBrunFiles=true";
                                                            var params = "ip="+strIp+"&pass="+strPass+"&user="+strUser+"&dbName="+runDBInputName+"&dbPath="+runDBInputPath;
                                                            httpRunFiles.open("POST", url, true);

                                                            //Send the proper header information along with the request
                                                            httpRunFiles.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                                                            httpRunFiles.setRequestHeader("Content-length", params.length);
                                                            httpRunFiles.setRequestHeader("Connection", "close");

                                                            httpRunFiles.onreadystatechange = function() {//Call a function when the state changes.
                                                                if(httpRunFiles.readyState == 4 && httpRunFiles.status == 200) {
                                                                    displayLoadingConsole(false);
                                                                    document.getElementById("runDBconsole").innerHTML += httpRunFiles.responseText;
                                                                    document.getElementById("daleButton").style.display = "inline";
                                                                }
                                                            }
                                                            displayLoadingConsole(true, "Corriendo archivos .sql");
                                                            httpRunFiles.send(params);

                                                            document.getElementById("runDBconsole").innerHTML += httpCreate.responseText;
                                                        }
                                                    }
                                                    displayLoadingConsole(true, "Creando base de datos");
                                                    httpCreate.send(params);
                                                    document.getElementById("runDBconsole").innerHTML += http.responseText;
                                                }
                                            }
                                            displayLoadingConsole(true, "Eliminando base de datos");
                                            http.send(params);
                                        }
                                    }

                                    function goRunDB(){

                                        var runDBInputName = document.getElementById("runDBinputName").value;
                                        if(runDBInputName != ""){
                                            document.getElementById("siteIDalternative").value = runDBInputName;
                                            document.frmSelectSite.submit();
                                        }

                                    }
                                </script>
							</td>
						</tr>
						<tr>
							<td height="1%" style="text-align: center;">
                                <form name="frmSelectSite" id="frmSelectSite" method="POST"></form>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
						</tr>
					</table>
                    <style type="text/css">
                        body{
                            font-family: lucida console;
                            background-color: black;
                            /*background-image: url("wt_config_background_NO_SUBIR/<?php //print $arrBackgrounds[$imgBackgroundSelected] ?>")*/
                            background-image: url("<?php print $imgBackgroundSelected ?>");
                            background-size: contain;
                            background-repeat: no-repeat;
                            background-position: center;
                            height: 77%;
                        }

                        input[type="radio"]{
                            cursor: pointer;
                        }
                        .contentTable{
                            padding: 20px;
                            /*background-color: rgba(255, 255, 255, 0.6);
                            background: rgba(255, 255, 255, 0.6);*/
                            color: rgba(255, 255, 255, 0.6);
                            -webkit-border-radius: 15px;
                            -moz-border-radius: 15px;
                            border-radius: 15px;
                        }
                        .runDumpContent{
                            background: rgba(255, 255, 255, 0.8);
                            -webkit-border-radius: 5px;
                            -moz-border-radius: 5px;
                            border-radius: 5px;
                            padding: 10px;

                        }
                    </style>
                    <div style="margin-left: 15px;border-left-color: #1b809e;position: absolute; bottom: 10px; cursor: pointer" class="bs-callout" id="callout-helper-context-color-specificity" onclick="window.open('index.php?homeland&notas_de_version=true')">
                        <h4>Notas de version <?php print $wt_config_version ?></h4>
                    </div>
				</body>
			</html>
			<?php
			die();
		}
	}
    else{
        configCore::setHost(getenv("HML_HOST"));
        configCore::setUser(getenv("HML_USER"));
        configCore::setPass(getenv("HML_PASS"));
        configCore::setDatabase(getenv("HML_DATABASE"));
        configCore::getConfig($config);
        /*else{
            configCore::setHost("{$_SESSION["siteLocalDevHostDB"]}");
            configCore::setUser("{$_SESSION["siteLocalDevUserDB"]}");
            configCore::setPass("{$_SESSION["siteLocalDevPassDB"]}");
            configCore::setDatabase("{$_SESSION["siteID"]}");
            configCore::setConfig("frontEnd_host","127.0.0.1");
            configCore::setConfig("frontEnd_user","root");
            configCore::setConfig("frontEnd_password","homeland");
            configCore::setConfig("frontEnd_database","db_tigopos_frontend");
            // Datos del local host para crear bases de datos
            configCore::setConfig("local_super_user","{$_SESSION["siteLocalDevUserDB"]}"); // OJO, todos los local user y local passwords tienen que ser iguales para que todos se puedan conectar con todos...
            configCore::setConfig("local_super_password","{$_SESSION["siteLocalDevPassDB"]}");

            configCore::getConfig($config);
        }*/
    }
}
else if($boolIsLocalDev && $boolCronjob == true){
	configCore::init($config);
	configCore::getConfig($config);
}
else if(!$boolIsLocalDev){

    // Database configuration
	configCore::init($config);
	configCore::getConfig($config);
}
