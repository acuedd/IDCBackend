<?php
$boolGlobalIsLocalDev = false;
$boolGlobalIsLocalPub = false;
$arrGlobalNotFreedQueries = array();
/**
* put your comment there...
*
* @param mixed $argHost
* @param mixed $argDatabase
* @param mixed $argUser
* @param mixed $argPass
* @param mixed $boolNewLink DESCONTINUADO
* @param mixed $boolStopScriptOnError
* @return mysqli
*/
function db_connect($argHost, $argDatabase, $argUser, $argPass, $boolNewLink = NULL, $boolStopScriptOnError = true) {
    global $config;
    global $boolGlobalIsLocalDev, $boolGlobalIsLocalPub;

    // Variables utiles para el log de velocidad de conexion...
    $intUid = (isset($_SESSION["wt"]["uid"]))?$_SESSION["wt"]["uid"]:0;
    $intUid = intval($intUid);
    $strPhpSessID = session_id();
    $intClickCount = (isset($_SESSION["wt"]["clickCount"]))?$_SESSION["wt"]["clickCount"]:0;

    // Variables para el comportamiento del script
    $boolGlobalIsLocalDev = (isset($config["is_LocalDev"]) && $config["is_LocalDev"]); //Local development, muestra errores en pantalla
    $boolGlobalIsLocalPub = (isset($config["is_LocalPub"]) && $config["is_LocalPub"]); //Instalaciones locales en clientes, para correr una version super light de los queries con chequeo de errores

    $ErrRep = error_reporting();
    error_reporting(0);

    // Checkpoint para el tiempo de conexion
    $intCheckPoint = getmicrotime();
    if (!$link = mysqli_connect($argHost, $argUser, $argPass)) {
        // No se puede conectar a MySql
        $today = getdate();
        $month = $today["month"];
        $year = $today["year"];
        $weekday = $today["weekday"];
        $day = $today["mday"];

        $horas = $today["hours"];
        $minutos = $today["minutes"];
        $segundos = $today["seconds"];

        $strError = "Server: " . $_SERVER["SERVER_NAME"] . "\n Error: " . mysqli_error() . "\n Time: {$weekday} {$year}-{$month}-{$day} {$horas}:{$minutos}:{$segundos}";

        if ($boolGlobalIsLocalPub || $boolGlobalIsLocalDev) {
            //Envia el error al logger segun php.ini
            error_log($strError, 0);
        }
        else {
            //Envia el error por email
            $strEmails = "webmaster@homeland.com.gt";
            error_log($strError, 1, $strEmails, "From: servidor@{$_SERVER["SERVER_NAME"]}\r\n");
        }

        if ($boolStopScriptOnError) {
            include("core/page_unavailable.php");
            die;
        }
        else {
            error_reporting($ErrRep);
            return false;
        }
    }
    error_reporting($ErrRep);

    // Obtengo el tiempo de conexion.  Si no se conecta no llega aqui y se da otro log...
    $intCurrentTime = getmicrotime();
    $intDeltaConnect = $intCurrentTime - $intCheckPoint;

    // Checkpoint para el tiempo de selct database
    $intCheckPoint = getmicrotime();
    if (mysqli_select_db($link, $argDatabase)) {
        // Obtengo el tiempo de select db...
        $intCurrentTime = getmicrotime();

        if ($intDeltaConnect > 10) {
            // Solo lo guardo si se tardo mas de 10 segundos...
            $strQueryLog = "INSERT INTO wt_queries_log
                            (uid, sessid, clickCounter, fecha, strQuery, processed)
                            VALUES
                                ({$intUid}, '{$strPhpSessID}', {$intClickCount}, NOW(), 'mysqli_connect', {$intDeltaConnect})";
            mysqli_query($link, $strQueryLog);
        }

        $intDeltaSelectCT = $intCurrentTime - $intCheckPoint;
        if ($intDeltaSelectCT > 10) {
            // Solo lo guardo si se tardo mas de 10 segundos...
            $strQueryLog = "INSERT INTO wt_queries_log
                            (uid, sessid, clickCounter, fecha, strQuery, processed)
                            VALUES
                            ({$intUid}, '{$strPhpSessID}', {$intClickCount}, NOW(), 'mysqli_select_db', {$intDeltaSelectCT})";
            mysqli_query($link, $strQueryLog);
        }
        return $link;
    }
    else {
        ?>
        <span style="color:red">
             Error al elegir la base de datos: <?php print $argDatabase;?><hr>
        </span>
        <?php
        return false;
    }
}

function db_select_database($argDatabase, $objConnection = false) {
    global $globalConnection;
    if ($objConnection === false) $objConnection = $globalConnection;

    return mysqli_select_db($objConnection, $argDatabase);
}

function db_close($objConnection) {
    return mysqli_close($objConnection);
}

function db_escape($strString, $objConnection = false) {
    global $globalConnection;

    if (!is_resource($objConnection)) $objConnection = $globalConnection;

    $strString = mysqli_real_escape_string($objConnection, $strString);

    return $strString;
}

function db_escape_reference(&$strString, $objConnection = false) {
    global $globalConnection;

    if (!is_resource($objConnection)) $objConnection = $globalConnection;

    $strString = mysqli_real_escape_string($objConnection, $strString);

    return $strString;
}

/**
 * Esta funcion es la que corre queries localmente en el server de development.  Agrega logs y cosas que solo lo harian mas lento en internet.
 *
 * @param string $argQry
 * @param boolean $boolLogError
 * @param mixed $objConnection
 * @param boolean $boolExplain Hacer explain para debug de de calidad de join...
 * @param boolean $boolForcePerfLog Forzar el uso del log de performance...EN ESTE CONTEXTO NO HACE NADA
 * @return mixed
 */
function db_query_localDev($argQry, $boolLogError = true, $objConnection = false, $boolExplain = true, $boolForcePerfLog = false) {
    global $globalConnection, $cfg;
    global $boolGlobalIsLocalDev, $boolGlobalIsLocalPub;

    if ($objConnection === false) $objConnection = $globalConnection;

    $arrBackTrace = false;
    $sinQueryStart = 0;
    $sinQueryEnd = 0;
    $boolQueryPerformanceLog = (isset($cfg["core"]["query_performance_log"]))?$cfg["core"]["query_performance_log"]:false;

    if ($boolQueryPerformanceLog) $sinQueryStart = getmicrotime();
    /*------------------------------INICIO JCABRERA 20130626 ESTAS LINEAS DE CODIGO TIENEN COMO OBJETIVO REVISAR EL PERFORMANCE DE LOS QUERYS----------------------*/
    $boolGut = true;
    if((isset($_SESSION["wt"]["swusertype"])) && ($_SESSION["wt"]["swusertype"] == "ext_homeland" && $boolExplain)){
        $strInstruc = "select";
        $strInstrucToJump = "insert";
        $strInstrucToJump2 = "replace";
        $sql = $argQry;
        $sqlPregMatch = strtolower($sql);
        //if(preg_match('/'.$strInstruc.'/', $sqlPregMatch)) {
        if(preg_match('/'.$strInstruc.'/', $sqlPregMatch) && !preg_match('/'.$strInstrucToJump.'/', $sqlPregMatch) && !preg_match('/'.$strInstrucToJump2.'/', $sqlPregMatch)){
            $sqlExplain ="EXPLAIN {$sql}";
            $qTMPPerformance = mysqli_query($objConnection, $sqlExplain);
            while($rTMPPerformance = mysqli_fetch_array($qTMPPerformance)){
                if($rTMPPerformance["type"] == "ALL" && $rTMPPerformance["rows"]>=10000){
                    $boolGut = false;
                    $strImagePath = "images/gm_att.gif";
                    $arrBackTrace = debug_backtrace();
                    $strPrint = "<div class='floatContent' style='border:1px solid red;'>
                                    <div align='center' style='font-size:15px; color: white; background-image: url({$strImagePath});'>Revisar performance, ya que recorre de la tabla <b>{$rTMPPerformance['table']}</b> unas <b>{$rTMPPerformance['rows']} filas</b></div>
                                    <div><pre>{$sql}</pre></div>";
                    $intCount = 0;
                    $arrTMP = array();
                    foreach ($arrBackTrace as $arrTMP["key"] => $arrTMP["value"]) {
                        $intEspacios = 10*$intCount;
                        $strTMP = "";
                        for($i = 1; $i<=$intEspacios; $i++){
                            $strTMP .= "&nbsp;";
                        }
                        $strPrint .= "{$strTMP}<label style='color:green'><b>Archivo</b></label> -> {$arrTMP["value"]["file"]}<br>
                                      {$strTMP}<label style='color:orange'><b>Funcion</b></label> -> {$arrTMP["value"]["function"]}
                                      <label style='color:blue'><b>Linea (aprox)</b></label> -> {$arrTMP["value"]["line"]}<br>";
                        $intCount++;
                    }
                    $strPrint.="</div>";
                    print $strPrint;
                }
                if(!$boolGut) continue;
            }
            mysqli_free_result($qTMPPerformance);
        }
    }
    /*------------------------------FIN JCABRERA 20130626 ESTAS LINEAS DE CODIGO TIENEN COMO OBJETIVO REVISAR EL PERFORMANCE DE LOS QUERYS----------------------*/
    if (!$objConnection) drawDebug("objConnection empty en mysqli ln 203");
    if (!$argQry) {
        core_SendScriptInfoToWebmaster("argQry empty en mysqli ln 204", false, "agudiel@homeland.com.gt", true);
    }
    $qTMP = mysqli_query($objConnection, $argQry);
    if ($boolQueryPerformanceLog) $sinQueryEnd = getmicrotime();

    $strError = mysqli_error($objConnection);
    if (strlen($strError)>0) {
        if ($boolLogError) {
            error_log($strError, 0);

            print_r("<hr>{$strError}<br><pre>");
            //*
            $arrBackTrace = debug_backtrace();
            unset($arrBackTrace[0]);
            browseArray(array("BACKTRACE"=>$arrBackTrace), true, true, true, false, false);
            //*/
            print_r("</pre><hr>");
        }
        $varReturn = false;
    }
    else {
        $varReturn = $qTMP;
    }

    if ($boolQueryPerformanceLog) {
        if ($arrBackTrace === false) $arrBackTrace = debug_backtrace();
        $strTMP = db_escape(var_export($arrBackTrace, true));

        $strPhpSessID = session_id();

        $sinTime = $sinQueryEnd - $sinQueryStart;

        $strQuery = db_escape($argQry);
        $strQueryLog = "INSERT INTO wt_queries_log
                        (uid, sessid, clickCounter, fecha, strQuery, strBackTrace, processed)
                        VALUES
                        ({$_SESSION["wt"]["uid"]}, '{$strPhpSessID}', {$_SESSION["wt"]["clickCount"]}, NOW(), '{$strQuery}', '{$strTMP}', '{$sinTime}')";
        mysqli_query($objConnection, $strQueryLog);
    }

    return $varReturn;
}

$boolGlobalLastQueryWasError = false;

/**
 * Esta funcion es la que corre queries localmente en computadoras de clientes, debe ser muy light y llevar un buen log de errores
 *
 * @param string $argQry
 * @param boolean $boolLogError
 * @param mixed $objConnection
 * @param mixed $boolExplain Hacer explain para debug de de calidad de join...EN ESTE CONTEXTO NO HACE NADA
 * @param boolean $boolForcePerfLog Forzar el uso del log de performance...EN ESTE CONTEXTO NO HACE NADA
 * @return mixed
 */
function db_query_localPub($argQry, $boolLogError = true, $objConnection = false, $boolExplain = false, $boolForcePerfLog = false) {
    global $globalConnection, $cfg;
    global $boolGlobalIsLocalDev, $boolGlobalIsLocalPub;
	global $boolGlobalLastQueryWasError;

    if ($objConnection === false) $objConnection = $globalConnection;

    // Esto desabilita el reporte de errores para el usuario en internet
    $ErrRep = error_reporting();
    error_reporting(0);

    $qTMP = mysqli_query($objConnection, $argQry);

    $strError = mysqli_error($objConnection);
    if (strlen($strError)>0) {
		$boolGlobalLastQueryWasError = true;
        if ($boolLogError) {
            error_log($strError, 0);
        }
        $varReturn = false;
    }
    else {
        $varReturn = $qTMP;
    }

    // Esto desabilita el reporte de errores para el usuario en internet
    $ErrRep = error_reporting();
    error_reporting(0);

    return $varReturn;
}

/**
 * Esta funcion es la que corre los queries en internet.  Solo corre lo escencial.
 *
 * @param string $argQry
 * @param boolean $boolLogError
 * @param mixed $objConnection
 * @param mixed $boolExplain Hacer explain para debug de de calidad de join...EN ESTE CONTEXTO NO HACE NADA
 * @param boolean $boolForcePerfLog Forzar el uso del log de performance...
 * @return mixed
 */
function db_query_online($argQry, $boolLogError = true, $objConnection = false, $boolExplain = false, $boolForcePerfLog = false) {
    global $globalConnection, $cfg;
    global $boolGlobalIsLocalDev, $boolGlobalIsLocalPub;
    global $boolGlobalLastQueryWasError;

    if ($objConnection === false) $objConnection = $globalConnection;

    // Esto desabilita el reporte de errores para el usuario en internet
    $ErrRep = error_reporting();
    error_reporting(0);

    $arrBackTrace = false;

    $boolQueryPerformanceLog = (isset($cfg["core"]["query_performance_log"]) && check_user_class("admin"))?$cfg["core"]["query_performance_log"]:false;
    $boolQueryPerformanceLog = ($boolQueryPerformanceLog || $boolForcePerfLog);

    $sinQueryStart = getmicrotime();
    $qTMP = mysqli_query($objConnection, $argQry);
    $sinQueryEnd = getmicrotime();

    $boolGlobalLastQueryWasError = false;
    $strError = mysqli_error($objConnection);
    if (strlen($strError)>0) {
        $boolGlobalLastQueryWasError = true;
        if ($boolLogError) {
            /*
            //20140903 AG - Version original - log por mail
            $strEmails = "webmaster@homeland.com.gt";
            $strHeaders  = "MIME-Version: 1.0" . "\r\n";
            $strHeaders .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
            $strHeaders .= "From: servidor@{$_SERVER["SERVER_NAME"]}\r\n";

            $strErrMsg = $argQry."<hr><br>{$strError} in file <b>".basename($_SERVER["PHP_SELF"])."</b><hr>UID: {$_SESSION["wt"]["uid"]}";

            if ($arrBackTrace === false) $arrBackTrace = debug_backtrace();
            $strTMP = var_export($arrBackTrace, true);
            $strErrMsg .= "<hr>Backtrace:<br><pre>{$strTMP}</pre>";


            @error_log($strErrMsg, 1, $strEmails, $strHeaders);
            //*/

            // Nueva version, un correo al primer error y luego cada 15 minutos, log a base de datos.
            //*
            $strQueryEscaped = mysqli_real_escape_string($objConnection, $argQry);
            $strErrorEscaped = mysqli_real_escape_string($objConnection, $strError);
            $arrBackTrace = debug_backtrace();
            $strBackTrace = var_export($arrBackTrace, true);
            $strBackTraceEscaped = mysqli_real_escape_string($objConnection, $strBackTrace);

            //$strQueryMD5 = md5($strQueryEscaped);

            //20150213 AG: Mejos usaremos el backtrace como referencia
            $strQueryReference = debug_backtrace();
            //Tendre que quitar a mano los argumentos pues el parametro de debug_backtrace no me esta funcionando
            $arrTMP = $strQueryReference;
            $arrItem = array();
            foreach ($arrTMP as $arrItem["key"] => $arrItem["value"]) {
                $strQueryReference[$arrItem["key"]]["args"] = false;
            }
            $strQueryReference = var_export($strQueryReference, true);
            $strQueryMD5 = md5($strQueryReference);

            mysqli_query($objConnection, "INSERT INTO wt_queries_error_log (fecha, hora, fechahora, notificado, query_md5, query_text, error_text, backtrace)
                         VALUES (CURDATE(), CURTIME(), NOW(), 'N', '{$strQueryMD5}', '{$strQueryEscaped}', '{$strErrorEscaped}', '{$strBackTraceEscaped}')");
            $intID = mysqli_insert_id($objConnection);
            if ($intID == 0) {
                // Si NO pudo insertar...
                $strEmails = "webmaster@homeland.com.gt";
                $strHeaders  = "MIME-Version: 1.0" . "\r\n";
                $strHeaders .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
                $strHeaders .= "From: servidor@{$_SERVER["SERVER_NAME"]}\r\n";

                $strErrMsg = $argQry."<hr>PROBLEMA SERIO PUES NO SE PUDO REGISTRAR wt_queries_error_log<hr><br>{$strError} archivo <b>".basename($_SERVER["PHP_SELF"])."</b><hr>UID: {$_SESSION["wt"]["uid"]}";
                $strErrMsg .= "<hr>Backtrace:<br><pre>{$strBackTrace}</pre>";

                // Quitamos esto pues esto nos causa problemas aun mas serios...
                //@error_log($strErrMsg, 1, $strEmails, $strHeaders);
            }
            else {
                $strQuery = "SELECT COUNT(id) AS yaReportados
                             FROM wt_queries_error_log
                             WHERE notificado = 'Y' AND
                                    query_md5 = '{$strQueryMD5}' AND
                                    fechahora > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
                $qTMP = mysqli_query($objConnection, $strQuery);
                $rTMP = mysqli_fetch_assoc($qTMP);
                mysqli_free_result($qTMP);

                if (!$rTMP["yaReportados"]) {
                    // Si no ha sido reportado en los ultimos 15 minutos, lo reporto
                    mysqli_query($objConnection, "UPDATE wt_queries_error_log SET notificado = 'P' WHERE notificado = 'N' AND query_md5 = '{$strQueryMD5}'");

                    $strQuery = "SELECT COUNT(id) AS aReportar
                                 FROM wt_queries_error_log
                                 WHERE notificado = 'P' AND
                                        query_md5 = '{$strQueryMD5}'";
                    $qTMP = mysqli_query($objConnection, $strQuery);
                    $rTMP = mysqli_fetch_assoc($qTMP);
                    mysqli_free_result($qTMP);

                    mysqli_query($objConnection, "UPDATE wt_queries_error_log SET notificado = 'Y' WHERE notificado = 'P' AND query_md5 = '{$strQueryMD5}'");

                    $strEmails = "webmaster@homeland.com.gt";
                    $strHeaders  = "MIME-Version: 1.0" . "\r\n";
                    $strHeaders .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
                    $strHeaders .= "From: servidor@{$_SERVER["SERVER_NAME"]}";

                    $strErrMsg = $argQry."<hr>Referencia:{$strQueryMD5}<br>Errores en los ultimos 15 minutos: {$rTMP["aReportar"]}<hr><br>{$strError} archivo <b>".basename($_SERVER["PHP_SELF"])."</b><hr>UID: {$_SESSION["wt"]["uid"]}";
                    $strErrMsg .= "<hr>Backtrace:<br><pre>{$strBackTrace}</pre>";

                    @error_log($strErrMsg, 1, $strEmails, $strHeaders);
                }
            }
            //*/
        }
        $varReturn = false;
    }
    else {
        $varReturn = $qTMP;
    }

    $sinTime = $sinQueryEnd - $sinQueryStart;
    if ($boolQueryPerformanceLog || $sinTime >= 20) {
        $arrBackTrace = debug_backtrace();
        unset($arrBackTrace[0]); //Para quitar la llamada a esta funcion...
        // Para quitar el query del backtrace pues ya lo tengo en otra variable...
        $arrTMP = $arrBackTrace;
        $arrItem = array();
        foreach ($arrTMP as $arrItem["key"] => $arrItem["value"]) {
            if ($arrBackTrace[$arrItem["key"]]["function"] == "db_query") $arrBackTrace[$arrItem["key"]]["args"] = false;
        }

        $strBackTrace = var_export($arrBackTrace, true);
        $strBackTraceEscaped = mysqli_real_escape_string($objConnection, $strBackTrace);
        $strPhpSessID = session_id();

        if ($sinTime > 2) {
            // Solo lo guardo si se tardo mas de 2 segundos...
            $strQuery = db_escape($argQry);
            $strQueryLog = "INSERT INTO wt_queries_log
                            (uid, sessid, clickCounter, fecha, strQuery, strBackTrace, processed)
                            VALUES
                            ({$_SESSION["wt"]["uid"]}, '{$strPhpSessID}', {$_SESSION["wt"]["clickCount"]}, NOW(), '{$strQuery}', '{$strBackTraceEscaped}', '{$sinTime}')";
            mysqli_query($objConnection, $strQueryLog);
        }
    }

    // Restauro el error reporting
    error_reporting($ErrRep);

    return $varReturn;
}

/**
 * Esta es la que corre los queries en general, manda a llamar a una funcion u otra segun el servidor en el que estoy.
 *
 * @param string $argQry
 * @param boolean $boolLogError
 * @param mixed $objConnection
 * @param boolean $boolExplain Hacer explain para debug de calidad de join...
 * @param boolean $boolForcePerfLog Forzar el uso del log de performance...
 * @return mixed
 */
function db_query($argQry, $boolLogError = true, $objConnection = false, $boolExplain = false, $boolForcePerfLog = false) {
    global $globalConnection, $cfg;
    global $boolGlobalIsLocalDev, $boolGlobalIsLocalPub;

    if ($objConnection === false) $objConnection = $globalConnection;

    if ($boolGlobalIsLocalDev) {
        return db_query_localDev($argQry, $boolLogError, $objConnection, $boolExplain, $boolForcePerfLog);
    }
    else if ($boolGlobalIsLocalPub) {
        return db_query_localPub($argQry, $boolLogError, $objConnection, $boolExplain, $boolForcePerfLog);
    }
    else {
        return db_query_online($argQry, $boolLogError, $objConnection, $boolExplain, $boolForcePerfLog);
    }
}

function db_insert_id($objConnection = false) {
    global $globalConnection, $boolGlobalLastQueryWasError;
    if ($objConnection === false) $objConnection = $globalConnection;

    if ($boolGlobalLastQueryWasError) {
        return 0;
    }
    else {
        return mysqli_insert_id($objConnection);
    }
}

function db_affected_rows($objConnection = false) {
    global $globalConnection;

    if ($objConnection === false) $objConnection = $globalConnection;

    return mysqli_affected_rows($objConnection);
}

function db_result($argIndex, $argRow=0, $argField=0) {
    drawDebug("mysqli_result Descontinuada");
    //return mysqli_result($argIndex, $argRow, $argField);
    db_seek($argIndex, $argRow);
    $arrTMP = db_fetch_array($argIndex);
    return $arrTMP[$argField];
}

function db_fetch_row($argIndex) {
    return mysqli_fetch_row($argIndex);
}

if (!defined("MYSQL_ASSOC")) define("MYSQL_ASSOC", true); //20180404 AG: Esto es para evitar el error que se da por las 249 llamadas que hay usando esta contante descontinuada.  Igual hay que limpiar eso.
function db_fetch_array($argIndex, $boolIsAssoc = false) {
    if ($boolIsAssoc) {
        return db_fetch_assoc($argIndex);
    }
    else {
        return mysqli_fetch_array($argIndex);
    }
}

function db_fetch_assoc($argIndex) {
    return mysqli_fetch_assoc($argIndex);
}

function db_fetch_object($argIndex) {
    return mysqli_fetch_object($argIndex);
}

function db_free_result($argIndex) {
    global $boolGlobalIsLocalDev, $cfg;

    /*
    // 20150423 AG: Quite esto pues solo llegaban errores de más por cada error.
    if (!mysqli_free_result($argIndex)) {
        core_SendScriptInfoToWebmaster("Error al liberar un query, ver el backtrace!.  Sitio: {$cfg["core"]["url"]}");
    }
    //*/

    mysqli_free_result($argIndex);

    return;
}

function db_num_rows($argIndex) {
    return mysqli_num_rows($argIndex);
}

function db_num_fields($argIndex) {
    return mysqli_num_fields($argIndex);
}

function db_error($objConnection = false) {
    global $globalConnection;
    if ($objConnection === false) $objConnection = $globalConnection;

    return mysqli_error($objConnection);
}

function db_seek($argIndex, $intRow) {
    return mysqli_data_seek ($argIndex, $intRow);
}

function db_get_fields($argIndex){
    $fields = array();
    if ($field = mysqli_fetch_field($argIndex)){
        do {
            $fields[$field->name]['name'] = $field->name;
            $fields[$field->name]['table'] = $field->table;
            $fields[$field->name]['max_length'] = $field->max_length;
            $fields[$field->name]['type'] = $field->type;
            if (isset($field->not_null)) $fields[$field->name]['not_null'] = $field->not_null;

        }while ($field = mysqli_fetch_field($argIndex));
    }
    return $fields;
}