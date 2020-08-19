<?php
date_default_timezone_set("America/Guatemala");

function execute_MySQL_File($strFile)
{
    $strStatus = "ok";
    $arrLines = file($strFile);
    $strCommand = "";
    while ($arrLine = each($arrLines)) {
        $strLine = trim($arrLine["value"]);
        if (substr($strLine, 0, 1) == "#") continue;
        if (substr($strLine, 0, 2) == "/*") continue;
        if (substr($strLine, 0, 2) == "--") continue;

        if (empty($strCommand)) {
            $strCommand = $strLine;
        }
        else {
            $strCommand .= " {$strLine}";
        }

        if (substr($strLine, -1) == ";") {
            $strCommand = substr($strCommand, 0, -1);
            if (!mysqli_query($strCommand)) {
                print "\n\t{$strCommand}... Failed!\n";
                print mysqli_error();
                print "\n";
                $strStatus = "fail";
            }
            $strCommand = "";
        }
    }
    return $strStatus;
}

if ((array_key_exists("CLIENTNAME", $_SERVER) || array_key_exists("SHELL", $_SERVER) || (isset($_SERVER["COMPUTERNAME"]) && ($_SERVER["COMPUTERNAME"] == "HMLADM-GUDIEL"))) &&
    !array_key_exists("HTTP_USER_AGENT", $_SERVER)) {

    $strTMP = "../wt_config.php";
    if (!file_exists($strTMP)) {
        $strTMP = pathinfo(__FILE__);
        $strTMP = $strTMP["dirname"];
        $strTMP = "{$strTMP}/../wt_config.php";
    }
    include_once($strTMP);

    /*$config["user"] = "root";
    $config["password"] = "homeland";
	$config["host"] = "10.1.1.30";
	$config["database"] = "db_colegio_valleverde";*/

    include_once("xmlfunctions.php");

    if (!$globalConnection = mysqli_connect($config["host"], $config["user"], $config["password"])) {
        $today = getdate();
        $month = $today["month"];
        $year = $today["year"];
        $weekday = $today["weekday"];
        $day = $today["mday"];

        $horas = $today["hours"];
        $minutos = $today["minutes"];
        $segundos = $today["seconds"];

        $strError = "Server: " . $_SERVER["SERVER_NAME"] . "\n File: " . __FILE__ . "\n Error: " . mysqli_error() . "\n Time: {$weekday} {$year}-{$month}-{$day} {$horas}:{$minutos}:{$segundos}";

        $strEmails = "webmaster@homeland.com.gt";
        error_log($strError, 1, $strEmails, "From: servidor@{$_SERVER["SERVER_NAME"]}\r\n");
    }
    else {
        $strServerAddress = "http://www.homeland.com.gt/";
        //$strServerAddress = "https://server4.homeland-servers.com/~hadmin/mail/";
        //$strServerAddress = "http://10.1.1.30/Private/SchoolWorld/";

        $strDate = date("Y-m-d H:i:s");

        $strCommand = "whoami";
        $strReturn = @exec($strCommand, $arrOutput, $arrReturn);

        // Busca archivos modulo_cron.php en los directorios de los modulos y los ejecuta, luego busca updates en el servidor de updates y los copia con FTP.
        print "Cronjob principal: Busca updates para los modulos y ejecuta cronjobs de cada modulo.\n Usuario: {$strReturn}\n Hora: {$strDate}";

        // 20100611 AG: Me aseguro que el directirio var/tmp tenga acceso de escritura... en este directorio vamos a ir agregando los caches que sean necesarios
        @mkdir("../var/tmp");
        $strReturn = @exec("chmod 777 ../var/tmp");

        mysqli_select_db($config["database"]);
        $cfg = array();
        $qTMP = mysqli_query("SELECT * FROM wt_config");
        while ($rTMP = mysqli_fetch_array($qTMP)) {
            $cfg[$rTMP["id"]] = unserialize(stripslashes($rTMP["config"]));
        }

        mysqli_free_result($qTMP);

        // Cleanup
        print "\n\nBorrando archivos viejos...";
        // Borro todos los archivos que no tengan nada que ver con ningun update, a excepcion de index.html y el directorio temp
        $arrValidFiles = array();
        if ($qTMP = @mysqli_query("SELECT filename FROM wt_updates WHERE update_type = 'S' AND rdy_to_delete = 'N'")) {
            while ($rTMP = mysqli_fetch_array($qTMP)) {
                $arrValidFiles[$rTMP["filename"]] = true;
            }
            mysqli_free_result($qTMP);
        }

        $boolOneFileDeleted = false;
        if ($objDirectory = @opendir("../updates/")) {
            while (($objFile = readdir($objDirectory)) !== false) {
                if ($objFile != "." && $objFile != ".." && $objFile != ".svn" && $objFile != "temp" && $objFile != "index.html" && $objFile != ".ftpquota") {
                    if (!key_exists($objFile, $arrValidFiles)) {
                        print "\n\t{$objFile}...";
                        unlink("../updates/{$objFile}");
                        print "Done...";
                        $boolOneFileDeleted = true;
                    }
                }
            }
            closedir($objDirectory);
        }

        // Borro el directorio output
        $arrPaths = array();
        $arrOutsidePaths = array();
        $arrDirObjects = array();
        $strCurPath = "../updates/temp/output/";
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
                    print "\n\t{$strCurPath}{$objFile}...";
                    unlink("{$strCurPath}{$objFile}");
                    print "Done...";
                    $boolOneFileDeleted = true;
                }
            }
            closedir($objCurDirectory);
            rmdir($strCurPath);
        }
        $strSuffix = ($boolOneFileDeleted) ? "\n" : "";
        print "{$strSuffix}Done...";

        // Luego obtengo el listado de updates
        $strFileName = "{$strServerAddress}xmlupdates.php?update=getList&website={$cfg["core"]["url"]}";
        //$strFileName = "{$strServerAddress}xmlupdates.php?update=getList&website={$cfg["core"]["url"]}&forceSite=_HOC";

        $strXMLInfo = file_get_contents($strFileName);
        $objXMLList = new XMLObject($strXMLInfo);

        if ($objXMLList->rootNode && $objXMLList->rootNode->intNextChildID) {
            $intCounter = 0;
            $boolContinue = true;

            class updateMainFunctions
            {

                function update_start()
                {
                    // Default functionality for open()
                }

                function update_end()
                {
                    // Default functionality for dump()
                }

            }

            while (($objUpdate = each($objXMLList->rootNode->children)) && $boolContinue) {
                $intCounter++;
                $objUpdate = $objUpdate["value"];

                //*
                print "\n\n********** Update {$intCounter}:{$objUpdate->attributes["filename"]} **********";
                if ($objUpdate->attributes["modulo"] == "core" || (isset($cfg["modules"][$objUpdate->attributes["modulo"]]) && $cfg["modules"][$objUpdate->attributes["modulo"]])) {
                    // Me conecto al FTP
                    $objFTP = ftp_connect("homeland.com.gt", 21, 180);
                    //$objFTP = ftp_connect("10.1.1.12", 21, 10);
                    if ($objFTP) {
                        $boolLoginResult = ftp_login($objFTP, "hmlupdate@homeland.com.gt", "hmlGetUpdatesOct2002");
                        if ($boolLoginResult) {
                            // Luego voy a traer el archivo tar del update
                            $strUpdateFile = "../updates/temp/{$objUpdate->attributes["filename"]}";
                            if (ftp_get($objFTP, $strUpdateFile, $objUpdate->attributes["filename"], FTP_BINARY)) {
                                if ($objFTP) @ftp_close($objFTP);

                                print "\n\nArchivo copiado: {$objUpdate->attributes["filename"]}...";
                                // Desinfo el tar con linux
                                print "\n\nDescomprimiendo archivo...";
                                @mkdir("../updates/temp/output");
                                $strCmd = "tar -xf {$strUpdateFile} -C ../updates/temp/output";
                                exec($strCmd);
                                print "Done...";

                                // Si tiene main, lo cargo
                                $boolUseMain = false;
                                $objMainUpdate = false;
                                if ($objUpdate->attributes["hasMain"] == "Y" && file_exists("../updates/temp/output/main.php")) {
                                    print "\n\nCargando main.php...";
                                    $strTMP = "../updates/temp/output/main.php";
                                    include_once($strTMP);
                                    print "Done...";
                                    $boolUseMain = true;
                                }
                                // Si existe la funcion update_start() la ejecuto
                                $strStatus = "ok";
                                if ($boolUseMain && $objMainUpdate !== false) {
                                    print "\n\nEjecutando update_start...";
                                    $strStatus = $objMainUpdate->update_start();
                                    print "Done...";
                                }
                                // Si la funcion update_start() devuelve ok, continuo
                                if ($strStatus == "ok") {
                                    mysqli_query("INSERT INTO wt_updates
                                                 (fecha, filename, modulo,
                                                  hasMain, hasMySQL, hasCode,
                                                  update_type)
                                                 VALUES
                                                 (NOW(), '{$objUpdate->attributes["filename"]}', '{$objUpdate->attributes["modulo"]}',
                                                  '{$objUpdate->attributes["hasMain"]}', '{$objUpdate->attributes["hasMySQL"]}', '{$objUpdate->attributes["hasCode"]}',
                                                  'C')");
                                    $intLocalUpdateID = mysqli_insert_id();

                                    // Si hay MySQL corro el dump
                                    if ($objUpdate->attributes["hasMySQL"] == "Y") {
                                        print "\n\nEjecutando archivos de MySQL...";
                                        if ($objDirectory = opendir("../updates/temp/output/mysql/")) {
                                            while (($objFile = readdir($objDirectory)) !== false) {
                                                $arrFileInfo = pathinfo($objFile);
                                                if (isset($arrFileInfo["extension"]) && $arrFileInfo["extension"] == "sql") {
                                                    print "\n\t{$objFile}...";

                                                    $strMySQLStatus = execute_MySQL_File("../updates/temp/output/mysql/{$objFile}");

                                                    if ($intLocalUpdateID == 0) {
                                                        mysqli_query("INSERT INTO wt_updates
                                                                     (fecha, filename, modulo,
                                                                      hasMain, hasMySQL, hasCode,
                                                                      update_type)
                                                                     VALUES
                                                                     (NOW(), '{$objUpdate->attributes["filename"]}', '{$objUpdate->attributes["modulo"]}',
                                                                      '{$objUpdate->attributes["hasMain"]}', '{$objUpdate->attributes["hasMySQL"]}', '{$objUpdate->attributes["hasCode"]}',
                                                                      'C')");
                                                        $intLocalUpdateID = mysqli_insert_id();
                                                    }

                                                    mysqli_query("REPLACE INTO wt_updates_client_details
                                                                 (updateid, filename, status)
                                                                 VALUES
                                                                 ({$intLocalUpdateID}, '{$objFile}', '{$strMySQLStatus}')");
                                                    unlink("../updates/temp/output/mysql/{$objFile}");
                                                    print "Done...";
                                                }
                                            }
                                            closedir($objDirectory);
                                            rmdir("../updates/temp/output/mysql");
                                        }
                                    }
                                    // Si hay codigo copio los archivos (Guardo status de cada file en cliente)
                                    if ($objUpdate->attributes["hasCode"] == "Y") {
                                        print "\n\nCopiando programa...";
                                        $arrPaths = array();
                                        $arrOutsidePaths = array();
                                        $arrDirObjects = array();
                                        $strCurPath = "../updates/temp/output/code/";
                                        $strCurOutsidePath = "";
                                        if ($objCurDirectory = opendir($strCurPath)) {
                                            array_push($arrPaths, $strCurPath);
                                            array_push($arrOutsidePaths, $strCurOutsidePath);
                                            array_push($arrDirObjects, $objCurDirectory);
                                        }
                                        while (count($arrDirObjects)) {
                                            $strCurPath = array_pop($arrPaths);
                                            $strCurOutsidePath = array_pop($arrOutsidePaths);
                                            $objCurDirectory = array_pop($arrDirObjects);
                                            while (($objFile = readdir($objCurDirectory)) !== false) {
                                                if ($objFile == "." || $objFile == "..")
                                                    continue;

                                                if (is_dir("{$strCurPath}{$objFile}")) {
                                                    array_push($arrPaths, $strCurPath);
                                                    array_push($arrOutsidePaths, $strCurOutsidePath);
                                                    array_push($arrDirObjects, $objCurDirectory);

                                                    $strCurPath .= "{$objFile}/";
                                                    $strCurOutsidePath .= "{$objFile}/";
                                                    $objCurDirectory = opendir($strCurPath);

                                                    $strCheck = "../" . substr($strCurOutsidePath, 0, -1);
                                                    if (!is_dir($strCheck)) {
                                                        print "\n\tCreando directorio: {$strCheck}...";
                                                        mkdir($strCheck, 0755);
                                                        print "Done...";
                                                    }
                                                }
                                                else {
                                                    print "\n\t{$strCurOutsidePath}{$objFile}...";
                                                    if (copy("{$strCurPath}{$objFile}", "../{$strCurOutsidePath}{$objFile}")) {
                                                        mysqli_query("REPLACE INTO wt_updates_client_details
                                                                     (updateid, filename, status)
                                                                     VALUES
                                                                     ({$intLocalUpdateID}, '{$strCurOutsidePath}{$objFile}', 'ok')");
                                                        print "Done...";
                                                    }
                                                    else {
                                                        mysqli_query("REPLACE INTO wt_updates_client_details
                                                                     (updateid, filename, status)
                                                                     VALUES
                                                                     ({$intLocalUpdateID}, '{$strCurOutsidePath}{$objFile}', 'fail')");
                                                        print "Fail!...";
                                                        $strStatus = "fail";
                                                    }
                                                    unlink("{$strCurPath}{$objFile}");
                                                }
                                            }
                                            closedir($objCurDirectory);
                                            rmdir($strCurPath);
                                        }
                                    }
                                    // Si la funcion update_end() existe, la ejecuto
                                    if ($boolUseMain && $objMainUpdate !== false) {
                                        print "\n\nEjecutando update_end...";
                                        $objMainUpdate->update_end();
                                        print "Done...";
                                    }
                                }

                                // Mando status al server
                                $strFileName = "{$strServerAddress}xmlupdates.php?update=done&website={$cfg["core"]["url"]}&updateID={$objUpdate->attributes["updateID"]}&status={$strStatus}&statusID={$objUpdate->attributes["statusID"]}";
                                //$strFileName = "{$strServerAddress}xmlupdates.php?update=done&website={$cfg["core"]["url"]}&updateID={$objUpdate->attributes["updateID"]}&status={$strStatus}&statusID={$objUpdate->attributes["statusID"]}&forceSite=_HOC";
                                $strXMLInfo = file_get_contents($strFileName);

                                // Borro el directorio output y el tar del update
                                if ($objUpdate->attributes["hasMain"] == "Y" && file_exists("../updates/temp/output/main.php")) {
                                    unlink("../updates/temp/output/main.php");
                                }
                                if ($objDirectory = @opendir("../updates/temp/")) {
                                    print "\n\nBorrando archivos del temp...";
                                    while (($objFile = readdir($objDirectory)) !== false) {
                                        if ($objFile == "." || $objFile == ".." || $objFile == "index.html")
                                            continue;

                                        if (is_dir("../updates/temp/{$objFile}")) {
                                            rmdir("../updates/temp/{$objFile}");
                                        }
                                        else {
                                            unlink("../updates/temp/{$objFile}");
                                        }
                                    }
                                    closedir($objDirectory);
                                    print "Done...";
                                }
                            }
                            else {
                                if ($objFTP) @ftp_close($objFTP);
                                print "\n\nNo se pudo copiar el archivo";
                                $strFileName = "{$strServerAddress}xmlupdates.php?update=done&website={$cfg["core"]["url"]}&updateID={$objUpdate->attributes["updateID"]}&status=fail&statusID={$objUpdate->attributes["statusID"]}";
                                //$strFileName = "{$strServerAddress}xmlupdates.php?update=done&website={$cfg["core"]["url"]}&updateID={$objUpdate->attributes["updateID"]}&status=fail&statusID={$objUpdate->attributes["statusID"]}&forceSite=_HOC";
                                $strXMLInfo = file_get_contents($strFileName);

                                $boolContinue = false;
                            }
                        }
                        else {
                            print "\n\nNo fue posible hacer login al FTP @login";
                            $strFileName = "{$strServerAddress}xmlupdates.php?update=done&website={$cfg["core"]["url"]}&updateID={$objUpdate->attributes["updateID"]}&status=fail&statusID={$objUpdate->attributes["statusID"]}";
                            //$strFileName = "{$strServerAddress}xmlupdates.php?update=done&website={$cfg["core"]["url"]}&updateID={$objUpdate->attributes["updateID"]}&status=fail&statusID={$objUpdate->attributes["statusID"]}&forceSite=_HOC";
                            $strXMLInfo = file_get_contents($strFileName);

                            $boolContinue = false;
                        }
                        if ($objFTP)
                            @ftp_close($objFTP);
                    }
                    else {
                        print "\n\nNo fue posible conectar al FTP @connect";
                        $strFileName = "{$strServerAddress}xmlupdates.php?update=done&website={$cfg["core"]["url"]}&updateID={$objUpdate->attributes["updateID"]}&status=fail&statusID={$objUpdate->attributes["statusID"]}";
                        //$strFileName = "{$strServerAddress}xmlupdates.php?update=done&website={$cfg["core"]["url"]}&updateID={$objUpdate->attributes["updateID"]}&status=fail&statusID={$objUpdate->attributes["statusID"]}&forceSite=_HOC";
                        $strXMLInfo = file_get_contents($strFileName);

                        $boolContinue = false;
                    }
                }
                else {
                    print "\n\nMódulo '{$objUpdate->attributes["modulo"]}' inválido...";
                }
                //*/
            }
        }
        else {
            print "\n\nNo hay actualizaciones pendientes para este sitio";
        }

        print "\n\n***** CRON JOB DE MODULOS *****";
        $strPath = "../modules";
        if (!file_exists($strPath)) {
            $strPath = pathinfo(__FILE__);
            $strPath = $strPath["dirname"];
            $strPath = "{$strPath}/../modules";
        }
        $strPath .= "/";

        $strPath = str_replace("/modules/", "/", $strPath);

        include_once("{$strPath}core/main_functions.php");
        include_once("{$strPath}core/mysql.lib.php");

        $strThemeCaja = "{$strPath}themes/{$cfg["core"]["theme"]}/functions_caja.php";
        if (file_exists($strThemeCaja)) {
            include_once($strThemeCaja);
        }

        while ($arrItem = each($cfg["modules"])) {
            if ($arrItem["value"]) {
                if (isset($cfg[$arrItem["key"]]["globalCronJob"]) && $cfg[$arrItem["key"]]["globalCronJob"]) {
                    $strFile = "{$strPath}modules/{$arrItem["key"]}/cronjob.php";
                    if (file_exists($strFile)) {
                        print "\n\nCronjob: {$arrItem["key"]}";
                        include_once($strFile);
                    }
                }
            }
        }
        reset($cfg["modules"]);

        //*
        print "\n\n***** ENVIO DE INFO AL SERVER *****";
        $strReturn = @exec("php {$strPath}sendtohml.php"); // Hago un exec local para que este vaya a traer otro archivo siempre como cronjob...
        print "\nsendtohml.php returned: {$strReturn}";
        //*/

        print "\n\nProceso terminado a las:" . date("Y-m-d H:i:s");
    }
}
else {
    print "Acceso denegado\n";
}
die();

function sqlGetValueFromKeyMainCronJob($strSQL, $boolFalseOnEmpty = false, $boolForceArray = false){
    $return = false;
    $qList = mysqli_query($strSQL." LIMIT 0,1 ");
    $listFields = db_get_fields_MainCronJob($qList);
    if ($rList = mysqli_fetch_array($qList)) {
        if (mysqli_num_fields($qList) == 1 && !$boolForceArray) {
            $return = $rList[0];
            if ($boolFalseOnEmpty) {
                $strTMP = html_entity_decode($return);
                $strTMP = strip_tags($strTMP);

                $strTMP = str_replace(" ", "", $strTMP);
                $strTMP = trim($strTMP);
                $strTMP = str_replace(" ", "", $strTMP);
                $strTMP = trim($strTMP);

                if (empty($return) || empty($strTMP)) $return = false;
            }
        }
        else {
            $return = array();
            foreach ($listFields as $field){
                $return[$field['name']] = $rList[$field['name']];
            }
        }
    }
    mysqli_free_result($qList);
    return  $return ;
}

function db_get_fields_MainCronJob($argIndex) {
    if ($field = mysqli_fetch_field($argIndex)){
        do {
            $fields[$field->name]['name'] = $field->name;
            $fields[$field->name]['table'] = $field->table;
            $fields[$field->name]['max_length'] = $field->max_length;
            $fields[$field->name]['not_null'] = $field->not_null;

        }while ($field = mysqli_fetch_field($argIndex));
    }
    return $fields;
}

function birthdays_postal_message_format ($arrInfoUser,$strSrcImage) {

    global $cfg;

    $strApreciable = ($arrInfoUser["sex"] == "Male")?"Estimado":(($arrInfoUser["sex"] == "Female")?"Estimada":"Apreciable");

    $arrName = explode(",",$arrInfoUser["realname"]);
    $strName = (!empty($arrName[1]))?$arrName[1]:$arrInfoUser["realname"];

    $strMessage = <<<EOT
        <html>
            <title>¡FELIZ CUMPLEAÑOS!</title>
        <body>
           <table align="center" width="600" cellspacing="4" style='position:relative;'>
             <tr>
                <td style='position:relative;'>
                    <table align="center" width="600" style='font-family:arial; position:relative;' cellspacing='0' cellpadding='0'>
                    	<tr>
                    		<td width="100%" align="left" style="position:relative; font-size:22px; background-color:#6AD3E6;">
								<div style="position:absolute; left:0; top:0; width:500; font-family:arial; font-size:22px; text-align:right; background-color:#6AD3E6; color:#190F91;">
                                    {$strApreciable} {$strName}:
                                </div>
                                &nbsp;
                    		</td>
                    	</tr>
                        <tr>
                            <td width="100%" align="left" style="position:relative;">
                                <div style="width:600">
                                    <img src="{$strSrcImage}" alt="¡{$cfg["core"]["title"]} te desea FELIZ CUMPLEAÑOS!" title="¡FELIZ CUMPLEAÑOS!" width="100%">
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
             </tr>
            </table>
        </body>
        </html>
EOT;

    return $strMessage;

}

?>
