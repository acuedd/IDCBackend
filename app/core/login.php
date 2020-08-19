<?php
function draw_small_login_box($strCellClass = "sideboxtext", $strTextBoxClass = "field_textbox", $strButtonClass = "button", $strCheckBoxClass = "field_checkbox",
							  $boolSingleRow = true, $strBackGroundImage = "", $strWidth = "100%", $strHeight = "100%") {
	global $lang, $cfg;

	if(!$_SESSION["wt"]["logged"]) {
		$strGet = (isset($_GET["login"]))?"?login={$_GET["login"]}":"";
		$strLogin = (isset($_POST["login_name"]))?$_POST["login_name"]:"";
		$strPW = (isset($_POST["login_passwd"]))?$_POST["login_passwd"]:"";

		?>
		<table cellspacing="0" cellpadding="0" border="0" <?php print ($boolSingleRow)?"":"width='100%'";?> <?php print (empty($strBackGroundImage))?"":"style=\"background-image:url({$strBackGroundImage})\";";?>>
		<form action="index.php<?php print $strGet;?>" name="login_form" id="login_form" method="post">
            <input type="hidden" name="submit_login" value="1">
			<tr>
				<td width="<?php print ($boolSingleRow)?"1":"50%";?>" valign="bottom" class="<?php print $strCellClass;?>" nowrap>
					<?php echo $lang["LOGIN_NAME"];?><br>
					<center><input type="text" name="login_name" <?php print ($boolSingleRow)?"size='10'":"style='width:90%;'";?> maxlength="40" class="field_textbox" value="<?php print $strLogin;?>"></center>
				</td>
				<td width="<?php print ($boolSingleRow)?"1":"50%";?>" valign="bottom" class="<?php print $strCellClass;?>" style="padding-left:6px;" colspan="<?php print ($boolSingleRow)?1:2;?>" nowrap>
					<?php echo $lang["LOGIN_PASSWD"];?><br>
					<center><input type="password" name="login_passwd" <?php print ($boolSingleRow)?"size='10'":"style='width:90%;'";?> maxlength="20" class="field_textbox" value="<?php print $strPW;?>"></center>
				</td>
			<?php if(!$boolSingleRow) { print "</tr><tr>";}?>
				<td <?php print ($boolSingleRow)?"width='110'":"";?> align="center" valign="middle" class="<?php print $strCellClass;?>"
					style="padding-left:2px; padding-right:2px;" colspan="<?php print ($boolSingleRow)?1:3;?>" nowrap>
					<table cellspacing="0" cellpadding="0" border="0" align="center">
						<tr>
							<td>
								<input type="hidden" name="screenInfo">
								<input type="checkbox" name="login_auto" value="1" class="field_checkbox"><span style="font-size:8px;">&nbsp;</span><?php print $lang["LOGIN_AUTOLOGIN"];?>
							</td>
							<td style="padding-left:4;">
								<button type="submit" class="btn btn-primary" name="submit_login" value="1">
									<?php print $lang["LOGIN_BUTTON"]; ?>
								</button>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<?php
			if (isset($_SESSION["wt"]["boolAlreadyConected"])) {
				?>
				<tr>
					<td align="center" colspan="3" class="<?php print $strCellClass;?>">
						<input type="checkbox" name="force_disconect" value="1" class="field_checkbox"><?php echo $lang["LOGIN_FORCEDISCONNECT"];?>
					</td>
				</tr>
				<?php
			}
			$boolShowLostPWD = (!isset($cfg["core"]["lostPWD"]) || !$cfg["core"]["lostPWD"]);
			if ($boolShowLostPWD) {
				?>
				<tr>
					<?php
					if ($boolShowLostPWD) {
						?>
						<td align="center" class="lst_pwd_normal" colspan="<?php print ($boolShowCreateAcc)?"2":"3";?>"
							onclick="document.location.href='lostpasswd.php'"
							onmouseover="this.className='lst_pwd_over'" onmouseout="this.className='lst_pwd_normal'"
							style="cursor:pointer;">
							<?php echo $lang["LOGIN_LOST_PWD"];?>
						</td>
						<?php
					}
					?>
				</tr>
				<?php
			}
			?>
		</form>
		</table>
		<script language="JavaScript" type="text/javascript">
			var objScreen = window.screen;
			var strScreenInfo = "";
			strScreenInfo += "availHeight=" + objScreen.availHeight + ",";
			strScreenInfo += "availWidth=" + objScreen.availWidth + ",";
			strScreenInfo += "height=" + objScreen.height + ",";
			strScreenInfo += "width=" + objScreen.width + ",";
			strScreenInfo += "bufferDepth=" + objScreen.bufferDepth + ",";
			strScreenInfo += "colorDepth=" + objScreen.colorDepth + ",";
			strScreenInfo += "updateInterval=" + objScreen.updateInterval + ",";

			<?php
			if(isset($_SESSION["wt"]["error"]) && strlen($_SESSION["wt"]["error"])>0) {
				?>
				alert("<?php print str_replace("<br>", "\\n", $_SESSION["wt"]["error"]);?>");
				<?php
				unset($_SESSION["wt"]["error"]);
			}
			if (isset($_SESSION["wt"]["boolAlreadyConected"])) unset($_SESSION["wt"]["boolAlreadyConected"]);
			if (isset($_SESSION["wt"]["boolUserPWIncorrect"])) unset($_SESSION["wt"]["boolUserPWIncorrect"]);
			?>

			document.login_form.login_name.focus();
			document.login_form.screenInfo.value = strScreenInfo;
		</script>
		<?php
	}
	else {
		$strTMP = $_SESSION["wt"]["nombres"];
		if (isset($cfg["core"]["use_nickname_in_welcome"]) && $cfg["core"]["use_nickname_in_welcome"]) {
			$strTMP = $_SESSION["wt"]["nickname"];
		}

		$strBackGroundImage = (!empty($strBackGroundImage))?"background-image:url({$strBackGroundImage})":"";
		$strSuffix = substr($strWidth, -1);
		if ($strSuffix != "%") $strSuffix = "";
		?>
		<table width="<?php print $strWidth;?>" height="<?php print $strHeight;?>" cellspacing="0" cellpadding="0" border="0" style="<?php print $strBackGroundImage;?>">
			<tr>
				<td style="font-size:1px;">&nbsp;</td>
			</tr>
			<tr>
				<td height="1<?php print $strSuffix;?>" align="center" class="<?php print $strCellClass;?>">
					<?php
					if ($_SESSION["wt"]["sex"] == "Male" || $_SESSION["wt"]["sex"] == "Female") {
						printf($lang["LOGIN_WELCOME_{$_SESSION["wt"]["sex"]}"], $strTMP);
					}
					else {
						printf($lang["LOGIN_WELCOME"], $strTMP);
					}
					?>
				</td>
			</tr>
			<tr>
				<td align="center" height="1<?php print $strSuffix;?>">
					<button type="button" class="btn btn-warning" onclick="document.location.href='index.php?act=logout'">
						<?php print $lang["LOGIN_LOGOUT"]; ?>
					</button>
				</td>
			</tr>
			<tr>
				<td style="font-size:1px;">&nbsp;</td>
			</tr>
		</table>
		<?php
	}
}

function draw_login_box($intFontSize = 14, $strSide = "right"){

	global $config, $lang, $cfg;

	$strSideBox = "theme_draw_{$strSide}box_open";
	//$strSideBox($lang["LOGIN_TITLE"], "DivSideBoxes_20000");
	$strSideBox($lang["LOGIN_TITLE"]);
	if (!$_SESSION["wt"]["logged"]) {
		$strLogin = (isset($_POST["login_name"]))?$_POST["login_name"]:"";
		$strPW = (isset($_POST["login_passwd"]))?$_POST["login_passwd"]:"";

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
		$strGet = (isset($_GET["login"]))?"?login={$_GET["login"]}":"";
		?>
		<table border="0" cellpadding="2" cellspacing="0" align="center" width="90%">
		<form action="index.php<?php print $strGet;?>" name="login_form" id="login_form" method="post">
			<tr><td class="sideboxtext"><?php echo $lang["LOGIN_NAME"]; ?><br>
				<input type="text" name="login_name" style="width:100%;" maxlength="40" class="field_textbox" value="<?php print $strLogin;?>"><br>
				<?php echo $lang["LOGIN_PASSWD"]; ?><br>
				<input type="password" name="login_passwd" style="width:100%;" maxlength="20" class="field_textbox" value="<?php print $strPW;?>"><br>
				<?php
				if (isset($_SESSION["wt"]["boolAlreadyConected"])) {
					?>
					<input type="checkbox" name="force_disconect" value="1" class="field_checkbox"><?php echo $lang["LOGIN_FORCEDISCONNECT"];?><br>
					<?php
				}
				?>
				<input type="checkbox" name="login_auto" value="1" class="field_checkbox"><?php echo $lang["LOGIN_AUTOLOGIN"]; ?></td>
			</tr>
			<tr><td class="sideboxtext" align="right">
				<input type="hidden" name="screenInfo" />
				<button type="submit" class="btn btn-primary" name="submit_login" value="1">
					<?php print $lang["LOGIN_BUTTON"]; ?>
				</button>
			</td>
			</tr>
		</form>
		</table><br>
		<?php
		if (isset($_SESSION["wt"]["boolAlreadyConected"])) unset($_SESSION["wt"]["boolAlreadyConected"]);
		if (isset($_SESSION["wt"]["boolUserPWIncorrect"])) unset($_SESSION["wt"]["boolUserPWIncorrect"]);
		?>
		<script language="JavaScript" type="text/javascript">
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
	}
	else{
		$strQuery = "SELECT sex FROM wt_users WHERE uid = {$_SESSION["wt"]["uid"]}";
		$qTMP = db_query($strQuery);
		$rTMP = db_fetch_array($qTMP);
		db_free_result($qTMP);

		$strTMP = $_SESSION["wt"]["nombres"];
		if (isset($cfg["core"]["use_nickname_in_welcome"]) && $cfg["core"]["use_nickname_in_welcome"]) {
			$strTMP = $_SESSION["wt"]["nickname"];
		}

		if ($rTMP["sex"] == "Male" || $rTMP["sex"] == "Female") {
			printf($lang["LOGIN_WELCOME_{$rTMP["sex"]}"], $strTMP);
		}
		else {
			printf($lang["LOGIN_WELCOME"], $strTMP);
		}
		?>
		<br style="font-size:3px;">
		<table align="center" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td align="center" class="logout_normal">
					<button type="button" class="btn btn-warning" onclick="document.location.href='index.php?act=logout'">
						<?php print $lang["LOGIN_LOGOUT"]; ?>
					</button>
				</td>
			</tr>
		</table>
		<br style="font-size:3px;">
		<?php
	}

	$strSideBox = "theme_draw_{$strSide}box_close";
	//$strSideBox("DivSideBoxes_20000");
	$strSideBox();
	// if message module is on, show how many messages the user has
	if(check_module("messages")) {
		check_user_messages($strSide);
	}
}

/**
 * @return boolean_true=expired
 * @desc Esta funcion verifica que la sesion no haya expirado y mantiene el registro del usuario en wt_online
*/
function check_session_timeout($boolLogPublic = true) {
global $cfg;

	$intUserID = (isset($_SESSION["wt"]["logged"]) && $_SESSION["wt"]["logged"])?$_SESSION["wt"]["uid"]:0;
	$strSessID = session_id();

	$intTimeOut = (isset($cfg["core"]["sess_timeout"]))?$cfg["core"]["sess_timeout"]:20;

	db_query("DELETE FROM wt_online WHERE hora < DATE_SUB(NOW(), INTERVAL {$intTimeOut} MINUTE)");
	db_query("DELETE FROM wt_tokens WHERE DATEDIFF(NOW(),created_at) > 1"); //Clean tokens where created at more a 2 days
	//db_query("DELETE wt_tokens FROM wt_tokens LEFT JOIN wt_online ON wt_online.id = wt_tokens.sessionid WHERE wt_online.id IS NULL"); // Borra los tokens de sesiones no activas
	db_query("DELETE FROM wt_cache_misc WHERE dateTimeRegistered < DATE_SUB(NOW(), INTERVAL duration_segs SECOND)"); // Borra el cache expirado
	db_query("DELETE wt_cache_misc FROM wt_cache_misc LEFT JOIN wt_online ON wt_online.id = wt_cache_misc.sessionid WHERE wt_online.id IS NULL"); // Borra el cache de sesiones no activas

	$boolReturn = false;
	$qTMP = db_query("SELECT id FROM wt_online WHERE id='{$strSessID}' AND uid='{$intUserID}'");
	if (db_num_rows($qTMP)) {
		// Si existe el registro, actualizo el tiempo.
		db_query("UPDATE wt_online SET hora=NOW() WHERE id='{$strSessID}' AND uid='{$intUserID}'");
	}
	else {
		// Si NO existe el registro.
		if ($intUserID>0) {
			// Si estaba en línea, lo deja afuera...
			clear_login();
			$boolReturn = true;
		}
		db_query("REPLACE INTO wt_online (id,hora,uid) VALUES('{$strSessID}',NOW(),0)");

		if (isset($cfg["core"]["visits_log"]) && $cfg["core"]["visits_log"] && $boolLogPublic) {
			db_query("INSERT INTO wt_log_visitas (sessid, from_ip, fecha) VALUES ('{$strSessID}','{$_SERVER["REMOTE_ADDR"]}',NOW())");
		}
	}
	db_free_result($qTMP);

	return $boolReturn;
}

function core_fillBrowserInformation() {
	$_SESSION["wt"]["browser"]["version"] = (isset($_SERVER["HTTP_USER_AGENT"]))?$_SERVER["HTTP_USER_AGENT"]:"";
	$strTMP = $_SESSION["wt"]["browser"]["version"];

	$arrInfo = array();
	$arrInfo["OS"] = "";
	$arrInfo["browser"] = "";
	$arrInfo["browserSN"] = "";
	$arrInfo["IEVer"] = "";
	$arrInfo["boolIsAndroid"] = false;
	$arrInfo["boolIsiPhone"] = false;
	$arrInfo["boolIsWindows"] = false;
	$arrInfo["boolIsMac"] = false;
	$arrInfo["boolIsLinux"] = false;

	//First get the platform?
	if (preg_match('/linux/i', $strTMP)) {
		$arrInfo["OS"] = 'linux';
		$arrInfo["boolIsLinux"] = true;
	}
	elseif (preg_match('/macintosh|mac os x/i', $strTMP)) {
		$arrInfo["OS"] = 'mac';
		$arrInfo["boolIsMac"] = true;
	}
	elseif (preg_match('/windows|win32/i', $strTMP)) {
		$arrInfo["OS"] = 'windows';
		$arrInfo["boolIsWindows"] = true;
	}

	if (preg_match('/android/i', $strTMP)) {
		$arrInfo["OS"] = 'Android';
		$arrInfo["boolIsAndroid"] = true;
	}
	elseif (preg_match('/iphone/i', $strTMP)) {
		$arrInfo["OS"] = 'iPhone';
		$arrInfo["boolIsiPhone"] = true;
	}

	$arrInfo["boolIsMSIE"] = false;
	$arrInfo["boolIsMozilla"] = false;
	$arrInfo["boolIsChrome"] = false;
	$arrInfo["boolIsSafari"] = false;
	$arrInfo["boolIsOpera"] = false;
	$arrInfo["boolIsNetscape"] = false;

	// Next get the name of the useragent yes seperately and for good reason
	if (preg_match('/MSIE/i',$strTMP) && !preg_match('/Opera/i',$strTMP)) {
		$arrInfo["browser"] = 'Internet Explorer';
		$arrInfo["browserSN"] = "MSIE";
		$arrInfo["boolIsMSIE"] = true;
	}
	elseif (preg_match('/Firefox/i',$strTMP)) {
		$arrInfo["browser"] = 'Mozilla Firefox';
		$arrInfo["browserSN"] = "Firefox";
		$arrInfo["boolIsMozilla"] = true;
	}
	elseif (preg_match('/Chrome/i',$strTMP)) {
		$arrInfo["browser"] = 'Google Chrome';
		$arrInfo["browserSN"] = "Chrome";
		$arrInfo["boolIsChrome"] = true;
	}
	elseif (preg_match('/Safari/i',$strTMP)) {
		if ($arrInfo["boolIsAndroid"]) {
			$arrInfo["browser"] = 'Google Chrome on Android';
			$arrInfo["browserSN"] = "Chrome";
			$arrInfo["boolIsChrome"] = true;
		}
		else {
			$arrInfo["browser"] = 'Apple Safari';
			$arrInfo["browserSN"] = "Safari";
			$arrInfo["boolIsSafari"] = true;
		}
	}
	elseif (preg_match('/Opera/i',$strTMP)) {
		$arrInfo["browser"] = 'Opera';
		$arrInfo["browserSN"] = "Opera";
		$arrInfo["boolIsOpera"] = true;
	}
	elseif (preg_match('/Netscape/i',$strTMP)) {
		$arrInfo["browser"] = 'Netscape';
		$arrInfo["browserSN"] = "Netscape";
		$arrInfo["boolIsNetscape"] = true;
	}

	// finally get the correct version number
	$arrKnown = array('Version', $arrInfo["browserSN"], 'other');
	$strPattern = '#(?<browser>' . join('|', $arrKnown) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($strPattern, $strTMP, $arrMatches)) {
		// we have no matching number just continue
	}

	// see how many we have
	$intTMP = count($arrMatches['browser']);
	if ($intTMP != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if (strripos($strTMP,"Version") < strripos($strTMP,$arrInfo["browserSN"])){
			$arrInfo["IEVer"] = $arrMatches['version'][0];
		}
		else {
			$arrInfo["IEVer"] = (isset($arrMatches['version'][1]))?$arrMatches['version'][1]:0;
		}
	}
	else {
		$arrInfo["IEVer"] = $arrMatches['version'][0];
	}

	// check if we have a number
	if ($arrInfo["IEVer"]==null || $arrInfo["IEVer"]=="") {$arrInfo["IEVer"]="?";}

	$_SESSION["wt"]["browser"]["detail"] = $arrInfo;
}

/**
 * @return void
 * @desc Esta funcion BORRA la sesion y resetea los datos de la misma.
*/
function clear_login() {
	global $cfg;

	// All Session vars used may be listed here
	$strSessID = session_id();
	$boolFrameDrawn = false;
	if (isset($_SESSION["wt"]["frameDrawn"])) {
		$boolFrameDrawn = true;
	}

	if (isset($cfg["core"]["visits_log"]) && $cfg["core"]["visits_log"]) {
		if(isset($_SESSION["wt"]["lastvisitID"]))  db_query("UPDATE wt_log_visitas SET fecha_out = NOW() WHERE id = '{$_SESSION["wt"]["lastvisitID"]}'");
	}

	$_SESSION["wt"] = array();
	$_SESSION["wt"]["uid"] = 0;
	$_SESSION["wt"]["name"] = "";
	$_SESSION["wt"]["class"] = "normal";
	$_SESSION["wt"]["logged"] = false;
	$_SESSION["wt"]["style"] = "style.css";
	$_SESSION["wt"]["url"] = $cfg["core"]["url"];
	$_SESSION["wt"]["access"] = array();
	$_SESSION["wt"]["swusertype"] = "*PUBLIC*";
	$_SESSION["wt"]["clickCount"] = 0;
	$_SESSION["wt"]["lastvisitID"] = 0;
	$_SESSION["wt"]["categories"] = array();
	if ($boolFrameDrawn) $_SESSION["wt"]["frameDrawn"] = true;

	core_fillBrowserInformation();

	db_query("UPDATE wt_online SET uid = 0 WHERE id = '{$strSessID}'");
	db_query("DELETE FROM wt_cache_misc WHERE sessionid = '{$strSessID}'"); //20131014 AG: Borro todo lo que tenga en el cache de la sesion...
}

function fill_login( $uid, $autologin = false, $login_passwd = "" ) {
	// fills Session with login data
	global $cfg, $intGlobalPageProcessedLogID;

	$uid = intval($uid);
	$ret = db_query("SELECT uid,
							name,
							nombres,
							apellidos,
							nickname,
							sex,
							class,
							swusertype,
							lastvisit
					 FROM wt_users
					 WHERE uid='{$uid}' AND active='Y'");
	if(!$ret || db_num_rows($ret)!=1) {
		$_SESSION["wt"]["error"] = "An error ocurred trying to fill login data";
	} else {
		$row = db_fetch_array($ret);

		// get modules access for the user
		$retacc = db_query("SELECT module FROM wt_user_access WHERE userid={$uid}");
		if(!$retacc) {
			$_SESSION["wt"]["error"] = "An error ocurred trying to get access";
		} else {
			while( $row_access = db_fetch_array($retacc) ) {
				$_SESSION["wt"]["access"][$row_access["module"]] = true;
			}
			db_free_result( $retacc );
		}

		if(check_module("sales")){
            if($row["class"] == "admin"){
                $strQuery = "SELECT 
                      id_category
                        FROM
                        wt_sales_mobil_plan_category";
            }
            else{
                $strQuery = "SELECT 
                      UAC.id_category
                        FROM
                        wt_users AS U
                      INNER JOIN wt_user_asig_profile AS UAP
                        ON U.uid = UAP.userid
                      INNER JOIN wt_user_access_categories AS UAC
                        ON UAP.profile_id = UAC.id_profile AND type = 'movil'
                      WHERE U.uid = '{$uid}'";
            }

            $qTMP = db_query($strQuery);
            if(db_num_rows($qTMP)){
                while($rTMP = db_fetch_assoc($qTMP)){
                    $_SESSION["wt"]["categories"][] = $rTMP["id_category"];
                    unset($rTMP);
                }
                db_free_result($qTMP);
            }
        }

		// update some data into user table
		@db_query("UPDATE wt_users
				   SET lastvisit=NOW(),
					   logins=logins+1,
					   last_browser='{$_SERVER["HTTP_USER_AGENT"]}'
				   WHERE uid='$uid'");
		$_SESSION["wt"]["uid"] = $row["uid"];
		$_SESSION["wt"]["name"] = $row["name"];
		$_SESSION["wt"]["nombres"] = $row["nombres"];
		$_SESSION["wt"]["apellidos"] = $row["apellidos"];
		$_SESSION["wt"]["nickname"] = $row["nickname"];
		$_SESSION["wt"]["sex"] = $row["sex"];
		$_SESSION["wt"]["class"] = $row["class"];
		$_SESSION["wt"]["swusertype"] = $row["swusertype"];
		$_SESSION["wt"]["logged"] = true;
		$_SESSION["wt"]["lastvisit"] = $row["lastvisit"];

		core_fillBrowserInformation();

		$strSessID = session_id();
		db_query("REPLACE INTO wt_online (id, uid, hora) VALUES ('{$strSessID}', {$_SESSION["wt"]["uid"]}, NOW())");

		if (isset($cfg["core"]["visits_log"]) && $cfg["core"]["visits_log"]) {
			db_query("UPDATE wt_log_visitas SET logged = 'Y', uid = {$uid} WHERE sessid = '{$strSessID}' AND from_ip = '{$_SERVER["REMOTE_ADDR"]}'");
			$_SESSION["wt"]["lastvisitID"] = sqlGetValueFromKey("SELECT max(id) FROM wt_log_visitas WHERE uid = '{$row["uid"]}' AND sessid LIKE '%{$strSessID}%'" );
		}

		if ($cfg["core"]["page_processed_LOG"] && $intGlobalPageProcessedLogID) {
	        db_query("UPDATE wt_page_processed_log SET uid = {$uid} WHERE id = {$intGlobalPageProcessedLogID}");
	    }

		// if user checked auto_login...
		if($autologin) {
			create_autologin($login_passwd);
		}

	}
	db_free_result( $ret );

	global $boolOnLoginEvent;
	$boolOnLoginEvent = true;
}

function create_autologin($passwd){
	global $cfg;

	$sess = $_SESSION["wt"];
	if (!$sess["logged"]) return;

	$cookie = sprintf( "%010d", $sess["uid"] );
	$cookie .= substr(md5($sess["name"]),0,15);
	$cookie .= substr(md5($passwd),0,15);

	$strName = "hml_".str_replace(array(" ","/","http://","www","."),"", $cfg["core"]["url"]);
	setcookie($strName, $cookie, time() + 43200);
}

function delete_autologin(){
	global $cfg;
	$strName = "hml_".str_replace(array(" ","/","http://","www","."),"", $cfg["core"]["url"]);
	if( isset($_COOKIE[$strName]) ){
		$cookie = $_COOKIE[$strName];
		setcookie($strName, $cookie, time()-3600);
	}

}

function check_autologin(){
	global $lang ,$cfg;

	// 20100610 AG: Esta informacion del cookie
	$strName = "hml_".str_replace(array(" ","/","http://","www","."),"", $cfg["core"]["url"]);

	if( !isset( $_COOKIE[$strName] ) ) return;
	$cookie = $_COOKIE[$strName];

	$id = intval(substr($cookie,0,10));
	$ret = db_query("select uid, name, password from wt_users where uid='{$id}' and active='Y'");

	if(!$ret){
		delete_autologin();
		clear_login();
		$_SESSION["wt"]["error"] = $lang["ERROR_14"];
		return;
	}
	if(db_num_rows($ret)!=1){
		db_free_result($ret);
		delete_autologin();
		clear_login();
		$_SESSION["wt"]["error"] = $lang["ERROR_13"];
		return;
	}
	$row = db_fetch_array($ret);
	db_free_result($ret);

	$mdh = substr($cookie,10,30);
	$mdr = substr(md5($row["name"]),0,15).substr(md5($row["password"]),0,15);

	// Verifico que el usuario no este registardo
	$strQuery = "SELECT wt_users.allow_multi_session,
						wt_online.id
				 FROM wt_users
					LEFT JOIN wt_online
					ON wt_online.uid = wt_users.uid
				 WHERE wt_users.uid={$id}";
	$qTMP = db_query($strQuery);
	$rTMP = db_fetch_array($qTMP);
	if (!is_null($rTMP["id"]) && $rTMP["allow_multi_session"] == "N" && !$cfg["core"]["allow_multi_session"]) {
		// El usuario YA esta registrado, evitar el Log In.
		delete_autologin();
		clear_login();
		$_SESSION["wt"]["error"] = $lang["ERROR_12"];
		$_SESSION["wt"]["boolAlreadyConected"] = true;
		return;
	}
	db_free_result($qTMP);

	if($mdh != $mdr){
		delete_autologin();
		clear_login();
		$_SESSION["wt"]["error"] = $lang["ERROR_13"];
		return;
	}
	// setup the Session
	fill_login($id);
}

function do_login(){
	global $config, $cfg, $lang;
	clear_login();
	$strSessID = session_id();
	db_query("UPDATE wt_users SET active='N', retirado='Y', fecha_retiro = NOW() WHERE isTemp='Y' AND CURDATE()>expirationdate AND active='Y'");
	$intTMP = db_affected_rows();
	if ($intTMP > 0) {
		LogInsert($_SESSION["wt"]["uid"], $_SESSION["wt"]["swusertype"], "Se retiraron usuarios por expiración.  Archivo:".basename(__FILE__).", Línea:".__LINE__);
	}

	if (isset($cfg["core"]["AccountRequest"]) && $cfg["core"]["AccountRequest"]) {
		// Borro los usuarios que ya se desctivaron y que no confirmaron su correo...
		db_query("DELETE FROM wt_users WHERE isTemp='Y' AND CURDATE() > expirationdate AND swusertype = '{$cfg["core"]["AccountRequest_type"]}' AND mail_confirmed = 'N'");

		// Borro los accesos de los usuarios que ya no existen
		db_query("DELETE wt_user_access
				  FROM wt_user_access
						LEFT JOIN wt_users
						ON wt_users.uid = wt_user_access.userid
				  WHERE wt_users.uid IS NULL");
	}

	//$login_name = addslashes(html_entity_decode(user_input_delmagic($_POST["login_name"])));
	$login_name = addslashes(user_input_delmagic($_POST["login_name"]));
	$login_passwd = addslashes(md5(user_input_delmagic($_POST["login_passwd"])));
	$login_passwdUnencrypt = addslashes(user_input_delmagic($_POST["login_passwd"]));

	$_SESSION["wt"]["spw"] = user_input_delmagic($_POST["login_passwd"]);

	//-------------------------------------------------------------------Pendiente 294 --------------------------------------------------//
	//Si esta el user universal configurado y no estoy en una instancia
	$boolUseNormalLogIn = true;
	if (isset($config["cloud"]["userUniversal"]) && $config["cloud"]["userUniversal"] && $config["strClientKey"] == "") {
		$boolUseNormalLogIn = false;

		$strInstancia = "";
		$strInstancias = "";
		$strPath = $cfg["core"]["url"];
		//Explode del login_name
		$arrExplodeLoginName = explode("@",$login_name);
		if (!isset($arrExplodeLoginName[1])) $arrExplodeLoginName[1] = "";

		//conexion remota
		$objRemoteConnection = db_connect($config["frontEnd_host"], $config["frontEnd_database"], $config["frontEnd_user"], $config["frontEnd_password"],true) or die();
		//Reviso si el lado izquierdo es una instancia
		$qTMP = db_query("SELECT client_key
						  FROM   wt_cloud_instances
						  WHERE  client_key='{$arrExplodeLoginName[1]}'",true,$objRemoteConnection);
		while($rTMP = db_fetch_array($qTMP)){
			$strInstancia = $rTMP["client_key"];
		}
		db_free_result($qTMP);
		//Si es una instancia
		if($strInstancia != ""){
			$strNameConect = addslashes(user_input_delmagic($arrExplodeLoginName[0]));
			$strPassConect = $_POST["login_passwd"];
			?>
			<form method="post" name="frmLoginUniversalUser" id="frmLoginUniversalUser" action="">
				<?php
				$strClass = "row1";
				//Para conectarme a la instancia
				$qTMP = db_query("SELECT CS.server_key, CS.server_ip, CI.bDatosName, CI.bDatosUser, CI.client_id, CI.nombre, CI.client_key
								  FROM   wt_cloud_servers AS CS, wt_cloud_instances AS CI
								  WHERE  CS.server_id = CI.server_id AND
										 CS.active = 'Y' AND
										 CI.active = 'Y' AND
										 CI.client_key = '{$strInstancia}' AND
										 CI.rdy = 'Y'", true, $objRemoteConnection);
				while($rTMP = db_fetch_array($qTMP)){
					?>
					<input type="hidden" name="conectar" value="<?php print $rTMP["client_key"];?>">
					<?php
				}
				db_free_result($qTMP);
				?>
				<input type="hidden" name="login_name" value="<?php print $strNameConect;?>">
				<input type="hidden" name="login_passwd" value="<?php print $strPassConect;?>">
				<input type="hidden" name="submit_login" value="1">
			</form>
			<script type="text/javascript">
				function validate_data(){
					myForm = document.frmLoginUniversalUser;
					objConectar =  myForm.conectar;
					myForm.action = '<?php print "{$strPath}index.php?cldmd=g&sl=";?>'+objConectar.value;
					myForm.submit();
				}
				window.onload = validate_data;
			</script>
			<?php
		}
		else{
			//Para enviarlos en el nuevo POST
			$strNameConect = $_POST["login_name"];
			$strPassConect = $_POST["login_passwd"];
			//Primero lo busco como usuario global
			$qTMP = db_query("SELECT global_uid
							  FROM   wt_cloud_users
							  WHERE (email='{$login_name}' AND email <> '' AND email IS NOT NULL) AND
									 password = '{$login_passwd}'",true,$objRemoteConnection);
			//Si existe voy a buscar las instancias donde esta registrado
			$intCount = db_num_rows($qTMP);
			if($intCount != 0) {
				while($rTMP = db_fetch_array($qTMP)){
					$intGlobalUID = $rTMP["global_uid"];
				}
				db_free_result($qTMP);
				//Array de User Instances
				$arrUserInstances = array();
				$strQuery = "SELECT global_uid,instance_id,instance_uid
							 FROM 	wt_cloud_users_instances
							 WHERE  global_uid = '{$intGlobalUID}'";
				$qTMP = db_query($strQuery,true,$objRemoteConnection);
				$intCount = 1;
				while($rTMP = db_fetch_array($qTMP)){
					$arrUserInstances[$rTMP["instance_id"]]["instancia"] = $rTMP["instance_id"];
					$arrUserInstances[$rTMP["instance_id"]]["user_id"] = $rTMP["instance_uid"];
					if($intCount == 1){
						$strInstancias = "{$rTMP["instance_id"]}";
					}
					else{
						$strInstancias .= ",{$rTMP["instance_id"]}";
					}
					$intCount++;
				}
				db_free_result($qTMP);
				//Si existen las instancias le indico al usuario a cual desea conectarse
				$intCountInstances = count($arrUserInstances);
				if($intCountInstances != 1 && $intCountInstances != 0){
					$_SESSION["cloud"]["url_conect"]  = $strPath;
					$_SESSION["cloud"]["login_name"] = $strNameConect;
					$_SESSION["cloud"]["login_passwd"] = $strPassConect;
					$_SESSION["cloud"]["instances"] = $strInstancias;
					header('Location: adm_cloud_instances_select.php');
				}
				//Solo esta registrado en una instancia, lo envio a esa de una vez
				else if($intCountInstances == 1){
					?>
					<form method="post" name="frmLoginUniversalUser" id="frmLoginUniversalUser" action="">
						<?php
						$strClass = "row1";
						//Para conectarme a la instancia
						$qTMP = db_query("SELECT CS.server_key, CS.server_ip, CI.bDatosName, CI.bDatosUser, CI.client_id, CI.nombre, CI.client_key
										  FROM   wt_cloud_servers AS CS, wt_cloud_instances AS CI
										  WHERE  CS.server_id = CI.server_id AND
												 CS.active = 'Y' AND
												 CI.active = 'Y' AND
												 CI.client_id IN({$strInstancias}) AND
												 CI.rdy = 'Y'", true, $objRemoteConnection);
						while($rTMP = db_fetch_array($qTMP)){
							?>
							<input type="hidden" name="conectar" value="<?php print $rTMP["client_key"];?>">
							<?php
						}
						db_free_result($qTMP);
						?>
						<input type="hidden" name="login_name" value="<?php print $strNameConect;?>">
						<input type="hidden" name="login_passwd" value="<?php print $strPassConect;?>">
						<input type="hidden" name="submit_login" value="1">
					</form>
					<script type="text/javascript">
						function validate_data(){
							myForm = document.frmLoginUniversalUser;
							objConectar =  myForm.conectar;
							myForm.action = '<?php print "{$strPath}index.php?cldmd=g&sl=";?>'+objConectar.value;
							myForm.submit();
						}
						window.onload = validate_data;
					</script>
					<?php
				}
				//No tiene ninguna instancia asignada
				else{
					$_SESSION["wt"]["error"] = "Registro incorrecto en la instancia"."<br>";
					$_SESSION["wt"]["boolUserPWIncorrect"] = true;
				}
			}
			//Si no existe
			else{
				db_free_result($qTMP);
				//Creo que tendria que usar el código de abajo

				$boolUseNormalLogIn = true;
			}
		}
		//Cierro la conexion remota
		if ($objRemoteConnection !== false) db_close($objRemoteConnection);
		db_select_database($config["database"]);
	}

	if (!$boolUseNormalLogIn) {
		die();
	}
	else {
	    $strFunction = "external_validation_{$cfg["core"]["site_profile"]}";
		if(function_exists($strFunction)){
		    $strFunction($login_name, $login_passwdUnencrypt);
		}

		if ($cfg["core"]["LogWithMail"]) {
			$ret = db_query("SELECT uid
							 FROM wt_users
							 WHERE (name='{$login_name}' OR (email='{$login_name}' AND email <> '' AND email IS NOT NULL)) AND
								   password='{$login_passwd}' AND
								   active='Y'");
		}
		else {
			$ret = db_query("SELECT uid
							 FROM wt_users
							 WHERE name='{$login_name}' AND
								   password='{$login_passwd}' AND
								   active='Y'");
		}
		if(!$ret) {
			$_SESSION["wt"]["error"] = $lang["ERROR_14"];
		}
		else {
			if(db_num_rows($ret)!=1) {
				$_SESSION["wt"]["error"] = "";
				if ($cfg["core"]["LogWithMail"]) {
					$qTMP = db_query("SELECT uid FROM wt_users WHERE (name='{$login_name}' OR (email='{$login_name}' AND email <> '' AND email IS NOT NULL))");
				}
				else {
					$qTMP = db_query("SELECT uid FROM wt_users WHERE name='{$login_name}'");
				}
				if (db_num_rows($qTMP) == 0) {
					$_SESSION["wt"]["error"] = $lang["ERROR_18"]."<br>";
					$_SESSION["wt"]["boolUserPWIncorrect"] = true;
					db_free_result($qTMP);
				}
				else {
					db_free_result($qTMP);
					if ($cfg["core"]["LogWithMail"]) {
						$qTMP = db_query("SELECT uid FROM wt_users WHERE
										  (name='{$login_name}' OR (email='{$login_name}' AND email <> '' AND email IS NOT NULL))
										  AND password='{$login_passwd}'");
					}
					else {
						$qTMP = db_query("SELECT uid FROM wt_users WHERE name='{$login_name}' and password='{$login_passwd}'");
					}
					if (db_num_rows($qTMP) == 0) {
						$_SESSION["wt"]["error"] .= $lang["ERROR_18"];
						$_SESSION["wt"]["boolUserPWIncorrect"] = true;
					}
					else {
						$_SESSION["wt"]["error"] .= $lang["ERROR_17"];
					}
					db_free_result($qTMP);
				}
			}
			else {
				$row = db_fetch_array($ret);
				$boolOK = true;
				if ($boolOK) {
					$boolStillOK = false;
					// Verifico que el usuario NO haya entrado ya...
					$strQuery = "SELECT wt_users.allow_multi_session,
										wt_online.id
								 FROM wt_users
									LEFT JOIN wt_online
									ON wt_online.uid = wt_users.uid
								 WHERE wt_users.uid={$row["uid"]}";
					$qTMP = db_query($strQuery);
					$rTMP = db_fetch_array($qTMP);
					if (!is_null($rTMP["id"]) && $rTMP["allow_multi_session"] == "N" && !$cfg["core"]["allow_multi_session"]) {
						if (isset($_POST["force_disconect"])) {
							db_query("DELETE FROM wt_online WHERE uid={$row["uid"]}");
							$boolStillOK = true;
						}
						else {
							// El usuario YA esta registrado, evitar el Log In.
							$_SESSION["wt"]["error"] = $lang["ERROR_12"];
							$_SESSION["wt"]["boolAlreadyConected"] = true;
						}
					}
					else {
						$boolStillOK = true;
					}

					if ($boolStillOK) {
						$autologin = ( isset($_POST["login_auto"]) && $_POST["login_auto"] == 1 );
						$strDate = date("Y-m-d");
						if (isset($cfg["core"]["visits_log"]) && $cfg["core"]["visits_log"]) {
							$strFound = sqlGetValueFromKey("SELECT id FROM wt_log_visitas WHERE sessid LIKE '%{$strSessID}%' AND DATE_FORMAT(fecha,'%Y-%m-%d')='{$strDate}' AND uid=0");
							if($strFound === false){
							   db_query("INSERT INTO wt_log_visitas (sessid, from_ip, fecha) VALUES ('{$strSessID}','{$_SERVER["REMOTE_ADDR"]}',NOW())");
							}
						}

						fill_login( $row["uid"], $autologin, $login_passwd );

						$_SESSION["wt"]["browser"]["screen"] = array();

						if (isset($_POST["screenInfo"])) {
							$arrTMP = explode(",", $_POST["screenInfo"]);
							foreach($arrTMP as $key => $value){
								$arrTMP2 = explode("=",$value);
								if (!empty($arrTMP2[0])) $_SESSION["wt"]["browser"]["screen"][$arrTMP2[0]] = $arrTMP2[1];
							}
						}
					}
					db_free_result($qTMP);
				}
				else {
					$_SESSION["wt"]["error"] = $lang["ERROR_17"]."<br>";
				}
			}
			db_free_result( $ret );
		}
	}
}


/**
 * Verifica que un usuario tenga todos los accesos dentro de un profile
 *
 * @param integer $intUserID
 * @param integer $intProfileID
 * @return boolean
 */
function core_validateUserAccessProfile($intProfileID, $intUserID = 0) {
	if ($intUserID == 0) $intUserID = $_SESSION["wt"]["uid"];

	if (check_user_class("admin") || check_user_class("helpdesk")) {
		return true;
	}
	else {
		$strQuery = "SELECT UAPD.module AS shouldHave, UA.module AS hasAccess
					 FROM wt_user_access_perfiles_d AS UAPD
							LEFT JOIN wt_user_access AS UA
							ON UA.module = UAPD.module AND
							   UA.userid = {$intUserID}
					 WHERE UAPD.perfil_id = {$intProfileID} AND
						   UA.module IS NULL
					 ORDER BY UAPD.module";
		$qTMP = db_query($strQuery);
		$intNumRows = db_num_rows($qTMP);
		db_free_result($qTMP);

		return ($intNumRows == 0);
	}
}

/**
 * Devuelve los profiles a los que un usuario tiene acceso, o sea todos los profiles para los cuales el usuario tiene todos los accesos.
 *
 * @param integer $intUserID
 * @param boolean $boolAsString
 * @return variant, puede ser un array o un string segun el parametro $boolAsString
 */
function core_getValidAccessProfiles($intUserID = 0, $boolAsString = false) {
	if ($intUserID == 0) $intUserID = $_SESSION["wt"]["uid"];

	if (check_user_class("admin") || check_user_class("helpdesk")) {
		$arrProfiles = sqlGetArray("SELECT id, nombre FROM wt_user_access_perfiles ORDER BY nombre", true, "id", "nombre");
		if (is_array($arrProfiles) && count($arrProfiles) == 0) $arrProfiles = false;

	}
	else {
		$arrProfiles = sqlGetArray("SELECT id, nombre FROM wt_user_access_perfiles ORDER BY nombre", true, "id", "nombre");
		if ($arrProfiles === false) $arrProfiles = array();

		$strQuery = "SELECT UAPD.perfil_id, UAPD.module AS shouldHave, UA.module AS hasAccess
					 FROM wt_user_access_perfiles_d AS UAPD
							LEFT JOIN wt_user_access AS UA
							ON UA.module = UAPD.module AND
							   UA.userid = {$intUserID}
					 WHERE UA.module IS NULL
					 GROUP BY UAPD.perfil_id
					 ORDER BY UAPD.perfil_id";
		$arrLosQueNo = sqlGetArray($strQuery, true, "perfil_id", "shouldHave");
		if ($arrLosQueNo === false) $arrLosQueNo = array();

		if (count($arrProfiles) > 0 && count($arrLosQueNo) > 0) {
			//$arrProfiles = array_diff_key($arrProfiles, $arrLosQueNo);
			//2009-01-22 AG: La funcion array_diff_key no esta definida en php4...
			while ($arrItem = each($arrLosQueNo)) {
				if (isset($arrProfiles[$arrItem["key"]])) {
					unset($arrProfiles[$arrItem["key"]]);
				}
			}
		}
		if (count($arrProfiles) == 0) $arrProfiles = false;
	}

	if ($boolAsString) {
		$strTMP = "0";
		if (is_array($arrProfiles)) {
			while ($arrItem = each($arrProfiles)) {
				$strTMP .= ",{$arrItem["key"]}";
			}
		}
		$arrProfiles = $strTMP;
	}

	return $arrProfiles;
}
?>