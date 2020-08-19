<?php
/**
 * jqueryloader.php (2012-may-10)
 * (c) by Alejandro Gudiel
 * All Rights Reserved
 * License does not permit use by third parties
 *
 * La idea de este archivo es tener un include de javascript que
 * baje comprimido, se pueda cachear y pueda ser modificado por php.
 *
 * Llego a este archivo a travez de un redirect... ver ../.htaccess
**/

include_once("core/miniMain.php"); // Este ya jala el theme

if (!isset($_GET["file"])) {
	die();
}
else {
	$strFile = user_input_delmagic($_GET["file"]);
}

// EXPERIMENTAL
$boolGlobalIsJS = true;

// Construyo el path y el type
$arrFileParts = explode(".", $strFile);
$intMaxPart = count($arrFileParts);
$intMaxPart--;

// Obtengo el tipo y reduzco el array
$strType = $arrFileParts[$intMaxPart];
unset($arrFileParts[$intMaxPart]);
$intMaxPart--;

if (isset($_GET["theme"]) && (empty($_GET["profile"]))) {
	// Si es un archivo del theme
	$strTheme = user_input_delmagic($_GET["theme"]);
	$strFullPath = "themes/{$strTheme}/{$strFile}";

	$strFilePath = "themes/{$strTheme}";
}
else if(isset($_GET["profile"]) && (empty($_GET["theme"]))) {
    // Si es un archivo del profile
    $strTheme = user_input_delmagic($_GET["profile"]);
    $strFullPath = "profiles/{$strTheme}/{$strFile}";

    $strFilePath = "profiles/{$strTheme}";
}
else if(isset($_GET["theme"]) && (isset($_GET["profile"]))) {
    $strTheme = user_input_delmagic($_GET["theme"]);
    $strProfile = user_input_delmagic($_GET["profile"]);
    $strFullPath = "themes/{$strTheme}/profiles/{$strProfile}/{$strFile}";

    $strFilePath = "themes/{$strTheme}/profiles/{$strProfile}";
}
else {
	// Si es un archivo plantilla

	// Quito el "min" para ignorarlo en el path
	if ($arrFileParts[$intMaxPart] == "min") {
		unset($arrFileParts[$intMaxPart]);
		$intMaxPart--;
	}

	$strPath = implode("/", $arrFileParts);
	$strFullPath = "core/{$strPath}/{$strFile}";

	$strFilePath = "core/{$strPath}";

	if (!file_exists("core/{$strPath}/{$strFile}")) {
		die();
	}
}


// Valido la fecha de modificacion
$intLastModified = filemtime($strFullPath);

// Para ver la fecha de modificacion del archivo de php que esta procesando...
$intLastModifiedThis = filemtime(__FILE__);
if ($intLastModifiedThis > $intLastModified) $intLastModified = $intLastModifiedThis;

header("Pragma: private");

$intHoras = 1;
$expires = 60*60*$intHoras; // El tiempo de expiracion en segundos

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
header("Cache-Control: maxage=".$expires);
header("Last-Modified: " . $strLastModified . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s", time()+$expires) . " GMT");

$strContents = file_get_contents($strFullPath);

if ($strType == "js") {
	header("Content-Type: text/javascript");
}
else if ($strType == "css") {
	header("Content-Type: text/css");

	/*
	if (core_apache_check_rewrite_modules()) {
		// OJO: el ../ es porque lo estoy jalando con friendlyurls que inicia con jq/
		$strContents = str_replace("url(", "url(../{$strFilePath}/", $strContents);
	}
	else {
		$strContents = str_replace("url(", "url({$strFilePath}/", $strContents);
	}
	*/
	// Lo del ".." no estaba funcionando bien asi que mejor le meti el url del sitio para que no haya pierde...
	$strBaseURL = core_getBaseDir();
	$strContents = str_replace("url(", "url({$strBaseURL}{$strFilePath}/", $strContents);

	// A veces, el url tiene url("") y estas comillas no se toman en cuenta en mi fix... esta linea trata de corregir el error que esto genera
	$strContents = str_replace("url({$strBaseURL}{$strFilePath}/\"", "url(\"{$strBaseURL}{$strFilePath}/", $strContents);
}

print $strContents."\n";
//readfile($strFullPath);