<?php
// ARRAYS GLOBALES!!
$arrHMLToolsArray = array();

/* $arrExtraRegisterFieldsFunctions[] = array("module"=>"gomovie",
  "functionDraw"=>"gomovie_drawComboSelector (recibe como parametro true si quiero dibujar los titulos de los campos en bold, false si no. Y el UserID default porsi estoy editando.)",
  "functionProcess"=>"gomovie_firstComboSelection (recibe como parametro el userid y si hago la activación de una vez, devuelve 1 para OK, -1 para RollBack, 0 para Done nothing)",
  "functionProcessRollBack"=>"gomovie_firstComboSelectionRollBack (recibe como parametro el userid)"); */
$arrExtraRegisterFieldsFunctions = array();

/**
 * Para monitorear el uso de memoria de un script y optimizar
 *
 * @param string $strMessage
 * @param bool $boolLog true lo manda al wt_log, false lo muestra en pantalla con un drawDebug
 */
function benchmark_checkMemoryUsage($strMessage, $boolLog = true) {
	global $boolGlobalIsLocalDev;

	$boolIsAdmin = (check_user_class("admin"));
	if (!$boolGlobalIsLocalDev && !$boolIsAdmin) {
		return;
	}

	$intMemoryUsage = memory_get_usage();
	$intMemoryUsage = $intMemoryUsage/1024;
	$intMemoryUsage = round($intMemoryUsage/1024, 2);
	if ($boolLog) {
		$strMessage_e = db_escape($strMessage);
		db_query("INSERT INTO wt_log (uid, date, descripcion) VALUES ({$_SESSION["wt"]["uid"]}, NOW(), '{$strMessage_e}: {$intMemoryUsage}MB')");
	}
	else {
		drawDebug($intMemoryUsage, $strMessage);
	}
}

/**
 * Para ver en que partes del codigo se está demorando más tiempo y poder hacer optimizaciones
 *
 * @param integer $intCheckPoint
 * @param string $strMessage
 * @param bool $boolLog true lo manda al wt_log, false lo muestra en pantalla con un drawDebug
 * @param bool $boolForceCheck false Por si necesito hacer chequeos desde la pagina publica, ignora que el usuario sea admin
 * @param bool $boolGetTimeDeltaOnly false Para que solo me devuelva la diferencia, evitando conexiones a base de datos...
 *
 * @return integer
 */
function benchmark_checkProcedureTime(&$intCheckPoint, $strMessage, $boolLog = true, $boolForceCheck = false, $boolGetTimeDeltaOnly = false) {
	global $boolGlobalIsLocalDev, $intGlobalPageProcessedLogID;

	if (!$boolForceCheck && !$boolGlobalIsLocalDev && !check_user_class("admin")) {
		return 0;
	}

	$intCurrentTime = getmicrotime();
	$intCurrDelta = $intCurrentTime - $intCheckPoint;
	if (!$boolGetTimeDeltaOnly) {
		$strExtraMsg = ($intCurrDelta > 1)?" - WARNING":"";
		if ($boolLog) {
			$strMessage_e = db_escape($strMessage);
			$intCurrDelta_e = round($intCurrDelta, 6);
			$intUid = (isset($_SESSION["wt"]["uid"]))?$_SESSION["wt"]["uid"]:0;
			$intUid = intval($intUid);
			db_query("INSERT INTO wt_log (uid, date, descripcion) VALUES ({$intUid}, NOW(), '{$intGlobalPageProcessedLogID} - {$strMessage_e}: {$intCurrDelta_e} {$strExtraMsg}')");
		}
		else {
			drawDebug("{$intCurrDelta} {$strExtraMsg}", $strMessage);
		}
	}
	$intCheckPoint = getmicrotime();

	return $intCurrDelta;
}

function core_validateEmailAddress($strEMail) {
	$strEMail = trim($strEMail);

	if (empty($strEMail))
		return false;

	return (preg_match("/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/i", $strEMail) == 1);
}

/**
 * Devuelve la informacion del script al webmaster para ayudar a debuguear.  Esta funcion se agruega en lugares especificos del codigo que se esten probando.
 *
 * @param string $strExtraMessage
 */
function core_SendScriptInfoToWebmaster($strExtraMessage = "", $boolIncludeServerInfo = false, $strEmailAddress = "webmaster@homeland.com.gt") {
	global $boolGlobalIsLocalDev;

	if ($boolGlobalIsLocalDev) {
		debug::drawDebug($strExtraMessage, "core_SendScriptInfoToWebmaster");
		return;
	}

	$strMessage = "<b>Extra info:</b> {$strExtraMessage}";

	$strMessage .= "<br><br><b>Informacion del script</b>";

	$arrBackTrace = debug_backtrace();
	$strTMP = var_export($arrBackTrace, true);
	$strMessage .= "<br><br><b>Backtrace:</b><br><pre>{$strTMP}</pre>";

	if ($boolIncludeServerInfo) {
		$strTMP = var_export($_SERVER, true);
		$strMessage .= "<br><br><b>_SERVER:</b><br><pre>{$strTMP}</pre>";
	}

	$arrPost = $_POST;
	if (isset($arrPost["login_passwd"])) $arrPost["login_passwd"] = "xxx";
	if (isset($arrPost["password"])) $arrPost["password"] = "xxx";

	if (isset($arrPost["frmRegister_password"])) $arrPost["frmRegister_password"] = "xxx";
	if (isset($arrPost["frmRegister_password2"])) $arrPost["frmRegister_password2"] = "xxx";

	if (isset($arrPost["form_user_passwd1"])) $arrPost["form_user_passwd1"] = "xxx";
	if (isset($arrPost["form_user_passwd2"])) $arrPost["form_user_passwd2"] = "xxx";

	if (isset($arrPost["CCN"])) $arrPost["CCN"] = substr($arrPost["CCN"], -4);
	if (isset($arrPost["ED"])) $arrPost["ED"] = "xxyy";
	if (isset($arrPost["TT"])) $arrPost["TT"] = "tttt";
	if (isset($arrPost["CV"])) $arrPost["CV"] = "cvcv";

	$strTMP = var_export($arrPost, true);
	$strMessage .= "<br><br><b>_POST:</b><br><pre>{$strTMP}</pre>";

	$arrPost = $_GET;
	if (isset($arrPost["login_passwd"])) $arrPost["login_passwd"] = "xxx";
	if (isset($arrPost["password"])) $arrPost["password"] = "xxx";

	if (isset($arrPost["frmRegister_password"])) $arrPost["frmRegister_password"] = "xxx";
	if (isset($arrPost["frmRegister_password2"])) $arrPost["frmRegister_password2"] = "xxx";

	if (isset($arrPost["form_user_passwd1"])) $arrPost["form_user_passwd1"] = "xxx";
	if (isset($arrPost["form_user_passwd2"])) $arrPost["form_user_passwd2"] = "xxx";

	if (isset($arrPost["CCN"])) $arrPost["CCN"] = substr($arrPost["CCN"], -4);
	if (isset($arrPost["ED"])) $arrPost["ED"] = "xxyy";
	if (isset($arrPost["TT"])) $arrPost["TT"] = "tttt";
	if (isset($arrPost["CV"])) $arrPost["CV"] = "cvcv";

	$strTMP = var_export($arrPost, true);
	$strMessage .= "<br><br><b>_GET:</b><br><pre>{$strTMP}</pre>";

	$strTMP = var_export($_SESSION, true);
	$strMessage .= "<br><br><b>_SESSION:</b><br><pre>{$strTMP}</pre>";


	if ($boolGlobalIsLocalDev) {
		drawDebug($strMessage);
	}
	else {
		//@mail($strEmailAddress, "Debug Info", $strMessage, $strHeaders);
        $objMail = new AttachMailer($strEmailAddress, "Debug Info", "");
        $objMail->setMessageHTML($strMessage);
        $objMail->send();
	}
}

function core_saveCustomSettings($strDirectory, $strKey, $varValue)
{
	global $cfg;

	$arrCurrentSettings = sqlGetValueFromKey("SELECT * FROM wt_user_settings
                                                WHERE userid = {$_SESSION["wt"]["uid"]} AND id = '{$strDirectory}'");
	if (empty($arrCurrentSettings)) {
		$arrCurrentSettings = array();
	}
	else {
		$arrCurrentSettings = unserialize(stripslashes($arrCurrentSettings["config"]));
		if (!is_array($arrCurrentSettings)) {
			$arrCurrentSettings = array();
		}
	}

	$arrCurrentSettings[$strKey] = $varValue;
	$strSaveSettings = addslashes(serialize($arrCurrentSettings));

	db_query("REPLACE INTO wt_user_settings (userid, id, config) VALUES ({$_SESSION["wt"]["uid"]}, '{$strDirectory}', '{$strSaveSettings}')");

	$cfg[$strDirectory][$strKey] = $varValue;
}

function check_user_class($attr) {
	global $lang;
	// classes used in the core
	// normal - normal user
	// admin - superuser
	// $_SESSION["wt"]["access"]{"modulename"] == true for every access module

	//20140512 AG: Si quiero ver un link que en realidad no este activo en el menu, que de false para todos, hasta admin para que nos demos cuenta que hay algo malo.
	if (is_null($attr))
		return false;

	if ($attr == "freeAccess")
		return true;

	if (!isset($_SESSION["wt"]))
		return false;

	if (!$_SESSION["wt"]["logged"])
		return false;

	if (isset($cfg["clientes"]) && !empty($cfg["clientes"]["Proveedores_Usuarios"])
		&& $_SESSION["wt"]["swusertype"] == $cfg["clientes"]["Proveedores_Usuarios"])
		return false;

	if ($_SESSION["wt"]["class"] == "admin")
		return true;

	if ($_SESSION["wt"]["class"] == "helpdesk" && $attr != "admin")
		return true;


	// if user is not admin, and the class must be admin, returns
	if ($attr == "admin")
		return false;

	if ($_SESSION["wt"]["class"] == $attr)
		return true;

	if (isset($_SESSION["wt"]["access"][$attr]) && $_SESSION["wt"]["access"][$attr] == true)
		return true;
	else
		return false;
}

function return_category_access()
{
    if (!isset($_SESSION["wt"]))
        return false;

    $strCategories = "";
    if(!empty($_SESSION["wt"]["categories"])){
        foreach ($_SESSION["wt"]["categories"] AS $keyCategory => $valueCategory){
            if(!empty($strCategories))
                $strCategories .= ", ";

            $strCategories .= "{$valueCategory}";
        }
        return $strCategories;
    }

    return false;
}

/**
 * Esta funcion se ejecuta cuando se dibuja la forma de confirmacion de correo, dibuja todos los campos de los modulos que lo requieran segun se defina en
 * el array global $arrExtraRegisterFieldsFunctions
 *
 * @param bool $boolBoldTitle Para que se ponga en bold los titulos de los campos
 * @param integer $intEditUserID Usuario que se esta editando
 */
function register_extra_draw_fields($boolBoldTitle = true, $intEditUserID = 0) {
	global $arrExtraRegisterFieldsFunctions;

	$arrEntry = array();
	foreach ($arrExtraRegisterFieldsFunctions as $arrEntry["key"] => $arrEntry["value"]) {
		if (check_module($arrEntry["value"]["module"]) && function_exists($arrEntry["value"]["functionDraw"])) {
			$strFunction = $arrEntry["value"]["functionDraw"];
			$strFunction($boolBoldTitle, $intEditUserID);
		}
	}
	reset($arrExtraRegisterFieldsFunctions);
}

/**
 * Funcion que procesa la informacion de los campos enviados en register_extra_draw_fields segun se configure en el array global $arrExtraRegisterFieldsFunctions
 *
 * @param integer $intUserID
 * @param boolean $boolDoActivateToo
 * @return boolean
 */
function register_extra_process_fields($intUserID, $boolDoActivateToo = false) {
	global $arrExtraRegisterFieldsFunctions;

	$boolReturn = true;
	$arrEntriesRan = array();
	$arrEntry = array();
	foreach ($arrExtraRegisterFieldsFunctions as $arrEntry["key"] => $arrEntry["value"]) {
		if (check_module($arrEntry["value"]["module"]) && function_exists($arrEntry["value"]["functionProcess"])) {
			$strFunction = $arrEntry["value"]["functionProcess"];
			$intReturn = $strFunction($intUserID, $boolDoActivateToo);
			if ($intReturn == 1) {
				$arrEntriesRan[$arrEntry["key"]] = true;
			}
            elseif ($intReturn == -1) {
				$boolReturn = false;
			}
		}
	}
	reset($arrExtraRegisterFieldsFunctions);

	if (!$boolReturn) {
		$arrEntry = array();
		foreach ($arrEntriesRan as $arrEntry["key"] => $arrEntry["value"]) {
			$strFunction = $arrExtraRegisterFieldsFunctions[$arrEntry["key"]]["functionProcessRollBack"];
			$strFunction($intUserID);
		}
	}

	return $boolReturn;
}

/**
 * Devuelve la diferencia entre dos fechas, en meses.  Espera fechas en formato MYSQL. Ignora las horas.
 *
 * @param string (SQL DATE) $strDateFrom
 * @param string (SQL DATE) $strDateTo
 * @return string
 */
function date_getDifferenceInMonths($strDateFrom, $strDateTo)
{
	$arrFrom = explode("-", $strDateFrom);
	$arrTo = explode("-", $strDateTo);
	$strDateFrom = "{$arrFrom[0]}{$arrFrom[1]}";
	$strDateTo = "{$arrTo[0]}{$arrTo[1]}";

	$strTMP = sqlGetValueFromKey("SELECT PERIOD_DIFF('{$strDateTo}','{$strDateFrom}') AS diferencia");

	return $strTMP;
}

/**
 * Devuelve la diferencia entre dos fechas, en dias.  Espera fechas en formato MYSQL. Ignora las horas.
 *
 * @param string (SQL DATE) $strDateFrom la fecha a cual le sera restada la otra fecha
 * @param string (SQL DATE) $strDateTo la fecha que resta
 * @return integer
 */
function date_getDifferenceInDays($strDateFrom, $strDateTo)
{
	$anioActual = substr($strDateFrom, 0, 4);
	$mesActual = substr($strDateFrom, 5, 2);
	$diaActual = substr($strDateFrom, 8, 2);

	$anioInicio = substr($strDateTo, 0, 4);
	$mesInicio = substr($strDateTo, 5, 2);
	$diaInicio = substr($strDateTo, 8, 2);

	//calculo timestam de las dos fechas
	/*
      Solo para probarlo...
      $dias_sql = sqlGetValueFromKey("SELECT (TO_DAYS('{$strDateFrom}') - TO_DAYS('{$strDateTo}')) as Dias");
     */
	$timestamp1 = mktime(0, 0, 0, $mesActual, $diaActual, $anioActual);
	$timestamp2 = mktime(0, 0, 0, $mesInicio, $diaInicio, $anioInicio);

	//resto a una fecha la otra
	$segundos_diferencia = $timestamp1 - $timestamp2;
	//echo $segundos_diferencia;
	//convierto segundos en días
	$dias_diferencia = $segundos_diferencia / (60 * 60 * 24);

	//obtengo el valor absoulto de los días (quito el posible signo negativo)
	$dias_diferencia = abs($dias_diferencia);

	//quito los decimales a los días de diferencia
	$dias_diferencia = floor($dias_diferencia);

	return $dias_diferencia;
}

/**
 * Devuelve un array con la edad en años, meses.
 *
 * @param string $strBirthDate
 * @param string $strDateTo
 * @return array
 */
function date_getAge($strBirthDate, $strDateTo="")
{
	$strDateTo = (empty($strDateTo)) ? date("Y-m-d") : $strDateTo;
	$intMonths = date_getDifferenceInMonths($strBirthDate, $strDateTo);
	$intYears = floor($intMonths / 12);
	$intMonths -= ( 12 * $intYears);

	return array("years" => $intYears, "months" => $intMonths);
}

function bisiesto($anio_actual)
{
	$bisiesto = false;
	//probamos si el mes de febrero del año actual tiene 29 días
	if (checkdate(2, 29, $anio_actual)) {
		$bisiesto = true;
	}
	return $bisiesto;
}

function date_getAgeFull($strBirthDate, $strDateTo="")
{

	$strDateTo = (empty($strDateTo)) ? date("Y-m-d") : $strDateTo;

	// separamos en partes las fechas
	$array_nacimiento = explode("-", $strBirthDate);
	$array_actual = explode("-", $strDateTo);
	$anos = $array_actual[0] - $array_nacimiento[0]; // calculamos años
	$meses = $array_actual[1] - $array_nacimiento[1]; // calculamos meses
	$dias = $array_actual[2] - $array_nacimiento[2]; // calculamos días
	//ajuste de posible negativo en $días
	if ($dias < 0) {
		--$meses;

		//ahora hay que sumar a $dias los dias que tiene el mes anterior de la fecha actual
		$dias_mes_anterior = 0;
		switch ($array_actual[1]) {
			case 1: $dias_mes_anterior = 31;
				break;
			case 2: $dias_mes_anterior = 31;
				break;
			case 3:
				if (bisiesto($array_actual[0])) {
					$dias_mes_anterior = 29;
					break;
				}
				else {
					$dias_mes_anterior = 28;
					break;
				}
			case 4: $dias_mes_anterior = 31;
				break;
			case 5: $dias_mes_anterior = 30;
				break;
			case 6: $dias_mes_anterior = 31;
				break;
			case 7: $dias_mes_anterior = 30;
				break;
			case 8: $dias_mes_anterior = 31;
				break;
			case 9: $dias_mes_anterior = 31;
				break;
			case 10: $dias_mes_anterior = 30;
				break;
			case 11: $dias_mes_anterior = 31;
				break;
			case 12: $dias_mes_anterior = 30;
				break;
		}

		$dias = $dias + $dias_mes_anterior;
	}

	//ajuste de posible negativo en $meses
	if ($meses < 0) {
		--$anos;
		$meses = $meses + 12;
	}

	return array("years" => $anos, "months" => $meses, "days" => $dias);
//echo "<br>Tu edad es: $anos años con $meses meses y $dias días";
}

/**
 * Encodea con utf8 los strings dentro de un array, ojo que es recursivo.
 *
 * @param array $arrToEncode
 * @return boolean
 */
function utf8_encode_array(&$arrToEncode) {
	if(is_array($arrToEncode))
        reset($arrToEncode);

    if(is_array($arrToEncode) || is_object($arrToEncode)) {
        foreach ($arrToEncode as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = false; //Para liberar memoria
                if (is_object($arrToEncode))
                    utf8_encode_array($arrToEncode->$key);
                else
                    utf8_encode_array($arrToEncode[$key]);
            } else if (is_string($value)) {
                if (is_object($arrToEncode))
                    $arrToEncode->$key = utf8_encode($value);
                else
                    $arrToEncode[$key] = utf8_encode($value);
                $value = false; //Para liberar memoria
            } else {
                // No hago nada porque voy a devolver el mismo array para ahorrar memoria
            }
        }
    }
	if(is_array($arrToEncode))
	    reset($arrToEncode);
	return true;
}

/**
 * Para des encodear de utf8 los strings dentro de un array, ojo que es recursiva
 *
 * @param array $arrToDecode
 */
function utf8_decode_array(&$arrToDecode) {
	if(is_array($arrToDecode))
        reset($arrToDecode);

	if(is_array($arrToDecode) || is_object($arrToDecode)){
        foreach ($arrToDecode as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = false; //Para liberar memoria
                if(is_object($arrToDecode))
                    utf8_decode_array($arrToDecode->$key);
                else
                    utf8_decode_array($arrToDecode[$key]);
            }
            else if (is_string($value)) {
                if(is_object($arrToDecode))
                    $arrToDecode->$key = utf8_decode($value);
                else
                    $arrToDecode[$key] = utf8_decode($value);
                $value = false; //Para liberar memoria
            }
            else {
                // No hago nada porque voy a devolver el mismo array para ahorrar memoria
            }
        }
    }

    if(is_array($arrToDecode))
	    reset($arrToDecode);
	return true;
}

/**
 * Elimina los slashes de un user input segun la configuracion de magic_quotes_gpc.  DEBE ser utilizada en TODOS los inputs.
 *
 * @param string $strInput
 * @param boolean $boolUTF8Decode
 * @return string
 */
function user_input_delmagic($strInput, $boolUTF8Decode = false){
	//htmlspecialchars_decode
	//html_entity_decode
	$strInput = trim($strInput);
	if (get_magic_quotes_gpc()) {
		$strInput = stripslashes($strInput);
	}

	// 20090515 AG: Esto arruina los gets... pero sirve con los posts de ajax...
	if ($boolUTF8Decode && mb_detect_encoding($strInput) == "UTF-8") {
		$strInput = utf8_decode($strInput);
	}
	return $strInput;
}

function user_input_delmagic_reference(&$strInput, $intKey = 0)
{
	$strInput = trim($strInput);
	if (get_magic_quotes_gpc()) {
		$strInput = stripslashes($strInput);
	}
	/*
      if (mb_detect_encoding($strInput)=="UTF-8") {
      $strInput = utf8_decode($strInput);
      }
     */
	return $strInput;
}

/**
 * Imprime en pantalla un string pero con sus caracteres convertidos a HTML para que no haya problemas ni errores.
 *
 * @param string $strString
 * @param boolean $boolPrint
 * @return string
 */
function htmlSafePrint($strString, $boolPrint = true) {
	if ($boolPrint) {
		echo htmlspecialchars($strString, ENT_QUOTES, "ISO-8859-1");
	}
	else {
		return htmlspecialchars($strString, ENT_QUOTES, "ISO-8859-1");
	}
}

/**
 * Para hacer html_entity_decode pero ya con los parametros que nosotros usamos...
 *
 * @param mixed $strString
 */
function htmlCharsDecode($strString) {
	//htmlspecialchars_decode... ???
	//html_entity_decode... OK

	$strString = html_entity_decode($strString, ENT_QUOTES, "ISO-8859-1");
	return $strString;
}

/**
 * Convierte un array en variables para pasar en el get: key1=value1&key2=value2...&keyn=valuen
 *
 * @param array $arrVariables
 * @return string
 */
function arrayToGETString($arrVariables)
{
	$strGET = "";
	$arrItem = array();
	foreach ($arrVariables as $arrItem["key"] => $arrItem["value"]) {
		$strGET .= "&{$arrItem["key"]}={$arrItem["value"]}";
	}
	$strGET = substr($strGET, 1);

	return $strGET;
}

/**
 * Devuelve el minimo comun multiplo entre dos numeros enteros (least common multiple LCM)
 *
 * @param integer $intA
 * @param integer $intB
 * @return integer
 */
function minimoComunMultiplo($intA, $intB)
{
	return ($intA * $intB) / euclid($intA, $intB);
}

/**
 * Devuelve el maximo comun denominador (greatest common divisor o GCD) entre dos numeros segun el algoritmo de Euclides
 *
 * @param integer $intA
 * @param integer $intB
 * @return integer
 */
function euclid($intA, $intB)
{
	while ($intB > 0) {
		$intTMP = $intB;
		$intB = $intA;
		$intA = $intTMP;
		$intB = $intB % $intA;
	}
	return $intA;
}

/**
 * Quita las tildes y las dieresis de un string
 *
 * @param string $strString
 * @return string
 */
function striptildes($strString)
{
	$strString = str_replace(array("á","é","í","ó","ú",
		"ä","ë","ï","ö","ü",
		"à","è","ì","ò","ù",
		"Á","É","Í","Ó","Ú",
		"Ä","Ë","Ï","Ö","Ü",
		"À","È","Ì","Ò","Ù",
		"ñ","Ñ"),
		array("a","e","i","o","u",
			"a","e","i","o","u",
			"a","e","i","o","u",
			"A","E","I","O","U",
			"A","E","I","O","U",
			"A","E","I","O","U",
			"n","N"),
		$strString);

	return $strString;
}

/**
 * Pasa a upper case un string y se asegura que las letras con tildes y dieresis tambien las pase a upper case.
 * Resuelve un bug extraño que Alejandro Gudiel encontró en Mineduc: strtoupper no cambiaba las letras con tildes en linux, en windows si.
 *
 * @param string $strString
 * @param bool $boolProper Si se quiere solo la primera letra.
 * @return string
 */
function upper_tildes($strString, $boolProper = false)
{
	if ($boolProper) {
		$strString = ucwords($strString);
	}
	else {
		$strString = strtoupper($strString);

		$strString = str_replace("á", "Á", $strString);
		$strString = str_replace("é", "É", $strString);
		$strString = str_replace("í", "Í", $strString);
		$strString = str_replace("ó", "Ó", $strString);
		$strString = str_replace("ú", "Ú", $strString);
		$strString = str_replace("ä", "Ä", $strString);
		$strString = str_replace("ë", "Ë", $strString);
		$strString = str_replace("ï", "Ï", $strString);
		$strString = str_replace("ö", "Ö", $strString);
		$strString = str_replace("ü", "Ü", $strString);
		$strString = str_replace("ñ", "Ñ", $strString);
	}

	return $strString;
}

/**
 * Lo mismo que upper_tildes pero al revés...
 *
 * @param string $strString
 * @return string
 */
function lower_tildes($strString)
{
	$strString = strtolower($strString);

	$strString = str_replace("Á", "á", $strString);
	$strString = str_replace("É", "é", $strString);
	$strString = str_replace("Í", "í", $strString);
	$strString = str_replace("Ó", "ó", $strString);
	$strString = str_replace("Ú", "ú", $strString);
	$strString = str_replace("Ä", "ä", $strString);
	$strString = str_replace("Ë", "ë", $strString);
	$strString = str_replace("Ï", "ï", $strString);
	$strString = str_replace("Ö", "ö", $strString);
	$strString = str_replace("Ü", "ü", $strString);
	$strString = str_replace("Ñ", "ñ", $strString);

	return $strString;
}

/**
 * Devuelve un array con el uid, name y password sugeridos para un usuario a partir de su swusertype, nombres y apellidos.
 *
 * @param string $strApellidos
 * @param string $strNombres
 * @param string $strSWUserType
 * @return array
 */
function get_newUsrInfo(&$strApellidos, &$strNombres, $strSWUserType)
{
	$strApellidos = ucwords(strtolower($strApellidos));
	$strNombres = ucwords(strtolower($strNombres));

	// Obtengo el userid
	$strQuery = "SELECT MAX(uid) AS NextVal FROM wt_users WHERE swusertype = '{$strSWUserType}'";
	$qTMP = db_query($strQuery);
	$rTMP = db_fetch_array($qTMP);
	$intNextUserID = $rTMP["NextVal"];
	db_free_result($qTMP);

	if (strlen($intNextUserID) >= 6) {
		$strQuery = "SELECT MAX(uid) AS NextUser FROM wt_users WHERE swusertype='{$strSWUserType}' AND MID(uid,2,2) < '90'";
		$strTMP = "";
		$qTMP = db_query($strQuery);
		$rTMP = db_fetch_array($qTMP);
		$intNextUserID = $rTMP["NextUser"];
		db_free_result($qTMP);
	}
	$intNextUserID = intval($intNextUserID);
	$intNextUserID++;

	// Verifico que ese numero no exista...
	$strQuery = "SELECT COUNT(*) AS conteo FROM wt_users WHERE uid = {$intNextUserID}";
	$qTMP = db_query($strQuery);
	$rTMP = db_fetch_array($qTMP);
	if ($rTMP["conteo"] > 0) {
		$qTMP2 = db_query("SELECT (MAX(uid) + 1) AS NextUser FROM wt_users");
		$rTMP = db_fetch_array($qTMP2);
		$intNextUserID = $rTMP["NextUser"];
		db_free_result($qTMP2);
	}
	db_free_result($qTMP);

	// Obtengo username
	$arrNombres = explode(" ", $strNombres);
	$arrApellidos = explode(" ", $strApellidos);
	$strUserName = "";
	for ($i = 0; $i < count($arrNombres); $i++) {
		$strUserName .= substr($arrNombres[$i], 0, 1);
	}
	$strUserName.= $arrApellidos[0];
	for ($i = 1; $i < count($arrApellidos); $i++) {
		$strUserName .= substr($arrApellidos[$i], 0, 1);
	}
	$strUserName = str_replace(array("á", "é", "í", "ó", "ú",
		"ä", "ë", "ï", "ö", "ü",
		"à", "è", "ì", "ò", "ù",
		"ñ", "-", ",", " ", "'", "\"", "\\"),
		array("a", "e", "i", "o", "u",
			"a", "e", "i", "o", "u",
			"a", "e", "i", "o", "u",
			"n", "", "", "", "", "", ""), strtolower($strUserName));

	$strQuery = "SELECT COUNT(uid) AS conteo FROM wt_users WHERE name LIKE '{$strUserName}'";
	$qTMP = db_query($strQuery);
	$rTMP = db_fetch_array($qTMP);
	db_free_result($qTMP);
	if ($rTMP["conteo"] != 0) {
		$strOriginalUserName = $strUserName;
		$strUserName = $arrNombres[0];
		for ($i = 1; $i < count($arrNombres); $i++) {
			$strUserName .= substr($arrNombres[$i], 0, 1);
		}
		$strUserName.= $arrApellidos[0];
		for ($i = 1; $i < count($arrApellidos); $i++) {
			$strUserName .= substr($arrApellidos[$i], 0, 1);
		}
		$strUserName = str_replace(array("á", "é", "í", "ó", "ú", "ä", "ë", "ï", "ö", "ü", "ñ", "-", ",", " ", "'", "\"", "\\"),
			array("a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n", "", "", "", "", "", ""), strtolower($strUserName));

		$strQuery = "SELECT COUNT(uid) AS conteo FROM wt_users WHERE name LIKE '{$strUserName}'";
		$qTMP = db_query($strQuery);
		$rTMP = db_fetch_array($qTMP);
		db_free_result($qTMP);
		$intCounter = 1;
		while ($rTMP["conteo"] != 0) {
			$strUserName = $strOriginalUserName . $intCounter;
			$strQuery = "SELECT COUNT(uid) AS conteo FROM wt_users WHERE name LIKE '{$strUserName}'";
			$qTMP = db_query($strQuery);
			$rTMP = db_fetch_array($qTMP);
			db_free_result($qTMP);
			$intCounter++;
		}
	}

	// Obtengo Password
	$strPassWd = substr(md5(uniqid(rand())), 0, 8);
	return array("uid" => $intNextUserID, "name" => $strUserName, "password" => $strPassWd);
}

/**
 * Funcion para zipear archivos
 *
 * @param array $arrFileNames Puede ser array con los nombres de los archivos o un string para un solo nombre
 * @param string $strZipArchiveFileName El nombre del archivo zip que se genera
 * @param string $strPath Debera ser el path al/los archivos, si no se da un path, el default sera var/tmp/$strSessID/
 * @param string $strData Si se quiere enviar el contenido del archivo en si mismo, si son varios contenidos se debe enviar la por posiciones autonumericas cada conenido con subposiciones de [name] y [content]
 * @param boolean $boolDeleteFiles Si se desea eliminar los archivos que se pasaron por $arrFileNames y estan el el mismo directorio que $strPath
 */
function zipFilesAndDownload($arrFileNames, $strZipArchiveFileName , $strPath = "", $strData, $boolDeleteFiles = false){

	if(empty($strPath)){
		$strSessID = session_id();
		$strPath = "var/tmp/$strSessID/";
		if(!file_exists($strPath)) {
			mkdir($strPath , 0777 , true);
		}
	}

	$boolIsContent = (is_array($strData) && count($strData) > 0);

	$zip = new ZipArchive();

	$arrTMP = explode(".",$strZipArchiveFileName);

	if(!isset($arrTMP[1]) || (isset($arrTMP[1]) && (!$arrTMP[1] == "zip" || !$arrTMP[1] == "ZIP"))){
		$strZipArchiveFileName .= ".zip";
	}

	if ($zip->open($strPath.$strZipArchiveFileName, ZIPARCHIVE::CREATE )!==TRUE) {
		exit("cannot open <$strZipArchiveFileName>\n");
	}

	if(!$boolIsContent){
		if(is_string($arrFileNames)){
			$zip->addFile($strPath.$arrFileNames,$arrFileNames);
		}
        elseif(is_array($arrFileNames)){
			foreach($arrFileNames as $strFileName){
				$zip->addFile($strPath.$strFileName,$strFileName);
			}
		}
	}
	else{
		foreach($strData AS $arrValues){
			if(isset($arrValues["name"]) && isset($arrValues["content"])){
				$zip -> addFromString($arrValues["name"], $arrValues["content"]);
			}
		}
	}

	$zip->close();

	header("Content-type: application/zip");
	header("Content-Disposition: attachment; filename={$strZipArchiveFileName}");
	header("Pragma: no-cache");
	header("Expires: 0");

	if(readfile($strPath.$strZipArchiveFileName)){
		unlink($strPath.$strZipArchiveFileName);

		if(!$boolIsContent && $boolDeleteFiles){
			if(is_string($arrFileNames)){
				unlink($strPath.$arrFileNames);
			}
            elseif(is_array($arrFileNames)){
				foreach($arrFileNames as $files){
					unlink($strPath.$files);
				}
			}
		}

		rmdir($strPath);
	}

	exit;
}

/*function download_zip_attachments(){
    require_once '/library/zip.lib.php';
    require_once '/library/unzip.lib.php';
    $zip = new zipfile();
    $filename = 'test-image.png'; // path of the file.
    $fsize = @filesize($filename); // file size
    $fh = fopen($filename, 'rb', false);
    $data = fread($fh, $fsize);
    $zip->addFile($data,$filename);
    $zippedfile = $zip->file();
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=TestArchive.zip");
    header("Content-Type: application/zip");
    header("Content-length: " . strlen($zippedfile) . "\n\n");
    header("Content-Transfer-Encoding: binary");
    // output data to the browser
    echo $zippedfile;
}*/
/**
 * Funcion que Recibe un string de SQL con uno o varios campos y devuelve solamente el PRIMER REGISTRO segun:
 * FALSE si no hay registros como resultado del query
 * UNA VARIABLE con el valor si solamente hay un campo en el query
 * UN ARRAY "campo"=>valor si hay varios campos en el query
 *
 * @param string $strSQL
 * @param boolean $boolFalseOnEmpty Si el query devuelve solo un campo, que devuelva false si es empty o si parece empty (solo <br> por ejemplo).
 * @param boolean $boolForceArray Para indicar que siempre devuelva un array, aunque sea un solo campo
 * @param boolean $boolLogError Para indicar si se lleva un log del error o no -> se forwardea a db_query
 * @param boolean $objConnection Para indicar la conexion a la DB a utilizar -> se forwardea a db_query
 * @return mixed or boolean or array
 */
function sqlGetValueFromKey($strSQL, $boolFalseOnEmpty = false, $boolForceArray = false, $boolLogError = true, $objConnection = false) {
	$return = false;

	$qList = db_query($strSQL . " LIMIT 0,1 ", $boolLogError, $objConnection);
	if(db_num_rows($qList)){
		$listFields = db_get_fields($qList);
		if ($rList = db_fetch_array($qList)) {
			if (db_num_fields($qList) == 1 && !$boolForceArray) {
				$return = $rList[0];
				if ($boolFalseOnEmpty) {
					$strTMP = html_entity_decode($return);
					$strTMP = strip_tags($strTMP);

					$strTMP = str_replace(" ", "", $strTMP);
					$strTMP = trim($strTMP);
					$strTMP = str_replace(" ", "", $strTMP);
					$strTMP = trim($strTMP);

					if (empty($return) || empty($strTMP))
						$return = false;
				}
			}
			else {
				$return = array();
				foreach ($listFields as $field) {
					$return[$field['name']] = $rList[$field['name']];
				}
			}
		}
		db_free_result($qList);
	}
	return $return;
}

/**
 * @return array or string
 * @param string $strSQL
 * @param boolean $inArray
 * @param string $strKeyName
 * @param string $strValueName
 * @desc Funcion que Recibe un string de SQL con UN SOLO CAMPO y devuelve
 * FALSE si no hay registros como restultado del query
 * UNA VARIABLE con el valor si solamente hay un registro
 * UN ARRAY con los valores respectivos
 * $inArray para forzar un array cuando solo existe un registro
 * $strKeyName y $strValueName para definir un array[$strKeyName]= $strValueName;
 */
function sqlGetArray($strSQL, $inArray = false, $strKeyName="", $strValueName="")
{
	$qList = db_query($strSQL);
	$strFieldName = ($strValueName != "") ? $strValueName : 0;
	$strKeyName = ($strKeyName != "") ? $strKeyName : 0;
	if (db_num_rows($qList) == 0) {
		$return = false;
	}
    elseif (db_num_rows($qList) == 1) {
		$rList = db_fetch_array($qList);
		if ($inArray) {
			if ($strKeyName != "") {
				$return = array($rList[$strKeyName] => $rList[$strFieldName]);
			}
			else {
				$return = array($rList[$strFieldName]);
			}
		}
		else {
			$return = $rList[$strFieldName];
		}
	}
	else {
		$rList = db_fetch_array($qList);
		$return = array();
		do {
			if ($strKeyName != "") {
				$return[$rList[$strKeyName]] = $rList[$strFieldName];
			}
			else {
				$return[] = $rList[$strFieldName];
			}
		} while ($rList = db_fetch_array($qList));
	}
	db_free_result($qList);
	return $return;
}

/**
 * @return array or boolean
 * @param string $strSQL
 * @desc Funcion que Recibe un string de SQL con dos campos ID Y VALUE y devuelve
 * FALSE si no hay registros como restultado del query
 * UN ARRAY ID=>VALUE con los valores respectivos

 */
function sqlToArray($strSQL)
{
	$qList = db_query($strSQL);
	if (db_num_rows($qList) == 0) {
		db_free_result($qList);
		return false;
	}
	else {
		$rList = db_fetch_assoc($qList);
		$return = array();
		do {
			$return[$rList['ID']] = $rList['VALUE'];
		} while ($rList = db_fetch_assoc($qList));
	}
	db_free_result($qList);
	return $return;
}

/**
 * @return javascript text with <script> tags
 * @desc Funcion que inserta el codigo de javascript para la funcion format_monto(float) que devuelve el numero con foramto ##,###.##
 */
function insert_javascript_format_number()
{
	?>
    <script language="Javascript" type="text/javascript">
        function outInts(number, boolAddComma) {
            if (number.length <= 3)
                return (number == '' ? '0' : number);
            else {
                var mod = number.length%3;
                var output = (mod == 0 ? '' : (number.substring(0,mod)));
                for (var i=0 ; i < Math.floor(number.length/3) ; i++) {
                    if (((mod ==0) && (i ==0)) || !boolAddComma)
                        output+= number.substring(mod+3*i,mod+3*i+3);
                    else
                        output+= ',' + number.substring(mod+3*i,mod+3*i+3);
                }
                return (output);
            }
        }

        function outCents(amount,intDec) {
            if (!intDec)
                intDec = 2;
            var intTenExp =    Math.pow(10,intDec);
            amount = Math.round( ( (amount) - Math.floor(amount) ) *intTenExp);
            var strZeros = "";
            for (var i=1;i<=intDec;i++){
                if (amount < Math.pow(10,i-1))
                    strZeros+="0";
            }
            if (amount==0)
                return "."+strZeros;
            else
                return "."+strZeros+amount;
        }

        function format_monto(monto,intDec) {
            if (!intDec) intDec = 2;

            var comas  =/,/ig;
            var strTotal = JavaScriptTextTrim(monto) + '';

            strTotal = strTotal.replace(comas,'');
            var intTotal = strTotal * 1;
            var intTenExp =    Math.pow(10,intDec);

            intTotal = Math.round(intTotal * intTenExp)/intTenExp;
            var addMinus = false;
            if (intTotal < 0 ){
                intTotal = Math.abs(intTotal);
                addMinus = true;
            }
            return ((addMinus ? '-':'')+(outInts(Math.floor(intTotal) + '', true) + outCents(intTotal,intDec)));
        }

        function format_monto_sincomas(monto, intDec) {
            var comas  =/,/ig;
            if (!intDec) intDec = 2;
            var intTenExp =    Math.pow(10,intDec);

            monto = JavaScriptTextTrim(monto);
            monto = monto.replace(comas,'');
            monto = Math.round(monto * intTenExp)/intTenExp;

            var strTotal = monto + "";
            strTotal = strTotal.replace(comas,'');
            var intTotal = strTotal * 1;

            return outInts(Math.floor(intTotal) + '', false) + outCents(intTotal, intDec);
        }
    </script>
	<?php
}

function insert_javascript_validate_date($strFunctionName, $strDateObject, $insertScriptTags = true, $strSeparator = "-")
{
	if ($insertScriptTags)
		print "<script language='javascript' type='text/javascript'>\n";
	?>
    function <?php print $strFunctionName; ?>(){
    var myFecha;
    var re = new RegExp("<?php print $strSeparator ?>","i");
    var r_mes; var r_dia; var r_ano; var r_aux;
    if (<?php print $strDateObject; ?>.length == 0 ){
    return false;
    }
    else {
    myFecha = <?php print $strDateObject; ?>;
    if (myFecha.search(re) < 1 ){
    return false;
    }
    }
    r_dia = 1 * myFecha.substr(0,myFecha.search(re));
    r_aux = myFecha.substr(myFecha.search(re)+1,myFecha.length - myFecha.search(re)-1);
    if (r_aux.search(re) < 1 ){
    return false;
    }
    r_mes = 1 * r_aux.substr(0,r_aux.search(re));
    r_ano = 1 * r_aux.substr(r_aux.search(re)+1,r_aux.length - r_aux.search(re)-1);

    if (isNaN(r_dia) || isNaN(r_mes) || isNaN(r_ano)) {
    return false;
    }

    if (r_dia < 1 || r_dia > 31 || r_mes < 1 || r_mes > 12 || r_ano < 1900 ){
    return false;
    }
    if (r_dia < 10) r_dia = '0' + (r_dia * 1);
    if (r_mes < 10) r_mes = '0' + (r_mes * 1);
	<?php print $strDateObject; ?> = r_dia + '<?php print $strSeparator; ?>' + r_mes + '<?php print $strSeparator; ?>' + r_ano;
    return true;
    }
	<?php
	if ($insertScriptTags)
		print "</script>\n";
}

/**
 * @return string
 * @param integer $intNumber El número a convertir, puede tener 2 decimales también
 * @param string $strMonedaName Nombre de la moneda
 * @desc Devuelve un numero en letras (estandar para dinero)
 */
function PrecioEnLetras($intNumber, $strMonedaName = "")
{
	$arrParts = explode(".", $intNumber);
	$arrParts["int"] = $arrParts[0];
	if (!isset($arrParts[1])) {
		$arrParts["dec"] = "00";
	}
	else {
		$intDecLen = strlen($arrParts[1]);
		if ($intDecLen == 1) {
			$arrParts["dec"] = $arrParts[1] . "0";
		}
		else {
			$arrParts["dec"] = $arrParts[1];
		}
	}
	unset($arrParts[0]);
	unset($arrParts[1]);

	$strReturn = NumeroEnLetras($intNumber);
	if ($strMonedaName)
		$strReturn.= " " . $strMonedaName . " con {$arrParts["dec"]}/100";
	else
		$strReturn.=" con {$arrParts["dec"]}/100";

	return trim($strReturn);
}

/**
 * @return string
 * @param integer $intNumber El número a convertir, puede tener 2 decimales también
 * @param boolean $arrStrings (NO USAR ESTE PARAMETRO, ES PARA USO DE LA RECURSION EN LA FUNCION)
 * @desc Devuelve un numero en letras (estandar para dinero)
 */
function NumeroEnLetras($intNumber, $arrStrings = false)
{
	if (!$arrStrings) {
		$intNumber = round($intNumber, 2) . "";
		$arrStrings = array();
		$arrStrings[0][1] = "uno";
		$arrStrings[0][2] = "dos ";
		$arrStrings[0][3] = "tres ";
		$arrStrings[0][4] = "cuatro ";
		$arrStrings[0][5] = "cinco ";
		$arrStrings[0][6] = "seis ";
		$arrStrings[0][7] = "siete ";
		$arrStrings[0][8] = "ocho ";
		$arrStrings[0][9] = "nueve ";

		$arrStrings[1][1] = "dieci";
		$arrStrings[1][2] = "veinti";
		$arrStrings[1][3] = "treinta y ";
		$arrStrings[1][4] = "cuarenta y ";
		$arrStrings[1][5] = "cincuenta y ";
		$arrStrings[1][6] = "sesenta y ";
		$arrStrings[1][7] = "setenta y ";
		$arrStrings[1][8] = "ochenta y ";
		$arrStrings[1][9] = "noventa y ";

		$arrStrings[2][1] = "ciento ";
		$arrStrings[2][2] = "doscientos ";
		$arrStrings[2][3] = "trescientos ";
		$arrStrings[2][4] = "cuatrocientos ";
		$arrStrings[2][5] = "quinientos ";
		$arrStrings[2][6] = "seiscientos ";
		$arrStrings[2][7] = "setecientos ";
		$arrStrings[2][8] = "ochocientos ";
		$arrStrings[2][9] = "novecientos ";

		$arrStrings[3][1] = "mil ";
	}

	if ($intNumber == 0)
		return "cero";

	$arrParts = explode(".", $intNumber);
	$arrParts["int"] = $arrParts[0];
	if (!isset($arrParts[1])) {
		$arrParts["dec"] = "00";
	}
	else {
		$intDecLen = strlen($arrParts[1]);
		if ($intDecLen == 1) {
			$arrParts["dec"] = $arrParts[1] . "0";
		}
		else {
			$arrParts["dec"] = $arrParts[1];
		}
	}
	unset($arrParts[0]);
	unset($arrParts[1]);

	$strTMP = $arrParts["int"];
	$arrParts["int"] = array();
	for ($i = strlen($strTMP); $i > 0; $i--) {
		$arrParts["int"][$i - 1] = substr($strTMP, strlen($strTMP) - $i, 1);
	}
	ksort($arrParts["int"]);

	$strReturn = "";
	foreach ($arrParts["int"] AS $arrThis["key"] => $arrThis["value"]) {
		if ($arrThis["key"] >= 3) break;

		$strTMP = "";
		if ($arrThis["key"] == 1 && $arrThis["value"] == 1 && $arrParts["int"][0] < 6) {
			switch ($arrParts["int"][0]) {
				case 0:
					$strReturn = "diez";
					break;
				case 1:
					$strReturn = "once";
					break;
				case 2:
					$strReturn = "doce";
					break;
				case 3:
					$strReturn = "trece";
					break;
				case 4:
					$strReturn = "catorce";
					break;
				case 5:
					$strReturn = "quince";
					break;
			}
		}
        elseif ($arrThis["key"] == 1 && $arrThis["value"] == 2 && $arrParts["int"][0] == 0) {
			$strReturn = "veinte";
		}
        elseif ($arrThis["key"] == 2 && $arrThis["value"] == 1 && $arrParts["int"][1] == 0 && $arrParts["int"][0] == 0) {
			$strReturn = "cien";
		}
		else {
			$strTMP = (isset($arrStrings[$arrThis["key"]][$arrThis["value"]])) ? $arrStrings[$arrThis["key"]][$arrThis["value"]] : "";
		}

		if (empty($strReturn)) {
			$strTMP = str_replace(" y ", "", $strTMP);
		}
		$strReturn = $strTMP . $strReturn;
	}

	$strMiles = "";
	$strMillones = "";
	for ($i = 3; $i < count($arrParts["int"]); $i++) {
		if ($i < 6) {
			$strMiles = $arrParts["int"][$i] . $strMiles;
		}
		else {
			$strMillones = $arrParts["int"][$i] . $strMillones;
		}
	}

	if (!empty($strMiles)) {
		if ($strMiles == 1) {
			$strReturn = "un mil " . $strReturn;
		}
		else if ($strMiles > 0) {
			$strReturn = NumeroEnLetras($strMiles, $arrStrings) . " mil " . $strReturn;
		}
	}

	if (!empty($strMillones)) {
		if ($strMillones == 1) {
			$strReturn = "un millón " . $strReturn;
		}
		else if ($strMillones > 0) {
			$strReturn = NumeroEnLetras($strMillones, $arrStrings) . " millones " . $strReturn;
		}
	}

	return trim($strReturn);
}

/**
 * @by HmlDev NelsonRodriguez
 * @return string
 * @param $intNumber Es el número a convertir
 * @param $strLenguage Es el idioma en el que se desea devolver la cadena, actualmente puede ser en español o inglés
 * @param $strCas Devuelve el número en mayúsculas o minúsculas (mayuscula = "upper") el default es "lower"
 */
function NumberByNumberToString($intNumber, $strLenguage = "spanish", $strCase = "lower")
{
    $strNumber = (string)$intNumber;
    $strNumber = str_replace("-","",$strNumber);
    $strNumber = str_replace("_","",$strNumber);
    $strNumber = str_replace(" ","",$strNumber);
    $strNumber = str_replace("/","",$strNumber);

    $arrNumbers = str_split($strNumber);
    $arrStrings = array();
    if( $strLenguage === "spanish" ){
        $arrStrings[0] = "cero ";
        $arrStrings[1] = "uno ";
        $arrStrings[2] = "dos ";
        $arrStrings[3] = "tres ";
        $arrStrings[4] = "cuatro ";
        $arrStrings[5] = "cinco ";
        $arrStrings[6] = "seis ";
        $arrStrings[7] = "siete ";
        $arrStrings[8] = "ocho ";
        $arrStrings[9] = "nueve ";
    }
    elseif ( $strLenguage === "english" ){
        $arrStrings[0] = "zero ";
        $arrStrings[1] = "one ";
        $arrStrings[2] = "two ";
        $arrStrings[3] = "three ";
        $arrStrings[4] = "four ";
        $arrStrings[5] = "five ";
        $arrStrings[6] = "six ";
        $arrStrings[7] = "seven ";
        $arrStrings[8] = "eight ";
        $arrStrings[9] = "nine ";
    }
    $strReturn = "";
    foreach ($arrNumbers AS $key => $val) {
        $strNumber = $arrStrings[$val];
        $strReturn .= $strNumber;
    }
    if($strCase === "upper"){
        $strReturn = strtoupper($strReturn);
    }
    return $strReturn;
}

/**
 * @by HmlDev NelsonRodriguez
 * @return string
 * @param $strCase Parametro para devolver el string en mayuscula o minuscula (recibe upper y lower respectivamente)
*/
function DateInStringSpanish($strCase = "lower")
{
    setlocale(LC_ALL,"es_ES");
    $date = date('Y-m-d');
    $strDate = show_date($date,false,true,true,true,true);
    $arrDate = explode(" ",$strDate);

    $strResult = "";
    foreach($arrDate AS $key => $val){
        if(is_numeric($val)){
            $strResult .= NumeroEnLetras($val) . " ";
        }
        else{
            $strResult .= $val . " ";
        }
    }

    if($strCase === "upper"){
        $strResult = strtoupper($strResult);
    }

    return $strResult;
}

/**
 * @return HTML CODE
 * @param string $strSQL
 * @param string $strTitle
 * @param array $arrAlign
 * @param array $arrNumberFormat
 * @param boolean $GroupOLF
 * @param boolean $GranTotal
 * @desc Funcion que despliega un query de SQL bajo los sigientes terminos.
nombre de campos que inician con G_ se agrupan y deben ir al inicio del query
nombre de campos que inician con T_ se les calcula total por cada grupo
$arrAlign como simpre key = nombre del campo value = alignValue
$arrNumberFormat definir key para los campos que se desean imprimir con formato de moneda
$GroupOLF indica si se desea que el ultimo campo de agrupación se dibuje como grouprow o no
$GranTotal indica si se desea un gran total por grupos al final de la tabla
 */
function Display_Group_Query($strSQL, $strTitle='', $arrAlign = array(), $arrNumberFormat = array("T_Nota"), $GroupOLF = true, $GranTotal = false)
{
	//$GroupOLF = Group On Last Field (hace que no se coloque como encabezado de agrupar el ultimo campo para grupar
	$qReport = db_query($strSQL) or die(db_error());
	//PRIMERO OBTENGO LA INFO DE LOS FIELDS
	$fields = db_get_fields($qReport);
	$arrFields = array();
	$arrIncludeTotals = array();
	$arrTotales = array();
	$intGroupFields = 0;
	foreach ($fields as $fieldName => $data) {
		$strFieldName = (strstr($fieldName, '_')) ? substr($fieldName, 2) : $fieldName;
		$arrFields[$fieldName] = array();
		if (substr($fieldName, 0, 2) == 'G_') {
			$arrFields[$fieldName]['G'] = true;
			$arrFields[$fieldName]['Title'] = $strFieldName;
			$arrFields[$fieldName]['LastValue'] = '-~-';
			$arrFields[$fieldName]['Totales'] = array();
			$intGroupFields++;
		}
        elseif (substr($fieldName, 0, 2) == 'T_') {
			$arrFields[$fieldName]['T'] = true;
			$arrFields[$fieldName]['Title'] = $strFieldName;
			$arrFields[$fieldName]['Grand_Total'] = 0;
			$arrIncludeTotals[$fieldName] = 0;
			if ($GranTotal)
				$arrTotales[$fieldName] = 0;
		}
		else {
			$arrFields[$fieldName]['Title'] = $strFieldName;
		}
	}
	foreach ($arrFields as $key => $data) {
		if (isset($data['Totales']))
			$arrFields[$key]['Totales'] = $arrIncludeTotals;
	}
	if (!$GroupOLF) {
		//FALTA HACER QUE NO AGRUPE EN EL ULTIMO CAMPO
		//FALTA AGREGAR GRAND TOTALES
		$field = end($arrFields);
		do {
			$key = key($arrFields);
			if (isset($arrFields[$key]['G'])) {
				reset($arrFields);
				unset($arrFields[$key]['G']);
				unset($arrFields[$key]['LastValue']);
				unset($arrFields[$key]['Totales']);
				$intGroupFields--;
				break;
			}
		} while ($field = prev($arrFields));
	}
	$totalCols = 0;
	foreach ($arrFields as $name => $data) {
		$totalCols++;
	}

	//************************************
	?>
    <table width="100%" border="0" cellpadding="2" cellspacing="0">
        <thead>
		<?php
		if (!empty($strTitle)) {
			print "<tr><td class='row0' align='center' colspan='{$totalCols}' style='border-bottom:none' >{$strTitle}</td></tr>";
		}
		?>
        <!-- PRINT DE TITLES -->
        <tr>
			<?php
			foreach ($arrFields as $name => $data) {
				print "<td class='row0' align='center'>" . ((isset($data['Title'])) ? $data['Title'] : $name) . "</td>";
			}
			?>
        </tr>
        </thead>
        <tbody>
		<?php
		if ($rReport = db_fetch_array($qReport)) {
			$row = 1;
			$firstGroupRow = true;
			do {
				//COLOCO LOS TOTALES POR GRUPO SI HAY TOTALES PARA CALCULAR
				$colPos = $totalCols - sizeof($arrIncludeTotals) - 1;
				if (!$firstGroupRow && sizeof($arrIncludeTotals)) {
					$field = end($arrFields);
					do {
						$key = key($arrFields);
						$TotalColSpan = $totalCols - $colPos - sizeof($arrIncludeTotals);

						if (isset($field['G']) && $field['LastValue'] != $rReport[$key]) {
							print "<tr>";
							if ($colPos)
								print "<td colspan='{$colPos}' class='rowgroup'>&nbsp;</td>";
							print "<td colspan='{$TotalColSpan}' align='rigth' class='rowgroup'>Total</td>";
							foreach ($arrIncludeTotals as $name => $data) {
								$strPrint = (isset($arrNumberFormat[$name])) ? number_format($arrFields[$key]['Totales'][$name], 2, '.', ',') : number_format($arrFields[$key]['Totales'][$name],
									2, '.', ',');
								print "<td colspan='1' align='right' class='rowgroup'>{$strPrint}</td>";
							}
							print "</tr>";
							$colPos--;
						}
					} while ($field = prev($arrFields));
				}
				//FIN DE DIBUJAR TOTALES
				//ENCABEZADOS DE GRUPOS
				$colPos = 0;
				$blankField = false;
				foreach ($arrFields as $name => $data) {
					//SI UN GROUP FIELD CAMBIA HAY QUE RESETEAR SUS HIJOS PARA QUE DESPLIEGUE EL HEADER
					if ($blankField && isset($arrFields[$name]['LastValue']))
						$arrFields[$name]['LastValue'] = '~-~';
					//*****************************************************************************
					if (isset($data['G']) && ($data['LastValue'] != $rReport[$name] || $blankField)) {
						print "<tr>";
						if ($colPos > 0)
							print "<td colspan='{$colPos}' class='rowgroup'>&nbsp;</td>";
						$tmpSpan = $totalCols - $colPos;
						print "<td colspan='{$tmpSpan}' class='rowgroup'>{$rReport[$name]}&nbsp;</td>";
						print "</tr>";
						$arrFields[$name]['LastValue'] = $rReport[$name];
						foreach ($arrIncludeTotals as $key => $data2) {
							$arrFields[$name]['Totales'][$key] = 0;
						}
						$blankField = true;
					}
					$colPos++;
				}
				$firstGroupRow = false;
				//**********************
				$colPos = 0;
				print "<tr>";
				$firstNoGroupField = false;
				$putColSpan = true;
				foreach ($arrFields as $name => $data) {
					if (isset($data['G'])) {
						$colPos++;
						foreach ($arrIncludeTotals as $key => $data2) {
							$arrFields[$name]['Totales'][$key]+= $rReport[$key];
						}
					}
					else {
						$firstNoGroupField = true;
					}
					if ($firstNoGroupField) {
						if ($putColSpan && $intGroupFields) {
							print "<td colspan='{$colPos}' class=row{$row}>&nbsp;</td>";
							$putColSpan = false;
						}
						$strAlign = (isset($arrAlign[$name])) ? " align='{$arrAlign[$name]}' " : "";
						$strPrint = (isset($arrNumberFormat[$name])) ? number_format($rReport[$name], 2, '.', ',') : $rReport[$name];
						print "<td colspan='1' {$strAlign} class=row{$row}>{$strPrint}</td>";
					}
				}
				print "</tr>";
				if ($GranTotal) {
					foreach ($arrTotales as $key => $vlaue) {
						$arrTotales[$key]+=$rReport[$key];
					}
				}
				$row = ($row == 1) ? 2 : 1;
			} while ($rReport = db_fetch_array($qReport));

			//COLOCO LOS TOTALES POR GRUPO SI HAY TOTALES PARA CALCULAR
			$colPos = $totalCols - sizeof($arrIncludeTotals) - 1;
			if (!$firstGroupRow && sizeof($arrIncludeTotals)) {
				$field = end($arrFields);
				do {
					$key = key($arrFields);
					$TotalColSpan = $totalCols - $colPos - sizeof($arrIncludeTotals);
					if (isset($field['G']) && $field['LastValue'] != $rReport[$key]) {
						print "<tr>";
						if ($colPos)
							print "<td colspan='{$colPos}' class='rowgroup'>&nbsp;</td>";
						print "<td colspan='{$TotalColSpan}' align='rigth' class='rowgroup'>Total</td>";
						foreach ($arrIncludeTotals as $name => $data) {
							$strPrint = (isset($arrNumberFormat[$name])) ? number_format($arrFields[$key]['Totales'][$name], 2, '.', ',') : number_format($arrFields[$key]['Totales'][$name],
								2, '.', ',');
							print "<td colspan='1' align='right' class='rowgroup'>{$strPrint}</td>";
						}
						print "</tr>";
						$colPos--;
					}
				} while ($field = prev($arrFields));
			}
			//FIN DE DIBUJAR TOTALES
			//DIBUJO EL GRAN TOTAL
			if ($GranTotal) {
				$TotalColSpan = $totalCols - sizeof($arrTotales);
				print "<tr>";
				print "<td class='rowgroup' align='center' colspan='$TotalColSpan'>GRAN TOTAL</td>";
				foreach ($arrTotales as $data) {
					print "<td class='rowgroup' align='right'>" . number_format($data, 2, '.', ',') . "</td>";
				}
				print "</tr>";
			}
		}
		db_free_result($qReport);
		?>
        </tbody>
    </table>
	<?php
}

/**
 * @param string $strQuery
 * @param bool $boolShowQueryString
 * @param mixed $arrFilter
 * @param bool $boolExplain
 * @param mixed $objConnection
 */
function debugQuery($strQuery, $boolShowQueryString = true, $arrFilter = false, $boolExplain = false, $objConnection = false)
{
	$boolFilter = is_array($arrFilter);
	if ($boolExplain)
		$strQuery = "EXPLAIN\n" . $strQuery;
	$qTMP = db_query($strQuery, false, $objConnection);
	?>
    <div  style="position:relative; z-index:20; background-color:white; color:black;">
		<?php
		if ($boolShowQueryString)
			print_r("<hr>" . nl2br($strQuery) . "<br><br>");
		?>
        <table border="1" cellspacing="0" cellpadding="2" align="center">
			<?php
			$boolFirstRow = true;
			$listFields = db_get_fields($qTMP);
			if ($rTMP = db_fetch_array($qTMP)) {
				do {
					if ($boolFirstRow) {
						$strRow = "<tr>";
						reset($listFields);
						foreach ($listFields as $key => $entry) {
							$strRow.="<th>{$key}</th>";
						}
						$strRow.= "</tr>\n";
						echo $strRow;
						$boolFirstRow = false;
						reset($rTMP);
					}
					if ($boolFilter) {
						$boolOK = true;
						$arrFItem = array();
						foreach ($arrFilter as $arrFItem["key"] => $arrFItem["value"]) {
							if ($rTMP[$arrFItem["key"]] != $arrFItem["value"])
								$boolOK = false;
						}
						reset($arrFilter);
						if (!$boolOK)
							continue;
					}
					$strRow = "<tr>";
					reset($listFields);
					foreach ($listFields as $key => $entry) {
						$strValue = $rTMP[$key];
						if (strlen($rTMP[$key]) == 0) {
							$strValue = "&nbsp;";
						}
						$strRow.="<td>{$strValue}</td>";
					}
					$strRow.= "</tr>\n";
					echo $strRow;
				} while ($rTMP = db_fetch_array($qTMP));
			}
			?>
        </table><br><?php print db_num_rows($qTMP); ?> rows<hr>
    </div>
	<?php
	db_free_result($qTMP);
}

/**
 * Función que me ingresa el Master del LOG de cualquier módulo.
 *
 * @param integer $user Usuario que registro este cambio
 * @param string $usertype UserType del usuaroi en el momento del Log
 * @param string $descripcion Descripcion del log (Encabezado del reporte)
 * @param string $strModulo Modulo de donde viene el Reporte
 * @param string $strReportID Identificador personal de cada módulo que me indica que reporte stoy viendo
 * @param string $strReportTitle Nombre del reporte a mostrar (Es lo que está visible al público)
 * @param string $strAccess string de acceso a dicho reporte como extra_access (check_user_class)
 * @param string $strKey codigo o key de un dato especifico
 * @return integer Regresa el ID de la ultima inserción del log
 */
function LogInsert($user, $usertype = "", $descripcion, $strModulo = "", $strReportID = "", $strReportTitle = "", $strAccess = "", $strKey = "")
{
	global $cfg;

	$descripcion = db_escape($descripcion);
	$strModulo = db_escape($strModulo);
	$strReportID = db_escape($strReportID);
	$strReportTitle = db_escape($strReportTitle);
	$strAccess = db_escape($strAccess);
	$strKey = db_escape($strKey);

	//TENIA ESTE FILTRO
	//&& !empty($usertype)
	$intLogID = 0;
	if (isset($cfg["core"]["logreport"]) && $cfg["core"]["logreport"]) {
		$sqlInsert = "INSERT INTO wt_log(uid, swusertype, date, descripcion, modulo, nombre, short_desc, access_rpt,cod)
                      VALUES ('{$user}', '{$usertype}', NOW(), '{$descripcion}', '{$strModulo}', '{$strReportID}', '{$strReportTitle}', '{$strAccess}', '{$strKey}');";
		db_query($sqlInsert, true, false, false);
		$intLogID = db_insert_id();
	}

	return $intLogID;
}

// 20090310 AG: Esta funcion es EXACTAMENTE IGUAL a la de arriba, solo que esta queda solo para logs PRIVADOS DE HOMELAND.
function LogInsertHMLPrivate($user, $usertype = "", $descripcion, $strModulo = "", $strReportID = "", $strReportTitle = "", $strAccess = "")
{
	global $cfg;

	$strModulo = db_escape($strModulo);
	$strReportID = db_escape($strReportID);
	$strReportTitle = db_escape($strReportTitle);
	$strAccess = db_escape($strAccess);

	//TENIA ESTE FILTRO
	//&& !empty($usertype)
	$intLogID = 0;
	if (isset($cfg["core"]["logreport"]) && $cfg["core"]["logreport"]) {
		$sqlInsert = "INSERT INTO wt_log(uid, swusertype, date, descripcion, modulo, nombre, short_desc, access_rpt)
                      VALUES ('{$user}', '{$usertype}', NOW(), '{$descripcion}', '{$strModulo}', '{$strReportID}', '{$strReportTitle}', '{$strAccess}');";
		db_query($sqlInsert, true, false, false);
		$intLogID = db_insert_id();
	}

	return $intLogID;
}

// 20090310 AG: Esta funcion es EXACTAMENTE IGUAL a la de arriba, solo es para diferencias y poder llevar un control de en donde ya se actualizo el uso del detail y en donde no...
function LogInsertWithDetail($user, $usertype = "", $descripcion, $strModulo = "", $strReportID = "", $strReportTitle = "", $strAccess = "", $objConection = false)
{
	global $cfg;

	$strModulo = db_escape($strModulo);
	$strReportID = db_escape($strReportID);
	$strReportTitle = db_escape($strReportTitle);
	$strAccess = db_escape($strAccess);

	//TENIA ESTE FILTRO
	//&& !empty($usertype)
	$intLogID = 0;
	if (isset($cfg["core"]["logreport"]) && $cfg["core"]["logreport"]) {
		$sqlInsert = "INSERT INTO wt_log(uid, swusertype, date, descripcion, modulo, nombre, short_desc, access_rpt)
                      VALUES ('{$user}', '{$usertype}', NOW(), '{$descripcion}', '{$strModulo}', '{$strReportID}', '{$strReportTitle}', '{$strAccess}');";
		db_query($sqlInsert, true, $objConection, false);
		$intLogID = db_insert_id($objConection);
	}

	return $intLogID;
}

/**
 * Detalle del LOG ingresado, en el reporte muestra una fila por cada Detail Ingresado
 *
 * @param integer $intLogID ID del log master
 * @param string $strShortName nombre que se muestra al usuario por cada fila agregada al detail
 * @param string $strTabla
 * @param string $strCampo Nombre del campo a modificar.
 * @param mixed $strCValue El texto que voy a mostrar como valor que cambié.
 * @param mixed $strKey El key de la tabla que modifiqué.  Sirve para busquedas. Si son varios van separados por comas.
 * @param mixed $strKValue El valor del key.
 * @param boolean $objConection El objeto de coneccion(se puso para poder usar en cloud).
 * @return integer
 */
function LogDetailInsert($intLogID, $strShortName = "", $strTabla = "", $strCampo = "", $strCValue = "", $strKey = "", $strKValue = "", $objConection = false)
{

	$strShortName = db_escape($strShortName);
	$strTabla = db_escape($strTabla);
	$strCampo = db_escape($strCampo);
	$strCValue = db_escape($strCValue);
	$strKey = db_escape($strKey);
	$strKValue = db_escape($strKValue);

	$strQuery = "INSERT INTO wt_log_detail
                 (logID, short_name, tabla_nombre, tabla_campo, campo_value, tabla_key, key_value)
                 VALUES('{$intLogID}', '{$strShortName}', '{$strTabla}', '{$strCampo}', '{$strCValue}', '{$strKey}', '{$strKValue}')";

	db_query($strQuery,true,$objConection);

	$intDetailID = db_insert_id($objConection);

	return $intDetailID;
}

// Variable NECESARIA para el correcto funcionamiento de esta funcion. Queda GLOBAL.
$intArrayBrowserIndex = 0;

function browseArray($arrThisArrayParam, $boolFirstRun = true, $boolShowType = true, $boolShowKeys = true, $boolClosePrev = false, $boolSort = false) {
	global $intArrayBrowserIndex;

	$arrThisArray = $arrThisArrayParam;

	if (!$intArrayBrowserIndex) {
		?>
        <script language="JavaScript" type="text/javascript">
            var PrevTable = false;
            var PrevName = false;

            function ExpandTable(strTableName, objThisClick) {
                // Localizo el layer
                var objLayer = getDocumentLayer(strTableName);

                if (objThisClick.innerHTML == "+") {
                    objThisClick.innerHTML = "-";
                    objLayer.style.display = "";

					<?php
					if ($boolClosePrev) {
					?>
                    if (PrevTable) {
                        PrevName.innerHTML = "+";
                        PrevTable.style.display = "none";
                    }
					<?php
					}
					?>
                    PrevTable = objLayer;
                    PrevName = objThisClick;
                }
                else {
                    objThisClick.innerHTML = "+";
                    objLayer.style.display = "none";

                    PrevTable = false;
                    PrevName = false;
                }
            }
        </script>
		<?php
	}

	$strMainType = gettype($arrThisArray);
	$boolIsArray = is_array($arrThisArray);
	$boolIsObject = is_object($arrThisArray);

	if ($boolIsArray || $boolIsObject) {
		if ($boolIsArray && $boolSort) {
			ksort($arrThisArray);
		}

		reset($arrThisArray);
		if (!$boolFirstRun) {
			?>
            <div id="tabla<?php echo $intArrayBrowserIndex; ?>" style="display:none">
			<?php
		}
		?>
        <table cellpadding="2" cellspacing="0" border="0">
			<?php
			$arrTMP = array();
			foreach ($arrThisArray as $arrTMP["key"] => $arrTMP["value"]) {
				$intArrayBrowserIndex++;

				$boolIsArray2 = is_array($arrTMP["value"]);
				$boolIsObject2 = is_object($arrTMP["value"]);
				?>
                <tr>
                    <td valign="top" align="left" nowrap>
						<?php
						if ($boolIsArray2 || $boolIsObject2) {
							?>
                            <table width="100%" border="0" cellspacing="0" cellpadding="2">
                                <tr>
									<?php
									if ($boolShowKeys) {
										?>
                                        <td width="90%" nowrap>
                                            <b><?php print $arrTMP["key"]; ?></b>
                                        </td>
										<?php
									}
									?>
                                    <td width="10%" nowrap>
										<?php
										if ($boolIsArray2 && count($arrTMP["value"])) {
											?>
                                            (<span onclick="ExpandTable('tabla<?php print $intArrayBrowserIndex; ?>', this);"
                                                   style="cursor:pointer;">+</span>)
											<?php
										}
										else {
											?>
                                            (N.E.)
											<?php
										}
										?>
                                    </td>
                                </tr>
                            </table>
							<?php
						}
						else {
							if ($boolShowKeys) {
								print "<b>" . $arrTMP["key"] . "</b>";
							}
						}
						?>
                    </td>
                    <td>
						<?php
						browseArray($arrTMP["value"], false, $boolShowType, $boolShowKeys, false, $boolSort);
						?>
                    </td>
                </tr>
				<?php
			}
			?>
        </table>
		<?php
		if (!$boolFirstRun) {
			?>
            </div>&nbsp;
			<?php
		}
	}
	else {
		print ((is_bool($arrThisArray)) ? (($arrThisArray) ? "true" : "false") : $arrThisArray);
		if ($boolShowType)
			print "<b><i>({$strMainType})</i></b>";
	}
}

function encrypt($strString, $charKey)
{
	$strString = trim($strString);

	$strNewCode = "";
	for ($i = 0; $i < strlen($strString); $i++) {
		$strCurrChar = substr($strString, $i, 1);
		$intTMP = ord($strCurrChar) ^ ord($charKey);
		if ($intTMP >= 0 && $intTMP <= 31) {
			$intTMP+=190;
		}
		else if ($intTMP >= 64 && $intTMP <= 95) {
			$intTMP+=160;
		}
		else {
			die("ERROR: {$strString}: {$strCurrChar} xor {$charKey} = char({$intTMP})");
		}

		if ($strNewCode == "") {
			$strNewCode = chr($intTMP);
		}
		else {
			$strNewCode .= chr($intTMP);
		}
		$charKey = $strCurrChar;
	}

	$strNewCode = addslashes($strNewCode);
	return $strNewCode;
}

function decrypt($strString, $charKey)
{
	$strString = stripslashes($strString);
	$strNewCode = "";
	for ($i = 0; $i < strlen($strString); $i++) {
		$strCurrChar = substr($strString, $i, 1);
		$intTMP = ord($strCurrChar);
		if ($intTMP >= 190 && $intTMP <= 221) {
			$intTMP-=190;
		}
		else if ($intTMP >= 224 && $intTMP <= 255) {
			$intTMP-=160;
		}
		else {
			if (check_user_class("admin")) {
				return $strString;
			}
			else {
				die("ERROR: {$strString}: {$strCurrChar} = char({$intTMP})");
			}
		}

		$charKey = chr($intTMP ^ ord($charKey));
		if ($strNewCode == "") {
			$strNewCode = $charKey;
		}
		else {
			$strNewCode .= $charKey;
		}
	}

	return $strNewCode;
}

function sw_array_slice($arrArray, $intOffset, $intSize) {
	$arrTMP = array();
	$intThisStart = 0;
	$intCounter = 1;
	$arrItem = array();
	foreach ($arrArray as $arrItem["key"] => $arrItem["value"]) {
		if ($intCounter > $intSize) break;

		if ($intThisStart >= $intOffset) {
			$arrTMP[$arrItem["key"]] = $arrItem["value"];
			$intCounter++;
		}
		$intThisStart++;
	}
	return $arrTMP;
}

function GetMonthInformation($intMonth, $intYear)
{
	settype($intMonth, "integer");
	settype($intYear, "integer");

	$objTMP = @mktime(0, 0, 0, $intMonth, 1, $intYear);
	if ($objTMP >= 0) {
		$arrFirstDay = getdate($objTMP);
	}
	else {
		$arrFirstDay = array();
		$arrFirstDay["wday"] = 0;
	}

	$objTMP = @mktime(0, 0, 0, $intMonth + 1, 0, $intYear);
	if ($objTMP >= 0) {
		$arrLastDay = getdate($objTMP);
	}
	else {
		$arrLastDay = array();
		$arrLastDay["wday"] = 0;
		switch ($intMonth) {
			case 1:
				$arrLastDay["mday"] = 31;
				break;
			case 2:
				$arrLastDay["mday"] = 29;
				break;
			case 3:
				$arrLastDay["mday"] = 31;
				break;
			case 4:
				$arrLastDay["mday"] = 30;
				break;
			case 5:
				$arrLastDay["mday"] = 31;
				break;
			case 6:
				$arrLastDay["mday"] = 30;
				break;
			case 7:
				$arrLastDay["mday"] = 31;
				break;
			case 8:
				$arrLastDay["mday"] = 31;
				break;
			case 9:
				$arrLastDay["mday"] = 30;
				break;
			case 10:
				$arrLastDay["mday"] = 31;
				break;
			case 11:
				$arrLastDay["mday"] = 30;
				break;
			case 12:
				$arrLastDay["mday"] = 31;
				break;
		}
	}

	$arrParams = array();
	$arrParams["intFirstDayWD"] = $arrFirstDay["wday"] + 1;
	$arrParams["intLastDayWD"] = $arrLastDay["wday"] + 1;
	$arrParams["intLastDay"] = $arrLastDay["mday"];
	$arrParams["strMonthName"] = Get_Month_Text($intMonth);
	$arrParams["intMonth"] = $intMonth;

	return $arrParams;
}

function getmicrotime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float) $usec + (float) $sec);
}

function check_lang($lang = ""){
	global $cfg;
	// up to only 4 letters
	$lang = (empty($lang))?$cfg["core"]["lang"]:$lang;
	$ret = substr($lang, 0, 4);
	if (preg_match("/^[a-z]{2,4}$/", $ret))
		return $ret;
	else
		return "esp";
}

/**
 * @return string
 * @param string $date
 * @param boolean $showtime
 * @param boolean $showyear
 * @param boolean $showmonth
 * @param boolean $showday
 * @param boolean $boolTextMonth
 * @param string $strDivChar
 * @param mixed $varAddDayText false => no, true => long, long, short
 * @param mixed $boolShortMonthText false => no, true => long, long, short
 * @param mixed $strFormat "" => formato, por default es el que viene el la variable de configuracion
 * @desc Devuelve la fecha en formato localizado.  Recibe una fecha en formato estricto de MySQL (yyyy-mm-dd HH:mm:ss)
 */
function show_date($date, $showtime = true, $showyear = true, $showmonth = true, $showday = true, $boolTextMonth = false, $strDivChar = "/", $varAddDayText = false, $boolShortMonthText = false, $strFormat = "") {
	global $cfg;

	$strDay = substr($date, 8, 2);
	$strMonth = substr($date, 5, 2);
	$strYear = substr($date, 0, 4);

	$strWeekDay = ($varAddDayText === false) ? "" : (Get_WeekDay_Text(Get_WeekDay_Integer($date), ($varAddDayText === true) ? "long" : $varAddDayText) . " ");

	$strMonthSplit = ($cfg["core"]["lang"]=="deu")?" ":(($cfg["core"]["lang"]=="eng")?" ":" de ");
	$strYearSplit = ($cfg["core"]["lang"]=="deu")?" ":(($cfg["core"]["lang"]=="eng")?", ":" de ");

	$strFormat = (empty($strFormat))?$cfg["core"]["date_format"]:$strFormat;
	switch ($strFormat) {
		case "fmtEUR":
			if ($boolTextMonth) {
				$strMonth = strtolower(Get_Month_Text($strMonth,$boolShortMonthText));
				$date_str = (($showday) ? round($strDay) . $strMonthSplit : "") . (($showmonth) ? $strMonth : "") . (($showyear) ? $strYearSplit . $strYear : "");
			}
			else {
				$date_str = (($showday) ? $strDay : "") . (($showmonth) ? ((($showday) ? $strDivChar : "") . $strMonth) : "") . (($showyear) ? $strDivChar . $strYear : "");
			}
			break;
		case "fmtDE":
			$strDivChar = ".";
			if ($boolTextMonth) {
				$strMonth = Get_Month_Text($strMonth,$boolShortMonthText);
				$date_str = (($showday) ? round($strDay) . $strMonthSplit : "") . (($showmonth) ? $strMonth : "") . (($showyear) ? $strYearSplit . $strYear : "");
			}
			else {
				$date_str = (($showday) ? $strDay : "") . (($showmonth) ? ((($showday) ? $strDivChar : "") . $strMonth) : "") . (($showyear) ? $strDivChar . $strYear : "");
			}
			break;
		case "fmtUSA":
			if ($boolTextMonth) {
				$strMonth = date("F",mktime(0,0,0,$strMonth,$strDay,$strYear));//Get_Month_Text($strMonth);
				$date_str = (($showmonth) ? $strMonth : "") . (($showday) ? (" " . date("jS",
							mktime(1, 1, 1, intval($strMonth), intval($strDay), intval($strYear)))) : "") . (($showyear) ? ", " . $strYear : "");
			}
			else {
				$date_str = (($showmonth) ? $strMonth . $strDivChar : "") . (($showday) ? $strDay : "") . (($showyear) ? $strDivChar . $strYear : "");
			}
			break;
		default: $date_str = (($showyear) ? $strYear . "-" : "") . (($showmonth) ? $strMonth . "-" : "") . (($showday) ? $strDay : "");
			break;
	}
	if ($showtime) $date_str .= " " . substr($date, 11, 5);
	$date_str = str_replace($strDivChar . $strDivChar, $strDivChar, $date_str);
	$date_str = trim($date_str);
	return $strWeekDay . $date_str;
}

/**
 * @return string ó boolean FALSE si hay error
 * @param string $strDate Fecha en formato localizado
 * @param string $strDivChar Caracter de separación entre dias, meses y años
 * @param string $strSourceFormat Formato de la fecha original (fmtEUR, ftmUSA). Si viene empty se utiliza la configurada en el core.
 * @param boolean $boolShowTime Agrega la hora a la fecha (si esta venia en el formato original)
 * @param boolean $boolDateOriginal
 * @desc Recibe una fecha localizada y devuelve una fecha en formato MySQL
 */
function unformat_date($strDate, $strDivChar = "/", $strSourceFormat = "", $boolShowTime = false, $boolDateOriginal=false) {
	global $cfg;

	if ($strSourceFormat == "")
		$strSourceFormat = $cfg["core"]["date_format"];

	$arrMainParts = explode(" ", $strDate);
	if (count($arrMainParts) == 0)
		return false;

	$arrDateParts = explode($strDivChar, $arrMainParts[0]);
	if (count($arrDateParts) != 3) {
		// si no logra dividir por $strDivChar, trata de dividir segun el formato fuente...
		$strDivChar = ($strSourceFormat == "fmtUS")?"/":(($strSourceFormat == "fmtEUR")?"/":(($strSourceFormat == "fmtDE")?".":"-"));
		$arrDateParts = explode($strDivChar, $arrMainParts[0]);
		if (count($arrDateParts) != 3) {
			return false;
		}
	}


	if ($boolDateOriginal) {
		$arrDateParts[2] = "{$arrDateParts[2]}";
	}
	else {
		if ($arrDateParts[2] < 1000) {
			$arrDateParts[2] = intval($arrDateParts[2]);
			if ($arrDateParts[2] < 10) {
				$arrDateParts[2] = "200{$arrDateParts[2]}";
			}
            elseif ($arrDateParts[2] < 90) {
				$arrDateParts[2] = "20{$arrDateParts[2]}";
			}
            elseif ($arrDateParts[2] < 100) {
				// Porsi todavia meten fechas de 199x
				$arrDateParts[2] = "19{$arrDateParts[2]}";
			}
			else {
				$arrDateParts[2] = "2{$arrDateParts[2]}";
			}
		}
	}

	if (strlen($arrDateParts[0]) < 2)
		$arrDateParts[0] = "0{$arrDateParts[0]}";
	if (strlen($arrDateParts[1]) < 2)
		$arrDateParts[1] = "0{$arrDateParts[1]}";

	switch ($strSourceFormat) {
		case "fmtEUR": $strReturn = "{$arrDateParts[2]}-{$arrDateParts[1]}-{$arrDateParts[0]}";
			break;
		case "fmtDE": $strReturn = "{$arrDateParts[2]}-{$arrDateParts[1]}-{$arrDateParts[0]}";
			break;
		case "fmtUSA": $strReturn = "{$arrDateParts[2]}-{$arrDateParts[0]}-{$arrDateParts[1]}";
			break;
		default: $strReturn = "{$arrDateParts[0]}-{$arrDateParts[1]}-{$arrDateParts[2]}";
			break;
	}

	if ($boolShowTime && isset($arrMainParts[1]))
		$strReturn .= " " . $arrMainParts[1];

	return $strReturn;
}

function strDateCleanup($strDate) {
	$arrDate = explode("-", $strDate);
	if (count($arrDate) != 3)
		returnFail();

	$strYear = intval($arrDate[0]);
	if ($strYear <= 0)
		$strYear = 1;
	if ($strYear < 1000) {
		if ($strYear < 10) {
			$strYear = "200{$strYear}";
		}
        elseif ($strYear < 90) {
			$strYear = "20{$strYear}";
		}
        elseif ($strYear < 100) {
			// Porsi todavia meten fechas de 199x
			$strYear = "19{$strYear}";
		}
		else {
			$strYear = "2{$strYear}";
		}
	}
	$strMonth = intval($arrDate[1]);
	if ($strMonth <= 0)
		$strMonth = 1;
	if ($strMonth > 12)
		$strMonth = 12;
	$arrMonthInfo = GetMonthInformation($strMonth, $strYear);

	$strDay = intval($arrDate[2]);
	if ($strDay <= 0)
		$strDay = 1;
	if ($strDay > $arrMonthInfo["intLastDay"])
		$strDay = $arrMonthInfo["intLastDay"];

	if (strlen($strMonth) < 2)
		$strMonth = "0" . $strMonth;
	if (strlen($strDay) < 2)
		$strDay = "0" . $strDay;

	$strDate = $strYear . "-" . $strMonth . "-" . $strDay;

	return $strDate;
}

/**
 * @return string
 * @param string $date
 * @param boolean $showtime
 * @desc Hace lo mismo que unformat_date... HAY QUE ELIMINAR ESTA FUNCION
 */
function conv_date($date, $showtime=true) {
	global $cfg;
	switch ($cfg["core"]["date_format"]) {
		case "fmtEUR": $date_str = substr($date, 6, 4) . "-" . substr($date, 3, 2) . "-" . substr($date, 0, 2);
			break;
		case "fmtDE": $date_str = substr($date, 6, 4) . "-" . substr($date, 3, 2) . "-" . substr($date, 0, 2);
			break;
		case "fmtUSA": $date_str = substr($date, 6, 4) . "-" . substr($date, 0, 2) . "-" . substr($date, 3, 2);
			break;
		default: $date_str = substr($date, 0, 4) . "-" . substr($date, 5, 2) . "-" . substr($date, 8, 2);
			break;
	}

	if ($showtime)
		$date_str .= " " . substr($date, 11, 5);

	return $date_str;
}

/**
 * @return mixed
 * @param string $Month
 * @param boolean $boolShort
 *
 * @desc Devuelve el texto de un mes (mandando el dia) ó el array de textos completo si no se manda parámetro...
 */
function Get_Month_Text($Month = "ALL", $boolShort = false) {
	global $lang, $cfg;

	/* 20111123 AG: Esto a veces corre desde cronjobs y no encuentra los langs.
     *                Decidi ver si puedo incluir el archivo y si no, declaro aqui los nombres en español.
     */
	if (!isset($lang["CALENDAR_MONTHS_SHRT"])) {
		$strFileName = "lang/msg_" . check_lang($cfg["core"]["lang"]) . ".php";
		if (file_exists($strFileName)) {
			include_once($strFileName);
		}
		else {
			$lang["CALENDAR_MONTHS_SHRT"] = array("Ene", "Feb", "Mar", "Abr", "May", "Jun",
				"Jul", "Ago", "Sep", "Oct", "Nov", "Dic");

			$lang["CALENDAR_MONTHS"] = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
				"Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
		}
	}

	$arrLabels = ($boolShort) ? $lang["CALENDAR_MONTHS_SHRT"] : $lang["CALENDAR_MONTHS"];
	$arrTMP = array();
	$arrItem = array();
	foreach ($arrLabels as $arrItem["key"] => $arrItem["value"]) {
		$arrTMP[$arrItem["key"] + 1] = $arrItem["value"];
	}

	if ($Month && $Month != "ALL") {
		settype($Month, "integer");
		if (isset($arrTMP[$Month])) {
			return $arrTMP[$Month];
		}
		else {
			return $Month;
		}
	}
	else {
		if ($Month == "ALL") {
			return $arrTMP;
		}
		else {
			return "";
		}
	}
}

/**
 * Devuelve el integer del weekday para una fecha
 *
 * @param string $strDate
 * @return integer
 */
function Get_WeekDay_Integer($strDate)
{
	$arrDate = explode("-", $strDate);
	$strDay = $arrDate[2];
	$strMonth = $arrDate[1];
	$strYear = $arrDate[0];

	$arrToday = getdate(mktime(0, 0, 0, $strMonth, $strDay, $strYear));
	$intWeekDay = $arrToday["wday"] + 1;

	return $intWeekDay;
}

/**
 * @return mixed
 * @param integer $intWeekDay
 * @param string $strDescType short/long
 * @param boolean $boolGetAll Indica si se devuelve todo el array o no.
 * @desc Devuelve el nombre de un día de la semana ó el array completo de días segun la variable $boolGetAll. Domingo = 1
 */
function Get_WeekDay_Text($intWeekDay, $strDescType = "short", $boolGetAll = false)
{
	global $lang;

	if ($intWeekDay < 1 || $intWeekDay > 7)
		$intWeekDay = 1;

	$arrTMP = array();
	for ($i = 1; $i <= 7; $i++) {
		$arrTMP[$i]["short"] = $lang["CALENDAR_DAYS_2"][$i - 1];
		$arrTMP[$i]["long"] = $lang["CALENDAR_DAYS_3"][$i - 1];
	}

	if ($boolGetAll) {
		return $arrTMP;
	}
	else {
		settype($intWeekDay, "integer");
		return $arrTMP[$intWeekDay][$strDescType];
	}
}

function crop_string($text, $maxlen, $boolAddDots = true, $boolFillSpaces = false)
{
	if (strlen($text) <= $maxlen) {
		if ($boolFillSpaces) {
			$intSpacesToFill = $maxlen - strlen($text);
			$text .= str_repeat(" ", $intSpacesToFill);
		}
		return $text;
	}
	else {
		if ($boolAddDots) {
			return (trim(substr($text, 0, ($maxlen - 3))) . "...");
		}
		else {
			return (trim(substr($text, 0, $maxlen)));
		}
	}
}

function crop_sentence($strSentence = "", $maxword = 10)
{
	$strReturn = "";
	$strArraySentence = explode(" ", $strSentence);
	if (count($strArraySentence) <= 0) {
		return $strReturn;
	}
	else {
		for ($i = 0; $i < $maxword; $i++) {
			if (isset($strArraySentence[$i])) {
				$strReturn .= ( empty($strReturn)) ? "{$strArraySentence[$i]}" : " {$strArraySentence[$i]}";
			}
			else {
				$strReturn .= "";
			}
		}
		return $strReturn;
	}
}

// GLOBAL ARRAYS!!
$arrModuleMainGot = array(); // Cache de inlusion de mains
$arrIncludedModulesPrivate = array(); // Cache de inclusion de librerias
$arrModuleDependency = array(); // Array de dependencia de modulos
$arrCheckedModules = array(); // Cache de respuestas

// OJO, SI AGREGAN PARAMETROS, POR FAVOR, TOMARLOS EN CUENTA EN $arrCheckedModules
/**
 * @return boolean
 * @param string $module Modulo a verificar
 * @param boolean $boolIncludePrivate indica a la funcion si debe incluir los langs y funciones privadas
 * @param string $strShowWhen ver nota de abajo [GUDIEL]
 * @param boolean $boolIncludeMain Icluir el main del modulo o no
 * @param boolean $boolCheckProveedor Para verificar si es proveedor o no.  Si es proveedor devuelve FALSE siempre si esta variable es true.
 * @desc Devuelve true o false dependiendo de si un modulo esta activo o no.
 */
function check_module($module, $boolIncludePrivate = true, $strShowWhen = "", $boolIncludeMain = true, $boolCheckProveedor = true) {
	// OJO, SI AGREGAN PARAMETROS, POR FAVOR, TOMARLOS EN CUENTA EN $arrCheckedModules
	global $cfg, $config, $arrLMPaths;
	global $lang; //Esta variable no se usa en este contexto pero si chinga si no la jalo, creo que tiene que ver con la recursion.
	global $intGlobalPageProcessedLogID, $arrIncludedModulesPrivate, $arrCheckedModules, $arrModuleDependency, $arrModuleMainGot, $arrHMLToolsArray;
	// global $arrInfocenterInformation, $arrInfocenterPadreInformation, $arrHMLToolsArray;

	$boolReturn = false; // inicializo variable

	/* DEBUG
    $varDB01 = intval($boolIncludePrivate);
    $varDB02 = $strShowWhen;
    $varDB03 = intval($boolIncludeMain);
    $varDB04 = intval($boolCheckProveedor);
    //*/

	// Primero, verifico el cache de respuesta de la funcion luego preparo los otros caches
	// 20170620 AG: Agregue [$_SESSION["wt"]["uid"]] a los caches de includes porque me di cuenta que en algunos casos (webservices) puede cambiar el usuario durante el proceso...
	$intUserIDToLog = (isset($_SESSION["wt"]["uid"]) && $_SESSION["wt"]["uid"] > 0)?$_SESSION["wt"]["uid"]:0;
	if (!isset($arrCheckedModules[$intUserIDToLog][$boolIncludePrivate])) $arrCheckedModules[$intUserIDToLog][$boolIncludePrivate] = array();
	if (!isset($arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen])) $arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen] = array();
	if (!isset($arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen][$boolIncludeMain])) $arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen][$boolIncludeMain] = array();
	if (!isset($arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen][$boolIncludeMain][$boolCheckProveedor])) $arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen][$boolIncludeMain][$boolCheckProveedor] = array();
	if ( isset($arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen][$boolIncludeMain][$boolCheckProveedor][$module])) {
		/* DEBUG
        db_query("INSERT INTO wt_log (uid, descripcion) VALUES ({$intGlobalPageProcessedLogID}, 'YA definido para {$module} - IncPriv:{$varDB01} - ShowW:{$varDB02} - IncMain:{$varDB03} - ChkProv:{$varDB04}')");
        //*/
		return $arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen][$boolIncludeMain][$boolCheckProveedor][$module];
	}
	else {
		/* DEBUG
        db_query("INSERT INTO wt_log (uid, descripcion) VALUES ({$intGlobalPageProcessedLogID}, 'NO definido para {$module} - IncPriv:{$varDB01} - ShowW:{$varDB02} - IncMain:{$varDB03} - ChkProv:{$varDB04}')");
        //*/
	}

	// Preparo cache de $arrIncludedModulesPrivate
	if (!isset($arrIncludedModulesPrivate[$intUserIDToLog])) $arrIncludedModulesPrivate[$intUserIDToLog] = array();

	// Preparo cache de $arrModuleMainGot
	if (!isset($arrModuleMainGot[$intUserIDToLog])) $arrModuleMainGot[$intUserIDToLog] = array();

	// Esto sirve para hacer que si se desactiva un grupo o familia, los miembros tengan acceso solo a algunos modulos
	if (isset($_SESSION["wt"]["_dis_grp_modules"]) && isset($cfg["_dis_grp_modules"])) {
		if (isset($cfg["_dis_grp_modules"][$module]) && !$cfg["_dis_grp_modules"][$module]) {
			$cfg["modules"][$module] = false;
			$cfg[$module]["showwhen"] = "NEVER";
		}
	}

	// Modulos con manejos aparte
	if ($module == "core") {
		$boolReturn = true;
	}
	else if ($module == "birthday") {
		$boolReturn = (isset($cfg["core"]["birthdayAlert"]) && $cfg["core"]["birthdayAlert"]);
	}
	// El modulo de admisiones como tal no existe pero habia que separar los links en el menú...
	else if ($module == "admisiones") {
		$boolReturn = check_module("inscripciones", $boolIncludePrivate, $strShowWhen, $boolIncludeMain, $boolCheckProveedor);
	}
	// Verifico si es un localmodule, estos ya estan incluidos desde el theme
	else if (isset($arrLMPaths[$module])) {
		$boolReturn = true;
	}
	else {
		// Esto me asegura que siempre se incluya el main del modulo al hacer check_module siempre y cuando el modulo esté habilitado en la DB.
		// Lo dejé antes de todo porque tengo mis dudas de si la variable showwhen va a estar si no se ha incluido el main, por el tema del default.
		// OJO, esto NO incluye variables que no sean globales...
		if ($boolIncludeMain && isset($cfg["modules"][$module]) && $cfg["modules"][$module] && !isset($arrModuleMainGot[$intUserIDToLog][$module])) {
			/*
             * Aqui, a veces da un problema:
             * En el profile se chequea el modulo sin hacer login y sin privates ni nada... como no he hecho log in, el modulo de zonas esta normal, show when = A. En este caso, el webservice jala bien.
             * Luego, al volver a entrar aqui ya loguineado, zonas debier ser NEVER pero ese cambio NO se hace porque el main ya corrio...
             * Por eso me trono una vez que quite el include del profile antes de correr el webservice...
             * En su momento lo arregle cambiando mobile_frontpage_schoolworld para que si el modulo no esta activo, no devuelva info de ese modulo sin dar error pero no me quedo del todo satisfecho...
             * Lo bueno es que ese cambio de no loguineado a si loguineado durante la ejecucion solo se da en los webservices y creo que esto solo falla con los modulos que se inactivan para cierto tipo de usuarios a nivel del main.
             */
			include_once("modules/{$module}/main.php");
			$arrModuleMainGot[$intUserIDToLog][$module] = true;
		}

		// [GUDIEL]
		// Esto sirve para los links del menu de arriba, probablemente lo quitemos al verificar todos los mains de los modulos.
		// Lo puse (gudiel) porque no recuerdo si existe algun modulo que tenga un link que su showwhen sea distinto del showwhen del modulo, así que no quise asumir nada.
		// Es muy probable que esta validacion se elimine
		$strShowWhen_wrk = $strShowWhen; // Con esta variable voy a trabajar, la original la mantengo para la recursion y cachd.
		if (empty($strShowWhen) && isset($cfg[$module]["showwhen"])) {
			$strShowWhen_wrk = $cfg[$module]["showwhen"];
		}

		if (!isset($cfg["modules"][$module])) {
			// Si el módulo no esta activo según la configuración del sitio, verificamos si el modulo es un local module...
			if (isset($config["local_modules"][$module])) {
				$boolReturn = true;
			}
			else {
				$boolReturn = false;
			}
		}
		else {
			// Si módulo tiene configuración y strShowWhen no esta en blanco, verifico que el estado de la sesion sea congruente con strShowWhen
			if (isset($cfg[$module]) && !empty($strShowWhen_wrk)) {
				if (isset($_SESSION["wt"]["logged"]) && $_SESSION["wt"]["logged"] == true) {
					$boolReturn = (($strShowWhen_wrk == "A" || $strShowWhen_wrk == "L") && $cfg["modules"][$module]);
				}
				else {
					$boolReturn = (($strShowWhen_wrk == "A" || $strShowWhen_wrk == "N") && $cfg["modules"][$module]);
				}

				$boolReturn = $boolReturn && isset($cfg["modules"][$module]) && $cfg["modules"][$module];
			}
			// Si no, veo que el modulo este activo
			else {
				$boolReturn = isset($cfg["modules"][$module]) && $cfg["modules"][$module];
			}

			// Verifico el acceso a proveedores, esto QUITA el acceso a proveedores a cualquier modulo... ¿¿??
			if ($boolCheckProveedor && isset($cfg["clientes"]) && !empty($cfg["clientes"]["Proveedores_Usuarios"]) && $_SESSION["wt"]["swusertype"] == $cfg["clientes"]["Proveedores_Usuarios"]) {
				$boolReturn = false;
			}
		}

		// Hasta aqui ya podria dar un veredicto... puede cambiar por la dependencia pero lo pongo aqui preliminarmente por temas de referencia circular.
		$arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen][$boolIncludeMain][$boolCheckProveedor][$module] = $boolReturn;

		// Voy a ver las dependencias antes de el include del private porque puede ser que en el private haya alguna llamada a las dependencias pues
		// En mi cabeza, las dependencias significan que este modulo depende de sus dependencias.

		// Dependencias... esto puede cambiar el resultado de $boolReturn
		// Luego, si tiene dependencia, incluyo y verifico los modulos de los cuales depende
		if ($boolReturn && isset($arrModuleDependency[$module])) {
			$arrTMP = $arrModuleDependency; // trabajo en base a una copia para que la recurrencia no se quede en un loop infinito cuando hay dependencia circular.
			unset($arrModuleDependency[$module]); // Quito el modulo de la dependencia por la dependencia circular
			foreach ($arrTMP[$module] as $strModuloRequerido) {
				if (!$boolReturn) break;
				/* DEBUG
                db_query("INSERT INTO wt_log (uid, descripcion) VALUES ({$intGlobalPageProcessedLogID}, 'recurrencia para {$strModuloRequerido} desde {$module} - IncPriv:{$varDB01} - ShowW:{$varDB02} - IncMain:{$varDB03} - ChkProv:{$varDB04}')");
                //*/
				$boolReturn = $boolReturn && check_module($strModuloRequerido, $boolIncludePrivate, $strShowWhen, $boolIncludeMain, $boolCheckProveedor);
			}
			$arrModuleDependency[$module] = $arrTMP[$module]; // Restauro la dependencia por si las dudas.
		}

		// Si estoy diciendo que quiero incluir datos privados del modulo y es un modulo normal
		if ($boolReturn && $boolIncludePrivate && $cfg["modules"][$module] && !isset($arrIncludedModulesPrivate[$intUserIDToLog][$module])) {
			if (is_dir("modules/{$module}/private_lang") && file_exists("modules/{$module}/private_lang/msg_" . check_lang($cfg["core"]["lang"]) . ".php")) {
				include_once("modules/{$module}/private_lang/msg_" . check_lang($cfg["core"]["lang"]) . ".php");
			}
			if (file_exists("modules/{$module}/private_functions.php")) {
				include_once("modules/{$module}/private_functions.php");
			}
			$arrIncludedModulesPrivate[$intUserIDToLog][$module] = true;
		}
	}

	$arrCheckedModules[$intUserIDToLog][$boolIncludePrivate][$strShowWhen][$boolIncludeMain][$boolCheckProveedor][$module] = $boolReturn;
	return $boolReturn;
}

/**
 * Función que devuelve un arreglo de fechas desde un intervalo dado
 * o bien desde una fecha inicial y un numero fijo de fechas
 *
 * @param string $strRecurrencia Indica el tipo de recurrencia, solo puede ser mensual, diario, semanal, anual, quincenal
 * @param int $intIntervalRecurrencia Indica el intervalo de tiempo para la recurrencia Ej: 1 dia, 3 meses, 2 años. Esto depende directamente de la recurrencia
 * @param string $fechaInicio Indica la fecha de inicio del calculo en el formato yyyy-mm-dd
 * @param string $fechaFin Indica la fecha de finalización del cálculo, formato yyyy-mm-dd, si viene en blanco entonces es OBLIGATORIO usar la variable $intCantidadDocumentos mayor que 0
 * @param int $intCantidadDocumentos Si no selecciono la fechaFin entonces puede mandar un numero exacto de cálculos para que me devuelva las fechas
 * @return mixed arrFechas me devuelve un array con las fechas en el formato arrFechas[1] = 'yyyy-mm-dd' arrFechas[2] = 'yyyy-mm-dd'
 */
function get_fechas_recurrencia($strRecurrencia, $intIntervalRecurrencia, $fechaInicio, $fechaFin = "", $intCantidadDocumentos = 0)
{

	if($intIntervalRecurrencia == 0){
		return false;
	}

	//Este arreglo contiene las recurrencias y el tipo de intervalo que utilizo para cada uno
	$arrRecurrencias = array();
	$arrRecurrencias["mensual"] = array();
	$arrRecurrencias["mensual"]["cantidad"] = $intIntervalRecurrencia;
	$arrRecurrencias["mensual"]["tipo"] = "MONTH";
	$arrRecurrencias["semanal"] = array();
	$arrRecurrencias["semanal"]["cantidad"] = $intIntervalRecurrencia * 7;
	$arrRecurrencias["semanal"]["tipo"] = "DAY";
	$arrRecurrencias["diario"] = array();
	$arrRecurrencias["diario"]["cantidad"] = $intIntervalRecurrencia;
	$arrRecurrencias["diario"]["tipo"] = "DAY";
	$arrRecurrencias["anual"] = array();
	$arrRecurrencias["anual"]["cantidad"] = $intIntervalRecurrencia;
	$arrRecurrencias["anual"]["tipo"] = "YEAR";
	$arrRecurrencias["quincenal"] = array();
	/* $arrRecurrencias["quincenal"]["cantidad"] = $intIntervalRecurrencia;
      $arrRecurrencias["quincenal"]["tipo"] = "YEAR"; */

	//Si se ingresa otra recurrencia extraña entonces lo establezco como diario el default
	if (!isset($arrRecurrencias[$strRecurrencia]))
		$strRecurrencia = "diario";
	//if ($intIntervalRecurrencia <= 0) $intIntervalRecurrencia = 1;

	//Inicio siempre con la fecha de inicio, por lo tanto
	//siempre que llamo a esta funcion me devuelve por lo menos esta fecha
	$arrFechas = array();
	$arrFechas[1] = $fechaInicio;
	$fecha = $fechaInicio;

	//SI ES QUINCENAL TIEN UNA PARTICULARIDAD ESPECIAL
	//AGARRA CADA QUINCE DIAS PARA CALCULOS DE DOS PAGOS MENSUALES SIN TOMAR
	//EN CUENTA DIAS SINO CADA 15 DIAS CON MÁXIMO DOS PAGOS POR MES EN LAS MISMAS FECHAS
	if ($strRecurrencia == "quincenal") {

		$arrExplodeFechas = explode("-", $arrFechas[1]);
		$arrDosFechas[1] = intval($arrExplodeFechas[2]);    //CONTIENE LOS DOS DIAS QUE SE VAN A COBRAR O CALCULAR
		//SI EL DIA QUE ME MANDAN ES MAYOR A LA QUINCENA
		//ENTONCES TENGOQ EU CALCULAR CUAL ES SU DIA 15 CORRESPONDIENTE AL MISMO MES
		//ES DECIR 15 DIAS MENOS
		//EN LAS VARIABLES INTYEAR Y INTMONTH VAN EL AÑO Y EL MES QUE LE CORRESPONDE CALCULAR
		//ES DECIR SI AL FECHA INICIAL ES MAYOR AL 15 DEL MES
		//SU PROXIMO CALCULO DEBERIA SER EL PROXIMO MES, DE LO CONTRARIO VA A SER EL MISMO MES

		if ($arrExplodeFechas[2] > 15) {
			$arrDosFechas[2] = intval($arrExplodeFechas[2] - 15);
			$intYear = (intval($arrExplodeFechas[1]) == 12) ? ( intval($arrExplodeFechas[0]) + 1) : intval($arrExplodeFechas[0]);
			$intMonth = (intval($arrExplodeFechas[1]) == 12) ? 1 : (intval($arrExplodeFechas[1]) + 1);
		}
		else {
			$arrDosFechas[2] = intval($arrExplodeFechas[2] + 15);
			$intYear = intval($arrExplodeFechas[0]);
			$intMonth = intval($arrExplodeFechas[1]);
		}

		$intYear = sprintf("%02d", $intYear);
		$intMonth = sprintf("%02d", $intMonth);
		$arrDosFechas[1] = sprintf("%02d", $arrDosFechas[1]);
		$arrDosFechas[2] = sprintf("%02d", $arrDosFechas[2]);

		//EN ESTA VARIABLE SE GUARDA EL INDICE DE $ARRDOSFECHAS[INTINDICETOCA] QUE LE CORRESPONDE CALCULAR
		$intIndiceToca = 2;

		if ($intCantidadDocumentos > 0 && empty($strFechaFin)) {

			for ($i = 2; $i <= $intCantidadDocumentos; $i++) {

				$arrMontInformation = GetMonthInformation($intMonth, $intYear);
				$intTMPFechaFinal = $arrDosFechas[$intIndiceToca];

				//SI EL CALCULO CORRESPONDE A MAYOR QUE EL ULTIMO DIA DEL MES, ENTONCES EL CALCULO CORRESPONDE AL ULTIMO DIA DE ESE MES
				if (intval($arrDosFechas[$intIndiceToca]) > intval($arrMontInformation["intLastDay"]))
					$arrDosFechas[$intIndiceToca] = intval($arrMontInformation["intLastDay"]);

				$arrFechas[$i] = "{$intYear}-{$intMonth}-{$arrDosFechas[$intIndiceToca]}";
				$arrDosFechas[$intIndiceToca] = $intTMPFechaFinal;    //VUELVO A PONER LA FECHA NORMAL QUE TENIA YA QUE SI CAMBIO NO LA DEJO PERMANENTEMENTE
				//VEO SI ME TENGO QUE CAMBIAR DE MES Y AÑO
				if ($arrDosFechas[$intIndiceToca] > 15) {
					$intYear = ($intMonth == 12) ? (1*$intYear + 1) : $intYear;
					$intMonth = ($intMonth == 12) ? 1 : (1*$intMonth + 1);
				}

				$intYear = sprintf("%02d", $intYear);
				$intMonth = sprintf("%02d", $intMonth);

				$intIndiceToca = ($intIndiceToca == 1) ? 2 : 1;
			}
		}
		else {

			$intContinue = sqlGetValueFromKey("SELECT IF( TO_DAYS('{$fecha}') <= TO_DAYS('{$fechaFin}'), 1,0 )");
			$intContinue = intval($intContinue);
			$intCountFechas = 2;

			while ($intContinue > 0) {

				$arrMontInformation = GetMonthInformation($intMonth, $intYear);
				$intTMPFechaFinal = $arrDosFechas[$intIndiceToca];

				if (intval($arrDosFechas[$intIndiceToca]) > intval($arrMontInformation["intLastDay"]))
					$arrDosFechas[$intIndiceToca] = intval($arrMontInformation["intLastDay"]);

				//VEO SI LA FECHA QUE ESTOY CALCULANDO HASTA ELMOMENTO ES MENOR O IGUAL A LA FECHA LIMITE QUE TENGO PARA EL CALCULO
				$intContinue = sqlGetValueFromKey("SELECT IF( TO_DAYS('{$intYear}-{$intMonth}-{$arrDosFechas[$intIndiceToca]}') <= TO_DAYS('{$fechaFin}'), 1,0 )");
				$intContinue = intval($intContinue);

				//SI ES FECHA VALIDA LO INGRESO
				if ($intContinue > 0)
					$arrFechas[$intCountFechas] = "{$intYear}-{$intMonth}-{$arrDosFechas[$intIndiceToca]}";

				$arrDosFechas[$intIndiceToca] = $intTMPFechaFinal;

				if ($arrDosFechas[$intIndiceToca] > 15) {
					$intYear = ($intMonth == 12) ? (1*$intYear + 1) : $intYear;
					$intMonth = ($intMonth == 12) ? 1 : (1*$intMonth + 1);
				}

				$intYear = sprintf("%02d", $intYear);
				$intMonth = sprintf("%02d", $intMonth);

				$intIndiceToca = ($intIndiceToca == 1) ? 2 : 1;
				$intCountFechas++;
			}
		}
	}
	else {

		//Si selecciono una cantidad exacta de documentos sin una fecha final establecida
		if ($intCantidadDocumentos > 0 && empty($strFechaFin)) {
			for ($i = 2; $i <= $intCantidadDocumentos; $i++) {
				$arrFechas[$i] = sqlGetValueFromKey("SELECT DATE_ADD('{$fecha}',
                                                     INTERVAL {$arrRecurrencias[$strRecurrencia]["cantidad"]}
                                                     {$arrRecurrencias[$strRecurrencia]["tipo"]})");
				$fecha = $arrFechas[$i];
			}
		}
		else {    //Si establezco una fecha final
			$intContinue = sqlGetValueFromKey("SELECT IF( TO_DAYS('{$fecha}') <= TO_DAYS('{$fechaFin}'), 1,0 )");
			$intContinue = intval($intContinue);

			$intCountFechas = 2;

			while ($intContinue > 0) {
				$fecha = sqlGetValueFromKey("SELECT DATE_ADD('{$fecha}',
                                             INTERVAL {$arrRecurrencias[$strRecurrencia]["cantidad"]}
                                             {$arrRecurrencias[$strRecurrencia]["tipo"]})");

				$intContinue = sqlGetValueFromKey("SELECT IF( TO_DAYS('{$fecha}') <= TO_DAYS('{$fechaFin}'), 1,0 )");
				$intContinue = intval($intContinue);

				if ($intContinue > 0) {
					$arrFechas[$intCountFechas] = $fecha;
				}

				$intCountFechas++;
			}
		}
	}

	return $arrFechas;
}

//FUNCION QUE DEVUELVE UN ARRAY CON LOS MESES COMO LA GENTE... Hace lo mismo que Get_Month_Text...
function getMeses($boolFillZeros = false, $boolShort=false)
{
	global $lang;
	$arrMeses = array();
	foreach ($lang["CALENDAR_MONTHS"] as $key => $mes) {
		$arrMeses[(($boolFillZeros && $key <= 8) ? "0" : "") . ($key + 1)] = ($boolShort) ? substr($mes, 0, 3) : $mes;
	}
	return $arrMeses;
}

/**
 * Funcion que prepara el script para el filtro de municipios por departamento.
 *
 */
$boolGlobalMunicipiosScriptAlreadySet = false;

function setupMunicipiosScript()
{
	global $boolGlobalMunicipiosScriptAlreadySet;

	if (!$boolGlobalMunicipiosScriptAlreadySet) {
		?>
        <script language="Javascript" type="text/javascript">
            var arrMunicipios = new Array();
            var arrDepartamentos = new Array();
			<?php
			$strSQL = "SELECT * FROM wt_municipios WHERE active = 'Y' ORDER BY nombre ";
			$qMuni = db_query($strSQL);
			while ($rMuni = db_fetch_array($qMuni)) {
				print "arrMunicipios[{$rMuni["id"]}] = new Array();\n";
				print "arrMunicipios[{$rMuni["id"]}]['id'] = {$rMuni["id"]};\n";
				print "arrMunicipios[{$rMuni["id"]}]['nombre'] = '{$rMuni["nombre"]}';\n";
				print "arrMunicipios[{$rMuni["id"]}]['departamento'] = {$rMuni["departamento"]};\n";
			}
			db_free_result($qMuni);

			$strSQL = "SELECT * FROM wt_departamentos WHERE active='Y'";
			$qDepartamentos = db_query($strSQL);
			while ($rDepartamentos = db_fetch_array($qDepartamentos)) {
				print "arrDepartamentos['D-{$rDepartamentos["id"]}'] = '{$rDepartamentos["orden_cedula"]}';";
			}
			db_free_result($qDepartamentos);
			?>

            // IES Fix
            var globalJavaScriptIntDepartamento = false;
            var globalJavaScriptObjMunicipio = false;
            var globalJavaScriptIntDefaultMunicipio = false;

            function setupMunicipios(objDepartamento, objMunicipio, intDefaultMunicipio) {
                var intDepartamento = objDepartamento.value;

                if (intDepartamento / intDepartamento != 1) return false;

                deleteSelectOptions(objMunicipio);

                // IES Fix
                globalJavaScriptIntDepartamento = intDepartamento;
                globalJavaScriptObjMunicipio = objMunicipio;
                globalJavaScriptIntDefaultMunicipio = intDefaultMunicipio;

                setTimeout("setupMunicipiosIES();", 1, "JavaScript");

                return arrDepartamentos['D-'+intDepartamento];
            }

            // IES Fix
            function setupMunicipiosIES() {
                var intTMP;

                var intDepartamento = globalJavaScriptIntDepartamento;
                var objMunicipio = globalJavaScriptObjMunicipio;
                var intDefaultMunicipio = globalJavaScriptIntDefaultMunicipio;

                for (intTMP in arrMunicipios) {
                    if (arrMunicipios[intTMP]['departamento'] == intDepartamento) {
                        addOptionToSelect(document, objMunicipio, arrMunicipios[intTMP]['id'], arrMunicipios[intTMP]['nombre'], intDefaultMunicipio == intTMP);
                    }
                }
            }
        </script>
		<?php
		$boolGlobalMunicipiosScriptAlreadySet = true;
	}
}

/**
 * Obtiene la direccion hacia una imagen buscando en el siguente orden:
 * 1-Profiles
 * 2-Theme
 * 3-Variable de configuracion $cfg["core"]["images_path"]
 * 4-Carpeta default images
 * Nota: Si no encuentra la imagen trata de devolver la imagen de no disponible, caso contrario retorna un false.
 * @param string $strNombreImagen El nombre de la imagen con su extencion
 * @param boolean $boolReturnImgNoDisponible si no encuentra la imagen en nunguna carpeta devuelve la imagen no disponible
 * @param boolean %boolForceCore esta hace que primero busque en el directorio del core "images/"
 * @return string Direccion hacia la imagen
 */
function strGetCoreImageWithPath($strNombreImagen, $boolReturnImgNoDisponible = false, $boolForceCore = false){

	global $cfg;

	$strPath = "";
	$strFolder = "images";

	// Si es PNG y es MSIE6 o menos, trata de poner un gif o un jpg
	$boolIsIE = $_SESSION["wt"]["browser"]["detail"]["boolIsMSIE"];
	$intIEVer = $_SESSION["wt"]["browser"]["detail"]["IEVer"];
	$arrImageNameParts = explode(".", $strNombreImagen);

	if($arrImageNameParts[1] == "png" && $boolIsIE && $intIEVer < 7){
		if(!empty($cfg["core"]["site_profile"])){
			if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strFolder}/{$arrImageNameParts[0]}.gif")){
				$strPath = "profiles/{$cfg["core"]["site_profile"]}/{$strFolder}/{$arrImageNameParts[0]}.gif";
			}
			else if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strFolder}/{$arrImageNameParts[0]}.jpg")){
				$strPath = "profiles/{$cfg["core"]["site_profile"]}/{$strFolder}/{$arrImageNameParts[0]}.jpg";
			}
		}
		else if(!empty($cfg["core"]["theme"])){
			if(file_exists("themes/{$cfg["core"]["theme"]}/{$strFolder}/{$arrImageNameParts[0]}.gif")){
				$strPath = "themes/{$cfg["core"]["theme"]}/{$strFolder}/{$arrImageNameParts[0]}.gif";
			}
			else if(file_exists("profiles/{$cfg["core"]["theme"]}/{$strFolder}/{$arrImageNameParts[0]}.jpg")){
				$strPath = "themes/{$cfg["core"]["theme"]}/{$strFolder}/{$arrImageNameParts[0]}.jpg";
			}
		}
		else if(!empty($cfg["core"]["images_path"])){
			if(file_exists("{$cfg["core"]["images_path"]}/{$arrImageNameParts[0]}.gif")){
				$strPath = "{$cfg["core"]["images_path"]}/{$arrImageNameParts[0]}.gif";
			}
			else if(file_exists("{$cfg["core"]["images_path"]}/{$arrImageNameParts[0]}.jpg")){
				$strPath = "{$cfg["core"]["images_path"]}/{$arrImageNameParts[0]}.jpg";
			}
		}
		else{
			if (file_exists("images/{$arrImageNameParts[0]}.gif")) {
				$strPath = "images/{$arrImageNameParts[0]}.gif";
			}
			else if (file_exists("images/{$arrImageNameParts[0]}.jpg")) {
				$strPath = "images/{$arrImageNameParts[0]}.jpg";
			}
		}
	}

	if($boolForceCore){
		if (file_exists("images/{$strNombreImagen}")){
			$strPath = "images/{$strNombreImagen}";
		}
	}

	if(empty($strPath)){
		if(!empty($cfg["core"]["site_profile"]) && file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strFolder}/{$strNombreImagen}")){
			$strPath = "profiles/{$cfg["core"]["site_profile"]}/{$strFolder}/{$strNombreImagen}";
		}
		else if(!empty($cfg["core"]["theme"]) && file_exists("themes/{$cfg["core"]["theme"]}/{$strFolder}/{$strNombreImagen}")){
			$strPath = "themes/{$cfg["core"]["theme"]}/{$strFolder}/{$strNombreImagen}";
		}
		// Si la imagen existe en el directorio de configuracion, usa esa, si no, usa la del core.
		else if (file_exists("{$cfg["core"]["images_path"]}/{$strNombreImagen}")) {
			$strPath = "{$cfg["core"]["images_path"]}/{$strNombreImagen}";
		}
		else if (file_exists("images/{$strNombreImagen}")){
			$strPath = "images/{$strNombreImagen}";
		}
		else if (file_exists("{$cfg["core"]["images_path"]}/imagennodisponibleGray.jpg") && $boolReturnImgNoDisponible) {
			$strPath = "{$cfg["core"]["images_path"]}/imagennodisponibleGray.jpg";
		}
		else if (file_exists("images/imagennodisponibleGray.jpg") && $boolReturnImgNoDisponible) {
			$strPath = "images/imagennodisponibleGray.jpg";
		}
		else{
			$strPath = false;
		}
	}

	return $strPath;
}

/**
 * Funcion que verifica que esten habilitados los modulos de apache que permiten hacer el rewrite (friendly URLs)
 *
 * @return boolean
 */
function core_apache_check_rewrite_modules() {
	// Si no hay soporte para .htaccess, entonces de una vez devuelvo false
	/*
    Esto funciona porque se define
    SetEnv HTACCESS on
    en el archivo .htaccess de nuestro codigo
    */
	if (!isset($_SERVER['HTACCESS']) ) {
		return false;
	}

	if (function_exists('apache_get_modules')) {
		$arrModules = apache_get_modules();
		$boolModRewrite = in_array('mod_rewrite', $arrModules);
		$boolModHeaders = in_array('mod_headers', $arrModules);
	}
	else {
		core_SendScriptInfoToWebmaster("No hay apache_get_modules");
		$boolModRewrite =  getenv('HTTP_MOD_REWRITE')=='On' ? true : false ;
		$boolModHeaders = true;
	}

	return $boolModRewrite && $boolModHeaders;
}

$arrGlobalIncludedJQueryLibraries = array();
/**
 * Funcion que incluye una libreria de jquery.  Busca automaticamente la version minimizada para incluirla, sino busca una version normal (sin sufijo "min")
 * Ademas, busca automaticamente un archivo css, primero en el theme y si no lo encuentra, en las librerias estandar.
 *
 * Si la libreria ya se incluyó, solo devuelve true.
 *
 * @param string $strLibName Nombre de la librería, lo paso a lowercase porque asumo que todos los directorios y librerias estan con lowercase.
 * @param mixed $varRecObj Objeto para datos de recurrencia, unicamente para uso interno de la funcion.
 *
 * @return boolean true, si la libreria fue incluida o si ya estaba incluida... false si no se pudo incluir
 */
function jquery_includeLibrary($strLibName, $varRecObj = false) {
	global $cfg, $config, $lang, $arrGlobalIncludedJQueryLibraries;

	$strLibNameOri = $strLibName;
	$strLibName = strtolower(user_input_delmagic($strLibName));
	if ($strLibName !== $strLibNameOri) {
		drawDebug("PRECAUCION! LAS LIBRERIAS SIEMPRE SE DEBEN DEFINIR CON MINUSCULAS");
		return false;
	}

	$boolUseModRewrite = core_apache_check_rewrite_modules();

	/*
      $strLibName siempre trae el path completo (mb.menu) aunque ya vaya por una sub libreria (menu)
      pero el path completo se define hasta el final de todo
     */
	if (isset($arrGlobalIncludedJQueryLibraries[$strLibName]))
		return true;

	if ($varRecObj === false) {
		$arrTMP = explode(".", $strLibName);
		$varRecObj = new stdClass();
		$varRecObj->arrLibsPath = $arrTMP;
		$varRecObj->strCurrPath = "";
		$varRecObj->strCurrLibs = "";
		$varRecObj->intCurrPointer = 0;
	}

	if (!empty($varRecObj->strCurrPath))
		$varRecObj->strCurrPath = "{$varRecObj->strCurrPath}/";
	if (!empty($varRecObj->strCurrLibs))
		$varRecObj->strCurrLibs = "{$varRecObj->strCurrLibs}.";
	$strOriginalLibName = $strLibName;
	$strLibName = "{$varRecObj->arrLibsPath[$varRecObj->intCurrPointer]}";

	$strBasicPath = "core/jquery/{$varRecObj->strCurrPath}{$strLibName}";

	if (is_dir($strBasicPath)) {
		// Si no he incluido ya la sub librería, la incluyo.
		if (!isset($arrGlobalIncludedJQueryLibraries["{$varRecObj->strCurrLibs}{$strLibName}"])) {
			$strJQueryNormal = "jquery.{$varRecObj->strCurrLibs}{$strLibName}.js";
			$strJQueryMinified = "jquery.{$varRecObj->strCurrLibs}{$strLibName}.min.js";
			$strCSS = "jquery.{$varRecObj->strCurrLibs}{$strLibName}.css";
			$strCSSMinified = "jquery.{$varRecObj->strCurrLibs}{$strLibName}.min.css";
			$strCSSComplement = "jquery.{$varRecObj->strCurrLibs}{$strLibName}.complement.css";

			if ($cfg["core"]["CACHE_CSS_AND_JAVA"]) {
				if ($boolUseModRewrite) {
					$strIncludePath = "jq/";
				}
				else {
					$strIncludePath = "jqueryloader.php?file=";
				}
			}
			else {
				$strIncludePath = "{$strBasicPath}/";
			}

			// JS, si este no existe, devuelve false.
			if (file_exists("{$strBasicPath}/{$strJQueryMinified}")) {
				?>
                <script language="JavaScript" src="<?php print "{$strIncludePath}{$strJQueryMinified}"; ?>" type="text/javascript"></script>
				<?php
			}
			else if (file_exists("{$strBasicPath}/{$strJQueryNormal}")) {
				?>
                <script language="JavaScript" src="<?php print "{$strIncludePath}{$strJQueryNormal}"; ?>" type="text/javascript"></script>
				<?php
			}
			else {
				return false;
			}

			$strProfilePath = "";
			// CSS, primero lo busco en el theme
			if ($cfg["core"]["CACHE_CSS_AND_JAVA"]) {
				if ($boolUseModRewrite) {
					$strThemedPath = "jq/theme/{$cfg["core"]["theme"]}/";
					if($cfg["core"]["theme_profile"]){
						if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$strCSS}") ||
							file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$strCSSMinified}")) {
							$strThemedPath = "jq/theme/{$cfg["core"]["theme"]}/profile/{$cfg["core"]["theme_profile"]}/";
						}
					}
					if($cfg["core"]["site_profile"]){
						if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strCSS}") ||
							file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strCSSMinified}")){
							$strThemedPath = "jq/profile/{$cfg["core"]["site_profile"]}/";
						}
					}
				}
				else {
					$strThemedPath = "jqueryloader.php?theme={$cfg["core"]["theme"]}&file=";
					if($cfg["core"]["theme_profile"]){
						if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$strCSS}") ||
							file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$strCSSMinified}")) {
							$strThemedPath = "jqueryloader.php?theme={$cfg["core"]["theme"]}&profile={$cfg["core"]["theme_profile"]}&file=";
						}
					}
					if($cfg["core"]["site_profile"]){
						if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strCSS}") ||
							file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strCSSMinified}")){
							$strThemedPath = "jqueryloader.php?profile={$cfg["core"]["site_profile"]}&file=";
						}
					}
				}
			}
			else {
				$strThemedPath = "themes/{$cfg["core"]["theme"]}/";
				if($cfg["core"]["theme_profile"]){
					if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$strCSS}") ||
						file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$strCSSMinified}")) {
						$strThemedPath = "themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/";
					}
				}
				if($cfg["core"]["site_profile"]){
					if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strCSS}") ||
						file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strCSSMinified}")){
						$strThemedPath = "profiles/{$cfg["core"]["site_profile"]}/";
					}
				}
			}


			if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strCSS}")){
				?> <link type="text/css" href="<?php print "{$strThemedPath}{$strCSS}"; ?>" rel="stylesheet" /> <?php
			}
			else if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$strCSSMinified}")){
				?> <link type="text/css" href="<?php print "{$strThemedPath}{$strCSSMinified}"; ?>" rel="stylesheet" /> <?php
			}
			else if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$strCSS}")) {
				?> <link type="text/css" href="<?php print "{$strThemedPath}{$strCSS}"; ?>" rel="stylesheet" /> <?php
			}
			else if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$strCSSMinified}")) {
				?> <link type="text/css" href="<?php print "{$strThemedPath}{$strCSSMinified}"; ?>" rel="stylesheet" /> <?php
			}
			else if (file_exists("themes/{$cfg["core"]["theme"]}/{$strCSS}")) {
				?> <link type="text/css" href="<?php print "{$strThemedPath}{$strCSS}"; ?>" rel="stylesheet" /> <?php
			}
			else if (file_exists("themes/{$cfg["core"]["theme"]}/{$strCSSMinified}")) {
				?> <link type="text/css" href="<?php print "{$strThemedPath}{$strCSSMinified}"; ?>" rel="stylesheet" /> <?php
			}
			else if (file_exists("{$strBasicPath}/{$strCSS}")) {
				?> <link type="text/css" href="<?php print "{$strIncludePath}{$strCSS}"; ?>" rel="stylesheet" /> <?php
			}
			else if (file_exists("{$strBasicPath}/{$strCSSMinified}")) {
				?> <link type="text/css" href="<?php print "{$strIncludePath}{$strCSSMinified}"; ?>" rel="stylesheet" /> <?php
			}

			if (file_exists("themes/{$cfg["core"]["theme"]}/{$strCSSComplement}")) {
				?> <link type="text/css" href="<?php print "{$strThemedPath}{$strCSSComplement}"; ?>" rel="stylesheet" /> <?php
			}

			$arrGlobalIncludedJQueryLibraries["{$varRecObj->strCurrLibs}{$strLibName}"] = true;
		}

		$varRecObj->strCurrPath .= $strLibName;
		$varRecObj->strCurrLibs .= $strLibName;
		$varRecObj->intCurrPointer++;
		if ($varRecObj->intCurrPointer < count($varRecObj->arrLibsPath)) {
			return jquery_includeLibrary($strOriginalLibName, $varRecObj);
		}
		else {
			return true;
		}
	}
	else {
		return false;
	}
}

$arrHMLToolsArray[] = array("name" => "Crear mapa del sitio para la parte publica", "access" => "admin", "function" => "hmlTools_core_createPublicMapsite", "module" => "core");

/**
 * Prepara un string de CSV para descargar correctamente en un archivo
 *
 * @param array $arrCampos
 * @param boolean $boolAgregarNewLine
 */
function CSV_prepararLinea($arrCampos, $boolAgregarNewLine = true, $boolWindowsNewLine = false) {
	// El delimitador es ","
	// Si el texto tiene "," el string va delimitado por "
	// Las comillas van dobles "" => "
	$arrLinea = array();
	$arrItem = array();
	foreach ($arrCampos as $arrItem["key"] => $arrItem["value"]) {
		$strTMP = $arrItem["value"];
		// Le doy escape a las "
		$strTMP = str_replace("\"", "\"\"", $strTMP);
		if (strstr($strTMP, ",") !== false || strstr($strTMP, "\"") !== false) {
			// Si tiene coma o comillas, lo meto entre comillas...
			$strTMP = "\"{$strTMP}\"";
		}
		$arrLinea[] = $strTMP;
	}

	$strTMP = implode(";", $arrLinea);

	//Algunos estandares dicen que debiera terminar con CRLF (\r\n)... pendiente de confirmar pues no hay un estandar formal
	if ($boolAgregarNewLine) {
		if ($boolWindowsNewLine) {
			$strTMP .= "\r\n";
		}
		else {
			$strTMP .= "\n";
		}
	}
	return $strTMP;
}

/**
 * Funcion que envia un post a una direccion, puede incluir archivos tambien...
 * Esta no funciono con el servidor de la UFM, aparentemente ese servidor no soporta Content-Type: multipart/form-data;
 *
 * @param string $strUrl
 * @param array $arrPostData
 * @param mixed $arrFiles
 * @param integer $intTimeOut timeout para conexion (en segundos)
 * @return string Resultado del post, idealmente un XML
 */
function do_post_request($strUrl, $arrPostData, $arrFiles = false, $intTimeOut = 5) {
	//encontrada en http://php.net/manual/en/function.stream-context-create.php
	$strData = "";
	$strBoundary = "---------------------" . substr(md5(rand(0, 32000)), 0, 10);

	//Collect Postdata
	if (is_array($arrPostData)) {
		$arrItem = array();
		foreach ($arrPostData as $arrItem["key"] => $arrItem["value"]) {
			$strData .= "--{$strBoundary}\n";
			$strData .= "Content-Disposition: form-data; name=\"{$arrItem["key"]}\"\n\n{$arrItem["value"]}\n";
		}
		$strData .= "--{$strBoundary}\n";
	}

	//Collect Filedata
	if (is_array($arrFiles)) {
		$arrItem = array();
		foreach ($arrFiles as $arrItem["key"] => $arrItem["value"]) {
			if (isset($arrItem["value"]["path"])) {
				$fileContents = file_get_contents($arrItem["value"]["path"]);
			}
			else if (isset($arrItem["value"]["contents"])) {
				$fileContents = $arrItem["value"]["contents"];
			}
			else {
				$fileContents = "";
			}

			$strData .= "Content-Disposition: form-data; name=\"{$arrItem["key"]}\"; filename=\"{$arrItem["value"]["name"]}\"\n";
			$strData .= "Content-Type: {$arrItem["value"]["contentType"]}\n";
			$strData .= "Content-Transfer-Encoding: binary\n\n";
			$strData .= $fileContents . "\n";
			$strData .= "--{$strBoundary}--\n";
		}
	}

	$arrParams = array('http' => array(
		'method' => 'POST',
		'header' => 'Content-Type: multipart/form-data; boundary=' . $strBoundary,
		'timeout' => $intTimeOut,
		'user_agent' => 'Homeland Guatemala',
		'content' => $strData
	)
	);

	$objContext = stream_context_create($arrParams);

	$objFilePointer = fopen($strUrl, 'rb', false, $objContext);
	if (!$objFilePointer)
		return false;

	$strResponse = @stream_get_contents($objFilePointer);
	if ($strResponse === false)
		return false;

	return $strResponse;
}

/**
 * Funcion que envia un post solo de campos, no utiliza Content-Type: multipart/form-data;
 *
 * @param string $strUrl
 * @param array $arrPostData
 * @param integer $intTimeOut timeout para conexion (en segundos)
 * @return string Resultado del post, idealmente un XML
 */
function do_postOnly_request($strUrl, $arrPostData, $intTimeOut = 5) {
	if (is_array($arrPostData)) {
		$strPostData = http_build_query($arrPostData);
		$arrPostData = null; //Liberar memoria
	}
	else if (is_string($arrPostData)) {
		$strPostData = $arrPostData;
		$arrPostData = null; //Liberar memoria
	}
	else {
		return false;
	}

	if ($strPostData === false) {
		return false;
	}
	else {
		$arrParams = array('http' => array(
			'method' => 'POST',
			'header' => 'Content-type: application/x-www-form-urlencoded',
			'timeout' => $intTimeOut,
			'user_agent' => 'Homeland Guatemala',
			'content' => $strPostData
		)
		);
		$objContext = stream_context_create($arrParams);
		$strResponse = file_get_contents($strUrl, false, $objContext);
		return $strResponse;
	}
}

/**
 * Funcion que envia un GET solo de campos, no utiliza Content-Type: multipart/form-data;
 *
 * @param string $strUrl
 * @param array $arrGetData
 * @param integer $intTimeOut
 * @return string Resultado del get, idealmente un XML
 */
function do_getOnly_request($strUrl, $arrGetData, $intTimeOut = 5, $boolErros = true) {
	if (is_array($arrGetData)) {
		$strGetData = http_build_query($arrGetData);
		$arrParams = array("http" => array("method" => "GET", "timeout" => $intTimeOut, 'user_agent' => 'Homeland Guatemala'));
		$objContext = stream_context_create($arrParams);
		if($boolErros){
			$strResponse = file_get_contents("{$strUrl}?{$strGetData}", false, $objContext);
		}
		else{
			$strResponse = @file_get_contents("{$strUrl}?{$strGetData}", false, $objContext);
		}
		return $strResponse;
	}
	else if (is_string($arrGetData)) {
		$arrParams = array("http" => array("method" => "GET", "timeout" => $intTimeOut, 'user_agent' => 'Homeland Guatemala'));
		$objContext = stream_context_create($arrParams);
		if($boolErros){
			$strResponse = file_get_contents("{$strUrl}?{$arrGetData}", false, $objContext);
		}
		else{
			$strResponse = @file_get_contents("{$strUrl}?{$arrGetData}", false, $objContext);
		}
		return $strResponse;
	}
	else {
		return false;
	}
}

/**
 * Dibuja o devuelve un string con una imagen con el renderer de texto de php
 *
 * @param string $strTexto Texto a dibujar
 * @param string $strColorFondoRGB Color del fondo
 * @param string $strColorTextoRGB Color del texto
 * @param string $intTransparencia Transaprencia 127 es totalmente transparente (el fondo)
 * @param integer $intFontSize Tamaño del font
 * @param integer $intFormatoIMG Formato de la imagen, usar constantes de hml_imaging.php
 * @param integer $intFONT Font, usar constantes de hml_imaging.php
 * @param integer $intWidth Para enviar un ancho fijo a la imagen
 * @param string $strStyle Para enviar un estilo NO INCLUYE EL STRING style=''
 * @param boolean $boolPrint Para indicar si se hace print a la imagen o si se devuelve en un string
 * @param integer $intAngle El angulo del texto
 * @param integer $intFontStyle Para indicar si es bold, usar constantes de hml_imaging.php
 *
 * @return mixed Solo si boolPrint es false
 */
function ttfrender($strTexto = "", $strColorFondoRGB = "0,0,0", $strColorTextoRGB = "0,0,0", $intTransparencia = "127", $intFontSize = 10,
                   $intFormatoIMG = 0, $intFONT = 0, $intWidth = 0, $strStyle = "", $boolPrint = true, $intAngle = 0, $intFontStyle = 0) {

	if (empty($strTexto)) return "";

	include_once("libs/hml_imaging/hml_imaging.php");

	if ($intFormatoIMG == 0) $intFormatoIMG = IMAGETYPE_PNG;
	if ($intFONT == 0) $intFONT = FF_ARIAL;
	/*if($cfg["core"]["theme_interno"] = "metro2007" && $cfg["core"]["site_profile"] = "metro2012"){
            $intFONT = FF_CENTURY_GOTHIC;
        }*/
	if ($intFontStyle == 0) $intFontStyle = FS_NORMAL;

	if (!empty($strStyle)) {
		$strStyle = "style='{$strStyle}'";
	}

	//$strReturn = "<img {$strStyle} border='0' src='ttfrender.php?texto={$strTexto}&width={$intWidth}&strColorFondoRGB={$strColorFondoRGB}&strColorTextoRGB={$strColorTextoRGB}&strTransparencia={$intTransparencia}&strFondo={$intFontSize}&strFormatoIMG={$strFormatoIMG}&strFONT={$strFONT}'>";
	//20110414 AG: Esto es porsi el texto tiene diagonales
	$strTexto = str_replace(array("/", "?"), array("~|~", "~||~"), $strTexto);
	//$strTexto = urlencode($strTexto);
	//20110407 AG: OJO, si se agregan parametros o se cambia el orden, hay que arreglar ttfrender.php, la parte de los Safe URLs
	$strReturn = "<img {$strStyle} border='0' alt='{$strTexto}' src='ttfrender.php/{$strTexto}/{$intWidth}/{$strColorFondoRGB}/{$strColorTextoRGB}/{$intTransparencia}/{$intFontSize}/{$intFormatoIMG}/{$intFONT}/{$intAngle}/{$intFontStyle}.jpg'>";

	if ($boolPrint) {
		print $strReturn;
	}
	else {
		return $strReturn;
	}
}

function getFile_contentType($strFileType = "")
{
	$ContentType = "";

	if (!empty($strFileType)) {
		switch ($strFileType) {
			case ".asf":
				$ContentType = "video/x-ms-asf";
				break;
			case ".avi":
				$ContentType = "video/avi";
				break;
			case ".doc":
				$ContentType = "application/msword";
				break;
			case ".zip":
				$ContentType = "application/zip";
				break;
			case ".xls":
				$ContentType = "application/vnd.ms-excel";
				break;
			case ".gif":
				$ContentType = "image/gif";
				break;
			case ".jpg":
				$ContentType = "image/jpeg";
				break;
			case "jpeg":
				$ContentType = "image/jpeg";
				break;
			case ".wav":
				$ContentType = "audio/wav";
				break;
			case ".mp3":
				$ContentType = "audio/mpeg3";
				break;
			case ".mpg":
				$ContentType = "video/mpeg";
				break;
			case "mpeg":
				$ContentType = "video/mpeg";
				break;
			case ".rtf":
				$ContentType = "application/rtf";
				break;
			case ".htm":
				$ContentType = "text/html";
				break;
			case "html":
				$ContentType = "text/html";
				break;
			case ".xml":
				$ContentType = "text/xml";
				break;
			case ".xsl":
				$ContentType = "text/xsl";
				break;
			case ".css":
				$ContentType = "text/css";
				break;
			case ".csv":
				$ContentType = "text/csv";
				break;
			case ".txt":
				$ContentType = "text/txt";
				break;
			case ".php":
				$ContentType = "text/php";
				break;
			case ".asp":
				$ContentType = "text/asp";
				break;
			case ".pdf":
				$ContentType = "application/pdf";
				break;
			case "docx":
				$ContentType = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
				break;
			case "dotx":
				$ContentType = "application/vnd.openxmlformats-officedocument.wordprocessingml.template";
				break;
			case "pptx":
				$ContentType = "application/vnd.openxmlformats-officedocument.presentationml.presentation";
				break;
			case "ppsx":
				$ContentType = "application/vnd.openxmlformats-officedocument.presentationml.slideshow";
				break;
			case "potx":
				$ContentType = "application/vnd.openxmlformats-officedocument.presentationml.template";
				break;
			case "xlsx":
				$ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
				break;
			case "xltx":
				$ContentType = "application/vnd.openxmlformats-officedocument.spreadsheetml.template";
				break;
		}
	}
	else {
		$ContentType = "application/octet-stream";
	}
	return $ContentType;
}

$intGlobalPageProcessedLogID = 0;
/**
 * Funcion que inicia el pageProcessedLog, devuelve el ID del proceso actual en la base de datos y define una variable global para actualizar al final del proceso*
 * @param string $strHoraI
 */
function core_start_page_processed_LOG() {
	global $cfg, $intGlobalPageProcessedLogID;

	$intGlobalPageProcessedLogID = 0;
	if ($cfg["core"]["page_processed_LOG"]) {
		if (empty($strHoraI)) $strHoraI = date("H:i:s");

		$strSelf = user_input_delmagic($_SERVER['PHP_SELF']);
		$strSelf = substr($strSelf,strrpos($strSelf,"/")+1);
		$strSelf = db_escape($strSelf);

		//$strQry = db_escape(user_input_delmagic((isset($_SERVER['QUERY_STRING']))?$_SERVER['QUERY_STRING']:""));

		$arrGet = $_GET;

		// Para no guardar el post de los passwords - SUPER IMPORTANTE
		if (isset($arrGet["login_passwd"])) $arrGet["login_passwd"] = "xxx";
		if (isset($arrGet["password"])) $arrGet["password"] = "xxx";

		if (isset($arrGet["frmRegister_password"])) $arrGet["frmRegister_password"] = "xxx";
		if (isset($arrGet["frmRegister_password2"])) $arrGet["frmRegister_password2"] = "xxx";

		if (isset($arrGet["form_user_passwd1"])) $arrGet["form_user_passwd1"] = "xxx";
		if (isset($arrGet["form_user_passwd2"])) $arrGet["form_user_passwd2"] = "xxx";

		if (isset($arrGet["txtCoreSupervisorAutContrasena"])) $arrGet["txtCoreSupervisorAutContrasena"] = "xxx";

		// Para no guardar el post de los numeros de tarjeta de credito - SUPER IMPORTANTE
		if (isset($arrGet["CCN"])) $arrGet["CCN"] = substr($arrGet["CCN"], -4);
		if (isset($arrGet["ED"])) $arrGet["ED"] = "xxyy";
		if (isset($arrGet["TT"])) $arrGet["TT"] = "tttt";
		if (isset($arrGet["CV"])) $arrGet["CV"] = "cvcv";
		if (isset($arrGet["SPAN"])) $arrGet["SPAN"] = "xxxx";
        if (isset($arrGet["TOKEN_BENEFIT"])) $arrGet["TOKEN_BENEFIT"] = "xxxx";
        if (isset($arrGet["DPI"])) $arrGet["DPI"] = "xxxx";
        if (isset($arrGet["BIRTH_YEAR"])) $arrGet["BIRTH_YEAR"] = "xxxx";

		$strQry = (isset($arrGet))?http_build_query($arrGet):"";
		$arrGet = null; //Liberar memoria
		$strQry = db_escape($strQry);

		$arrPost = $_POST;

		// Para no guardar el post de los passwords - SUPER IMPORTANTE
		if (isset($arrPost["login_passwd"])) $arrPost["login_passwd"] = "xxx";
		if (isset($arrPost["password"])) $arrPost["password"] = "xxx";

		if (isset($arrPost["frmRegister_password"])) $arrPost["frmRegister_password"] = "xxx";
		if (isset($arrPost["frmRegister_password2"])) $arrPost["frmRegister_password2"] = "xxx";

		if (isset($arrPost["form_user_passwd1"])) $arrPost["form_user_passwd1"] = "xxx";
		if (isset($arrPost["form_user_passwd2"])) $arrPost["form_user_passwd2"] = "xxx";

		if (isset($arrPost["txtCoreSupervisorAutContrasena"])) $arrPost["txtCoreSupervisorAutContrasena"] = "xxx";

		// Para no guardar el post de los numeros de tarjeta de credito - SUPER IMPORTANTE
		if (isset($arrPost["CCN"])) $arrPost["CCN"] = substr($arrPost["CCN"], -4);
		if (isset($arrPost["ED"])) $arrPost["ED"] = "xxyy";
		if (isset($arrPost["TT"])) $arrPost["TT"] = "tttt";
		if (isset($arrPost["CV"])) $arrPost["CV"] = "cvcv";
		if (isset($arrPost["SPAN"])) $arrPost["SPAN"] = "xxxx";
        if (isset($arrPost["TOKEN_BENEFIT"])) $arrPost["TOKEN_BENEFIT"] = "xxxx";
        if (isset($arrPost["DPI"])) $arrPost["DPI"] = "xxxx";
        if (isset($arrPost["BIRTH_YEAR"])) $arrPost["BIRTH_YEAR"] = "xxxx";

		$strPost = (isset($arrPost))?http_build_query($arrPost):"";
		$arrPost = null; //Liberar memoria
		$strPost = db_escape($strPost);

		//$strVar = urldecode($strVar);  20100611 PARA HACER UNDO!

		$intUID = (isset($_SESSION["wt"]["uid"]))?intval($_SESSION["wt"]["uid"]):0;
		$intEmulatedBy = (isset($_SESSION["wt"]["originalUserToTest"]))?intval($_SESSION["wt"]["originalUserToTest"]):0;
		$strRemoteAddress = (isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:"N/A";
		if (isset($_SERVER["HTTPS"])) {
			$strSQL = "INSERT INTO wt_page_processed_log
	                   (uid, isSecure, uid_emulatedby, self, qry, qry_post, ip, fecha, hora, processed)
	                   VALUES
					   ({$intUID}, 'Y', {$intEmulatedBy}, '{$strSelf}', '{$strQry}', '{$strPost}', '{$strRemoteAddress}', CURDATE(), '{$strHoraI}', -1)";
		}
		else {
			$strSQL = "INSERT INTO wt_page_processed_log
	                   (uid, uid_emulatedby, self, qry, qry_post, ip, fecha, hora, processed)
	                   VALUES
					   ({$intUID}, {$intEmulatedBy}, '{$strSelf}', '{$strQry}', '{$strPost}', '{$strRemoteAddress}', CURDATE(), '{$strHoraI}', -1)";
		}
		db_query($strSQL);

		$intGlobalPageProcessedLogID = db_insert_id();
	}

	return $intGlobalPageProcessedLogID;
}

/**
 * Esta funcion corrije el problema del core_end_page_processed_LOG con mysqli que no conserva la conexion al finalizar el outpub buffer.
 */
function core_script_end_functions() {
	if (function_exists("webservice_save_response")) {
		// Si estoy trabajando con un webservice...
		$OutputHtml = ob_get_contents();
		webservice_save_response($OutputHtml);
		$OutputHtml = null; //Liberar memoria...
	}
	core_end_page_processed_LOG();
}

function core_end_page_processed_LOG() {
	global $cfg, $globalConnection, $intGlobalPageProcessedLogID, $time_start;

	if ((isset($cfg["core"]) && $cfg["core"]["page_processed_LOG"]) || (isset($intGlobalPageProcessedLogID) && $intGlobalPageProcessedLogID > 0)) {
		$timeProcessed = getmicrotime() - $time_start;
		$timeProcessed = 1*$timeProcessed;

		$intUID = (isset($_SESSION["wt"]["uid"]))?intval($_SESSION["wt"]["uid"]):0;
		$strUid = ($intUID)?"uid = {$intUID},":"";

		db_query("UPDATE wt_page_processed_log
                  SET {$strUid} processed = '{$timeProcessed}'
                  WHERE id = {$intGlobalPageProcessedLogID}");
		$intGlobalPageProcessedLogID = false;
	}

	if (isset($globalConnection)) {
		// Para cerrar la conexion
		//db_close($globalConnection);
	}
}

/**
 * Registra un token en la base de datos
 *
 * @param string $strTokenName Nombre del token (hasta 50 digitos)
 * @param mixed $strTokenValue Valor del token (hasta 32 digitos), random genera un token aleatorio
 *
 * @return string Token Value
 */
function core_token_set($strTokenName, $strTokenValue = "random") {
	if ($strTokenValue == "random") {
		$strTokenValue = md5(rand());
	}

	$strSessID = session_id();
	$strTokenName = db_escape($strTokenName);
	$strTokenValueE = db_escape($strTokenValue);

	db_query("REPLACE INTO wt_tokens (sessionid, tokenName, tokenString) VALUES ('{$strSessID}', '{$strTokenName}', '{$strTokenValueE}')");

	return $strTokenValue;
}

/**
* Verifica que un token esté registrado y lo compara con un valor en particular
*
* @param string $strTokenName Nombre del token
* @param string $strTokenValue Valor del token a verificar
*
* @return boolean
*/
function core_token_check($strTokenName, $strTokenValue, $boolCheckSessID = true) {
	$strTokenName = db_escape($strTokenName);
    if($boolCheckSessID){
	    $strSessID = session_id();
	    $strTokenStored = sqlGetValueFromKey("SELECT tokenString FROM wt_tokens WHERE sessionid = '{$strSessID}' AND tokenName = '{$strTokenName}'");

	    return $strTokenStored === $strTokenValue;
    }
    else{
	    $strTokenStored = sqlGetValueFromKey("SELECT tokenString FROM wt_tokens WHERE tokenName = '{$strTokenName}' AND tokenString = '{$strTokenValue}'");
	    return $strTokenStored === $strTokenValue;
    }
}

/**
* Elimina un token de la base de datos
*
* @param mixed $strTokenName
*/
function core_token_clear($strTokenName, $boolCheckSessID = true) {
	$strTokenName = db_escape($strTokenName);
    if($boolCheckSessID){
	    $strSessID = session_id();
	    db_query("DELETE FROM wt_tokens WHERE sessionid = '{$strSessID}' AND tokenName = '{$strTokenName}'");
    }
    else{
	    db_query("DELETE FROM wt_tokens WHERE tokenName = '{$strTokenName}'");
    }
}

/**
 * Obtiene la direccion basica del sitio tomando en cuenta si esta en el lado secure o si estoy en ambiente de desarrollo
 *
 * @param string $strMode Modo de operacion, A = auto, N = Devuelve el url normal, S = Devuelve el URL secure
 *
 * @return string URL con http y termina con /
 */
function core_getBaseDir($strMode = "A") {
	global $boolGlobalIsLocalDev, $cfg, $config;

	if (!isset($cfg["core"]["inSecureSide"])) $cfg["core"]["inSecureSide"] = false;
	if (!isset($cfg["core"]["HTTPS"])) $cfg["core"]["HTTPS"] = false;

	if (!$cfg["core"]["HTTPS"]) $strMode = "N";

	if ($boolGlobalIsLocalDev) {
		$arrTMP = explode("/", $_SERVER["SCRIPT_NAME"]);
		if (count($arrTMP) > 0) unset($arrTMP[count($arrTMP) - 1]);
		$strPath = implode("/", $arrTMP);
		$strPort = ($config["isDEBUG"])?":8081":"";

		$strBaseURL = "http://{$_SERVER["SERVER_NAME"]}{$strPort}{$strPath}/";
	}
	else {
		if ($strMode == "A") {
			$strBaseURL = ($cfg["core"]["inSecureSide"])?$cfg["core"]["url_secure"]:$cfg["core"]["url"];
		}
		else if ($strMode == "N") {
			$strBaseURL = $cfg["core"]["url"];
		}
		else if ($strMode == "S") {
			$strBaseURL = $cfg["core"]["url_secure"];
		}
		else {
			$strBaseURL = $cfg["core"]["url"];
		}
	}

	return $strBaseURL;
}

function core_getBaseDomain() {
	$strUrl = core_getBaseDir();
	$arrUrlInfo = parse_url($strUrl);
	$strDomain = str_replace("www.", "", $arrUrlInfo["host"]);

	return $strDomain;
}

/**
 * Funcion que hace el manejo del usuario global para verificarlo, actualizarlo, crearlo o hacer las notificaciones pertinentes.  Asume que los cambios ya se hicieron en la instancia local.
 *
 * @param integer $intUserID
 * @param string $strOldEmail
 * @param string $strNewEmail
 * @param string $strOldPwdMD5 OJO, ya con MD5
 * @param string $strNewPwdMD5 OJO, ya con MD5
 * @param boolean $boolIsNewUser
 * @param string $strClientKey Para indicar la llave de la localidad, util cuando creo el usuario principal de la instancia
 * @param mixed $objLocalConnection Conexion a utilizar para queries locales.  Solo sirve cuando creo el usuario principal de la instancia
 *
 * @return mixed array["status"] = ok, warning, error
 *                 ["text"]
 */
function core_check_universal_user($intUserID, $strOldEmail, $strNewEmail, $strOldPwdMD5, $strNewPwdMD5, $boolIsNewUser, $strClientKey = "", $objLocalConnection = false) {
	global $config, $lang, $globalConnection;

	// Para segurar que los queries locales corran en la instancia local.  Si se esta creando una nueva, el parametro trae la conexion a la base de datos de la instancia nueva.
	if ($objLocalConnection === false) $objLocalConnection = $globalConnection;

	// Pongo los email con minusculas...
	$strOldEmail = strtolower($strOldEmail);
	$strNewEmail = strtolower($strNewEmail);

	$strNewEmail_e = db_escape($strNewEmail);

	$arrReturn = array();
	$arrReturn["status"] = "ok";
	$arrReturn["text"] = "";

	// Primero verifico si estan habilitados los usuarios globales
	if (isset($config["cloud"]["userUniversal"]) && $config["cloud"]["userUniversal"]) {
		if (isset($config["strClientKey"]) && !empty($config["strClientKey"]) && empty($strClientKey)) {
			$strInstancia = &$config["strClientKey"]; //Llave de la instancia, se define desde el wt_config_cloud_comm.php al cargar todo...
		}
		else {
			$strInstancia = $strClientKey; // Si no esta definida tengo que estar en el front end, en cuyo caso estoy registrando un usuario nuevo desde cloud
		}

		// Abro la conexion remota al frontend
		$objRemoteConnection = db_connect($config["frontEnd_host"], $config["frontEnd_database"], $config["frontEnd_user"], $config["frontEnd_password"],true) or die();
		if (!$objRemoteConnection) {
			core_SendScriptInfoToWebmaster("No fue posible conectarme a base de datos del front end");
			return null;
		}

		// Informacion de la instancia
		$arrInstanceInfo = sqlGetValueFromKey("SELECT client_id, bDatosName FROM wt_cloud_instances WHERE client_key = '{$strInstancia}'",
			false, false, true, $objRemoteConnection);
		$intInstanceID = &$arrInstanceInfo["client_id"];
		if (!$intInstanceID) {
			core_SendScriptInfoToWebmaster("intInstanceID = false!!!.  intInstanceID={$intInstanceID}; strInstancia={$strInstancia}; config['strClientKey']={$config["strClientKey"]}");
			$intInstanceID = 0;
		}

		// Verifico que el correo nuevo sea global (independientemente de cambio o no)
		$arrGlobalInfo = sqlGetValueFromKey("SELECT CU.global_uid, CU.email, CU.password, COUNT(CUI.instance_id) AS otrasInstancias
                                             FROM wt_cloud_users AS CU
                                                     LEFT JOIN wt_cloud_users_instances AS CUI
                                                     ON CUI.global_uid = CU.global_uid AND
                                                        CUI.instance_id != {$intInstanceID}
                                             WHERE CU.email LIKE '{$strNewEmail_e}'",
			false, false, true, $objRemoteConnection);
		if (is_null($arrGlobalInfo["global_uid"])) $arrGlobalInfo = false; //Si no hay registro, no es falso por el COUNT

		$boolMailChanged = ($strOldEmail != $strNewEmail);
		$boolPwdChanged = ($strOldPwdMD5 != $strNewPwdMD5);

		// Si hubo algun cambio en email o contraseña
		if (!$boolMailChanged && !$boolPwdChanged) {
			// Si no hubo ningun cambio, verifico si el mail es global y si no tiene asociacion, mando un mail de confirmacion
			if (!empty($strNewEmail)) {
				if ($arrGlobalInfo == false) {
					// Si el userid no esta asocido a ningun usuario global - caso super raro... quizas solo homelandwm
					//Insert en el master
					db_query("INSERT INTO wt_cloud_users (email, password) VALUES ('{$strNewEmail_e}', '{$strNewPwdMD5}')",
						true, $objRemoteConnection);
					$intGlobalUID = db_insert_id($objRemoteConnection);

					//Insert en el detail
					db_query("REPLACE INTO wt_cloud_users_instances (global_uid, instance_id, instance_uid) VALUES ('{$intGlobalUID}', '{$intInstanceID}', '{$intUserID}')",
						true, $objRemoteConnection);
				}
				else {
					// Si el mail si es global, como ya se que no cambie correo, solo mando la confirmacion si el usuario no esta asociado ya al mismo usuario global
					$intGlobalUserTMP = sqlGetValueFromKey("SELECT global_uid FROM wt_cloud_users_instances WHERE instance_id = {$intInstanceID} AND instance_uid = {$intUserID}", false, false, true, $objRemoteConnection);
					if ($intGlobalUserTMP !== $arrGlobalInfo["global_uid"]) {
						core_universal_user_sendConfirmationMail($intUserID, $strInstancia, $strOldEmail, $strNewEmail, $boolPwdChanged, $boolMailChanged, true, $objLocalConnection);
					}
				}
			}
		}
		else {
			// Si hubo cambios...
			if (empty($strNewEmail)) {
				// Si el correo nuevo es empty, desasocio de cualquier usuario global
				db_query("DELETE FROM wt_cloud_users_instances WHERE instance_id = {$intInstanceID} AND instance_uid = {$intUserID}", true, $objRemoteConnection);
				// Borro los usuarios que no esten en ninguna instancia
				db_query("DELETE wt_cloud_users
                          FROM wt_cloud_users LEFT JOIN wt_cloud_users_instances ON wt_cloud_users_instances.global_uid = wt_cloud_users.global_uid
                          WHERE wt_cloud_users_instances.instance_id IS NULL", true, $objRemoteConnection);

				$strName = sqlGetValueFromKey("SELECT name FROM wt_users WHERE uid = '{$intUserID}'", false, false, true, $objLocalConnection);
				$strName = "{$strName}@{$strInstancia}";

				$arrReturn["status"] = "warning";
				$arrReturn["text"] = sprintf($lang["UNIVERSAL_MAIL_WARNING_001"], $strName);
			}
			else {
				// Si el mail nuevo no esta en blanco, verifico si la cuenta nueva pertenece a algun otro usuario de la misma instancia
				$intOtherUsers = sqlGetValueFromKey("SELECT COUNT(uid) AS conteo FROM wt_users WHERE email LIKE '{$strNewEmail_e}' AND uid != {$intUserID}", false, false, true, $objLocalConnection);
				if ($intOtherUsers) {
					// Si esta asociado a otros usuarios, hago undo al cambio local del email, la contraseña si pela...
					db_query("UPDATE wt_users SET email = '{$strOldEmail}' WHERE uid = '{$intUserID}'", true, $objLocalConnection);

					$arrReturn["status"] = "error";
					$arrReturn["text"] = $lang["UNIVERSAL_MAIL_ERROR_001"];
				}
				else {
					// Si el mail nuevo no es de nadie mas, al menos localmente...
					if ($arrGlobalInfo == false) {
						// Si el mail nuevo no es global...
						// Verifico si el userid tiene algun usuario global
						$intGlobalUID = sqlGetValueFromKey("SELECT global_uid FROM wt_cloud_users_instances WHERE instance_id = {$intInstanceID} AND instance_uid = {$intUserID}",
							false, false, true, $objRemoteConnection);
						if ($intGlobalUID == false) {
							// Si el userid no esta asocido a ningun usuario global - caso super raro... quizas solo homelandwm
							//Insert en el master
							db_query("INSERT INTO wt_cloud_users (email, password) VALUES ('{$strNewEmail_e}', '{$strNewPwdMD5}')",
								true, $objRemoteConnection);
							$intGlobalUID = db_insert_id($objRemoteConnection);

							//Insert en el detail
							db_query("REPLACE INTO wt_cloud_users_instances (global_uid, instance_id, instance_uid) VALUES ('{$intGlobalUID}', '{$intInstanceID}', '{$intUserID}')",
								true, $objRemoteConnection);
						}
						else {
							// Si el usuario si esta asociado a un usuario global, aplico el cambio en todas las instancias asociadas
							core_universal_user_apply_changes_everywhere($intGlobalUID, $strNewEmail, $strNewPwdMD5, $objRemoteConnection);
						}
					}
					else {
						// Si el mail nuevo si es global
						$intGlobalUID = &$arrGlobalInfo["global_uid"];
						if ($arrGlobalInfo["otrasInstancias"] == 0) {
							// Si el usuario global NO esta asociado a otras instancias
							/*
                            Aqui solo debiera entrar cuando:
                                - para un usuario con correo webmaster@homeland.com.gt
                                - cuando no este cambiando nada del usuario que solo esta registrado globalmente para la misma instancia
                            */
							//Insert en el detail - Para asegurar que este registrado bien
							db_query("REPLACE INTO wt_cloud_users_instances (global_uid, instance_id, instance_uid) VALUES ('{$intGlobalUID}', '{$intInstanceID}', '{$intUserID}')",
								true, $objRemoteConnection);
							core_universal_user_apply_changes_everywhere($intGlobalUID, $strNewEmail, $strNewPwdMD5, $objRemoteConnection);

							if ($boolIsNewUser) core_SendScriptInfoToWebmaster("CONDICION RARA EN USUARIO NUEVO - parece que todo esta bien pero igual verificar");
						}
						else {
							// Si el usuario global SI esta asociado a otras instancias
							if (!$boolMailChanged) {
								// Si no cambio el email, tuvo que haber cambiado el password y este si lo cambio en todos lados.
								// Si es usuario nuevo no debiera entrar aqui porque el mail si habra cambiado de empty al nuevo...
								core_universal_user_apply_changes_everywhere($intGlobalUID, $strNewEmail, $strNewPwdMD5, $objRemoteConnection);
							}
							else {
								// Si cambio el email, mando el correo de notificacion a la direccion nueva y pongo localmente los registros correspondientes.  No toco nada en el front end.
								core_universal_user_sendConfirmationMail($intUserID, $strInstancia, $strOldEmail, $strNewEmail, $boolPwdChanged, $boolMailChanged, $boolIsNewUser, $objLocalConnection);
							}
						}
					}
				}
			}
		}

		if ($objRemoteConnection !== false) db_close($objRemoteConnection);
	}

	return $arrReturn;
}

/**
 * Actualiza todas las instancias de un usuario global con un mismo usuario y contraseña, tambien deja los tokens en 0.
 *
 * @param integer $intGlobalUID
 * @param string $strNewEmail
 * @param string $strNewPwdMD5
 * @param mixed $objRemoteConnection Conexion a la base de datos remota, no la abro localmente
 *
 * @return integer cantidad de cambios hechos en bases de datos de instancias
 */
function core_universal_user_apply_changes_everywhere($intGlobalUID, $strNewEmail, $strNewPwdMD5, $objRemoteConnection = false) {
	global $config;

	$intCambiosEnInstancias = 0;

	// Primero verifico si estan habilitados los usuarios globales
	if (isset($config["cloud"]["userUniversal"]) && $config["cloud"]["userUniversal"]) {
		$strNewEmail_e = db_escape($strNewEmail);
		$strNewPwdMD5_e = db_escape($strNewPwdMD5);

		if ($objRemoteConnection === false) $objRemoteConnection = db_connect($config["frontEnd_host"], $config["frontEnd_database"], $config["frontEnd_user"], $config["frontEnd_password"],true) or die();

		// Busco las bases de datos y los usuarios del lado de la instancia para el usuario global
		$strQuery = "SELECT CI.bDatosName, CUI.instance_uid
                     FROM wt_cloud_users_instances AS CUI, wt_cloud_instances AS CI
                     WHERE CI.client_id = CUI.instance_id AND
                           CUI.global_uid = {$intGlobalUID}";
		$qTMP = db_query($strQuery, true, $objRemoteConnection);
		while ($rTMP = db_fetch_assoc($qTMP)) {
			$strQuery = "UPDATE {$rTMP["bDatosName"]}.wt_users
                         SET email = '{$strNewEmail_e}', password = '{$strNewPwdMD5_e}', email_confirm = '', mail_confirmed = 'Y', expirationdate = '0000-00-00', token = ''
                         WHERE uid = {$rTMP["instance_uid"]}";
			db_query($strQuery); // Este si corre con la conexion local...
			$intCambiosEnInstancias += db_affected_rows();
		}
		db_free_result($qTMP);

		// Ya que actualice todas las instancias, me aseguro de actualizar el global
		$strQuery = "UPDATE wt_cloud_users SET email = '{$strNewEmail_e}', password = '{$strNewPwdMD5_e}' WHERE global_uid = {$intGlobalUID}";
		db_query($strQuery, true, $objRemoteConnection);
	}

	return $intCambiosEnInstancias;
}

/**
 * Envia correo de notificacion de cambio en cuenta
 *
 * @param integer $intUserID
 * @param string $strInstancia
 * @param string $strOldEmail
 * @param string $strNewEmail
 * @param boolean $boolPwdChanged
 * @param boolean $boolMailChanged
 * @param boolean $boolIsNewUser
 * @param mixed $objLocalConnection Conexion a utilizar para queries locales.  Solo sirve cuando creo el usuario principal de la instancia
 */
function core_universal_user_sendConfirmationMail($intUserID, $strInstancia, $strOldEmail, $strNewEmail, $boolPwdChanged, $boolMailChanged, $boolIsNewUser, $objLocalConnection = false) {
	global $cfg, $config, $lang, $globalConnection;

	// Para segurar que los queries locales corran en la instancia local.  Si se esta creando una nueva, el parametro trae la conexion a la base de datos de la instancia nueva.
	if ($objLocalConnection === false) $objLocalConnection = $globalConnection;

	if (isset($config["cloud"]["userUniversal"]) && $config["cloud"]["userUniversal"]) {
		$intUserID = intval($intUserID);
		$strOldEmail = strtolower($strOldEmail);
		$strNewEmail = strtolower($strNewEmail);

		$strOldEmail_e = db_escape($strOldEmail);

		$strRandomToken = md5(uniqid(rand()));
		$strRandomToken_e = db_escape($strRandomToken);

		db_query("UPDATE wt_users SET email_confirm = LOWER(email) WHERE uid = {$intUserID}", true, $objLocalConnection);
		db_query("UPDATE wt_users SET email = '{$strOldEmail_e}', token = '{$strRandomToken_e}', expirationdate = '0000-00-00', mail_confirmed = 'N' WHERE uid = {$intUserID}", true, $objLocalConnection);
		// Esta linea es importante porque el token se trunca en la DB
		$strRandomToken = sqlGetValueFromKey("SELECT token FROM wt_users WHERE uid = {$intUserID}", false, false, true, $objLocalConnection);

		$strPath = $cfg["core"]["url"];
		$strSitio = $cfg["core"]["title"];

		//Mando el correo
		if (core_validateEmailAddress($strNewEmail)) {
			$strTo = "{$strNewEmail}\r\n";

			if ($boolIsNewUser) {
				$strSubject = sprintf($lang["UNIVERSAL_MAIL_NEW_SUBJECT"], $strSitio, $strPath);
				$strA = sprintf($lang["UNIVERSAL_MAIL_NEW_MESSAGE_A"], $strSitio, $strPath);

				$strMessage = "{$strA}
                               <br><a href='{$strPath}confirm_universal_user.php?token={$strRandomToken}&inst={$strInstancia}&email={$strNewEmail}&uidInstance={$intUserID}'>
                                    {$strPath}confirm_universal_user.php?token={$strRandomToken}&inst={$strInstancia}&email={$strNewEmail}&uidInstance={$intUserID}
                               </a><br>
                               {$lang["UNIVERSAL_MAIL_NEW_MESSAGE_B"]}";
			}
			else {
				$strSubject = sprintf($lang["UNIVERSAL_MAIL_CHANGE_SUBJECT"], $strSitio, $strPath);
				if (!$boolPwdChanged) {
					$strA = sprintf($lang["UNIVERSAL_MAIL_CHANGE_MESSAGE_MAIL_ONLY_A"], $strSitio, $strPath, $strNewEmail);

					$strMessage = "{$strA}
                                   <br><a href='{$strPath}confirm_universal_user.php?token={$strRandomToken}&inst={$strInstancia}&email={$strNewEmail}&uidInstance={$intUserID}'>
                                        {$strPath}confirm_universal_user.php?token={$strRandomToken}&inst={$strInstancia}&email={$strNewEmail}&uidInstance={$intUserID}
                                   </a><br>
                                   {$lang["UNIVERSAL_MAIL_CHANGE_MESSAGE_MAIL_ONLY_B"]}";
				}
				else {
					$strA = sprintf($lang["UNIVERSAL_MAIL_CHANGE_MESSAGE_MAIL_AND_PWD_A"], $strSitio, $strPath, $strNewEmail);

					$strMessage = "{$strA}
                                   <br><a href='{$strPath}confirm_universal_user.php?token={$strRandomToken}&inst={$strInstancia}&email={$strNewEmail}&uidInstance={$intUserID}'>
                                        {$strPath}confirm_universal_user.php?token={$strRandomToken}&inst={$strInstancia}&email={$strNewEmail}&uidInstance={$intUserID}
                                   </a><br>
                                   {$lang["UNIVERSAL_MAIL_CHANGE_MESSAGE_MAIL_AND_PWD_B"]}";
				}
			}

			//@mail($strTo, $strSubject, $strMessage, $strHeaders);
            $objMail = new AttachMailer($strTo, $strSubject, "");
            $objMail->setMessageHTML($strMessage);
            $objMail->send();
		}
	}
}

/**
 * Define un cache en base de datos para la sesion actual.
 *
 * @param string $strCacheName Nombre del cache
 * @param integer $intCacheDurationS tiempo de vida del cache, en segundos
 * @param string $strCacheContents Contenido del cache, tambien se puede definir con la funcion core_sesscache_setContents
 */
function core_sesscache_set($strCacheName, $intCacheDurationS, $strCacheContents = "") {
	$strSessID = session_id();
	$strCacheName = db_escape($strCacheName);
	$intCacheDurationS = intval($intCacheDurationS);
	$strCacheContents = db_escape($strCacheContents);

	db_query("REPLACE INTO wt_cache_misc
              (sessionid, cacheName, cacheString, dateTimeRegistered, duration_segs)
              VALUES
              ('{$strSessID}', '{$strCacheName}', '{$strCacheContents}', NOW(), {$intCacheDurationS})");
}

/**
 * Re asigna el contenido a un cache, no re-define el tiempo de expiracion
 *
 * @param string $strCacheName Nombre del cache
 * @param string $strCacheContents Contenido del cache
 */
function core_sesscache_setContents($strCacheName, $strCacheContents) {
	$strSessID = session_id();
	$strCacheName = db_escape($strCacheName);
	$strCacheContents = db_escape($strCacheContents);

	db_query("UPDATE wt_cache_misc SET cacheString = '{$strCacheContents}' WHERE sessionid = '{$strSessID}' AND cacheName = '{$strCacheName}'");
}

/**
 * Obtiene el contenido de un cache, devuelve falso si ya expiro o no existe
 *
 * @param string $strCacheName Nombre del cache
 * @return string el contenido del cache en DB, false si esta expirado
 */
function core_sesscache_getContents($strCacheName) {
	$strSessID = session_id();
	$strCacheName = db_escape($strCacheName);

	$strCacheContents = sqlGetValueFromKey("SELECT cacheString FROM wt_cache_misc WHERE sessionid = '{$strSessID}' AND cacheName = '{$strCacheName}'");

	return $strCacheContents;
}

/**
 * Borra un cache
 *
 * @param string $strCacheName Nombre del cache
 */
function core_sesscache_clear($strCacheName) {
	$strSessID = session_id();
	$strCacheName = db_escape($strCacheName);

	db_query("DELETE FROM wt_cache_misc WHERE sessionid = '{$strSessID}' AND cacheName = '{$strCacheName}'");
}

/**
 * Corre una vez al día limpiando los logs viejitos.  El trigger es el primer hit que tenga la página en el día.
 *
 */
function core_rotateInternalLogs() {
	$intRows = sqlGetValueFromKey("SELECT COUNT(table_name) FROM wt_catalogos_last_update WHERE table_name = 'wt_page_processed_log' AND fecha = curdate()");
	if ($intRows == 0) {
		// Si NO hay corrido hoy... lo corro
		db_query("REPLACE INTO wt_catalogos_last_update VALUES ('wt_page_processed_log', curdate(), curtime())");

		db_query("DELETE FROM wt_log_visitas WHERE fecha < DATE_SUB(NOW(), INTERVAL 1 YEAR)");
		//db_query("OPTIMIZE TABLE wt_log_visitas");

		db_query("DELETE FROM wt_log WHERE date < DATE_SUB(NOW(), INTERVAL 12 MONTH)");
		//db_query("OPTIMIZE TABLE wt_log");
		db_query("DELETE wt_log_detail FROM wt_log_detail LEFT JOIN wt_log ON wt_log.ID = wt_log_detail.logID WHERE wt_log.ID IS NULL");
		//db_query("OPTIMIZE TABLE wt_log_detail");

		db_query("DELETE FROM wt_page_processed_log WHERE fecha < DATE_SUB(NOW(), INTERVAL 3 MONTH)");
		//db_query("OPTIMIZE TABLE wt_page_processed_log");
	}
}

/**
 * Manda los encabezados para el control del cache de contenido
 *
 * @param integer $intHoras Horas de duracion del contenido en cache
 * @param integer $intLastModified Ultima hora de modificacion (Unix timestamp)
 * @param string $strContentType Ejemplo text/html, text/css, etc.
 * @param string $strPragma Pragma del cache, se podria usar public para que se aprovechen los proxies
 */
function getCacheHeaders($intHoras, $intLastModified, $strContentType, $strPragma = "private") {
	header("Pragma: {$strPragma}");

	$expires = floor(60*60*$intHoras); // El tiempo de expiracion en segundos

	if ($intLastModified >0) {
		$arrApacheHeaders = apache_request_headers();
		if (isset($arrApacheHeaders["If-Modified-Since"])) {
			// Si esta el parametro If-Modified-Since es porque estoy validando una fecha
			$intIfModifiedSince = strtotime($arrApacheHeaders["If-Modified-Since"]); //Combierto esta fecha a timestamp
			if ($intIfModifiedSince >= $intLastModified) {
				// Si el cache tiene un archivo que no ha cambiado segun el parametro de la fecha de modificacion, devuelvo un 304 not modified
				$strProtocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
				header("Cache-Control: maxage=".$expires);
				header("Expires: " . gmdate("D, d M Y H:i:s", time()+$expires) . " GMT");
				header($strProtocol . " 304 Not Modified");
				die();
			}
		}
		// Si los archivos cambiaron desde la ultima descarga, los bajo de nuevo
		$strLastModified = gmdate("D, d M Y H:i:s", $intLastModified);
	}

	header("Cache-Control: maxage=".$expires);
	if ($intLastModified > 0) header("Last-Modified: " . $strLastModified . " GMT");
	header("Expires: " . gmdate("D, d M Y H:i:s", time()+$expires) . " GMT");
	header("Content-Type: {$strContentType}");
}

$arrGlobalIsUserOfTypeCache = array();
$arrGlobalHasSubTypes = array();
/**
 * Funcion que verifica si un usuario es de un tipo en particular.
 *
 * @param integer $intUid userid a verificar
 * @param string $strUserType swusertype a verificar
 * @param boolean $boolCheckSubType tomar en cuenta los sub tipos
 *
 * @return boolean
 */
function swusertypes_isUserOfType($intUid, $strUserType, $boolCheckSubType = true) {
	global $arrGlobalIsUserOfTypeCache, $arrGlobalHasSubTypes;

	$intUid = intval($intUid);
	if (isset($arrGlobalIsUserOfTypeCache[$intUid][$strUserType])) return $arrGlobalIsUserOfTypeCache[$intUid][$boolCheckSubType][$strUserType];

	if (!isset($arrGlobalIsUserOfTypeCache[$intUid])) $arrGlobalIsUserOfTypeCache[$intUid] = array();

	$arrGlobalIsUserOfTypeCache[$intUid][$boolCheckSubType][$strUserType] = false;

	$strMainType = sqlGetValueFromKey("SELECT swusertype FROM wt_users WHERE uid = {$intUid}");
	$arrGlobalIsUserOfTypeCache[$intUid][$boolCheckSubType][$strMainType] = true;

	if ($boolCheckSubType) {
		$strQuery = "SELECT subtype FROM wt_swusertypes_users_st WHERE userid = {$intUid}";
		$qTMP = db_query($strQuery);
		while ($rTMP = db_fetch_assoc($qTMP)) {
			$arrGlobalIsUserOfTypeCache[$intUid][$boolCheckSubType][$rTMP["subtype"]] = true;
			$arrGlobalHasSubTypes[$intUid] = true;
		}
		db_free_result($qTMP);
	}

	return $arrGlobalIsUserOfTypeCache[$intUid][$boolCheckSubType][$strUserType];
}

/**
 * Funcion que indica si un usuario tiene subtipos.
 *
 * @param integer $intUid
 *
 * @return boolean
 */
function swusertypes_hasSubTypes($intUid) {
	global $arrGlobalHasSubTypes;

	if (isset($arrGlobalHasSubTypes[$intUid])) return $arrGlobalHasSubTypes[$intUid];

	$strQuery = "SELECT COUNT(subtype) AS conteo FROM wt_swusertypes_users_st WHERE userid = {$intUid}";
	$intCount = sqlGetArray($strQuery);

	$arrGlobalHasSubTypes[$intUid] = ($intCount > 0);

	return $arrGlobalHasSubTypes[$intUid];
}


/**
 * Devuelve el pais predeterminado.  Por el momento este se define en el campo isLocalDefault de la tabla de paises
 *
 * @param string $strGetWhat id=>solo el ID en un integer, nombre=>solo el nombre en un string, array=>ambos en un array
 * @return mixed
 */
function getDefaultCountry($strGetWhat = "array") {
	$arrDefault = sqlGetValueFromKey("SELECT id, nombre FROM wt_paises WHERE isLocalDefault = 'Y'");
	if (!$arrDefault) {
		// Si no hay, devuelvo guate...
		$arrDefault["id"] = 72;
		$arrDefault["nombre"] = "Guatemala";
	}

	if ($strGetWhat != "array") {
		return $arrDefault[$strGetWhat];
	}
	else {
		return $arrDefault;
	}
}

/**
 * Le da formato al ID oficial de una persona segun el pais.  Por el momento solo valido para Guatemala y El Salvdor.
 *
 * @param integer $intCountryID
 * @param string $strID
 *
 * @return string
 */
function cleanupIDforCountry($intCountryID, $strID) {
	// Quito espacios y guiones
	$strTMP = str_replace(array(" ", "-"), "", trim($strID));
	switch ($intCountryID) {
		case 72:
			//Guatemala: nnnn-nnnnn-nnnn
			$strTMP = preg_replace('/[^0-9]*/','', $strTMP);

			$strID = substr($strTMP, 0, 4);
			$strTMP = substr($strTMP, 4);
			$strID .= "-".substr($strTMP, 0, 5);
			$strTMP = substr($strTMP, 5);
			$strID .= "-".substr($strTMP, 0, 4);

			if (strlen($strID) <= 3) $strID = ""; //Si solo tengo tres caracteres, la doy por empty mejor...
			break;
		case 54:
			//El Salvador: nnnnnnnn-n
			$strTMP = preg_replace('/[^0-9]*/','', $strTMP);

			$strID = substr($strTMP, 0, 8);
			$strTMP = substr($strTMP, 8);
			$strID .= "-".substr($strTMP, 0, 1);

			if (strlen($strID) <= 3) $strID = ""; //Si solo tengo tres caracteres, la doy por empty mejor...
			break;
		default:
			$strID = trim($strID);
			break;
	}
	return $strID;
}

function cropStringNotCutWord($strTextToCrop = "", $intLenght = 50){

	$strTextToCrop = strip_tags(trim($strTextToCrop));

	if(strlen($strTextToCrop) > $intLenght){
		$strTextToCrop = substr($strTextToCrop, 0, $intLenght);
		$index = strrpos($strTextToCrop, " ");
		$strTextToCrop = substr($strTextToCrop, 0, $index);
		$strTextToCrop.="...";
	}
	return $strTextToCrop;
}

/**
 * Finds the matching string between 2 strings
 *
 * @param string $string_1
 * @param string $string_2
 *
 * @return NULL|string
 *
 * @link http://en.wikibooks.org/wiki/Algorithm_implementation/Strings/Longest_common_substring#PHP
 */
function string_intersect($string_1, $string_2) {
	$string_1_length = strlen($string_1);
	$string_2_length = strlen($string_2);
	$return          = "";

	if ($string_1_length === 0 || $string_2_length === 0) {
		// No similarities
		return $return;
	}

	$longest_common_subsequence = array();

	// Initialize the CSL array to assume there are no similarities
	for ($i = 0; $i < $string_1_length; $i++) {
		$longest_common_subsequence[$i] = array();
		for ($j = 0; $j < $string_2_length; $j++) {
			$longest_common_subsequence[$i][$j] = 0;
		}
	}

	$largest_size = 0;

	for ($i = 0; $i < $string_1_length; $i++) {
		for ($j = 0; $j < $string_2_length; $j++) {
			// Check every combination of characters
			if ($string_1[$i] === $string_2[$j]) {
				// These are the same in both strings
				if ($i === 0 || $j === 0) {
					// It's the first character, so it's clearly only 1 character long
					$longest_common_subsequence[$i][$j] = 1;
				} else {
					// It's one character longer than the string from the previous character
					$longest_common_subsequence[$i][$j] = $longest_common_subsequence[$i - 1][$j - 1] + 1;
				}

				if ($longest_common_subsequence[$i][$j] > $largest_size) {
					// Remember this as the largest
					$largest_size = $longest_common_subsequence[$i][$j];
					// Wipe any previous results
					$return       = "";
					// And then fall through to remember this new value
				}

				if ($longest_common_subsequence[$i][$j] === $largest_size) {
					// Remember the largest string(s)
					$return = substr($string_1, $i - $largest_size + 1, $largest_size);
				}
			}
			// Else, $CSL should be set to 0, which it was already initialized to
		}
	}

	// Return the list of matches
	return $return;
}

/**
 * Return the first day of the Week/Month/Quarter/Year that the
 * current/provided date falls within
 *
 * @param string   $period The period to find the first day of. ('year', 'quarter', 'month', 'week')
 * @param DateTime $date   The date to use instead of the current date
 *
 * @return DateTime
 * @throws InvalidArgumentException
 * @link http://davidhancock.co/2013/11/get-the-firstlast-day-of-a-week-month-quarter-or-year-in-php/
 */
function firstDayOf($period, DateTime $date = null)
{
	$period = strtolower($period);
	$validPeriods = array('year', 'quarter', 'month', 'week');

	if ( ! in_array($period, $validPeriods))
		throw new InvalidArgumentException('Period must be one of: ' . implode(', ', $validPeriods));

	$newDate = ($date === null) ? new DateTime() : clone $date;

	switch ($period) {
		case 'year':
			$newDate->modify('first day of january ' . $newDate->format('Y'));
			break;
		case 'quarter':
			$month = $newDate->format('n') ;

			if ($month < 4) {
				$newDate->modify('first day of january ' . $newDate->format('Y'));
			} elseif ($month > 3 && $month < 7) {
				$newDate->modify('first day of april ' . $newDate->format('Y'));
			} elseif ($month > 6 && $month < 10) {
				$newDate->modify('first day of july ' . $newDate->format('Y'));
			} elseif ($month > 9) {
				$newDate->modify('first day of october ' . $newDate->format('Y'));
			}
			break;
		case 'month':
			$newDate->modify('first day of this month');
			break;
		case 'week':
			$newDate->modify(($newDate->format('w') === '0') ? 'monday last week' : 'monday this week');
			break;
	}

	return $newDate;
}

/**
 * Return the last day of the Week/Month/Quarter/Year that the
 * current/provided date falls within
 *
 * @param string   $period The period to find the last day of. ('year', 'quarter', 'month', 'week')
 * @param DateTime $date   The date to use instead of the current date
 *
 * @return DateTime
 * @throws InvalidArgumentException
 * @link http://davidhancock.co/2013/11/get-the-firstlast-day-of-a-week-month-quarter-or-year-in-php/
 */
function lastDayOf($period, DateTime $date = null)
{
	$period = strtolower($period);
	$validPeriods = array('year', 'quarter', 'month', 'week');

	if ( ! in_array($period, $validPeriods))
		throw new InvalidArgumentException('Period must be one of: ' . implode(', ', $validPeriods));

	$newDate = ($date === null) ? new DateTime() : clone $date;

	switch ($period)
	{
		case 'year':
			$newDate->modify('last day of december ' . $newDate->format('Y'));
			break;
		case 'quarter':
			$month = $newDate->format('n') ;

			if ($month < 4) {
				$newDate->modify('last day of march ' . $newDate->format('Y'));
			} elseif ($month > 3 && $month < 7) {
				$newDate->modify('last day of june ' . $newDate->format('Y'));
			} elseif ($month > 6 && $month < 10) {
				$newDate->modify('last day of september ' . $newDate->format('Y'));
			} elseif ($month > 9) {
				$newDate->modify('last day of december ' . $newDate->format('Y'));
			}
			break;
		case 'month':
			$newDate->modify('last day of this month');
			break;
		case 'week':
			$newDate->modify(($newDate->format('w') === '0') ? 'now' : 'sunday this week');
			break;
	}

	return $newDate;
}


/**
 * Regresa un array de fechas correspondientes al numero del dia de la semana entre el rango de fechas dadas
 *
 * @param string $strFechaInicial La fecha inicial en el rango de las fechas
 * @param string $strFechaFinal   La fecha final en el rango de las fechas
 * @param number $intNumeroDiaSemana   Numero del dia de la semana (de 0 a 6)
 *
 * @return array
 */
function getDateForSpecificDayBetweenDates($strFechaInicial, $strFechaFinal , $intNumeroDiaSemana){

	$strFechaInicial = strtotime($strFechaInicial);
	$strFechaFinal = strtotime($strFechaFinal);

	$arrFechas = array();

	do{
		if(date("w", $strFechaInicial) != $intNumeroDiaSemana){
			$strFechaInicial += (24 * 3600); // agrega 1 dia
		}
	} while(date("w", $strFechaInicial) != $intNumeroDiaSemana);


	while($strFechaInicial <= $strFechaFinal){
		$objDateTmp = new DateTime(date("Y-m-d", $strFechaInicial));
		$intNoSemana = $objDateTmp->format("W");
		$arrTMP["fecha"] = date("Y-m-d", $strFechaInicial);
		$arrTMP["no_semana"] = $intNoSemana;
		$arrFechas[] = $arrTMP;
		$strFechaInicial = strtotime("+1 week", $strFechaInicial); // agrega 7 dias
	}

	return($arrFechas);
}

/**
 * Retorna un string limpio de acentos o cualquier caracter extraño
 *
 * @param string $string Cadena que deseamos limpiar
 * @param boolean $boolQuitBlank Cadena que deseamos limpiar
 * @return string
 */
function clear_string($string, $boolQuitBlank = false)
{

	$string = trim($string);

	$string = str_replace(
		array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
		array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
		$string
	);

	$string = str_replace(
		array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
		array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
		$string
	);

	$string = str_replace(
		array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
		array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
		$string
	);

	$string = str_replace(
		array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
		array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
		$string
	);

	$string = str_replace(
		array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
		array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
		$string
	);

	$string = str_replace(
		array('ñ', 'Ñ', 'ç', 'Ç'),
		array('n', 'N', 'c', 'C',),
		$string
	);

	//Esta parte se encarga de eliminar cualquier caracter extraño
	$string = str_replace(
		array("\\", "¨", "º", "-", "~",
			"#", "@", "|", "!", "\"",
			"·", "$", "%", "&", "/",
			"(", ")", "?", "'", "¡",
			"¿", "[", "^", "`", "]",
			"+", "}", "{", "¨", "´",
			">", "< ", ";", ",", ":",
			"."),
		'',
		$string
	);

	if($boolQuitBlank){
		$string = str_replace(" ",'', $string);
	}

	return $string;
}

class password {
	static $_instance;
	function __construct() {

	}

	/* Evitamos el clonaje del objeto.*/
	private function __clone() { }

	/* Funcion encargada de crear, si es necesario, el objeto. Esta es la funcion que debemos llamar desde fuera de la clase para instanciar el objeto, y asi, poder utilizar sus metodos
    *  instanciar todos los objetos con este metodo ya que por medio de él podemos acceder indiscriminadamente a funciones estaticas y no estaticas
    */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private $arrWords = array();

	private static function setWords(&$arrWords){
		$arrWords[1] = 'Air';
		$arrWords[2] = 'Pen';
		$arrWords[3] = 'Sol';
		$arrWords[4] = 'Sun';
		$arrWords[5] = 'Agua';
		$arrWords[6] = 'Aire';
		$arrWords[7] = 'Ball';
		$arrWords[8] = 'Book';
		$arrWords[9] = 'Desk';
		$arrWords[10] = 'Door';
		$arrWords[11] = 'Fire';
		$arrWords[12] = 'Hoja';
		$arrWords[13] = 'Leer';
		$arrWords[14] = 'Luna';
		$arrWords[15] = 'Moon';
		$arrWords[16] = 'Nota';
		$arrWords[17] = 'Note';
		$arrWords[18] = 'Hijo';
		$arrWords[19] = 'Rain';
		$arrWords[20] = 'Read';
		$arrWords[21] = 'Room';
		$arrWords[22] = 'Shoe';
		$arrWords[23] = 'Work';
		$arrWords[24] = 'Carro';
		$arrWords[25] = 'Child';
		$arrWords[26] = 'Cielo';
		$arrWords[27] = 'Class';
		$arrWords[28] = 'Compu';
		$arrWords[29] = 'Fuego';
		$arrWords[30] = 'Lapiz';
		$arrWords[31] = 'Libro';
		$arrWords[32] = 'Pants';
		$arrWords[33] = 'Paper';
		$arrWords[34] = 'Pluma';
		$arrWords[35] = 'Shirt';
		$arrWords[36] = 'Silla';
		$arrWords[37] = 'Techo';
		$arrWords[38] = 'Water';
		$arrWords[39] = 'Write';
		$arrWords[40] = 'Heaven';
		$arrWords[41] = 'Lluvia';
		$arrWords[42] = 'Mother';
		$arrWords[43] = 'Pagina';
		$arrWords[44] = 'Parent';
		$arrWords[45] = 'Pelota';
		$arrWords[46] = 'Pencil';
		$arrWords[47] = 'Puerta';
		$arrWords[48] = 'Puerta';
		$arrWords[49] = 'Tierra';
		$arrWords[50] = 'Window';
	}

	/**
	 * Retorna un password generada mas user-friendly
	 *
	 * @return string
	 */
	public function generate_humanpass(){
		$arrWords = array();
		self::setWords($arrWords);
		$strTMP = "";
		$strPassword = "";
		$k = count($arrWords);

		while( (strlen($strPassword) < 8)){
			$intTMP = rand(1,$k);
			$strTMP = (!empty($arrWords[$intTMP]))?$arrWords[$intTMP]:"";

			$strConcat = $strPassword . $strTMP;
			while( (strlen($strConcat) > 10) ){
				$intTMP = rand(1,$k);
				$strTMP = (!empty($arrWords[$intTMP]))?$arrWords[$intTMP]:"";
				$strConcat = $strPassword . $strTMP;
			}
			$strPassword = $strPassword . $strTMP;
		}
		//Ahora agrego los numeros como prefijo y sufijo

		$intTMP = rand(10,99);
		$strPassword = $intTMP . $strPassword;
		$intTMP = rand(10,99);
		$strPassword .= $intTMP;

		return $strPassword;
	}

	public function generate_alternative_pass(){
		$arrGlobal = array();
		self::setWords($arrGlobal);
		$strPassword = "";
		$countArrG = count($arrGlobal);
		while( (strlen($strPassword)) == 0 ){
			$intTMP = rand(1,$countArrG);
			$strTMP = (!empty($arrGlobal[$intTMP]))?$arrGlobal[$intTMP]:"";
			$strPassword = $intTMP . $strTMP;
		}
		$intTMP = rand(10,99);
		$strPassword .= $intTMP;
		return $strPassword;
	}

	/**
	 * Retorna un password generado en base a la longitud ingresada
	 *
	 * @param string $longitudPass = 10 Longitud del password
	 * @return string
	 */
	public function generate_frikipass($longitudPass = 10){
		//Se define la longitud de la contraseña, por default usamos 10 pero podemos setearlo

		//Se define una cadena de caractares. Te recomiendo que uses esta.
		$cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
		//Obtenemos la longitud de la cadena de caracteres
		$longitudCadena=strlen($cadena);

		//Se define la variable que va a contener la contraseña
		$pass = "";

		//Creamos la contraseña
		for($i=1;$i<=$longitudPass;$i++){
			//Definimos numero aleatorio entre 0 y la longitud de la cadena de caracteres -1
			$pos = rand(0,$longitudCadena-1);
			//ahora formando la contraseña en cada iteracción del ciclo, añadimos a la cadena $pass la letra correspondiente a la posición $pos en la cadena de caracteres definida
			$pass .= substr($cadena, $pos,1);
		}
		return $pass;
	}
}

/**
 * Retorna un string Suplantando un Sociedad Anonima por un SA
 *
 * @param string $strChar Cadena que deseamos limpiar
 * @return string
 */
function getAbreviation($strChar){
	//En el primer array seria de agregar el tipo
	$arrSimilares = array("Sociedad","sociedad","S.","s.","S,","s,");
	$strChar = str_replace($arrSimilares, "S", $strChar);

	$arrSimilaresB = array("Anónima","Anonima","anonima","anónima","A.","a.");
	$strChar = str_replace($arrSimilaresB, "A", $strChar);

	return $strChar;
}

/**
 * Retorna un string con una sugerencia de niknames o alias
 *
 * @param string $strChar Cadena que deseamos limpiar
 * @param integer $intMin Minimo de caracteres que se desean para el alias
 * @param integer $inMax Maximo de caracteres que se desean para el alias
 * @param array $arrKeys, opcional si queremos comprobar si existe en algun arreglo que enviamos para que no se repita el alias
 * @return string
 */
function generateAlias($strChar , $intMin, $inMax, $arrKeys = array()){
	function getSkipedWords(){
		$arr["y"] = "y";
		$arr["los"] = "los";
		$arr["las"] = "las";
		$arr["de"] = "de";
		$arr["del"] = "del";
		$arr["la"] = "la";
		return $arr;
	}

	function check_names(&$strAlias,$intMin, $inMax,$arrKeys){
		$boolT = true;
		if(count($arrKeys) >0 && (strlen($strAlias)>0)){
			if(isset($arrKeys[$strAlias])) $boolT = true;
			else{
				$intLength = strlen($strAlias) - 1;
				$arrW = array(); $j=0;
				$k = 0;
				for($i=0;$i<=$intLength;$i++){
					$k++;
					if(ctype_upper($strAlias[$i]) || (is_numeric($strAlias[$i]))){
						if(count($arrW) == 0) $j =0;
						else $j++;
					}
					if(!isset($arrW[$j])) $arrW[$j] = "";
					if($k<=$inMax){
						$arrW[$j] .= $strAlias[$i];
					}
					else{
						$strNewAlias = implode("",$arrW);
						if(isset($arrKeys[$strNewAlias])){
							$strNewAlias = substr($strNewAlias,0,-4);
							$strNewAlias .= rand(1000,9999);
							$strAlias = $strNewAlias;
							return false;
						}
						else{
							$strAlias = $strNewAlias;
							return false;
						}
					}
				}

				$arrSkipedWords = getSkipedWords();
				$arrW2 = $arrW;
				foreach($arrW2 as $key => $value){
					$value = strtolower($value);
					if(key_exists($value,$arrSkipedWords)){
						if( $key == 0 ) continue;
						else{
							$arrW[$key-1] .= $value;
							unset($arrW[$key]);
						}
					}


					unset($key);unset($value);
				}

				if(count($arrW)>1){
					if((strlen($strAlias) <= $intMin)){
						$boolT = true;
					}
					else
						$boolT = false;
				}
				else $boolT = true;
			}
		}
		else{
			$boolT = ((strlen($strAlias) <= $inMax) || (strlen($strAlias) <= $intMin));
		}
		return $boolT;
	}

	//Limpio caracteres, solo permito letras y numeros
	$strChar = clear_string($strChar);
	$strChar = getAbreviation($strChar);
	$arrWords = explode(" ",$strChar);
	$strChar = clear_string($strChar,true);

	$strAlias = "";
	$intLength = count($arrWords) - 1;
	$i=0;
	$strLast = "";
	while( check_names($strAlias, $intMin, $inMax, $arrKeys) ){
		if($i == 0){
			$strTMP = (!empty($arrWords[$i]))?$arrWords[$i]:"";
			$i++;
		}
		else{
			if($i > $intLength){
				if(false){
					$strTMP = rand(10,99);
				}
			}
			else{
				$strTMP = (!empty($arrWords[$i]))?$arrWords[$i]:"";
			}
			$i++;
		}
		if($strTMP == $strLast){
			break;
		}
		else{
			$strTMP = ucfirst($strTMP);
			$strAlias = $strAlias . $strTMP;
			$strLast = $strTMP;
		}
	}
	return $strAlias;
}

class hml_crypt{
	private $strcharset;
	private  $intRot;
	var $strTxt;

	public function setRotacion($n){
		$this->intRot = $n;
	}
	public function getRotacion(){
		return $this->intRot;
	}

	public function __construct($s) {
		$this->strcharset = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%/()=";
		$this->strTxt = $s;
		$this->intRot = rand(5,20);
	}

	function encode(){
		$result = "";
		for($i=0; $i<strlen($this->strTxt); $i++)
			$result .= $this->rotate($this->strTxt{$i}, $this->intRot);

		return base64_encode($result);
	}

	function decode(){
		$this->strTxt = base64_decode($this->strTxt);
		$result = "";
		for($i=0; $i<strlen($this->strTxt); $i++)
			$result .= $this->rotate($this->strTxt{$i}, -$this->intRot);

		return $result;
	}

	function rotate($c, $n){
		$result = "";
		$tamC = strlen($this->strcharset);
		$k = 0;
		$n %= $tamC;
		$c = strtoupper($c);
		if(strstr($this->strcharset, $c)){
			$k = (strpos($this->strcharset, $c) + $n);
			if($k < 0){
				$k += $tamC;
			}
			else $k %= $tamC;

			$result .= $this->strcharset{$k};
		}
		else{
			$result .= $c;
		}
		return $result;
	}
}

#handling debugs
/**
 * This class conteins the logical to handling points of debug
 * @author Edward Acu <acued89@gmail.com>
 * @version 0.2
 */
class debug{
	static $_instance;
	private $DEBUG_STR = "";
	private $CLASSREF;
	/**
	 * the debug level for this instance
	 *
	 * @var    integer
	 * @access private
	 */
	private $debugLevel = 0;

	function __construct($objClass = false) {
		$this->CLASSREF = $objClass;
		if(!$this->CLASSREF) $this->CLASSREF = $this;
	}
	public static function getInstance($objClass = false) {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($objClass);
		}
		return self::$_instance;
	}
	static function drawdebug($ThisVar, $VariableName = "", $ShowWhat=0, $boolForceShow = false){
		$strType = gettype($ThisVar);
		$strPreOpen = "";
		$strPreClose = "";
		if (!is_string($ThisVar)) {
			$strPreOpen = "<pre>";
			$strPreClose = "</pre>";
		}

		echo "\n<hr>";
		if (!empty($VariableName))
			echo "<b><i> $VariableName</b></i> ";
		echo "Var  Type of var = <b>" . $strType . "</b><br><br>\n{$strPreOpen}";
		if ($ShowWhat == 0) {
			if (is_bool($ThisVar))
				print_r(($ThisVar) ? "true" : "false");
			else
				print_r($ThisVar);
		}

		else if ($ShowWhat == 1) {
			print_r(array_values($ThisVar));
		}
		else if ($ShowWhat == 2) {
			print_r(array_keys($ThisVar));
		}
		print_r("<hr>{$strPreClose}\n");
	}
	#Function that append point to debug
	function addDebug($strDebugString){
		if ($this->debugLevel > 0) {
			$this->DEBUG_STR .= (empty($this->DEBUG_STR)) ? "" : ", ";
			$this->DEBUG_STR .= $this->getmicrotime() . ' ' . get_class($this->CLASSREF) . ": {$strDebugString}\n<br>";
		}
	}
	/**
	 * Define el nivel de debug
	 *
	 * @param    int    $intLevel    Debug level 0-9, where 0 turns off
	 * @access    public
	 */
	public function setDebugLevel($intLevel) {
		$this->debugLevel = $intLevel;
	}
	#Function that clear the debug's variable
	function clearDebug(){
		$this->DEBUG_STR = '';
	}
	#Fucntion that return all debugs in string
	function getDebug(){
		return $this->DEBUG_STR;
	}
	#for times
	function getmicrotime(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}
}
#handling errors
/**
 * This class contains the logical to handling error
 * @author Edward Acu <acued89@gmail.com>
 * @version 0.3
 */
class errores{
	/**
	 * Array that conteins all errors descriptions
	 *
	 * @var array -> array that conteins all errors
	 * @access protected
	 */
	private $arrErrorMsgs = false;
	/**
	 * Method thad added an errer into array's error
	 *
	 * @param string $strMsg
	 * @access protected
	 */
	public function addError($strMsg, $strKey = "") {
		if(!empty($strKey)){
			$this->arrErrorMsgs[$strKey][] = $strMsg;
		}
		else $this->arrErrorMsgs[] = $strMsg;

	}
	/**
	 * Method that ordering the error's array
	 */
	public function sortErrorsByText() {
		if ($this->hasError()) {
			sort($this->arrErrorMsgs);
		}
	}
	/**
	 * Method that indicating if it has errors
	 *
	 * @access public
	 * @return boolean
	 */
	public function hasError($strKey = "") {
		if(!empty($strKey)){
			return (isset($this->arrErrorMsgs[$strKey]) && is_array($this->arrErrorMsgs[$strKey]) && (count($this->arrErrorMsgs[$strKey]) > 0));
		}
		else return (is_array($this->arrErrorMsgs) && (count($this->arrErrorMsgs) > 0));
	}
	/**
	 * Method that return the error's array, support array view or string view
	 *
	 * @param string $strMode modes that return message array|string
	 * @param mixed $varModeHelper indicates which is the glue if a string
	 * @return mixed
	 */
	public function getErrors($strMode = "array", $varModeHelper = false, $strKey = "") {
		if(!empty($strKey)){
			if (!$this->hasError($strKey))
				return false;
			if ($strMode == "string") {
				if ($varModeHelper == false)
					$varModeHelper = ", ";
				return implode($varModeHelper, $this->arrErrorMsgs[$strKey]);
			}
			else {
				return $this->arrErrorMsgs[$strKey];
			}
		}
		else{
			if (!$this->hasError())
				return false;
			if ($strMode == "string") {
				if ($varModeHelper == false)
					$varModeHelper = ", ";

				$strVar = "";
				foreach ($this->arrErrorMsgs as $element) {
					if(is_array($element) && (count($element)>0)){
						foreach($element as $element2){
							$strVar .=  (empty($strVar))? $element2 : $varModeHelper . $element2;
						}
					}
					else{
						$strVar .= (empty($strVar))? $element : $varModeHelper . $element;
					}
				}
				return $strVar;
			}
			else {
				return $this->arrErrorMsgs;
			}
		}
	}
}
#Class for logical operations
/**
 * This class contains a logical operation to binary level
 * @author Edward Acu <acued89@gmail.com>
 * @version 0.1
 */
class logical_operation{
	static $_instance;
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	/**
	 *  Realiza una operacion OR a nivel de bits entre dos variantes
	 * @param type $int1
	 * @param type $int2
	 * @return type decimal
	 */
	function disjunction($int1, $int2){
		$var1 = decbin($int1);
		$var2 = decbin($int2);
		$len1 = strlen($var1);
		$len2 = strlen($var2);
		$cont = 0;
		if($len1 > $len2){
			$var2 = str_pad($var2, $len1,"0",STR_PAD_LEFT);
			$cont = $len1;
		}
		else{
			$var1 = str_pad($var1, $len2,"0",STR_PAD_LEFT);
			$cont = $len2;
		}
		$strNewValorBin = "";
		for($i=0;$i<$cont;$i++){

			if($var1[$i] == "0" && $var2[$i] =="0"){
				$strNewValorBin .= "0";
			}
            elseif($var1[$i] == "0" && $var2[$i] =="1"){
				$strNewValorBin .= "1";
			}
            elseif($var1[$i] == "1" && $var2[$i] =="0"){
				$strNewValorBin .= "1";
			}
            elseif($var1[$i] == "1" && $var2[$i] =="1"){
				$strNewValorBin .= "1";
			}
		}
		$strNewValor = bindec($strNewValorBin);
		return $strNewValor;
	}
	/**
	 * Realiza una operación AND a nivel de bits entre dos variantes
	 * @param type $int1
	 * @param type $int2
	 * @return type decimal
	 */
	function conjunction($int1, $int2){
		$var1 = decbin($int1);
		$var2 = decbin($int2);
		$len1 = strlen($var1);
		$len2 = strlen($var2);
		$cont = 0;
		if($len1 > $len2){
			$var2 = str_pad($var2, $len1,"0",STR_PAD_LEFT);
			$cont = $len1;
		}
		else{
			$var1 = str_pad($var1, $len2,"0",STR_PAD_LEFT);
			$cont = $len2;
		}
		$strNewValorBin = "";
		for($i=0;$i<$cont;$i++){

			if($var1[$i] == "0" && $var2[$i] =="0"){
				$strNewValorBin .= "0";
			}
            elseif($var1[$i] == "0" && $var2[$i] =="1"){
				$strNewValorBin .= "0";
			}
            elseif($var1[$i] == "1" && $var2[$i] =="0"){
				$strNewValorBin .= "0";
			}
            elseif($var1[$i] == "1" && $var2[$i] =="1"){
				$strNewValorBin .= "1";
			}
		}
		$strNewValor = bindec($strNewValorBin);
		return $strNewValor;
	}
}

define("SOCKET_CONNECTED", true);
define("SOCKET_DISCONNECTED", false);
/**
 * Socket class
 *
 * This class can be used to connect to external sockets and communicate with the server
 * @author Edward Acu <acued89@gmail.com>
 * @version 0.8
 */

Class StreamSocket{
	/**
	 * @var singleton $instance
	 * @desc Singleton var
	 */
	private static $instance;
	/**
	 * @var resource $connection
	 * @desc Connection resource
	 */
	private $connection = null;
	/**
	 * @var string $connectionState
	 * @desc
	 */
	private $connectionState=SOCKET_DISCONNECTED;
	private $server = "127.0.0.1";
	private $port = "65500";
	/**
	 * @var object $error
	 * @desc instance of object error (search class error)
	 */
	private $error;
	function __construct() {
		$this->error = new errores();
	}
	/**
	 * Singleton pattern. Returns the same instance to all callers
	 *
	 * @return SreamSocket
	 */
	public static function singleton() {
		if (self::$instance == null || !self::$instance instanceof Socket) {
			self::$instance = new Socket();
		}
		return self::$instance;
	}

	function connect($serv,$port){
		$this->server = $serv;
		$this->port = $port;
		$errno = "";
		$errstr ="";
		$this->connection = stream_socket_client("tcp://{$this->server}:{$this->port}",$errno, $errstr);
		if(!$this->connection){
			$this->error->addError ($errno);
		}
		else{
			$this->connectionState = SOCKET_CONNECTED;
		}
	}
	function validateConnection(){
		return (is_resource($this->connection) && ($this->connectionState != SOCKET_DISCONNECTED));
	}
	function disconnect(){
		if($this->validateConnection()){
			fclose($this->connection);
			$this->connectionState = SOCKET_DISCONNECTED;
			return true;
		}
		return false;
	}
	function sendCmd($command){
		$result = fwrite($this->connection, $command, strlen($command));
		//debug::drawdebug($result,"return");
		return $result;
	}
	function readCmd($length = 2048){
		if($this->validateConnection()){
			return fread($this->connection, $length);
		}
	}
	public function __destruct() {
		$this->disconnect();
	}
	public function getErrors($strmode = "string"){
		return $this->error->getErrors($strmode);
	}
}

class reset_pass{

	public $intUid = 0;
	public $strEmail = "";
	public $strPass = "";
	public $boolOk = true;
	public $objConection = false;

	public function __construct($arrData,$objCon) {
		$this -> objConection = $objCon;
		if(isset($arrData["uid"])){
			$this -> intUid = intval($arrData["uid"]);
			$this -> boolOk = true;
		}
        elseif(isset($arrData["user"])){
			$arrData["user"] = user_input_delmagic(db_escape($arrData["user"]));
			$this -> intUid = sqlGetValueFromKey("SELECT uid FROM wt_users WHERE name = '{$arrData["user"]}'",false,false,true,$this -> objConection);
			if(!$this -> intUid){
				$this -> boolOk = false;
			}
			else{
				$this -> boolOk = true;
			}
		}
		else{
			$this -> boolOk = false;
		}
		$this -> strEmail = db_escape($arrData["mail"]);
	}

	public function reset(){
		if($this -> boolOk){
			$intCount = sqlGetValueFromKey("SELECT COUNT(uid) FROM wt_users WHERE uid = {$this -> intUid} AND active = 'Y' AND email = '{$this -> strEmail}'",false,false,true,$this -> objConection);
			if(!$intCount){
				$this -> boolOk = false;
			}
			else{
				$this -> strPass = password::generate_humanpass();
				$strQuery = "UPDATE wt_users SET password = MD5('{$this -> strPass}'), uepassword = '{$this -> strPass}' WHERE uid = {$this -> intUid} AND active = 'Y' AND email = '{$this -> strEmail}'";
				db_query($strQuery,true,$this -> objConection);
				$this -> boolOk = true;
			}
		}
		return $this -> boolOk;
	}

	public function getPass(){
		return $this -> strPass;
	}

	public function sendCredenciales($strHtml = ""){
		if($this -> boolOk){
			$this -> strEmail = sqlGetValueFromKey("SELECT email FROM wt_users WHERE uid = {$this -> intUid} AND active = 'Y'",false,false,true,$this -> objConection);
			$strMesagge = (!empty($strHtml))?$strHtml:$this ->htmlToMail();
            $objMail = new AttachMailer($this->strEmail, "Reinicio de Contraseña", "");
            $objMail->setMessageHTML($strMesagge);
            $objMail->send();
			//@mail($this -> strEmail,"Reinicio de Contraseña",$strMesagge,$strHeaders);
		}
	}

	private function htmlToMail(){
		$strBaseURL = core_getBaseDir();
		$strUser = sqlGetValueFromKey("SELECT name FROM wt_users WHERE uid = {$this -> intUid}",false,false,true,$this -> objConection);
		$html = "";
		$html .= <<<EOD
                <table>
                    <tr>
                        <td colspan="2">Has solicitado que se envie tu contraseña</td>
                    </tr>
                    <tr>
                        <td><b>Link:</b></td>
                        <td>{$strBaseURL}</td>
                    </tr>
                    <tr>
                        <td><b>Usuario:</b></td>
                        <td>{$strUser}</td>
                    </tr>
                    <tr>
                        <td><b>Contraseña:</b></td>
                        <td>{$this -> strPass}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            Se recomienda que cuando ingreses a la pagina con tus nuevas credenciales,<br>
                            te dirigas a "Mi cuenta" para cambiar contraseña.
                        </td>
                    </tr>
                </table>
EOD;
		return $html;
	}
}

class response{
	/**
	 * @var singleton $instance
	 * @desc Singleton var
	 */
	private static $instance;
	/**
	 * Singleton pattern. Returns the same instance to all callers
	 *
	 * @return Socket
	 */
	private $Response = false;
	private $strMessage = "";
	private $valido = 0;
	private $arrDetail = array();
	public static function singleton($valido =0,$strMensaje = "",$arrDetail=array()) {
		if (self::$instance == null || !self::$instance instanceof response) {
			self::$instance = new response($valido,$strMensaje,$arrDetail);
		}
		return self::$instance;
	}
	public function __construct($valido=0,$strMensaje = "",$arrDetail=array()) {
		$this->setValido($valido);
		$this->setStrMessage($strMensaje);
		$this->setArrDetail($arrDetail);
	}
	function setStrMessage($strMessage) {
		$this->strMessage = $strMessage;
	}
	function setValido($valido) {
		$this->valido = $valido;
	}

	function setArrDetail($arrDetail) {
		$this->arrDetail = $arrDetail;
	}

	public function setResponse($valido,$strMensaje = "", $arrDetail=array(), $boolUseUTF8 = true){
		$this->setValido($valido);
		$this->setStrMessage($strMensaje);
		$this->setArrDetail($arrDetail);

		$this->Response = $this->arrDetail;
		$this->Response["valido"] = $this->valido;
		$this->Response["status"] = ($this->valido<=0)?"fail":"ok";
		$this->Response["razon"] = $this->strMessage;
		$this->Response["msj"] = $this->strMessage;
		if($boolUseUTF8)
			utf8_encode_array($this->Response);
	}

	public function getResponse($valido = 0,$strMensaje = "", $arrDetail=array(), $boolUseUTF8 = true,$boolPrintJson = false){
		if(($valido !== false)){
			$this->setResponse ($valido,$strMensaje,$arrDetail, $boolUseUTF8);
		}
		if($boolPrintJson){
			header('Content-Type: application/json');
			print json_encode($this->Response);
		}
		else{
			return $this->Response;
		}
	}

	public static function standard($valido = 0,$strMensaje = "", $arrDetail=array(),$boolUseUTF8 = true,$boolPrintJson = false){
		$response = array();
		$valido = intval($valido);
		$response = $arrDetail;
		$response["valido"] = $valido;
		$response["status"] = ($valido<=0)?"fail":"ok";
		$response["razon"] = $strMensaje;
		$response["msj"] = $strMensaje;
		if($boolUseUTF8)
			utf8_encode_array($response);
		if($boolPrintJson){
			header('Content-Type: application/json');
			print json_encode($response);
		}
		else{
			return $response;
		}
	}

}

class delete_dir{
	/**
	 * @var singleton $instance
	 * @desc Singleton var
	 */
	private static $instance;
	/**
	 * Singleton pattern. Returns the same instance to all callers
	 *
	 * @return Socket
	 */
	private $strPath = "";
	private $arrResponse = false;
	public static function singleton($strPath = "") {
		if (self::$instance == null || !self::$instance instanceof delete_dir) {
			self::$instance = new delete_dir($strPath);
		}
		return self::$instance;
	}
	public function __construct($strPath = "") {
		$this->setPath($strPath);
	}

	public function setPath($strPath){
		if(file_exists($strPath)){
			$this -> strPath = $strPath;
		}
		else{
			$this -> arrResponse = response::standard(0,"Directorio no existe");
		}
	}

	public function finally_removed(){
		$this -> arrResponse = response::standard(0,"Directorio no existe");
		if(!empty($this -> strPath)){
			$strCurPath = $this -> strPath;
			$arrPaths = array();
			$arrOutsidePaths = array();
			$arrDirObjects = array();
			$strCurOutsidePath = "";
			if ($objCurDirectory = @opendir($strCurPath)) {
				array_push($arrPaths, $strCurPath);
				array_push($arrOutsidePaths, $strCurOutsidePath);
				array_push($arrDirObjects, $objCurDirectory);
			}
			while (count($arrDirObjects)) {
				$strCurPath = array_pop($arrPaths);
				$strCurOutsidePath = array_pop($arrOutsidePaths);
				$objCurDirectory = array_pop($arrDirObjects);
				while (($objFile = readdir($objCurDirectory)) !== false) {
					if ($objFile == "." || $objFile == ".." || $objFile == ".svn")
						continue;

					if (is_dir("{$strCurPath}{$objFile}")) {
						array_push($arrPaths, $strCurPath);
						array_push($arrOutsidePaths, $strCurOutsidePath);
						array_push($arrDirObjects, $objCurDirectory);

						$strCurPath .= "{$objFile}/";
						$strCurOutsidePath .= "{$objFile}/";
						$objCurDirectory = opendir($strCurPath);
					}
					else {
						unlink("{$strCurPath}{$objFile}");
					}
				}
				closedir($objCurDirectory);
				rmdir($strCurPath);
			}
			$this -> arrResponse = response::standard(1,"Directorio eliminado correctamente");
		}

		return $this -> arrResponse;
	}
}

use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use PHPMailer\PHPMailer\PHPMailer;

class AttachMailer{
    private $from, $pass, $to, $subject, $mess, $hash, $output, $html_mess, $file, $cc, $bcc, $boolPrintImage;
    private $documents = Array();
    private $headers = false;

	/**
	 *
	 * @param type $header
	 */
	function setHeader($header) {
		$this->headers = $header;
	}

	function setFrom ($strFrom)
    {
        $this->from = $strFrom;
    }

    function setPassword($strPassword)
    {
        $this->pass = $strPassword;
    }

	function setMessageHTML($html_message)
    {
        $this->html_mess = $html_message;
    }

    function setAttachFile($file)
    {
        $this->file = $file;
    }

    function __construct($_to, $_subject, $_mess, $cc = false, $bcc = false, $boolPrintImage = true)
    {
        $this->to = $_to;
        $this->subject = $_subject;
        $this->mess = $_mess;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->hash = md5(date('r', time()));
        $this->boolPrintImage = $boolPrintImage;
    }

	public function attachFile($url, $name = "")
    {
	    global $cfg;
        if (isset($cfg["core"]["SEND_MAIL_SMTP"])){
            $this->documents[$name] = $url;
        }
        else{
            $this->documents[$name] = $url;
        }
	}

	private function makeMessage(PHPMailer &$objMailer)
    {
        $strMessageReturn = "";
        if ($this->boolPrintImage){
            $arrResponse = [];
            $strQuery = ("SELECT title, path,position_Image,link FROM wt_Profile_Configuration_Correo WHERE allow = '1'");
            $qTMP = db_query($strQuery);
            while ($rTMP = db_fetch_assoc($qTMP)) {
                $arrResponse[$rTMP["position_Image"]] = $rTMP;
            }
            $strImage ="";
            $strImageInferior="";

            foreach ($arrResponse as $value) {
                $strTitle = str_replace(" ", "_", $value["title"]);
                $strPath = $value['path'];
                $positionImage = $value['position_Image'];
                $strlinkSocial = $value['link'];

                $objMailer->addEmbeddedImage($strPath, $strTitle);

                if ($positionImage == 'Borde_superior' and $strPath != "") {
                    $strImage .= <<<EOD
                                    <div>
                                        <img width="100%" height="100px" style="display: block; width: 74%; 
                                                height: auto; margin-left: 13%;" 
                                                src="cid:{$strTitle}" />
                                    </div>
EOD;
                }
                if ($positionImage == 'Borde_inferior' and $strPath != "") {
                    $strImageInferior .= <<<EOD
                                <div style='display: contents;'>
                                    <img width="100%" style="display: block; width: 74%; height: auto; margin-left: 13%;"
                                     src="cid:{$strTitle}" />
                                </div>
EOD;
                }
                if ($positionImage == "Footer" and $strPath != "" ){
                    $strImageInferior .= <<<EOD
                            <table cellpadding="0" align="left" cellspacing="0" style=" display: block;"  >
                                    <tr>
                                        <td>
                                            <a href='{$strlinkSocial}' target=\"_blank\">
                                               <img align="left" width="30"  style="padding: 5px; margin-left: 500%" 
                                                src="cid:{$strTitle}" />
                                            </a>
                                        </td
                                    </tr>
                            </table>
EOD;
                }
            }

            $strMessageReturn .= $strImage;
            $strMessageReturn .= <<<EOD
                <div style="text-align: center">
                    {$this->mess}
                    {$this->html_mess}                    
                </div>
EOD;
            $strMessageReturn .= $strImageInferior;
        }
        else{
            $strMessageReturn .= $this->mess;
            $strMessageReturn .= $this->html_mess;
        }
        return $strMessageReturn;
	}

	public function send()
    {
        global $cfg;
        if (!empty($cfg["core"]["SEND_MAIL_SMTP"])){
            $this->sendMailSMTP();
        }
        else{
            if(empty($this->from)){
                $strDomain = core_getBaseDomain();
                $from = "info@{$strDomain}";
                $this->setFrom($from);
            }
            $this->sendMail();
        }
	}

	public function sendMailSMTP()
    {
        global $cfg;
        if (empty($cfg["core"]["mailToSendEmail"])){
            return false;
        }
        if (empty($cfg["core"]["passToMailToSendEmail"])){
            return false;
        }


        $mailToSend = ( !empty($cfg["core"]["mailToSendEmail"]) ) ? $cfg["core"]["mailToSendEmail"] : $this->from;
        $mailToSend = trim($mailToSend);
        $strPassword = ( !empty($cfg["core"]["passToMailToSendEmail"]) )?$cfg["core"]["passToMailToSendEmail"]:$this->pass;
        $mail = new PHPMailer();
        //Luego tenemos que iniciar la validación por SMTP:
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = "smtp.gmail.com"; // SMTP a utilizar. Por ej. smtp.elserver.com
        $mail->Username = "{$mailToSend}"; // Correo completo a utilizar
        $mail->Password = "{$strPassword}"; // Contraseña
        $mail->Port = 587; // Puerto a utilizar
        $mail->isHTML(true);

        $mail->setFrom($mailToSend);
        $mail->addAddress($this->to);
        $mail->Subject = $this->subject;

        if(!empty($this->cc) && is_array($this->cc)){
            foreach($this->cc AS $emailcc){
                $mail->addCc("{$emailcc}");
            }
        }

        if(!empty($this->bcc) && is_array($this->bcc)){
            foreach ($this->bcc AS $emailbcc){
                $mail->addBcc("{$emailbcc}");
            }
        }

        if(!empty($this->documents)){
            foreach($this->documents AS $file){
                $mail->addAttachment("{$file}");
            }
        }

        $mail->msgHTML($this->makeMessage($mail));

        return $mail->send();
    }

	public function sendMail()
    {
        $mail = new PHPMailer();
        $mail->setFrom($this->from);
        $mail->addAddress($this->to);
        $mail->Subject = $this->subject;
        $mail->isHTML(true);
        $mail->msgHTML($this->makeMessage($mail));

        if(!empty($this->cc) && is_array($this->cc)){
            foreach($this->cc AS $emailcc){
                $mail->addCC($emailcc);
            }
        }

        if(!empty($this->bcc) && is_array($this->bcc)){
            foreach ($this->bcc AS $emailbcc){
                $mail->addBCC($emailbcc);
            }
        }

        if(!empty($this->documents)){
            foreach($this->documents AS $filename => $file){
                $mail->addAttachment($file, $filename);
            }
        }

        return $mail->send();
    }

}

class Encrypter {

	private static $strKey = "dublin";

	private static function set_key($char){
		self::$strKey = $char;
	}

	public static function encrypt ($input,$key = "") {
		if(!empty($key))self::set_key($key);
		$output = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(self::$strKey), $input, MCRYPT_MODE_CBC, md5(md5(self::$strKey))));
		return $output;
	}

	public static function decrypt ($input,$key = "") {
		if(!empty($key))self::set_key($key);
		$output = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(self::$strKey), base64_decode($input), MCRYPT_MODE_CBC, md5(md5(self::$strKey))), "\0");
		return $output;
	}

}

class ping{
	static $_instance;

	public static function getInstance($objClass = false) {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($objClass);
		}
		return self::$_instance;
	}
	static function to($host,$port = 80,$timeout = 6){
		$fsock = @fsockopen($host, $port, $errno, $errstr, $timeout);
		$boolOK = (!$fsock)?false:true;
		if($boolOK)fclose($fsock);
		return $boolOK;
	}

	static function toHttp($url,$timeout = 6){
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data=curl_exec($ch);
		$httpcode=curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpcode>=200 && $httpcode<300){
		    return true;
		}
		else{
		    return false;
        }
    }
}

class GCM {
	//private $URL = "https://gcm-http.googleapis.com/gcm/send";
	private $URL = "https://fcm.googleapis.com/fcm/send";
	private $headers = false;

	/**
	 *
	 * @param String $strKeys uno o varios keys
	 */
	public function __construct(){
	}

	private function getCGM($strId){
	    $strQueryAdd = "";
	    if($strId){
	        $strQueryAdd .= "WHERE US.uid IN ($strId)";
        }
	    $strQuery = "SELECT US.uid, WS.token_gcm FROM wt_users AS US
                     JOIN wt_webservices_devices AS WS
                     ON WS.userid = US.uid
                     {$strQueryAdd}";
	    $arrTokenDB = db_query($strQuery);
	    $arrTokenCgm = [];
	    foreach($arrTokenDB as $token){
	        $arrTokenCgm[] = $token["token_gcm"];
        }
	    return $arrTokenCgm;
    }

    public function sendNotification($strApiKey, $arrIds, $strTitle, $strBody)
    {
        $arrTokens = $this->getCGM($arrIds);

        /*lanza error si se pasa un caracter especiales*/

        $fields = [
            'notification' => [
                "alert" => "Tienes una nueva notificacion",
                "title" => $strTitle,
                "body" => $strBody,
                "vibrate" => 1,
                "show_in_foreground" => "true",
                "content_available" => "true",
                "sound" => "default",
                "color" => "#000000",
                "visibility" => "public",
                "priority" => "high"
            ],
            "data" => [
                "objeto" => $strTitle,
                "cuerpo" => $strBody,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                "priority" => "high"
            ]
        ];
        utf8_encode_array($fields);

        $this->headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$strApiKey
        );

        $arrResponses = [];
        foreach($arrTokens as $token){
            $fields["to"] = $token;
            $arrResponses[] = $this->sendPushNotifications($fields);
        }
        return $arrResponses;
    }

	private function sendPushNotifications($fields){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
}

class global_function {

	static $MINUTOS_X_HORA = 60;
	static $SEGUNDOS_X_MINUTO = 60;
	static $HORAS_X_DIA = 24;

	public static function myTruncate($string, $limit, $break = ".", $pad = "?") {
		// return with no change if string is shorter than $limit
		if (strlen($string) <= $limit)
			return $string;
		// is $break present between $limit and the end of the string?
		if (false !== ($breakpoint = strpos($string, $break, $limit))) {
			if ($breakpoint < strlen($string) - 1) {
				$string = substr($string, 0, $breakpoint) . $pad;
			}
		}
		return $string;
	}

	public static function formatDate($strDate, $boolIncludeTime = false) {
		$arrDate = explode("-", $strDate);
		$arrMeses[1] = "enero";
		$arrMeses[2] = "febrero";
		$arrMeses[3] = "marzo";
		$arrMeses[4] = "abril";
		$arrMeses[5] = "mayo";
		$arrMeses[6] = "junio";
		$arrMeses[7] = "julio";
		$arrMeses[8] = "agosto";
		$arrMeses[9] = "septiembre";
		$arrMeses[10] = "octubre";
		$arrMeses[11] = "noviembre";
		$arrMeses[12] = "diciembre";

		$arrDate[1] = intval($arrDate[1]);

		$arrDateHour = explode(" ", $arrDate[2]);
		$strReturn = "{$arrDateHour[0]} de {$arrMeses[$arrDate[1]]} del {$arrDate[0]}";
		if ($boolIncludeTime) {
			$strReturn .= ", a las {$arrDateHour[1]}.";
		}
		return $strReturn;
	}

	public function getHorarios($sinMin = 0, $sinMax = 0, $boolFormatAMPM = false) {
		$sinMax = ($sinMax == 0) ? self::$HORAS_X_DIA : $sinMax;
		$sinMin = str_replace(":", ".", $sinMin);
		$sinMax = str_replace(":", ".", $sinMax);

		$horas = 0;
		$horasMasc = 0;
		$minutos = 0;
		$strMasc = "AM";
		$arrReturn = array();
		if ($sinMin == 0) {
			if ($boolFormatAMPM)
				$arrReturn["00:00"] = "00:00 {$strMasc}";
			else
				$arrReturn["00:00"] = "00:00";
		}

		$horasConstante = $sinMax;
		$MinutosConstante = 30;
		$intMinutosXHora = self::$MINUTOS_X_HORA;

		$i = 0;
		$boolContinue = true;
		$boolFormat12 = $boolFormatAMPM;
		while (($boolContinue)) {
			$i++;
			$minutos += $MinutosConstante;
			if ($minutos >= $intMinutosXHora) {
				$horas++;
				$horasMasc++;
				$minutos -= $intMinutosXHora;
				if ($horas > 12 && ($boolFormat12)) {
					$boolFormat12 = false;
					$horasMasc = 1;
					$strMasc = "PM";
				}
				if (($horas >= $horasConstante))
					break;
			}
			else {
				if (($horas >= $horasConstante))
					break;
			}
			$horasMasc = str_pad($horasMasc, 2, "0", STR_PAD_LEFT);
			$horas = str_pad($horas, 2, "0", STR_PAD_LEFT);
			$minutos = str_pad($minutos, 2, "0", STR_PAD_LEFT);

			if (floatval("{$horas}.{$minutos}") >= floatval($sinMin)) {
				if ($boolFormatAMPM)
					$arrReturn["{$horas}:{$minutos}"] = "{$horasMasc}:{$minutos} {$strMasc}";
				else
					$arrReturn["{$horas}:{$minutos}"] = "{$horas}:{$minutos}";
			}
		}

		return $arrReturn;
	}

	public static function multiplicaTime($strTime, $intExponte = 1, $boolIncludeSec = true) {
		$arrTime = explode(":", $strTime);

		$intHoras = (!empty($arrTime[0])) ? $arrTime[0] : 0;
		$intMinutos = (!empty($arrTime[1])) ? $arrTime[1] : 0;
		$intSegundos = (!empty($arrTime[2])) ? $arrTime[2] : 0;

		$intHoras = ($intHoras * $intExponte);
		$intMinutos = ($intMinutos * $intExponte);
		$intSegundos = ($intSegundos * $intExponte);

		if ($intSegundos >= self::$SEGUNDOS_X_MINUTO) {
			$boolOK = true;
			while ($boolOK) {
				if ($intSegundos < self::$SEGUNDOS_X_MINUTO)
					break;
				$intMinutos++;
				$intSegundos -= self::$SEGUNDOS_X_MINUTO;
			}
		}
		if ($intMinutos >= self::$MINUTOS_X_HORA) {
			$boolOK = true;
			while ($boolOK) {
				if ($intMinutos < self::$MINUTOS_X_HORA)
					break;
				$intHoras++;
				$intMinutos -= self::$MINUTOS_X_HORA;
			}
		}
		$intHoras = str_pad($intHoras, 2, "0", STR_PAD_LEFT);
		$intMinutos = str_pad($intMinutos, 2, "0", STR_PAD_LEFT);
		$intSegundos = str_pad($intSegundos, 2, "0", STR_PAD_LEFT);
		$strReturn = "{$intHoras}:{$intMinutos}";
		if ($boolIncludeSec)
			$strReturn .= ":{$intSegundos}";

		return $strReturn;
	}

	public static function calculo_dif_fechas($dia1, $dia2, $mes1, $mes2, $ano1, $ano2) {
		$timestamp1 = mktime(0, 0, 0, $mes1, $dia1, $ano1);
		$timestamp2 = mktime(4, 12, 0, $mes2, $dia2, $ano2);

		$segundos_diferencia = $timestamp1 - $timestamp2;
		$dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
		$dias_diferencia = $dias_diferencia;
		$dias_diferencia = floor($dias_diferencia) + 1;
		return $dias_diferencia;
	}

	public static function calcular_tiempo_trasnc($hora1, $hora2) {
		$separar[1] = explode(':', $hora1);
		$separar[2] = explode(':', $hora2);

		$total_minutos_trasncurridos[1] = ($separar[1][0] * 60) + $separar[1][1];
		$total_minutos_trasncurridos[2] = ($separar[2][0] * 60) + $separar[2][1];
		$total_minutos_trasncurridos = $total_minutos_trasncurridos[1] - $total_minutos_trasncurridos[2];
		if ($total_minutos_trasncurridos <= 59)
			return($total_minutos_trasncurridos . ' Minutos');
        elseif ($total_minutos_trasncurridos > 59) {
			$HORA_TRANSCURRIDA = round($total_minutos_trasncurridos / 60);
			if ($HORA_TRANSCURRIDA <= 9)
				$HORA_TRANSCURRIDA = '0' . $HORA_TRANSCURRIDA;
			$MINUITOS_TRANSCURRIDOS = $total_minutos_trasncurridos % 60;
			if ($MINUITOS_TRANSCURRIDOS <= 9)
				$MINUITOS_TRANSCURRIDOS = '0' . $MINUITOS_TRANSCURRIDOS;
			return ($HORA_TRANSCURRIDA . ':' . $MINUITOS_TRANSCURRIDOS);
		}
	}

	/**
	 * Elimina los slashes de un user input segun la configuracion de magic_quotes_gpc.  DEBE ser utilizada en TODOS los inputs.
	 *
	 * @param string $strInput
	 * @param boolean $boolUTF8Decode
	 * @return string
	 */
	public static function user_magic_quotes($strInput, $boolUTF8Decode = false) {

		$strInput = trim($strInput);
		if (get_magic_quotes_gpc()) {
			$strInput = stripslashes($strInput);
		}
		/*Esto arruina los gets... pero sirve con los posts de ajax...*/
		if ($boolUTF8Decode && mb_detect_encoding($strInput) == "UTF-8") {
			$strInput = utf8_decode($strInput);
		}
		return $strInput;
	}

	/**
	 * Encodea con utf8 los strings dentro de un array, ojo que es recursivo.
	 *
	 * @param array $arrToEncode
	 * @return boolean
	 */
	public static function utf8_encode_array(&$arrToEncode) {
		utf8_encode_array($arrToEncode);
		return true;
	}

	/**
	 * Para des encodear de utf8 los strings dentro de un array, ojo que es recursiva
	 *
	 * @param array $arrToDecode
	 */
	public static function utf8_decode_array(&$arrToDecode) {
		utf8_decode_array($arrToDecode);
		return true;
	}

	public static function CSV_prepararLinea($arrCampos, $boolAgregarNewLine = true, $boolWindowsNewLine = false) {
		return CSV_prepararLinea($arrCampos, $boolAgregarNewLine, $boolWindowsNewLine);
	}

	function getCacheHeaders($intHoras, $intLastModified, $strContentType, $strPragma = "private") {
		header("Pragma: {$strPragma}");

		$expires = floor(60*60*$intHoras); // El tiempo de expiracion en segundos

		if ($intLastModified >0) {
			$arrApacheHeaders = apache_request_headers();
			if (isset($arrApacheHeaders["If-Modified-Since"])) {
				// Si esta el parametro If-Modified-Since es porque estoy validando una fecha
				$intIfModifiedSince = strtotime($arrApacheHeaders["If-Modified-Since"]); //Combierto esta fecha a timestamp
				if ($intIfModifiedSince >= $intLastModified) {
					// Si el cache tiene un archivo que no ha cambiado segun el parametro de la fecha de modificacion, devuelvo un 304 not modified
					$strProtocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
					header("Cache-Control: maxage=".$expires);
					header("Expires: " . gmdate("D, d M Y H:i:s", time()+$expires) . " GMT");
					header($strProtocol . " 304 Not Modified");
					die();
				}
			}
			// Si los archivos cambiaron desde la ultima descarga, los bajo de nuevo
			$strLastModified = gmdate("D, d M Y H:i:s", $intLastModified);
		}

		header("Cache-Control: maxage=".$expires);
		if ($intLastModified > 0) header("Last-Modified: " . $strLastModified . " GMT");
		header("Expires: " . gmdate("D, d M Y H:i:s", time()+$expires) . " GMT");
		header("Content-Type: {$strContentType}");
	}

	public static function core_validateEmailAddress($strEMail) {
		$strEMail = trim($strEMail);

		if (empty($strEMail))
			return false;

		return (preg_match("/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/i", $strEMail) == 1);
	}

	/**
	 * Imprime en pantalla un string pero con sus caracteres convertidos a HTML para que no haya problemas ni errores.
	 *
	 * @param unknown_type $strString
	 */
	public static function safeprint($strString, $boolPrint = true){
		if ($boolPrint) {
			print htmlspecialchars($strString);
		}
		else {
			return htmlspecialchars($strString);
		}
	}

	/**
	 * Limpia los parametros que vengan del cliente pero OJO, tambien hace el db_escape, esto se debe correr para registrarlos o utilizarlos en base de datos pero no entre interfases.
	 *
	 * @param type $arrParam
	 * @param type $strTerm
	 * @param type $default
	 * @param type $boolUTF8
	 * @return type
	 */
	public static function getParam(&$arrParam , $strTerm, $default = "", $boolUTF8 = false, $boolCheckParam = true) {
		if (!empty($arrParam[$strTerm])) {
			if($boolCheckParam){
				if (is_int($arrParam[$strTerm]))
					$arrParam[$strTerm] = intval($arrParam[$strTerm]);
				else if (is_float($arrParam[$strTerm]))
					$arrParam[$strTerm] = floatval($arrParam[$strTerm]);
				else if (is_string($arrParam[$strTerm]))
					$arrParam[$strTerm] = db_escape(global_function::user_magic_quotes($arrParam[$strTerm],$boolUTF8));
				else
					$arrParam[$strTerm] = db_escape(global_function::user_magic_quotes($arrParam[$strTerm],$boolUTF8));
			}
			return $arrParam[$strTerm];
		}
		return $default;
	}

	public static function getHeaders($strType = "json", $strPragma = "private"){
		$strContentType = "";
		if($strType == "json"){
			$strContentType = "aplication/json";
		}
		else if($strType == "xml"){
			$strContentType = "text/xml";
		}
		else if($strType =="csv"){
			$strContentType = "text/csv";
		}
		else if($strType == "html"){
			$strContentType = "text/html; charset=iso-8859-1";
		}
		else{
			$strContentType = "text/html; charset=iso-8859-1";
		}
		header("Pragma: {$strPragma}");
		header("Content-Type: {$strContentType}");
	}

	public static function checkUptoDateCatalogue($strDate, $strTime, $strInventario) {
		$boolOK = false;
		if (!empty($strDate) && !empty($strTime)) {
			$arrDate = explode("-", $strDate);
			if (checkDate($arrDate[1], $arrDate[2], $arrDate[0])) {
				$strQuery = "SELECT IF ('{$strDate}' < fecha ,
                                        'TRUE',
                                        IF('{$strDate}' = fecha,
                                            IF('{$strTime}' < hora, 'TRUE', 'FALSE'),
                                        'FALSE')
                                    ) AS last_update
                            FROM wt_catalogos_last_update
                            WHERE table_name = '{$strInventario}'";
				$strChanges = sqlGetValueFromKey($strQuery, true,false,true);
				if ($strChanges == "TRUE")
					$boolOK = true;
			}
		}
		return $boolOK;
	}

	public static function setUptoDateCatalogue($strCatalogue){
		$strQuery = "REPLACE wt_catalogos_last_update (table_name, fecha, hora) VALUES('{$strCatalogue}', CURRENT_DATE(), CURRENT_TIME())";
		db_query($strQuery);
	}

	public static function getUpToDateCatalogue($strCatalogue){
		$strQuery = "SELECT table_name AS 'catalogue', fecha, hora FROM wt_catalogos_last_update WHERE table_name = '{$strCatalogue}'";
		return sqlGetValueFromKey($strQuery);
	}

	public static function scriptingSupportBrowsers(){
		$boolScript = false;
		$navigator = $_SESSION["wt"]["browser"]["detail"]["browser"];
		$versionNav = $_SESSION["wt"]["browser"]["detail"]["IEVer"];
		if($navigator == "Google Chrome" && $versionNav < 44.0){ /* edge, opera y chrome */
			$boolScript = false;
		}
		else if($navigator == "Mozilla Firefox" && $versionNav < 39.0){
			$boolScript = false;
		}
		else if($navigator == "Apple Safari"){
			$boolScript = false;
		}
		else if($navigator == ""){ /* internet explorer */
			$boolScript = false;
		}
		else{
			$boolScript = true;
		}
		return $boolScript;
	}

	public static function clearBrowserCache() {
		header("Pragma: no-cache");
		header("Cache: no-cache");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 10 May 1998 05:00:00 GMT");
	}

    public static function unique_multidim_array($array, $key) {
		$temp_array = array();
		$i = 0;
		$key_array = array();

		foreach($array as $val) {
			if (!in_array($val[$key], $key_array)) {
				$key_array[$i] = $val[$key];
				$temp_array[$i] = $val;
			}
			$i++;
		}
		return $temp_array;
	}

	public static function buildTree($arrDiagestData, $termKey,$strField  = "father")
	{
		$new = array();
		foreach ($arrDiagestData as $a){
			$new[$a[$strField]][] = $a;
		}

		return self::createTree($new, $new[0],$termKey);
	}

	public static function createTree(&$list, $parent, $termKey)
	{
		$tree = array();
		foreach ($parent as $k=>$l){
			if(isset($list[$l["{$termKey}"]])){
				$l['children'] = self::createTree($list, $list[$l["{$termKey}"]],$termKey);
			}
			$tree[] = $l;
		}
		return $tree;
	}

	public static function createLink($strModule, $strObject, $arrayDetail = false)
    {
        $strReturn = "adm_main.php?mde={$strModule}&wdw={$strObject}";
        if(is_array($arrayDetail)){
            foreach($arrayDetail AS $key => $value){
	            $strReturn .= "&{$key}={$value}";
            }
        }
        return $strReturn;
    }
}

function convertStringToHtmlCharacters($strConvert)
{
    $arrCharacters = array(
        "Á" => "&Aacute;",
        "á" => "&aacute;",
        "É" => "&Eacute;",
        "é" => "&eacute;",
        "Í" => "&Iacute;",
        "í" => "&iacute;",
        "Ó" => "&Oacute;",
        "ó" => "&oacute;",
        "Ú" => "&Uacute;",
        "ú" => "&uacute;",
    );
    foreach ($arrCharacters AS $key => $value){
        $strConvert = str_replace("{$key}","{$value}", $strConvert);
    }
    return $strConvert;
}

/*Funciones viejas que sirven en modulos de empresas*/
function validate_and_check_form(){
	jquery_includeLibrary("notice");
	?>
    <style type="text/css">
        .AlertValidateDialogDiv{
            margin: 10px;
            padding: 10px;
            -moz-border-radius: 10px; /* Firefox*/
            -ms-border-radius: 10px; /* IE 8.*/
            -webkit-border-radius: 10px; /* Safari,Chrome.*/
            border-radius: 10px; /* El estándar.*/
            vertical-align: top;
        }
    </style>
    <script type="text/javascript">
        function validate_and_check_form(strFormId, boolSubmit, strColorNotifyInput, strColorTextAlert, intDurationAlert, strColorBackgroundNotify){
            /*                                                                  //
            EXPLICO VARIABLES QUE LE MANDO A MI FUNCION
                strFormId = id del formulario, div, tabla u objeto donde se encuentren contenidos mis inputs, textarea, radios ó checkbox a validar.
                boolSubmit = Puede venir en false ó true. Si viene en true quiere decir que si todo esta bien validado voy a hacer submit de mi form. (Obviamente solo la mando si es un form el que valido)
                strColorNotifyInput = El color del contorno que se agregan a los objetos que estan mal llenos por el usuario. Se manda como color Hexadecimal
                boolUseJqueryNotice = Este determina que tipo de alerta se va a utilizar, si se usa el jquery notice o un dialog...
                strColorTextAlert = Este es el color del texto que se muestra en la alerta

            TIPOS DE DATOS DEFINIDOS (LOS QUE VALIDA), (necesita en el objeto la etiqueta de vtype)
                Automaticamente si el input se pide para validacion, se valida si va lleno.
                int = entero
                decimal = decimal
                date = fechas
                mail = valida correo electronico

            ETIQUETAS QUE SE USAN EN EL HTML (DENTRO DEL INPUT PARA CONFIGURAR LA VALIDADA)
                validate="true", este indica si el input se valida ó no. !importante
                vtype="", este indica con que tipo de dato se debe validar el input u objeto, pueden ser los tipos de datos comentados arriba
                vmessage="", Mensaje personalizado para cuando se llene mal
                vmessaje_vacio="", mensaje personalizado para cuando este vacio
                vtarget = "", esta etiqueta se utiliza para indicar en que input u objeto saldra la notificacion. Esto se hizo por si se queria validar un hidden por ejemplo, pero notificarlo en otro objeto.(notificarlo me refiero al resplandor del input)
            */

            if(!strFormId)strFormId="";
            if(!boolSubmit)boolSubmit=false;
            if(typeof(strColorNotifyInput) == "undefined" || strColorNotifyInput == "")strColorNotifyInput="#C10000";
            if(typeof(strColorTextAlert) == "undefined" || strColorTextAlert == "")strColorTextAlert="white";
            if(typeof(strColorBackgroundNotify) == "undefined" || strColorBackgroundNotify == "")strColorBackgroundNotify="error";
            if(typeof(intDurationAlert) == "undefined" || intDurationAlert == "")intDurationAlert=5000;
            boolCheckErrorsInForm = true;

            if(strFormId != ""){
                arrErrorsCampos = {};
                $("#"+strFormId+" input, #"+strFormId+" textarea, #"+strFormId+" select").each(function(intKey){
                    strTypeAction = "";
                    if($(this).attr("validate") && $(this).attr("validate") != "false"){
                        ($(this).attr("vtype"))?strInputDataType = $(this).attr("vtype"):strInputDataType="string";
                        ($(this).val() && $(this).val() != "")?strInputValue = $(this).val():strInputValue="";
                        if($(this).attr("vtarget") && $(this).attr("vtarget") != ""){
                            strVtarget = $(this).attr("vtarget");
                            strVtarget = $(strVtarget);
                        }else{
                            strVtarget = $(this);
                        }

                        if(strInputDataType != ""){

                            if($(this).attr("vmessage") && $(this).attr("vmessage") != ""){
                                CustomText = $(this).attr("vmessage");
                            }else{
                                CustomText = "empty";
                            }
                            if($(this).attr("vmessaje_vacio") && $(this).attr("vmessaje_vacio") != ""){
                                CustomTextEmpty = $(this).attr("vmessaje_vacio");
                            }else{
                                CustomTextEmpty = "empty";
                            }
                            if(this.type == undefined)
                                this.type = "";

                            if((this.type).toLowerCase() == "checkbox"){
                                if(!$(this).is(":checked")){
                                    strInputValue = "";
                                    boolOk = false;
                                }else{
                                    boolOk=true;
                                    strInputValue = "lleno";
                                }
                                boolCheckErrorsInForm = goNotifyArray(boolOk,intKey,strVtarget,strColorNotifyInput);
                            }
                            else if((this.tagName).toLowerCase() == "select"){
                                if(!$(this).attr("multiple")){
                                    boolBadInput = validateDataType($(this).attr("vtype"),$(this).val());
                                    boolCheckErrorsInForm = goNotifyArray(boolBadInput,intKey,strVtarget,strColorNotifyInput);
                                }
                                else{
                                    strInputValue = this.value;
                                    boolOk = (strInputValue.length != 0);
                                    boolCheckErrorsInForm = goNotifyArray(boolOk,intKey,strVtarget,strColorNotifyInput);
                                }
                            }
                            else if((this.type).toLowerCase() == "radio"){
                                if(this.name == undefined)
                                    this.name = "";

                                if(this.name != ""){
                                    var boolOk = false;
                                    strInputValue = "";
                                    $("#" + strFormId + " input[name='" + this.name + "']").each(function (){
                                        if($(this).is(":checked")){
                                            boolOk = true;
                                            if(strInputValue == ""){
                                                strInputValue = "lleno";
                                            }
                                        }
                                    });
                                    boolCheckErrorsInForm = goNotifyArray(boolOk,intKey,strVtarget,strColorNotifyInput);
                                }
                            }
                            else{
                                boolBadInput = validateDataType($(this).attr("vtype"),$(this).val());
                                boolCheckErrorsInForm = goNotifyArray(boolBadInput,intKey,strVtarget,strColorNotifyInput);
                            }
                        }
                    }
                    else if($(this).attr("validate") && $(this).attr("validate") == "false"){
                        showInputsBadInDOM($(this),"quit");
                    }
                })
                if(boolCheckErrorsInForm === false){
                    showAlertForBadInputs(arrErrorsCampos,strColorTextAlert,intDurationAlert,strColorBackgroundNotify);
                    return false;
                }else{
                    if(boolSubmit === true)
                        $("#"+strFormId).submit();
                    else
                        return true;
                }
            }
        }

        function goNotifyArray(boolBadInput,intKey,strVtarget,strColorNotifyInput){
            if(boolBadInput === false){
                if(typeof(arrErrorsCampos["bad"]) == "undefined")arrErrorsCampos["bad"] = {};
                if(typeof(arrErrorsCampos["bad"][intKey]) == "undefined")arrErrorsCampos["bad"][intKey] = {};
                arrErrorsCampos["bad"][intKey]["CustomText"] = CustomText;
                arrErrorsCampos["bad"][intKey]["CustomTextEmpty"] = CustomTextEmpty;
                (strInputValue != "")?arrErrorsCampos["bad"][intKey]["empty"] = false:arrErrorsCampos["bad"][intKey]["empty"] = true;
                boolCheckErrorsInForm = false;
                showInputsBadInDOM(strVtarget,"show",strColorNotifyInput);
            }else{
                showInputsBadInDOM(strVtarget,"quit");
            }
            return boolCheckErrorsInForm;
        }

        function validateDataType(strDataType, strValue){
            //para todo, si retorno true lo dejo pasar, false no...
            if(strDataType == "int"){
                if((parseFloat(strValue) == parseInt(strValue)) && !isNaN(strValue)){
                    return true;
                }else{
                    return false;
                }
            }
            else if(strDataType == "decimal"){
                if (isNaN(strValue) || strValue.toString().indexOf(".") < 0) {
                    return false;
                }else{
                    return true;
                }
            }
            else if(strDataType == "phone"){
                var RegExPattern = /^[0-9]{8}$|^[0-9]{11}$/;
                if (strValue.match(RegExPattern)) {
                    return true;
                } else {
                    return false;
                }
            }
            else if(strDataType == "mail"){
                var RegExPattern = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
                if (strValue.match(RegExPattern)) {
                    return true;
                } else {
                    return false;
                }
            }
            else if(strDataType == "date"){
                intDay = 0;
                intMonth = 0;
                intYear = 0;
                boolCheck = false;
                arrDate = strValue.split("-");
                if(arrDate.length == 3){
                    intDay = arrDate[0];
                    intMonth = arrDate[1];
                    intYear = arrDate[2];
                    boolCheck = true;
                }
                else{
                    arrDate = strValue.split("/");
                    if(arrDate.length == 3){
                        intDay = arrDate[0];
                        intMonth = arrDate[1];
                        intYear = arrDate[2];
                        boolCheck = true;
                    }
                }
                if(intDay.length == 4 && boolCheck == true){
                    intYear = arrDate[0];
                    intMonth = arrDate[1];
                    intDay = arrDate[2];
                    boolCheck = true;
                }
                if(boolCheck == true){
                    if(intYear >= 1990){
                        intDateCheck = boolCheckDate(intYear,intMonth,intDay)
                        if(intDateCheck == false){
                            return false;
                        }else{
                            return true;
                        }
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }
            else if(strDataType == "url"){
                var RegExPattern = /^(ht|f)tps?:\/\/(\w+([\.\-\w]+)?\.([a-z]{2,4})|(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}))(:\d{2,5})?(\/.*)?$/i
                if (strValue.match(RegExPattern)) {
                    return true;
                } else {
                    return false;
                }
            }
            else{
                if(strValue != 0)
                    return true;
                else
                    return false;
            }
        }

        function showAlertForBadInputs(arrErrorsCampos,strColorTextAlert,intDurationAlert,strColorBackgroundNotify){
            boolShowGeneralBadMessaje = false;
            boolShowGeneralEmptyMessaje = false;
            strMessaje = "<table>";
            $.each(arrErrorsCampos,function(intKey,ObjInputs){
                $.each(ObjInputs,function(intKey2,strNombre){
                    if(strNombre["empty"] == true && strNombre["CustomTextEmpty"] == "empty"){
                        //mensaje predeterminado para inputs vacios
                        if(boolShowGeneralEmptyMessaje == false){
                            strMessaje += "<tr>";
                            strMessaje += "<td style='font-size: 25px; color: "+strColorTextAlert+";'><b>&#8226;</b></td>";
                            strMessaje += "<td style='font-size:14px; padding-left: 10px; color: "+strColorTextAlert+";'>";
                            strMessaje += "Los campos marcados son obligatorios y no pueden estar vacios.";
                            strMessaje += "</td>";
                            strMessaje += "</tr>";
                            boolShowGeneralEmptyMessaje = true;
                        }
                    }
                    else if(strNombre["empty"] == true && strNombre["CustomTextEmpty"] != "empty"){
                        //mensaje custom para input vacios
                        strMessaje += "<tr>";
                        strMessaje += "<td style='font-size: 25px; color: "+strColorTextAlert+";'><b>&#8226;</b></td>";
                        strMessaje += "<td style='font-size:14px; padding-left: 10px; color: "+strColorTextAlert+";'>";
                        strMessaje += strNombre["CustomTextEmpty"]+".";
                        strMessaje += "</td>";
                        strMessaje += "</tr>";

                    }
                    else if(strNombre["empty"] == false && strNombre["CustomText"] == "empty"){
                        //mensaje predeterminado para inputs llenos
                        if(boolShowGeneralBadMessaje == false){
                            strMessaje += "<tr>";
                            strMessaje += "<td style='font-size: 25px; color: "+strColorTextAlert+";'><b>&#8226;</b></td>";
                            strMessaje += "<td style='font-size:14px; padding-left: 10px; color: "+strColorTextAlert+";'>";
                            strMessaje += "Por favor llene correctamente los campos.";
                            strMessaje += "</td>";
                            strMessaje += "</tr>";
                            boolShowGeneralBadMessaje = true;
                        }
                    }
                    else if(strNombre["empty"] == false && strNombre["CustomText"] != "empty"){
                        //mensaje custom para inputs llenos
                        strMessaje += "<tr>";
                        strMessaje += "<td style='font-size: 25px; color: "+strColorTextAlert+";'><b>&#8226;</b></td>";
                        strMessaje += "<td style='font-size:14px; padding-left: 10px; color: "+strColorTextAlert+";'>";
                        strMessaje += strNombre["CustomText"]+".";
                        strMessaje += "</td>";
                        strMessaje += "</tr>";
                    };
                })
            })
            strMessaje += "</table>";

            //si quiero usar el jquery notice...
            if(intDurationAlert > 1){
                jQuery.noticeAdd({
                    text: strMessaje,
                    type: strColorBackgroundNotify,
                    stay: false,
                    stayTime: intDurationAlert
                });
            }
            else{
                jQuery.noticeAdd({
                    text: strMessaje,
                    type: strColorBackgroundNotify,
                    stay: true
                });
            }
        }

        function showInputsBadInDOM(ObjInput,strAction,strColorNotifyInput){
            if(strAction == "show"){
                strColors = "0px 0px 5px 1px "+strColorNotifyInput;
                $(ObjInput).css({"box-shadow":strColors,"-webkit-box-shadow":strColors,"-webkit-box-shadow":strColors});
            }else if(strAction == "quit"){
                strColors = "0px 0px 0px 0px #FFFFFF";
                $(ObjInput).css({"box-shadow":strColors,"-webkit-box-shadow":strColors,"-webkit-box-shadow":strColors})
            }
        }
    </script>
	<?php
}

function jqueryNoticeAlert(){
	jquery_includeLibrary("notice");
	?>
    <script type="text/javascript">
        function jqueryNoticeAlert(strMessaje,strColorTextAlert, strColorBackgroundNotify, intDurationAlert){
            if(typeof(strMessaje) == "undefined" || strMessaje == "")strMessaje="";
            if(typeof(strColorTextAlert) == "undefined" || strColorTextAlert == "")strColorTextAlert="white";
            if(typeof(strColorBackgroundNotify) == "undefined" || strColorBackgroundNotify == "")strColorBackgroundNotify="error";
            if(typeof(intDurationAlert) == "undefined" || intDurationAlert == "")intDurationAlert=5000;
            strMessajeReady = "<div style='color:"+strColorTextAlert+";'>";
            strMessajeReady += strMessaje;
            strMessajeReady += "</div>";
            if(intDurationAlert > 0){
                jQuery.noticeAdd({
                    text: strMessajeReady,
                    type: strColorBackgroundNotify,
                    stay: false,
                    stayTime: intDurationAlert
                });
            }else{
                jQuery.noticeAdd({
                    text: strMessajeReady,
                    type: strColorBackgroundNotify,
                    stay: true
                });
            }

        }
    </script>
	<?php
}

function getHMLConfigVars($intEmpresa, $strModuleName = "", $strVarConfigName = ""){
	global $lang, $config, $cfg;

	$arrInfoVarinDB = array();
	$intPais = sqlGetValueFromKey("SELECT pais FROM wt_empresas WHERE cod = '{$intEmpresa}'");
	$intPais = intval($intPais);
	$strFilterModule = "";
	if($intPais != 0){
		if($strModuleName != ""){
			$strFilterModule = "AND module_name = '{$strModuleName}'";
		}
		$strQuery = "SELECT var,value,module_name FROM wt_hml_config_vars WHERE pais_id = '{$intPais}' {$strFilterModule}";
		$qTMP = db_query($strQuery);
		while($rTMP = db_fetch_array($qTMP)){
			$arrInfoVarinDB[$rTMP["module_name"]][$rTMP["var"]] = $rTMP["value"];
			if(isset($cfg[$rTMP["module_name"]][$rTMP["var"]])){
				$cfg[$rTMP["module_name"]][$rTMP["var"]] = $rTMP["value"];
			}
		}
		if($strVarConfigName == ""){
			return $arrInfoVarinDB;
		}
		else{
			if(isset($arrInfoVarinDB[$strModuleName][$strVarConfigName])){
				return $arrInfoVarinDB[$strModuleName][$strVarConfigName];
			}
			else{
				return false;
			}
		}
	}
	else{
		return false;
	}
}

function GetHotKey($Action)
{
	global $HotKey;
	if (isset($HotKey[$Action])) {
		return ("accessKey=\"{$HotKey[$Action]}\" title=\"Alt+{$HotKey[$Action]}\"");
	}
	else {
		return "";
	}
}

function callAPI($method, $url, $data = false, $arrOpts = false)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	if($method == "POST") {
	    curl_setopt($curl, CURLOPT_POST, 1);
	    if($data){
		    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }
    else if($method == "PUT") {
	    curl_setopt($curl, CURLOPT_PUT, 1);
    }
    else{
        if ($data)
	        $url = sprintf("%s?%s", $url, http_build_query($data));
	}

	curl_setopt($curl, CURLOPT_URL, $url);
    if($arrOpts && is_array($arrOpts)){
	    curl_setopt($curl, CURLOPT_HTTPHEADER, $arrOpts);
    }
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	// Optional Authentication:
	//curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	//curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    /*Active for debug*/
	/*curl_setopt($curl, CURLOPT_VERBOSE, true);
	$verbose = fopen('temp', 'w+');
	curl_setopt($curl, CURLOPT_STDERR, $verbose);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);*/

	$result = curl_exec($curl);

	curl_close($curl);

	return $result;
}



/*
 * @return HTML-Text
 * @param string $name Nombre del control
 * @param mixed $selectedValue Valor seleccionado del listbox
 * @param string $strQuery Query para obtener el listado
 * @param mixed $key Nombre del valor a asignar a cada elemento del listbox
 * @param string $value Texto a agregar a cada elemento de listbox
 * @param boolean $boolAutoSubmit Indica si desea que al elegir un elemento el listbox genere un submit de la forma
 * @param string $formName Nombre de la forma si se desea que esto haga un autopost al momento de elegir alo
 * @param boolean $drawKey Indica si desea que se le agregue el key al texto de cada elemento del listbox
 * @param boolean $strTag String que define acciones extras en Jscript para el control. Si esta definido se ignora el autosubmit del $formName
 * @param mixed $zeroValue Valor utilizado para el primer elemento del select. Default 0
 * @param string $zeroString Valor utilizado para el primer elemento del select. Default 0
 * @param array $arrExtraValues vector que agrega estos elementos adicionales a los elemntos del select
 * @param string $strJavaFilterFunction nombre de la funcion que filtra/llena los elementos de listbox
 * @param string $strFilterField nombre del campo (del query) con el cual se filtran los datos.
 * @param string $strParentSelect nombre del select que genera el filtro (sirve para listar los valores iniciales)
 * @param string $strGroupField nombre del campo para agrupar las opciones con un optgroup.  Opcional
 * @desc Funcion que devuelve el string que dibuja un ListBox a partir de un query.
 */
function draw_general_query_filter_listbox($name, $selectedValue, $strQuery, $key, $value, $boolAutoSubmit = false, $formName="", $drawKey = false,
                                           $strTag='', $zeroValue=0, $zeroString="", $arrExtraValues = array(), $strJavaFilterFunction = "", $strFilterField = "", $strParentSelect = "",
                                           $strGroupField = "",$strFunctionScript="",$boolObject=false)
{
	$strResult = '';
	if (!empty($strJavaFilterFunction)) {
		$strResult.="<SCRIPT language='javascript'>\n";
		$strResult.="var arr{$name} = new Array();\n";
		$qArray = db_query($strQuery);
		if ($rArray = db_fetch_array($qArray)) {
			do {
				$strResult.= "arr{$name}['{$rArray[$key]}'] = new Array();\n";
				$strResult.= "arr{$name}['{$rArray[$key]}']['id'] = '{$rArray[$key]}' ;\n";
				$strResult.= "arr{$name}['{$rArray[$key]}']['filter'] = '{$rArray[$strFilterField]}' ;\n";
				$strResult.= "arr{$name}['{$rArray[$key]}']['value'] = '{$rArray[$value]}' ;\n";
			} while ($rArray = db_fetch_array($qArray));
		}
		db_free_result($qArray);
		$strResult.= "function {$strJavaFilterFunction}(filterValue){
						var optTMP;
						var i;
						document.{$formName}.{$name}.options.length = 0;
						for (i in arr{$name}){
							if (arr{$name}[i]['filter'] == filterValue){
								optTMP = document.createElement('option');
								  optTMP.value = arr{$name}[i]['id'];
								  optTMP.text = arr{$name}[i]['value'];

								if (arr{$name}[i]['id'] == '{$selectedValue}') {
									optTMP.selected = true;
								}
								else {
									optTMP.selected = false;
								}
								document.{$formName}.{$name}.options.add(optTMP);
								if (arr{$name}[i]['id'] == '{$selectedValue}') {
									optTMP.selected = true;
								}
								else {
									optTMP.selected = false;
								}
							}
						}
						return true;
					}";
		$strResult.="</SCRIPT>\n";
	}
	$strResult.= "<select class='field_selectbox' name='{$name}'";

	if(!empty($strFunctionScript)){  //mgonzalez 09/07/2015 - Con esta variable le asigno una funcion que quiera ejecutar en el onchange
		if($boolObject){
			$strResult.=" onChange='{$strFunctionScript}(this);' ";
		}else{
			$strResult.=" onChange='{$strFunctionScript}(this.value);' ";
		}
	}

	if ($strTag == "") {
		if ($boolAutoSubmit && $formName != "")
			$strResult.=" onChange='document.{$formName}.submit()'";
	}
	else {
		$strResult .= " {$strTag} ";
	}
	$strResult.=">\n";

	if (is_array($arrExtraValues) && count($arrExtraValues)) {
		$arrTMP = array();
		foreach ($arrExtraValues as $arrTMP["key"]=>$arrTMP["value"]) {
			$strKey = htmlSafePrint($arrTMP["key"], false);
			$strValue = htmlSafePrint($arrTMP["value"], false);

			$strResult.= "<option ";
			if ($selectedValue == $arrTMP["key"])
				$strResult.=" selected ";
			if ($drawKey)
				$strResult.= "value=\"" . $strKey . "\">" . $strKey . "|" . $strValue . "</option>\n";
			else
				$strResult.= "value=\"" . $strKey . "\">" . $strValue . "</option>\n";
		}
	}
	else {
		$strResult.="<option value=\"{$zeroValue}\" style='font-weight:bold;'>{$zeroString}</option>\n";
	}

	$boolUseGroup = !empty($strGroupField);
	$strLastGroup = "";

	if (empty($strJavaFilterFunction)) {
		$qTMP = db_query($strQuery);
		if ($rTMP = db_fetch_array($qTMP)) {
			do {
				if ($boolUseGroup && $strLastGroup != $rTMP[$strGroupField]) {
					$strLastGroup = $rTMP[$strGroupField];
					$strLastGroup_e = htmlSafePrint($strLastGroup, false);
					$strResult .= "<optgroup label='{$strLastGroup_e}' class='field_listbox'>\n";
				}
				$strResult.= "<option ";

				//2008-04-24 Quité esto:  || ( db_num_rows($qTMP) == 1 && !$arrExtraValues && !$boolAutoSubmit ) del IF de abajo porque elegia un valor cuando no habia nada seleccionado y esto confundia.

				$strKey = htmlSafePrint($rTMP[$key], false);
				$strValue = htmlSafePrint($rTMP[$value], false);

				if ($selectedValue == $rTMP[$key])
					$strResult.=" selected ";
				if ($drawKey)
					$strResult.= "value=\"" . $strKey . "\">" . $strKey . "|" . $strValue . "</option>\n";
				else
					$strResult.= "value=\"" . $strKey . "\">" . $strValue . "</option>\n";
			} while ($rTMP = db_fetch_array($qTMP));
		}
		$strResult.="</select>\n";
		db_free_result($qTMP);
	}
	if (!empty($strParentSelect)) {
		$strResult .= "<script language='javascript'>\n
						   {$strJavaFilterFunction}(document.{$formName}.{$strParentSelect}.value);
					   </script>\n
					   ";
	}
	return ($strResult);
}

function insertWebserviceTailLog($key, $datasent, $dataReceive, $table ="", $idTable=0){
	$strLog = "INSERT INTO wt_webservices_tail_received (description, data_sent, data_received, from_table, id_table, at_created)
				                         VALUES('{$key}','{$datasent}','{$dataReceive}', '{$table}', '{$idTable}', NOW())";
	db_query($strLog);
}

class tokens {
    private $arrData;
	private static $_instance;
	private $pinMode;
	private $strSessID;
	private $debug;

	/**
	 * @return mixed
	 */
	public function getStrSessID()
	{
		return $this->strSessID;
	}

	/**
	 * @param mixed $strSessID
	 */
	public function setStrSessID($strSessID)
	{
		$this->strSessID = $strSessID;
	}

	public function setLevelDebug($intLevel){
        if($this->debug){
	        $this->debug->setDebugLevel($intLevel);
        }
    }

	public function clearDebug() {
		$this->debug->clearDebug();
	}

	public function getDebug() {
	    return $this->debug->getDebug();
	}

	public static function getInstance($pinMode = false){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self($pinMode);
		}
		return self::$_instance;
	}

	public function __construct($pinMode = false){
        $this->pinMode = $pinMode;
        $this->debug = debug::getInstance($this);
    }

    public function generate($strTokenName, $strSessionData = "", $strTokenValue = "random"){
	    if($this->pinMode){
		    $strTokenValue = substr(str_shuffle(uniqid()),0,4);
        }
        else{
	        if ($strTokenValue == "random") {
		        $strTokenValue = md5(rand());
	        }
        }
	    $strTokenValue = strtoupper($strTokenValue);
	    if(empty($this->strSessID)){
		    $this->strSessID = session_id();
        }
	    $strTokenName = db_escape($strTokenName);
	    $strTokenValueE = db_escape($strTokenValue);
	    $sessionData = db_escape($strSessionData);

	    $strQuery = "REPLACE INTO wt_tokens (sessionid, tokenName, tokenString,sessionData) VALUES ('{$this->strSessID}', '{$strTokenName}', '{$strTokenValueE}', '{$sessionData}')";
	    $this->debug->addDebug($strQuery);
	    db_query($strQuery);
	    return $strTokenValue;
    }

    public function check($strTokenValue, $strTokenName = "",$boolCheckSessID = true){
        $strFilter = "";
        if(!empty($strTokenName)){
	        $strTokenName = db_escape($strTokenName);
	        $strFilter .= "AND tokenName = '{$strTokenName}'";
        }

        if($boolCheckSessID){
            if(empty($this->strSessID)){
	            $this->strSessID = session_id();
            }
	        $strFilter .= "AND sessionid = '{$this->strSessID}'";
        }

        $strQuery = "SELECT * FROM wt_tokens WHERE  tokenString = '{$strTokenValue}' {$strFilter}";
        $this->debug->addDebug($strQuery);
        $this->arrData = sqlGetValueFromKey($strQuery);
        return $strTokenValue === $this->arrData["tokenString"];
    }

    public function clear($strTokenName, $boolCheckSessID = true){
	    $strFilter = "";
	    $strTokenName = db_escape($strTokenName);
	    if($boolCheckSessID){
		    if(empty($this->strSessID)){
			    $this->strSessID = session_id();
		    }
		    $strFilter .= " AND sessionid = '{$this->strSessID}'";
	    }

	    $strQuery = "DELETE FROM wt_tokens WHERE tokenName = '{$strTokenName}' {$strFilter}";
	    $this->debug->addDebug($strQuery);
        db_query($strQuery);
    }

    public function getData(){
        if(empty($this->arrData)) $this->arrData = array();

        return $this->arrData;
    }
}

function str_replace_error_encoding($strReplace)
{
    $strReturn = $strReplace;
    utf8_encode($strReplace);
    $arrStringError = [ "Á", "Ä", "À", "É", "Ë", "È", "Í", "Ï", "Ì", "Ó", "Ö", "Ò", "Ú", "Ü", "Ù", "á", "ä", "à", "é", "ë", "è", "í", "ï", "ì", "ó", "ö", "ò", "ú", "ü", "ù", "Ñ", "ñ" ];
    $arrStringGood = [ "A", "A", "A", "E", "E", "E", "I", "I", "I", "O", "O", "O", "U", "U", "U", "a", "a", "a", "e", "e", "e", "i", "i", "i", "o", "o", "o", "u", "u", "u", "N", "n", ];
    utf8_encode_array($arrStringError);
    foreach ($arrStringError AS $key => $value){
        if(strstr($strReplace, $value)){
            $strReturn = str_replace($value, $arrStringGood[$key], $strReplace);
        }
    }
    return $strReturn;
}