<?php
// Este archivo recibe y devuelve el XML de y para Flash.
include_once("core/miniMain.php");
include_once("core/functions_core.php");
include_once("core/xmlfunctions.php");

$boolDebug = isset($_GET["debug"]);

	if (isset($_GET["update"]) && $cfg["core"]["update_server"]) {
		$strUpdate = user_input_delmagic($_GET["update"]);
		if ($strUpdate == "getList" && isset($_GET["website"])) {
			$strWebsite = user_input_delmagic($_GET["website"]);
			$XMLList = core_checkForUpdates($strWebsite, $_SERVER["REMOTE_ADDR"]);
			if ($XMLList) {
				if ($boolDebug) {
					header("Content-Type: text/xml");
					print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
				}
				print $XMLList->toString();
			}
		}
		elseif ($strUpdate == "done" && isset($_GET["website"]) && isset($_GET["updateID"]) && isset($_GET["status"]) && isset($_GET["statusID"])) {
			$strWebsite = db_escape(user_input_delmagic($_GET["website"]));
			$intUpdateID = intval(user_input_delmagic($_GET["updateID"]));
			$strStatus = db_escape(user_input_delmagic($_GET["status"]));
			$intStatusID = intval(user_input_delmagic($_GET["statusID"]));
			if ($intStatusID) {
				$strQuery = "REPLACE INTO wt_updates_server_status
							 (id, updateid, website, remote_ip, fecha, status)
							 VALUES
							 ({$intStatusID}, {$intUpdateID}, '{$strWebsite}', '{$_SERVER["REMOTE_ADDR"]}', NOW(), '{$strStatus}')";
			}
			else {
				$strQuery = "INSERT INTO wt_updates_server_status
							 (updateid, website, remote_ip, fecha, status)
							 VALUES
							 ({$intUpdateID}, '{$strWebsite}', '{$_SERVER["REMOTE_ADDR"]}', NOW(), '{$strStatus}')";
			}
			db_query($strQuery);
		}
	}
?>