<?php
// -------------------------------------------------------------------------------------
// Obtenido de http://www.tellinya.com/read/2007/09/09/106.html
// Helper function to detect if GZip is supported by client!
// If not supported the tricks are pointless
function acceptsGZip(){
    if(isset($_SERVER['HTTP_ACCEPT_ENCODING'])){
        $accept = str_replace(" ", "", strtolower($_SERVER['HTTP_ACCEPT_ENCODING']));
        $accept = explode(",",$accept);
        return in_array("gzip",$accept);
    }
    return false;
}
// -------------------------------------------------------------------------------------
$boolGlobalIsCSS = false;
$boolGlobalIsJS = false;
function playWithHtml($OutputHtml) {
	global $boolGlobalIsCSS, $boolGlobalIsJS;

    //20110407 AG: No he logrado limpiar el Javascript...
    $boolGlobalIsJS = false;
    if ($boolGlobalIsCSS) {
        // Tabs
        $OutputHtml = str_replace("\t", "", $OutputHtml);

        // Otros
        $arrPaterns = array();
        $arrPaterns[] = "/\/\*.+\*\//"; //Comentarios
        $arrPaterns[] = "/;\s+/"; //Enters despues de ;
        $arrPaterns[] = "/\s+}/"; //Enters antes de }
        $arrPaterns[] = "/}\s{2,}/"; //Enters despues de }
        $arrPaterns[] = "/\s*([:;,{]+)\s*/"; //Espacios espacios al rededor de :;,{


        $arrReplacements = array();
        $arrReplacements[] = ""; //Comentarios
        $arrReplacements[] = ";"; //Enters despues de ;
        $arrReplacements[] = "}"; //Enters antes de }
        $arrReplacements[] = "}\r\n"; //Enters despues de }
        $arrReplacements[] = "\\1"; //Espacios espacios al rededor de :;,{

        $OutputHtml = trim(preg_replace($arrPaterns, $arrReplacements, $OutputHtml));
    }
    else if ($boolGlobalIsJS) {
        // Otros
        $arrPaterns = array();
        $arrPaterns[] = "/;\s+/"; //Enters despues de ;
        $arrPaterns[] = "/\s+}/"; //Enters antes de }
        $arrPaterns[] = "/}\s{2,}/"; //Enters despues de }
        $arrPaterns[] = "/\s*([:;,{=\(\)]+)\s*/"; //Espacios espacios al rededor de :;,{=()


        $arrReplacements = array();
        $arrReplacements[] = ";"; //Enters despues de ;
        $arrReplacements[] = "}"; //Enters antes de }
        $arrReplacements[] = "}\r\n"; //Enters despues de }
        $arrReplacements[] = "\\1"; //Espacios espacios al rededor de :;,{=()

        $OutputHtml = preg_replace($arrPaterns, $arrReplacements, $OutputHtml);

        $OutputHtml = trim(str_replace(array(";function", ";/*"), ";\r\n/*", $OutputHtml));
    }
    else {
        //HTML
        // This will mess up HTML code like my site has done!
        // View the source to understand! All ENTERs are removed.
        // If your site has PREformated code this will break it!
        // Use regexp to find it and save it and place it back ...
        // or just uncomment the next line to keep enters
        //return preg_replace("/\s+/"," ",$OutputHtml);
    }

    return $OutputHtml;
}

// -------------------------------------------------------------------------------------
function obOutputHandler($OutputHtml) {
    global $cfg, $time_start, $config, $boolGlobalIsLocalDev;

    //-- Play with HTML before output
    $OutputHtml = playWithHtml($OutputHtml);

    if (function_exists("webservice_save_response")) {
		// Si estoy trabajando con un webservice...
		webservice_save_response($OutputHtml);
    }

    //-- If GZIP not supported compression is pointless.
    // If headers were sent we can not signal GZIP encoding as
    // we will mess it all up so better drop it here!
    // If you disable GZip encoding to use plain output buffering we stop here too!

    //20110901 AG: Tome la desicion de registrar tambien lo público en el log... ($_SESSION['wt']['logged'] || $boolGlobalIsLocalDev)
    //20110901 AG: Tome la desicion de registrar tambien los ajax en el log... (!$config["boolIsMiniMain"] || ($config["boolIsMiniMain"] && isset($_POST) && count($_POST)))
    core_end_page_processed_LOG();

    if(!acceptsGZip() || headers_sent() || !$cfg["core"]["GZIP"]) {
    	$intSize = strlen($OutputHtml);
    	header("Content-Length: {$intSize}");
    	return $OutputHtml;
    }
    else {
        //-- We signal GZIP compression and dump encoded data
        $strGziped = gzencode($OutputHtml);
        $intSize = strlen($strGziped);
        header("Content-Encoding: gzip");
        header("Content-Length: {$intSize}");

        return $strGziped;
    }
}
// This code has to be before any output from your site!
// If output exists uncompressed HTML will be delivered!
//ob_start("obOutputHandler");