<?php
	if (strstr($_SERVER["PHP_SELF"], "/swadmin/"))  die ("You can't access this file directly...");
	include_once("core/main.php");
    require_once("core/config_vars.php");

    if(!check_user_class("admin")) {
        Header("Location: index.php");
        exit;
    }

    $conn = db_connect($config["host"],$config["database"],$config["user"],$config["password"]) or die( db_error() );

    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
    <head>
        <title>SchoolWorld WebAdmin</title>
        <link href="swadmin/style.css" rel="stylesheet" type="text/css">
        <link href="core/packages/bootstrap/bootstrap.min.css" rel="stylesheet" type="text/css">
    </head>
    <body style="background-color: #92A4BC">
        <table width="750" border="0" cellspacing="0" cellpadding="2" align="center" class="maintable">
            <tr>
                <td height="50" colspan="3" class="topbar" align="right" valign="center">
                    <img src="swadmin/hoc.jpg">
                </td>
            </tr>
            <tr>
                <td height="15" colspan="3" align="center">
                    <a href="index.php">Back to site</a>
                </td>
            </tr>
            <?php
            // SQL Upload
            if (isset($_GET["SQLUpload"]) && $_GET["SQLUpload"]==true) {
                ?>
                <tr>
                    <td align="center" colspan="3"><a href="admin.php">Back to Admin</a></td>
                </tr>
                <?php
                if (isset($_FILES["SQLFile"])) {
                    $InPutFile = fopen($_FILES["SQLFile"]["tmp_name"],"r");
                    do {
                        $strQuery="";
                        do {
                            if ($strInput = fgets($InPutFile)) {
                                $strInput = str_replace(chr(10),"",str_replace(chr(13),"",$strInput));
                                if (substr($strInput, 0, 1) == "#") continue;

                                $strQuery.=chr(10).chr(13).$strInput;
                                $strLastChar=substr($strInput,strlen($strInput)-1,1);
                                $boolOK=true;
                            }
                            else {
                                $boolOK=false;
                            }
                        } while ($strLastChar!=";" && $boolOK);
                        //$strQuery = str_replace(";", "", $strQuery);

                        if (strlen($strQuery)>0) {
                            $ret = db_query($strQuery, false);
                            if (!$ret) {
                                print_r("<tr><td colspan='3'>");
                                echo "<b>Error : </b><i>" . db_error() . "</i><br>$strQuery";
                                print_r("</tr></td>");
                            }
                        }
                    } while ($boolOK);
                }
                ?>
                <tr>
                    <td colspan="3" align="center">
                        <form method="post" action="admin.php?SQLUpload=true" name="admin_SQLUpload" enctype="multipart/form-data">
                            <input name="SQLFile" type="file"><br>
                            <input name="SubmitSQLFile" type="submit" value="Submit">
                        </form>
                    </td>
                </tr>
            <?php
            } // SQL Query
            else if (isset($_GET["SQLQuery"]) && $_GET["SQLQuery"]==true) {
                ?>
                <tr>
                    <td align="center" colspan="3"><a href="admin.php">Back to Admin</a></td>
                </tr>
                <?php
                if (isset($_POST["SQLQuery"])) {
                    $strQuery = stripslashes($_POST["SQLQuery"]);
                    echo "
                    <tr>
                        <td align=\"left\" colspan=\"3\">Query: {$strQuery}</td>
                    </tr>
                    ";
                    $qTMP = db_query($strQuery);
                    $strError = db_error();
                    echo "
                    <tr>
                        <td align=\"left\" colspan=\"3\">Errores: {$strError}</td>
                    </tr>
                    ";
                    ?>
                    <tr>
                        <td colspan="3">
                            <table align="left" border="1" cellspacing="0" cellpadding="2">
                            <?php
                            $boolFirstRow = true;
                            $intNumRows =0;
                            if(is_object($qTMP)){
	                            $intNumRows = db_num_rows($qTMP);
	                            $listFields = db_get_fields($qTMP);
	                            if ($rTMP=db_fetch_array($qTMP)) {
		                            do {
			                            if ($boolFirstRow) {
				                            $strRow = "<tr>";
//                                        $intCounter=0;
				                            reset ($listFields);
				                            foreach ($listFields as $key => $entry ) {
					                            $strRow.="<td><b>{$key}</b></td>";
				                            }
				                            $strRow.= "</tr>\n";
				                            echo $strRow;
				                            $boolFirstRow=false;
				                            reset($rTMP);
			                            }
//                                    drawDebug($rTMP);
			                            $strRow = "<tr>";
//                                    $intCounter=0;
			                            reset ($listFields);
			                            foreach ($listFields as $key => $entry ) {
				                            $strValue = $rTMP[$key];
				                            if (strlen($rTMP[$key])==0) {
					                            $strValue = "&nbsp;";
				                            }
				                            $strRow.="<td>{$strValue}</td>";
			                            }
			                            $strRow.= "</tr>\n";
			                            echo $strRow;
		                            } while ($rTMP=db_fetch_array($qTMP));
	                            }
                            }
                            ?>
                            </table>
                        </td>
                    </tr>
                    <?php
                     echo "
                    <tr>
                        <td align=\"left\" colspan=\"3\">Filas: {$intNumRows}</td>
                    </tr>
                    ";
                }
                ?>
                <tr>
                    <td colspan="3" align="center">
                        <form method="post" action="admin.php?SQLQuery=true" name="admin_SQLQuery">
                            <textarea style="position:relative; width:100%; z-index:1" align="left" name="SQLQuery" rows="15"></textarea>
                            <input name="SubmitSQLQuery" type="submit" value="Submit">
                        </form>
                    </td>
                </tr>
                <?php
            } // MAKE BACKUP
            else if (isset($_GET["BACKUP"]) && $_GET["BACKUP"]==true) {
                ?>
                <tr>
                    <td align="center" colspan="3"><a href="admin.php">Back to Admin</a></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <?php
                        if (isset($_POST["submit"])){
                        	$strPath = dirname(__FILE__);
                        	$strPath .= "/HomelandBackups";
                        	$strPath = str_replace("\\", "/", $strPath);

                            $strTables = "SHOW TABLES";
                            $qTables = db_query($strTables);
                            while ($rTable = db_fetch_array($qTables)){
                                echo "- ".$rTable[0]."<br>";
                                db_query("LOCK TABLES {$rTable[0]} READ");
                                db_query("FLUSH TABLE {$rTable[0]}");
                                if ($_POST["BackupType"]=="Hard") {
                                    db_query("BACKUP TABLE {$rTable[0]} TO '{$strPath}'");
                                }
                                else {
                                    $qTMP = db_query("SELECT * INTO OUTFILE '{$strPath}/{$rTable[0]}.txt' FROM {$rTable[0]}");
                                    db_free_result($qTables);
                                }

                                $strError = db_error();
                                echo "Errores: " . $strError . "<br>";
                                db_query("UNLOCK TABLES");
                                echo "********************<br>";
                            }
                            db_free_result($qTables);
                        }
                        else {
                            ?>
                            <form method="post" action="admin.php?BACKUP=true">
                                <table width="50%" align="center">
                                    <tr>
                                        <td align="center"><input name="BackupType" type="radio" value="Hard">Hard</td>
                                        <td align="center"><input name="BackupType" type="radio" value="Soft">Soft</td>
                                    </tr>
                                    <tr><td colspan="2" align="center"><input name="submit" type="submit" value="Submit"></td></tr>
                                </table>
                            </form>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            else if (isset($_GET["RESTORE"]) && $_GET["RESTORE"]==true) {
                ?>
                <tr>
                    <td align="center" colspan="3"><a href="admin.php">Back to Admin</a></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <?php
                        if (isset($_POST["submit"])){
                            $strPath = dirname(__FILE__);
                        	$strPath .= "/HomelandBackups";
                        	$strPath = str_replace("\\", "/", $strPath);

                            if ($_POST["BackupType"]=="Hard") {
                                //RESTORE TABLE tbl_name[,tbl_name...] FROM '/path/to/backup/directory'
                                if ($directory = @opendir($strPath)) {
                                    while (($file = readdir($directory)) !== false) {
                                        $FileInfo = pathinfo($file);
                                        if (isset($FileInfo["extension"]) && $FileInfo["extension"]=="MYD") {
                                            $strTableName = substr($FileInfo["basename"],0,strlen($FileInfo["basename"])-4);
                                            db_query("DROP TABLE {$strTableName}");
                                            $qStatus = db_query("RESTORE TABLE {$strTableName} FROM '{$strPath}'");
                                            ?>
                                            <table width="100%" border="1">
                                                <tr>
                                                    <th colspan="4" align="left"><?php print $strTableName;?></th>
                                                </tr>
                                                <?php
                                                while ($rStatus = db_fetch_array($qStatus)){
                                                    echo "<tr>";
                                                        echo "<td width='40%'>{$rStatus[0]}</td>";
                                                        echo "<td width='10%'>{$rStatus[1]}</td>";
                                                        echo "<td width='10%'>{$rStatus[2]}</td>";
                                                        echo "<td width='40%'>{$rStatus[3]}</td>";
                                                    echo "</tr>";
                                                }
                                                ?>
                                            </table>
                                            <hr>
                                            <?php
                                        }
                                    }
                                    closedir($directory);
                                }
                            }
                            else {
                                $strTables = "SHOW TABLES";
                                $qTables = db_query($strTables);
                                while ($rTable = db_fetch_array($qTables)){
                                    $strTableName = $rTable[0];
                                    echo "- ".$strTableName."<br>";
                                    db_query("LOAD DATA INFILE '{$strPath}/{$strTableName}.txt' REPLACE INTO TABLE {$strTableName}");
                                    $strError = db_error();
                                    echo "Errores: " . $strError . "<br>";
                                    echo "********************<br>";
                                }
                                db_free_result($qTables);
                            }
                        }
                        else {
                            ?>
                            <form method="post" action="admin.php?RESTORE=true">
                                <table width="50%" align="center">
                                    <tr>
                                        <td align="center"><input name="BackupType" type="radio" value="Hard">Hard</td>
                                        <td align="center"><input name="BackupType" type="radio" value="Soft">Soft</td>
                                    </tr>
                                    <tr><td colspan="2" align="center"><input name="submit" type="submit" value="Submit"></td></tr>
                                </table>
                            </form>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
            else {
                ?>
                <tr>
                    <td width="1%">&nbsp;</td>
                    <td width="20%" valign="top" class="leftpanel">
                        <?php
                        // list menu modules
                        $arrTMP = array();
                        $arrTMP["core"] = $config_var["core"];
                        unset($config_var["core"]);

                        if(isset($config_var["modules"])){
                            $arrTMP["modules"] = $config_var["modules"];
                            unset($config_var["modules"]);
                        }

                        if (isset($config_var["_https_links"])) {
                        	$arrTMP["_https_links"] = $config_var["_https_links"];
                        	unset($config_var["_https_links"]);
                        }

                        if(check_module("groups")){
	                        $arrTMP["_dis_grp_modules"] = $config_var["_dis_grp_modules"];
	                        unset($config_var["_dis_grp_modules"]);

	                        $arrTMP["_dis_fam_opts"] = $config_var["_dis_fam_opts"];
	                        unset($config_var["_dis_fam_opts"]);
                        }

                        ksort($config_var);

                        echo "<center><b>Main</b></center>";
                        reset($arrTMP);
                        foreach($arrTMP AS $key => $value ){
	                        echo "<a href=\"admin.php?page={$key}&type=root\">{$key}</a><br>";
                        }
                        echo "<hr>";
                        echo "<center><b>Modules</b></center>";
                        reset($config_var);

                        foreach($config_var AS $key => $value ){
	                        if (isset($cfg['modules'][$key]) && $cfg['modules'][$key]) {
		                        echo "<a href=\"admin.php?page={$key}&type=root\">{$key}</a><br>";
	                        }
                            elseif (isset($cfg['modules']["notas"]) && $cfg['modules']["notas"] && ($key == "notasimpresion" || $key == "notasotros")) {
		                        echo "<a href=\"admin.php?page={$key}&type=root\">{$key}</a><br>";
	                        }
                        }

                        $config_var = array_merge ($arrTMP, $config_var);
                        ?>
                        <hr>
                        <center><b>Tools</b></center>
                        <a href="admin.php?SQLUpload=true">SQL Upload</a><br>
                        <a href="admin.php?SQLQuery=true">SQL Query</a><br><br>
                        <a href="admin.php?BACKUP=true">Make Backup</a><br>
                        <a href="admin.php?RESTORE=true">Restore Backup</a><br>
                        <hr>
                    </td>
                    <td width="79%" valign="top">
                        <?php
                        // check page
	                    if( isset( $_GET["page"] ) ) {
	                        $page = strip_tags($_GET["page"]);
	                        if( !preg_match("/^[_a-z0-9-]+$/i", $page) ) $page = "core";
	                    } else $page = "core";

	                    if ($page == "_dis_grp_modules"){
	                    	print "Modulos que quedan habilitados a pesar de desactivar al grupo de un usuario<br><br>";
	                    }
	                    elseif ($page == "_dis_fam_opts"){
	                    	print "Opciones para familias deshabilitadas.<br><br>";
	                    }

	                    //Save config
                        if(isset($_POST["Submit"])) {
                            $ncfg = array();

                            foreach($config_var[$page] AS $key => $val){
                                switch ($val["type"]){
                                    case "textbox" || "listbox" || "textarea":
                                        $valor = (isset($_POST[$key]))?$_POST[$key]:"";
                                    break;
                                    case "checkbox":
                                        $valor = (isset($_POST[$key]))?true:false;
                                    break;
                                }
                                $ncfg[$key] = $valor;
                            }

                            if ($page == "modules") {
                                /*
                                20101015 AG: Si estoy guardando modulos, elimino de la variable "modules" los que no esten activos y elimino
                                             las configuraciones de los modulos inactivos, esto para mejorar performance del main.php, sobre
                                             todo en sitios que tienen mucho tiempo de estar con nosotros.
                                */
                                $arrCfgBkp = $ncfg;
                                foreach( $arrCfgBkp AS $arrTMP["key"] =>  $arrTMP["value"]){
                                    if (!$arrTMP["value"]) {
                                        if ($arrTMP["key"] == "core" ||
                                            $arrTMP["key"] == "modules" ||
                                            $arrTMP["key"] == "_addresses_fields" ||
                                            $arrTMP["key"] == "_dis_fam_opts" ||
                                            $arrTMP["key"] == "_dis_grp_modules" ||
                                            $arrTMP["key"] == "_user_nonedit_fields" ||
                                            $arrTMP["key"] == "_user_fields") continue;

                                        unset($ncfg[$arrTMP["key"]]);
                                        db_query("DELETE FROM wt_config WHERE id = '{$arrTMP["key"]}'");
                                    }
                                }
                            }

                            $tempcfg = serialize( $ncfg );

                            $intExist = sqlGetValueFromKey("SELECT COUNT(*) FROM wt_config WHERE id = '{$page}'");

                            if( $intExist ) {
                                db_query( "update wt_config set config='".addslashes($tempcfg)."' where id='$page'" );
                            } else {
                                db_query( "insert into wt_config (id, config) values ( '$page','".addslashes($tempcfg)."')" );
                            }
                        }



	                    // load config
                        $arrRet = sqlGetValueFromKey("SELECT * FROM wt_config WHERE id = '{$page}'");
                        if($arrRet){
                            $rcfg[$arrRet["id"]] = unserialize($arrRet["config"]);
                        }
                        else{
                            $rcfg = 0;
                        }

	                    reset($config_var);
	                    $intCols = 1;
	                    if ($page=="modules"){
	                        ksort($config_var[$page]);
	                        //$intCols = 2;
	                    }

                        $arrFields = array();
                        foreach($config_var[$page] AS $key => $val){
                            $strTitle = "<b>".$val["desc"]."</b><br><i>".$val["obs"]."</i>";
                            $strHTML = "";

                            switch( $val["type"] ) {
                                case "textbox":
                                    $value = $val["default"];
                                    if(isset($rcfg) && is_array($rcfg) && isset($rcfg[$page][$key])){
                                        $value = $rcfg[$page][$key];
                                    }
                                    $strHTML .= <<<EOD
                                    <label for="{$key}">{$strTitle}</label>
                                    <input type="text" class="form-control" id="{$key}" name="{$key}" value="{$value}">
EOD;
                                    break;
                                case "listbox":

                                    $value = $val["default"];
                                    if(isset($rcfg) && is_array($rcfg) && isset($rcfg[$page][$key])){
                                        $value = $rcfg[$page][$key];
                                    }

                                    $strHTML .= <<<EOD
                                    <label for="{$key}">{$strTitle}</label>
                                    <select class="form-control" name="{$key}" id="{$key}">
EOD;
                                    $arrValues = explode(",",$val["values"]);
                                    foreach($arrValues AS $val2){
                                        $arrValores = explode(";",$val2);

                                        $strSelected = "";
                                        if($arrValores[0] == $value){
                                            $strSelected = "selected";
                                        }
                                        $inputName = (isset($arrValores[1]))?$arrValores[1]:$arrValores[0];
                                        $strHTML .= <<<EOD
                                        <option value="{$arrValores[0]}" {$strSelected} >{$inputName}</option>
EOD;
                                        unset($val2);
                                    }
                                    $strHTML .= <<<EOD
                                    </select>
EOD;

                                    break;
                                case "checkbox":
                                    $value = ($val["default"])?"checked":"";
                                    if(isset($rcfg) && is_array($rcfg) && isset($rcfg[$page][$key])){
                                        $value = ($rcfg[$page][$key])?"checked":"";
                                    }
                                    $strHTML .= <<<EOD
                                    <label>
                                      <input type="checkbox" name="{$key}" {$value}>
                                      {$strTitle}
                                    </label>
EOD;
                                    break;
                                case "textarea":
                                    $value = $val["default"];
                                    if(isset($rcfg) && is_array($rcfg) && isset($rcfg[$page][$key])){
                                        $value = $rcfg[$page][$key];
                                    }

                                    $strHTML .= <<<EOD
                                    <label for="{$key}">{$strTitle}</label>
                                    <textarea class="form-control" cols="60" rows="4" name="{$key}">{$value}</textarea>
EOD;
                                    break;
                                /*default: drawDebug(array("key"=>$key,"value"=>$val), "Ignored... Tipo incorrecto"); continue 2; break;*/
                            }
                            $arrFields[] = $strHTML;

                            unset($val);
                            unset($key);
                        }
                        ?>
                        <form name="admin_<?php print $page; ?>" action="admin.php?page=<?php print $page; ?>" method="post">
                            <table width="90%" border="0" cellspacing="0" cellpadding="2" align="center">
                                <?php
                                $intCol = 0;
                                $strPct = round(100/$intCols) . "%";
                                foreach($arrFields AS $val){
                                    if ($intCol==0) {
                                        print "<tr>";
                                    }
                                    ?>
                                    <td width="<?php print $strPct;?>" valign="top">
                                        <?php print $val;?><br><br>
                                    </td>
                                    <?php
                                    $intCol++;
                                    if ($intCol == $intCols) {
                                        $intCol = 0;
                                        print "</tr>";
                                    }
                                    unset($val);
                                }
                                ?>
                                <tr>
                                    <td colspan="<?php print $intCols;?>" align="center">
                                        <br>
                                        <button type="submit" name="Submit">Guardar</button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td height="15" colspan="3" align="right">&nbsp;</td>
                </tr>
                <?php
                }
            ?>
        </table>
    </body>
    </html>
