<?php
/**
 * dynamiccss.php (2010-sept-21)
 * (c) by Alejandro Gudiel
 * All Rights Reserved
 * License does not permit use by third parties
 *
 * La idea de este archivo es tener un include de CSS que
 * baje comprimido, se pueda cachear y pueda ser modificado por php.
 *
 * OJO que no puede llevar variables de GET ni POST ni de SESSION por el cache...
**/

include_once("core/miniMain.php"); // Este ya jala el theme

// EXPERIMENTAL
$boolGlobalIsCSS = true;

if ($cfg["core"]["CACHE_CSS_AND_JAVA"]) {
	// Defino los archivos
	$arrFiles = array();
    if (file_exists("themes/{$cfg["core"]["theme"]}/frames.css")) $arrFiles[] = "themes/{$cfg["core"]["theme"]}/frames.css";
    if (file_exists("themes/{$cfg["core"]["theme"]}/style.css")) $arrFiles[] = "themes/{$cfg["core"]["theme"]}/style.css";
	if (file_exists("themes/{$cfg["core"]["theme"]}/forms.css")) $arrFiles[] = "themes/{$cfg["core"]["theme"]}/forms.css";
	reset($cfg['modules']);
    $mod = array();
    foreach ($cfg['modules'] as $mod[0]=>$mod[1]) {
    	if ($mod[1]) {
            if(file_exists("themes/{$cfg["core"]["theme"]}/{$mod[0]}.css")){
                $arrFiles[] = "themes/{$cfg["core"]["theme"]}/{$mod[0]}.css";
            }
            if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$mod[0]}.css")) {
                $arrFiles[] = "themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/{$mod[0]}.css";
            }
            if(file_exists("profiles/{$cfg["core"]["site_profile"]}/{$mod[0]}.css")){
                $arrFiles[] = "profiles/{$cfg["core"]["site_profile"]}/{$mod[0]}.css";
            }
		}
    }

    if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/frames.css")) {
        $arrFiles[] = "themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/frames.css";
    }
    if (file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/style.css")) {
        $arrFiles[] = "themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/style.css";
    }

    if(file_exists("profiles/{$cfg["core"]["site_profile"]}/frames.css")){
        $arrFiles[] = "profiles/{$cfg["core"]["site_profile"]}/frames.css";
    }
    if(file_exists("profiles/{$cfg["core"]["site_profile"]}/style.css")){
        $arrFiles[] = "profiles/{$cfg["core"]["site_profile"]}/style.css";
    }

    // Verifico fechas de modificacion
    $intMaxLastModified = 0;
    $intLastModified = 0;

    $arrFile = array();
    foreach ($arrFiles as $arrFile["key"]=>$arrFile["value"]) {
		$intLastModified = filemtime($arrFile["value"]);
		if ($intLastModified > $intMaxLastModified) $intMaxLastModified = $intLastModified;
	}

	// Para ver la fecha de modificacion del archivo de php que esta procesando...
	$intLastModifiedThis = filemtime(__FILE__);
	if ($intLastModifiedThis > $intMaxLastModified) $intMaxLastModified = $intLastModifiedThis;

	header("Pragma: private");

	$intHoras = 24;
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
	header("Content-Type: text/css");

    $arrFile = array();
	foreach ($arrFiles as $arrFile["key"]=>$arrFile["value"]) {
		// Esto es porque los CSS tienen las url relativas a su propio path pero al bajarlo con dynamiccss este path cambia pues dynamiccss esta en el root.
		$strContents = file_get_contents($arrFile["value"]);
	    $strContents = str_replace("url(", "url(themes/{$cfg["core"]["theme"]}/", $strContents);
	    print $strContents."\n";
	    $strContents = ""; //Para liberar memoria
	}
}
else {
    print " ";
}