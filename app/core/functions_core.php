<?php
function core_show_login_only($strTargetLink = "index.php", $strTargetGet = false) {
    global $cfg, $config, $lang, $boolGlobalIsLocalDev;

    if ($cfg["core"]["HTTPS"] && $cfg["core"]["HTTPS_logged"] && !$cfg["core"]["inSecureSide"] && !$boolGlobalIsLocalDev) {
    	// Me aseguro de ir al secure...
    	$strLink = basename($_SERVER["PHP_SELF"]);
		$strGetVars = (isset($_SERVER["QUERY_STRING"])) ? "?{$_SERVER["QUERY_STRING"]}" : "";

		$strSite = core_getBaseDir("S");
    	$strSite = "{$strSite}{$strLink}{$strGetVars}";

    	header("Location: " . $strSite);
    	die();
	}

    $strModifiedFunction = "core_show_login_only_{$cfg["core"]["theme"]}";
    if (function_exists($strModifiedFunction)) {
        return $strModifiedFunction($strTargetLink, $strTargetGet);
    }
	$strStyle = (isset($config["theme_vars"]["NotLoggedBackGround"]))?$config["theme_vars"]["NotLoggedBackGround"]:"background-color:#FFFFFF";

    $strLogin = (isset($_POST["login_name"]))?user_input_delmagic($_POST["login_name"]):"";
	$strPW = (isset($_POST["login_passwd"]))?user_input_delmagic($_POST["login_passwd"]):"";

	$strTitulo = (isset($config["theme_vars"]["Titulo"]))?$config["theme_vars"]["Titulo"]:$cfg["core"]["title"];

    $style = "";
    $size = "2";
    if (array_key_exists("boolUseCloud", $config) && array_key_exists("strClientKey", $config) && $config["boolUseCloud"] === true && is_string($config["strClientKey"])) {
        $strTitulo = $config["strClientKey"];
        $style = "style='text-shadow: 0.07em 0.07em #A9A9A9;font-family:mv boli;color:black;'";
        $size = "6";
    }

    if ($strTargetGet == false) {
		$strGet = (isset($_GET["login"]))?"?login={$_GET["login"]}":"";
	}
	else {
		$strGet = "?{$strTargetGet}";
	}

	$strTarget = "{$strTargetLink}{$strGet}";

    $strImagePath = "themes/{$cfg["core"]["theme"]}/images/logo.jpg";
    if (!empty($cfg["core"]["theme_profile"]) && file_exists("themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/images/logo.jpg") && empty($cfg["core"]["site_profile"])) {
        $strImagePath = "themes/{$cfg["core"]["theme"]}/profiles/{$cfg["core"]["theme_profile"]}/images/logo.jpg";
    }

    if(!empty($cfg["core"]["site_profile"]) && file_exists("profiles/{$cfg["core"]["site_profile"]}/images/logo.jpg")) {
        $strImagePath = "profiles/{$cfg["core"]["site_profile"]}/images/logo.jpg";
    }

    if (isset($_GET["ajax_ih"])) {
        header("Content-Type: text/html; charset=iso-8859-1");
    }
    else {
        ?>
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
        <html>
        <?php draw_header_tag();?>
        <body id="PageBody" tabindex="-1" class="BODY">
        <?php
    }
    ?>
    <table border="0" width="100%" height="100%" cellspacing="0" cellpadding="0" style="<?php print $strStyle;?>">
        <tr>
            <td colspan="3" style="<?php print $strStyle;?>">&nbsp;</td>
        </tr>
        <tr>
            <td style="<?php print $strStyle;?>">&nbsp;</td>
            <td align="center" valign="middle" width="500" style="<?php print $strStyle;?>">
                <hr>
                <img src="<?php print $strImagePath;?>"><br><br>
                <b>
                <font size="<?php print $size; ?>" <?php print $style; ?>>
                    <?php print $strTitulo;?>
                </font>
                </b>
                <hr>
                <?php
                if(isset($_SESSION["wt"]["error"]) && strlen($_SESSION["wt"]["error"])>0) {
			        ?>
			        <center>
			        	<span class="error">
			        		<?php print $_SESSION["wt"]["error"];?>
			        	</span>
			        </center>
			        <?php
			        unset($_SESSION["wt"]["error"]);
			    }
			    ?>
                <form action="<?php print $strTarget;?>" name="login_form" id="login_form" method="post">
                    <input type="hidden" name="submit_login" value="1">
                    <table border="0" cellspacing="1" cellpadding="2">
                        <tr>
                            <th align="right" style="<?php print $strStyle;?>">
                               <?php echo $lang["LOGIN_NAME"]; ?>
                            </th>
                            <td align="left" style="<?php print $strStyle;?>">
                                <input type="text" name="login_name" size="20" maxlength="25" class="field_textbox" value="<?php htmlSafePrint($strLogin);?>">
                            </td>
                        </tr>
                        <tr>
                            <th align="right" style="<?php print $strStyle;?>">
                                <?php echo $lang["LOGIN_PASSWD"];?>
                            </th>
                            <td align="left" style="<?php print $strStyle;?>">
                                <input type="password" name="login_passwd" size="20" maxlength="20" class="field_textbox" value="<?php htmlSafePrint($strPW);?>">
                            </td>
                        </tr>
                        <?php
                        if (isset($_SESSION["wt"]["boolAlreadyConected"])) {
                            ?>
                            <tr>
	                            <td align="center" colspan="2" style="<?php print $strStyle;?>">
	                                <input type="checkbox" name="force_disconect" value="1" class="field_checkbox"><?php echo $lang["LOGIN_FORCEDISCONNECT"];?>
	                            </td>
	                        </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <td align="center" colspan="2" style="<?php print $strStyle;?>">
                                <input type="checkbox" name="login_auto" value="1" class="field_checkbox"><?php echo $lang["LOGIN_AUTOLOGIN"]; ?>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" colspan="2" style="<?php print $strStyle;?>">
                            	<input type="hidden" name="screenInfo">
                            	<table align="center" cellspacing="0" cellpadding="0" border="0">
                            		<tr>
                            			<td align="center">
                                            <button type="submit" name="submit_login" value="1">
                                                <?php print $lang["LOGIN_BUTTON"]; ?>
                                            </button>
                            			</td>
                            		</tr>
                            	</table>
                            </td>
                        </tr>
                    </table>
                </form>
            </td>
            <td style="<?php print $strStyle;?>">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3" style="<?php print $strStyle;?>">&nbsp;</td>
        </tr>
    </table>
    <script for="window" event="onload" language="JavaScript" type="text/javascript">
        var objScreen = window.screen;
        var strScreenInfo = "";
        strScreenInfo += "availHeight=" + objScreen.availHeight + ",";
        strScreenInfo += "availWidth=" + objScreen.availWidth + ",";
        strScreenInfo += "height=" + objScreen.height + ",";
        strScreenInfo += "width=" + objScreen.width + ",";
        strScreenInfo += "bufferDepth=" + objScreen.bufferDepth + ",";
        strScreenInfo += "colorDepth=" + objScreen.colorDepth + ",";
        strScreenInfo += "updateInterval=" + objScreen.updateInterval + ",";

        document.login_form.login_name.focus();
        document.login_form.screenInfo.value = strScreenInfo;
    </script>
    <?php

    if (!isset($_GET["ajax_ih"])) {
        ?>
        </body>
        </html>
        <?php
    }

    if (isset($_SESSION["wt"]["boolAlreadyConected"])) unset($_SESSION["wt"]["boolAlreadyConected"]);
}

function core_show_AccountsToBeActivated() {
	global $cfg, $config, $lang;

	$boolReturn = false;

	if (isset($cfg["core"]["AccountRequest"]) &&
		$cfg["core"]["AccountRequest"] &&
		isset($cfg["core"]["AccountRequest_manual"]) &&
		$cfg["core"]["AccountRequest_manual"] &&
		check_user_class($config["admmenu"][$lang["ACCOUNT_ACTIVATION"]]["class"])) {

		$strQuery = "SELECT uid, name, realname, dateregistered, expirationdate
					 FROM wt_users
					 WHERE active = 'N' AND
					 	   isTemp = 'Y' AND
					 	   mail_confirmed = 'Y' AND
					 	   swusertype = '{$cfg["core"]["AccountRequest_type"]}'
					 ORDER BY expirationdate";
		$qTMP = db_query($strQuery);
		if (db_num_rows($qTMP)) {
			theme_draw_centerbox_open($lang["ACCOUNT_ACTIVATION"]);

			?>
			<table width="100%" cellspacing="0" cellpadding="2" border="0">
				<tr>
					<td class="row0"><?php print $lang["NAME"];?></td>
					<td class="row0" width="1%" nowrap><?php print $lang["MYACCT_DATEREGISTERED"];?></td>
					<td class="row0" width="1%" nowrap><?php print $lang["MYACCT_EXPDATE"];?></td>
				</tr>
				<?php
				$strClass = "row1";
				while ($rTMP = db_fetch_assoc($qTMP)) {
					?>
					<tr style="cursor:pointer;" onclick="document.location.href='adm_account_activation.php?userid=<?php print $rTMP["uid"];?>'">
						<td class="<?php print $strClass;?>"><?php print $rTMP["realname"];?></td>
						<td class="<?php print $strClass;?>" align="center" nowrap><?php print show_date($rTMP["dateregistered"], false);?></td>
						<td class="<?php print $strClass;?>" align="center" nowrap><?php print show_date($rTMP["expirationdate"], false);?></td>
					</tr>
					<?php
					$strClass = ($strClass == "row1")?"row2":"row1";
				}
				?>
			</table>
			<?php
			theme_draw_centerbox_close();

			$boolReturn = true;
		}
		db_free_result($qTMP);
	}

	return $boolReturn;
}

function core_checkForKnownIP($strRemoteIP) {
    $arrValidIP = array();
    $arrValidIP["216.152.129.18"] = "server3";
    $arrValidIP["216.152.129.19"] = "server5";
    $arrValidIP["216.152.129.20"] = "server4";
    $arrValidIP["216.152.129.21"] = "server6";
    //$arrValidIP["190.0.199.148"] = "HML IP";
    //$arrValidIP["10.1.1.30"] = "LocalDev";

    return isset($arrValidIP[$strRemoteIP]);
}
function core_checkForUpdates($strRemoteWebsite, $strRemoteIP) {
	include_once("core/xmlfunctions.php");
	global $cfg, $config, $lang;

	if (!core_checkForKnownIP($strRemoteIP) || !$cfg["core"]["update_server"]) {
		return false;
	}

	$objXML = new XMLNode("updatesList");
	$strQuery = "SELECT U.*, US.id AS statusID, US.status
				 FROM wt_updates AS U
				 		LEFT JOIN wt_updates_server_status AS US
				 		ON US.updateid = U.id AND
				 		   US.website = '{$strRemoteWebsite}'
				 WHERE (US.status IS NULL OR US.status = 'fail') AND
				 	   U.update_type = 'S' AND
				 	   U.rdy_to_delete = 'N'
				 ORDER BY U.fecha, U.id";
	$qTMP = db_query($strQuery);
	while ($rTMP = db_fetch_assoc($qTMP)) {
		$objUpdate = &$objXML->children[$objXML->addChild("update")];
		$objUpdate->addAttribute("updateID", $rTMP["id"]);
		$objUpdate->addAttribute("statusID", (empty($rTMP["statusID"]))?0:$rTMP["statusID"]);
		$objUpdate->addAttribute("filename", $rTMP["filename"]);
		$objUpdate->addAttribute("modulo", $rTMP["modulo"]);
		$objUpdate->addAttribute("hasMain", $rTMP["hasMain"]);
		$objUpdate->addAttribute("hasMySQL", $rTMP["hasMySQL"]);
		$objUpdate->addAttribute("hasCode", $rTMP["hasCode"]);
		$objUpdate->addAttribute("status", $rTMP["status"]);
	}
	db_free_result($qTMP);

	return $objXML;
}

function core_showNotifications($strModulo, $strCenterBoxTitle, $arrNotificaciones, $boolIgnoreZeroes = true, $strExtraExplanation = "") {

    global $cfg;

	if ($boolIgnoreZeroes) {
		$arrTMP = array();
		while ($arrItem = each($arrNotificaciones)) {
			if ($arrItem["value"]["severity"] == 0) continue;

			$arrTMP[] = $arrItem["value"];
		}

		$arrNotificaciones = $arrTMP;
	}

	$intNotifications = count($arrNotificaciones);
	if ($intNotifications) {
		$strImage = strGetCoreImageWithPath("module_icons/{$strModulo}.png");
		if (!file_exists($strImage)) {
			$strImage = strGetCoreImageWithPath("module_icons/default.png");
		}

		theme_draw_centerbox_open($strCenterBoxTitle);

		$boolFirst = true;
		$intCols = 2;
		$intCol = 1;
		$intRowSpan = ceil($intNotifications/$intCols);
		$intTextWidth = (round(95/$intCols) - 3)."%";

		?>
		<table width="100%" cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td width="1%" valign="top" align="center">
					<img src="<?php print $strImage;?>">
				</td>
				<td style="padding:0;">
					<table width="100%" cellspacing="0" cellpadding="2" border="0">
						<?php
						if (!empty($strExtraExplanation)) {
							?>
							<tr>
								<td colspan="<?php print (($intCols * 3) + ($intCols - 1));?>" class="info" align="center" style="font-weight:bold;">
									<?php htmlSafePrint($strExtraExplanation);?>
								</td>
							</tr>
							<?php
						}
						$strClass = "row1";
						while ($arrItem = each($arrNotificaciones)) {
							if ($intCol == 1) {
								print "<tr>";
							}
							else {
								print "<td width='1%' class='{$strClass}'>&nbsp;</td>";
							}
							switch ($arrItem["value"]["severity"]) {
								case 0:
									$strImage = "ok.gif";
									break;
								case 1:
									$strImage = "attention.gif";
									break;
								case 2:
									$strImage = "attention.gif";
									break;
								case 3:
									$strImage = "attention.gif";
									break;
							}
							?>
							<td class="<?php print $strClass;?>" width="1%" align="center">
								<img src="<?php print strGetCoreImageWithPath($strImage);?>">
							</td>
							<td class="<?php print $strClass;?>" width="<?php print $intTextWidth;?>" align="left">
								<?php
								if ($arrItem["value"]["severity"] == 0) {
									print $arrItem["value"]["text"];
								}
								else {
									?>
									<a href="<?php print $arrItem["value"]["link"];?>"><?php print $arrItem["value"]["text"];?></a>
									<?php
								}
								?>
							</td>
							<td class="<?php print $strClass;?>" width="1%" align="center" style="font-weight:bolder;">
								<?php
								if ($arrItem["value"]["severity"] == 0) {
									print $arrItem["value"]["number"];
								}
								else {
									?>
									<a href="<?php print $arrItem["value"]["link"];?>"><?php print $arrItem["value"]["number"];?></a>
									<?php
								}
								?>
							</td>
							<?php

							$intCol++;
							if ($intCol > $intCols) {
								print "</tr>";
								$intCol = 1;
								$strClass = ($strClass == "row1")?"row2":"row1";
							}

							$boolFirst = false;
						}
						$boolFinishRow = ($intCol <= $intCols && $intCol > 1);
						while ($intCol <= $intCols && $intCol > 1) {
							?>
							<td width="1%" class='<?php print $strClass;?>'>&nbsp;</td>
							<td class="<?php print $strClass;?>" width="1%" align="center">
								&nbsp;
							</td>
							<td class="<?php print $strClass;?>" width="<?php print $intTextWidth;?>" align="left">
								&nbsp;
							</td>
							<td class="<?php print $strClass;?>" width="1%" align="center" style="font-weight:bolder;">
								&nbsp;
							</td>
							<?php
							$intCol++;
						}
						if ($boolFinishRow) print "</tr>";
						reset($arrNotificaciones);
						?>
					</table>
				</td>
			</tr>
		</table>
		<?php
		theme_draw_centerbox_close();
	}
}