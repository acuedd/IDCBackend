<?php
$boolGlobalIsLocalDev = false;
$boolGlobalIsLocalPub = false;
function db_connect($argHost, $argDatabase, $argUser, $argPass, $boolNewLink = false, $boolStopScriptOnError = true) {
	global $config;
	global $boolGlobalIsLocalDev, $boolGlobalIsLocalPub;

	$boolGlobalIsLocalDev = (isset($config["is_LocalDev"]) && $config["is_LocalDev"]); //Local development, muestra errores en pantalla
	$boolGlobalIsLocalPub = (isset($config["is_LocalPub"]) && $config["is_LocalPub"]); //Instalaciones locales en clientes, para correr una version super light de los queries con chequeo de errores

    $ErrRep = error_reporting();
    error_reporting(0);
    $link = mysqli_connect($argHost, $argUser, $argPass);
    if(mysqli_connect_errno()){
        // No se puede conectar a MySql
        $today = getdate();
	    $month = $today["month"];
	    $year = $today["year"];
	    $weekday = $today["weekday"];
	    $day = $today["mday"];

	    $horas = $today["hours"];
	    $minutos = $today["minutes"];
	    $segundos = $today["seconds"];

	    $strError = "Server: " . $_SERVER["SERVER_NAME"] . "\n Error: " . mysqli_connect_error() . "\n Time: {$weekday} {$year}-{$month}-{$day} {$horas}:{$minutos}:{$segundos}";

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
    if (mysqli_select_db($link, $argDatabase)) {
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

    return mysqli_select_db($objConnection,$argDatabase);
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
 * @param variant $objConnection
 * @return unknown
 */
function db_query_localDev($argQry, $boolLogError = true, $objConnection = false, $boolExplain = true) {
	global $globalConnection, $cfg;

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
        if(false && preg_match('/'.$strInstruc.'/', $sqlPregMatch) && !preg_match('/'.$strInstrucToJump.'/', $sqlPregMatch) && !preg_match('/'.$strInstrucToJump2.'/', $sqlPregMatch)){
            $sqlExplain ="EXPLAIN {$sql}";
            $qTMPPerformance = mysqli_query($globalConnection, $sqlExplain);
            while($rTMPPerformance = mysqli_fetch_assoc($qTMPPerformance)){
                if($rTMPPerformance["type"] == "ALL" && $rTMPPerformance["rows"]>=10000){
                    $boolGut = false;
                    $strImagePath = "images/gm_att.gif";
                    $arrBackTrace = debug_backtrace();
                    $strPrint = "<div class='floatContent' style='border:1px solid red;'>
                                    <div align='center' style='font-size:15px; color: white; background-image: url({$strImagePath});'>Revisar performance, ya que recorre de la tabla <b>{$rTMPPerformance['table']}</b> unas <b>{$rTMPPerformance['rows']} filas</b></div>
                                    <div><pre>{$sql}</pre></div>";
                    $intCount = 0;
                    while($arrTMP = each($arrBackTrace)){
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

    $qTMP = mysqli_query($objConnection,$argQry);
    if ($boolQueryPerformanceLog) $sinQueryEnd = getmicrotime();

    $strError = mysqli_error($objConnection);
    if (strlen($strError)>0) {
        if ($boolLogError) {
        	error_log($strError, 0);
        	print_r("<hr>{$strError}<br><pre>");
			$arrBackTrace = debug_backtrace();
            browseArray(array("BACKTRACE"=>$arrBackTrace), true, true, true, false, false);
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
    	mysqli_query($objConnection,$strQueryLog);
    }
    return $varReturn;
}

/**
 * Esta funcion es la que corre queries localmente en computadoras de clientes, debe ser muy light y llevar un buen log de errores
 *
 * @param string $argQry
 * @param boolean $boolLogError
 * @param variant $objConnection
 * @return unknown
 */
function db_query_localPub($argQry, $boolLogError = true, $objConnection = false) {
	global $globalConnection;

	if ($objConnection === false) $objConnection = $globalConnection;

	// Esto desabilita el reporte de errores para el usuario en internet
    $ErrRep = error_reporting();
    error_reporting(0);

    $qTMP = mysqli_query($objConnection, $argQry);

    $strError = mysqli_error($objConnection);
    if (strlen($strError)>0) {
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
 * @param variant $objConnection
 * @return unknown
 */
function db_query_online($argQry, $boolLogError = true, $objConnection = false) {
	global $globalConnection, $cfg;

	if ($objConnection === false) $objConnection = $globalConnection;

	// Esto desabilita el reporte de errores para el usuario en internet
    $ErrRep = error_reporting();
    error_reporting(0);

    $arrBackTrace = false;

    $boolQueryPerformanceLog = (isset($cfg["core"]["query_performance_log"]) && check_user_class("admin"))?$cfg["core"]["query_performance_log"]:false;

    if ($boolQueryPerformanceLog) $sinQueryStart = getmicrotime();

    $qTMP = mysqli_query($objConnection, $argQry);
    if ($boolQueryPerformanceLog) $sinQueryEnd = getmicrotime();

    $strError = mysqli_error($objConnection);
    if (strlen($strError)>0) {
        if ($boolLogError) {
    		$strEmails = "webmaster@homeland.com.gt";
    		$strHeaders  = "MIME-Version: 1.0" . "\r\n";
			$strHeaders .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
			$strHeaders .= "From: servidor@{$_SERVER["SERVER_NAME"]}\r\n";

			$strErrMsg = $argQry."<hr><br>{$strError} in file <b>".basename($_SERVER["PHP_SELF"])."</b><hr>UID: {$_SESSION["wt"]["uid"]}";

			if ($arrBackTrace === false) $arrBackTrace = debug_backtrace();
			$strTMP = var_export($arrBackTrace, true);
			$strErrMsg .= "<hr>Backtrace:<br><pre>{$strTMP}</pre>";

    		@error_log($strErrMsg, 1, $strEmails, $strHeaders);
        }
        $varReturn = false;
    }
    else {
        $varReturn = $qTMP;
    }

    if ($boolQueryPerformanceLog) {
        if ($arrBackTrace === false) $arrBackTrace = debug_backtrace();
        $strTMP = db_escape(var_export($arrBackTrace, true));
        $strTMP = "";

        $strPhpSessID = session_id();

        $sinTime = $sinQueryEnd - $sinQueryStart;

        $strQuery = db_escape($argQry);
        $strQueryLog = "INSERT INTO wt_queries_log
                        (uid, sessid, clickCounter, fecha, strQuery, strBackTrace, processed)
                        VALUES
                        ({$_SESSION["wt"]["uid"]}, '{$strPhpSessID}', {$_SESSION["wt"]["clickCount"]}, NOW(), '{$strQuery}', '{$strTMP}', '{$sinTime}')";
        mysqli_query($objConnection, $strQueryLog);
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
 * @param variant $objConnection
 * @param boolean $boolExplain
 * @return unknown
 */
function db_query($argQry, $boolLogError = true, $objConnection = false, $boolExplain = true) {
	global $globalConnection, $boolGlobalIsLocalDev, $boolGlobalIsLocalPub;

	if ($objConnection === false) $objConnection = $globalConnection;

	if ($boolGlobalIsLocalDev) {
		return db_query_localDev($argQry, $boolLogError, $objConnection, $boolExplain);
	}
	else if ($boolGlobalIsLocalPub) {
		return db_query_localPub($argQry, $boolLogError, $objConnection);
	}
	else {
		return db_query_online($argQry, $boolLogError, $objConnection);
	}
}

function db_insert_id($objConnection = false) {
	global $globalConnection;
	if ($objConnection === false) $objConnection = $globalConnection;

	return mysqli_insert_id($objConnection);
}

function db_affected_rows($objConnection = false) {
	global $globalConnection;

	if ($objConnection === false) $objConnection = $globalConnection;

	return mysqli_affected_rows($objConnection);
}

function db_result($argIndex, $argRow=0, $argField=0) {
    return mysqli_result($argIndex, $argRow, $argField);
}

function db_fetch_row($argIndex) {
    return mysqli_fetch_row($argIndex);
}

function db_fetch_array($argIndex) {
    return mysqli_fetch_array($argIndex);
}

function db_fetch_assoc($argIndex) {
    return mysqli_fetch_assoc($argIndex);
}

function db_fetch_object($argIndex) {
    return mysqli_fetch_object($argIndex);
}

function db_free_result($argIndex) {
    //Esta funcion siempre devuelve null
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
	if ($field = mysqli_fetch_field($argIndex)){
		do {
			$fields[$field->name]['name'] = $field->name;
			$fields[$field->name]['table'] = $field->table;
			$fields[$field->name]['max_length'] = $field->max_length;
			//$fields[$field->name]['not_null'] = $field->not_null;

		}while ($field = mysqli_fetch_field($argIndex));
	}
	return $fields;
}