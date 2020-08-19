<?php
if (isset($_GET["doBKP"])) {
	include_once("core/main.php");
	
	$index_page = false;
	$page_name = $lang["DB_BKP"];
	
	if ($cfg["core"]["allow_user_bkp"] && check_user_class("backup_database")) {
		draw_header();
		theme_draw_centerbox_open($page_name);
		if (isset($_POST["submit"])) {
			$strPath = dirname(__FILE__);
        	$strPath .= "/swadmin/HomelandBackups";
        	$strPath = str_replace("\\", "/", $strPath);
        	
			if ($_POST["BackupType"] == "backup") {
                $strTables = "SHOW TABLES";
                $qTables = db_query($strTables);
                while ($rTable = db_fetch_array($qTables)){
                    echo "- ".$rTable[0]."<br>";
                    db_query("LOCK TABLES {$rTable[0]} READ");
                    db_query("FLUSH TABLE {$rTable[0]}");
                    db_query("BACKUP TABLE {$rTable[0]} TO '{$strPath}'");
                    db_query("UNLOCK TABLES");
                    /*
                    $strError = db_error();
                    echo "Errores: " . $strError . "<hr>";
                    */
                }
                db_free_result($qTables);
			}
			else {
                //RESTORE TABLE tbl_name[,tbl_name...] FROM '/path/to/backup/directory'
                if ($directory = @opendir($strPath)) {
                	?>
                	<table width="100%" border="1">
                		<?php 
	                    while (($file = readdir($directory)) !== false) {
	                        $FileInfo = pathinfo($file);
	                        if (isset($FileInfo["extension"]) && $FileInfo["extension"]=="MYD") {
	                            $strTableName = substr($FileInfo["basename"],0,strlen($FileInfo["basename"])-4);
	                            db_query("DROP TABLE {$strTableName}");
	                            $qStatus = db_query("RESTORE TABLE {$strTableName} FROM '{$strPath}'");
	                            ?>
                                <tr>
                                    <th class="row0" colspan="4" align="left"><?php print $strTableName;?></th>
                                </tr>
                                <?php
                                while ($rStatus = db_fetch_array($qStatus)){
                                    echo "<tr>";
                                        echo "<td class='row1' width='40%'>{$rStatus[0]}</td>";
                                        echo "<td class='row1' width='10%'>{$rStatus[1]}</td>";
                                        echo "<td class='row1' width='10%'>{$rStatus[2]}</td>";
                                        echo "<td class='row1' width='40%'>{$rStatus[3]}</td>";
                                    echo "</tr>";
                                }
	                        }
	                    }
	                    ?>
	                </table>
	                <?php 
                    closedir($directory);
                }
			}
			print "<h3>Terminado</h3>";
		}
		else {
			?>
			<form method="post" action="admin.php?doBKP=true">
                <input class="field_checkbox" name="BackupType" type="radio" value="backup" checked>Backup<br>
                <input class="field_checkbox" name="BackupType" type="radio" value="restore">Restore<br><br>
                <input class="button" name="submit" type="submit" value="Enviar">
            </form>
			<?php 
		}
		theme_draw_centerbox_close();
		draw_footer();
	}
	else {
		die($lang["ACCESS_DENIED"]);
	}
}
else {
	require_once("swadmin/index.php");
}
?>