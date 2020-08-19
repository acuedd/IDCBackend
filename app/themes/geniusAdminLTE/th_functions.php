<?php
/**
 * Autor: Alexander Flores
 * Fecha: 25-08-2014
 * Descripcion: funciones del theme
 */

include_once("themes/geniusAdminLTE/main.php");

$strProfile = $cfg["core"]["theme_profile"];
if (!empty($strProfile) && is_dir("themes/geniusAdminLTE/profiles/{$strProfile}") ) {
	$strTextProfile = "themes/geniusAdminLTE/profiles/{$strProfile}";
	include_once("{$strTextProfile}/main.php");
}
function theme_draw_map()
{
	global $sitemap;
	echo "<table border=0 width=\"100%\" cellpadding=2 cellspacing=0><tr><td class=\"navbar\">";
	foreach($sitemap as $item) {
		echo "&gt; <a href=\"$item[1]\" class=\"navbarlink\">$item[0]</a> ";
	}
	echo "</td></tr></table>\n";
}

function draw_menu_default() {
	global $config;

	$arr_menu = $config["menu"];
	ksort($arr_menu, SORT_NUMERIC);
	reset($arr_menu);

	$arrDrawThisArray = array();
	$intMaxLen = 0;
	while($entry = each( $arr_menu )){
		if (isset($entry["value"]["moduleID"])) {
			if (!check_module($entry["value"]["moduleID"], false, $entry["value"]["type"])) {
				continue;
			}
			else {
				if(isset($entry[1]["type"]) && $entry[1]["type"] != "A"){
					if( $entry[1]["type"] == "L" ) {
						if( !$_SESSION["wt"]["logged"] ) continue;
					} else if(  $entry[1]["type"] == "N" ) {
						if( $_SESSION["wt"]["logged"] ) continue;
					} else {
						continue;
					}
				}
			}
		}
		else {
			if($entry[1]["type"] != "A"){
				if( $entry[1]["type"] == "L" ) {
					if( !$_SESSION["wt"]["logged"] ) continue;
				} else if(  $entry[1]["type"] == "N" ) {
					if( $_SESSION["wt"]["logged"] ) continue;
				} else {
					continue;
				}
			}
		}
		$arrDrawThisArray[$entry["key"]] = $entry["value"];
		$intLen = strlen($entry["value"]["title"]);
		if ($intLen > $intMaxLen) $intMaxLen = $intLen;
	}

	$intItems = count($arrDrawThisArray);
	$intCols = ceil(70 / $intMaxLen);

	if ($intItems){
		$intCol = 1;
		while($entry = each($arrDrawThisArray)){
			if ($intCol > 1) print "<span style='color: #FFFFFF;'> | </span>";
			if (isset($entry[1]["title"])) {
				//print theme_draw_menu_item($entry[1]["title"], $entry[1]["file"], true);
				?><a href="<?php print $entry[1]["file"];?>"><?php print $entry[1]["title"];?></a><?php
			}
			/*
			if (isset($entry[1]["subitems"])) {
				reset($entry["value"]["subitems"]);
				while ($subEntry = each($entry["value"]["subitems"])) {
					if (!($subEntry["key"]==="type")) {
						print theme_draw_menu_item($subEntry[1]["title"], $subEntry[1]["file"], 15);
					}
				}
			}
			*/
			$intCol++;
			if ($intCol > $intCols) {
				$intCol = 1;
				print "<br>";
			}
		}
	}
	/*
	if (check_module("email")){
		draw_email_menu();
	}

	// DrawAdminBox
	draw_admin_menu();
	*/
}

$intMenuItemCounter = 0;
function theme_draw_menu_item($text, $link, $boolBottom = false) {
	global $cfg, $intMenuItemCounter;
	$link = (empty($link))?"index.php":$link;

	/*
	$strMenuItem = "
	<div id='FlashCell_menuItems{$intMenuItemCounter}'>
	<script language='JavaScript' type='text/javascript'>
		drawFlashObject(156, 29, 'themes/default/img/menuItems.swf', '{$text}', '{$link}', '', 'FlashCell_menuItems{$intMenuItemCounter}');
	</script>
	</div>
	";
	//*/

	$strMenuItem = "<div class='menuLink' style='width:100%; height:29px;'
    					 onmouseover=\"this.className='menuLink_Over';\" onmouseout=\"this.className='menuLink';\"
    					 onclick=\"document.location.href='{$link}'\">
    					{$text}
    				</div>";
	//*/

	$intMenuItemCounter++;

	return $strMenuItem;
}

/**
 * override de la funcion core para login
 *
 * @param string $strTargetLink
 * @param boolean $strTargetGet
 */
function core_show_login_only_geniusAdminLTE($strTargetLink = "index.php", $strTargetGet = false) {
	global $cfg, $config, $lang;
    clearstatcache();
	$strEmpresa = (isset($_POST["login_empresa"]))?$_POST["login_empresa"]:"";
	$strLogin = (isset($_POST["login_name"]))?$_POST["login_name"]:"";

	if ($strTargetGet == false) {
		$strGet = (isset($_GET["login"]))?"?login={$_GET["login"]}":"";
	}
	else {
		$strGet = "?{$strTargetGet}";
	}
	$strTarget = "{$strTargetLink}{$strGet}";

	if (array_key_exists("boolUseCloud", $config) && array_key_exists("strClientKey", $config) && $config["boolUseCloud"] === true && is_string($config["strClientKey"])) {
		$strEmpresa = $config["strClientKey"];
		$style = "style='text-shadow: 0.07em 0.07em #A9A9A9;font-family:mv boli;color:black;'";
		$size = "6";
	}
    $consulta = sqlGetValueFromKey("SELECT path, title FROM wt_profile_configuration WHERE specified = 'img-start'");
	$consulta_background = sqlGetValueFromKey("SELECT path, title FROM wt_profile_configuration WHERE specified = 'img-start-bkg'");
    $source = $consulta["path"];
    $arrColors = [];
    $colors = db_query("SELECT color, specified FROM wt_profile_configuration where `type` = 'color'");
    foreach($colors as $color){
        $arrColors[$color["specified"]] = $color;
    }
    $menu_background = strlen($arrColors["color-menu"]["color"]) >= 4 ? $arrColors["color-menu"]["color"] : "";
    $menu_color_deg = strlen($arrColors["color-deg"]["color"]) >= 4 ? $arrColors["color-deg"]["color"] : "";
    $image_background_color = strlen($arrColors["color-start-bkg"]["color"]) >= 4 ? $arrColors["color-start-bkg"]["color"] : "";
    $image_face = $menu_color_deg ? "linear-gradient($menu_background, $menu_color_deg)" : $menu_background;
    $image_background_souce = $consulta_background["path"] ? $consulta_background["path"] : "";
    $loginCaption = sqlGetValueFromKey("Select * from wt_caption_info where caption_position = 'caption-login'");
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <html>
	<?php draw_header_tag($cfg["core"]["title"]);?>
    <script for="window" language="JavaScript" type="text/javascript">
        $(function(){
            $('#login_name').keypress(function(event){
                var keycode = (event.keyCode ? event.keyCode : event.which);
                if(keycode == '13'){
                    var frm = $("#login_form");
                    frm.submit();
                }
            });

            $('#login_passwd').keypress(function(event){
                var keycode = (event.keyCode ? event.keyCode : event.which);
                if(keycode == '13'){
                    var frm = $("#login_form");
                    frm.submit();
                }
            });
        });
        function gotolostpass(){
            document.location.href = "adm_main.php?mde=users&wdw=lostpass";
        }
        //setTimeout(function(){ $(".menTemp").hide() }, 8000);
    </script>

    <style>
        @font-face {
            font-family: Cronos-Pro_12459;
            src: url(./themes/geniusAdminLTE/fonts/Cronos-Pro_12459.ttf)format('truetype');
        }
        *{font-family: Cronos-Pro_12459;}
        /*body{background: #323C45;}*/
        #fPTB{
            height: 100%;
            margin: 0;
            /*background-image: url(./images/img_1.jpg);*/
            background-image: url(./images/tigoBusiness.png);
            background-repeat: no-repeat;
            background-size: 100% 100%;
            background-position: 0 0 !important;
        }
        .containerTitle{  padding-left: 50px;  }
        .titlePrincipal{
            font-size: 38px;
            position: absolute;
        }
        .imgPrincipalFirst{  padding-top: 22%;  }
        .fondoLogin{
            background: #1E2831;
            color: white;
            height: 100%;
        }
        .titleLogin{margin-bottom: 30px;}
        .menTemp{
            color:#43c7c7 ;
        }
        .formLogin{
            padding-top: 23%;
        }
        .loginIcons{color: #323C45;}
        .contIcons{background: white; border: 1px solid white; border-radius: 0;}
        .checkLogin{
            margin: 10px;
            color: black;
            font-size: 20px;
        }
        .passLost{  color: #F2C02A;  }
        .passLost:hover{  color: #F2C02A;  }
        .buttonLogin{
            background: #323C45;
            width: 80%;
            padding: 12px;
            margin-top: 13%;
            margin-left: auto;
        }
        .buttonLogin:hover{
            color: white;
        }
        .imgGTvL{
            height: 80%;
            margin-top: 10%;
        }
        /*=====================================================*/
        @media (max-width: 991px){
            .formLogin{
                width: 80%;
                padding-left: 25%;
                padding-top: 10%;
                margin-bottom: 60px;
            }
        }
        @media (max-width: 940px){
            #fPTB{
                height: 80%;
            }
            .formLogin{
                padding-top: 10%;
            }
        }
        .login_container{
            background: <?php $image_face ? print $image_face : print "#1c2637"; ?>;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login_container{
            box-shadow: 0px 0px 9px rgba(0,0,0, .4);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login_container form{
            padding: 0;
            margin: 0 auto;
            width: 100%;
        }
        .login_container form .form_{
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form_ .button_login{
            background: rgba(255, 255, 255, .3);
            font-size: 1.3em;
            padding: .3em;
            width: 50%;
            /*height: 100%;*/
            color: #fff;
        }
        @media (min-width: 90px) {
            .button_login {
                height: 50px !important;
            }
        }
        @media (min-height: 90px) {
            .button_login {
                height: 50px !important;
            }
        }
        @media (max-height: 900px) {
            .button_login {
                height: 50px !important;
            }
        }
        @media (min-width: 800px){
            .form_ .button_login:hover{
                box-shadow: 0px 0px 10px rgba(0,0,0, .3);
                transform: scale(1.1);
                transition: all .1s linear;
            }
        }
        .login_container form .form_ button{
            margin: 0;
        }
        .form_{
            padding: 0 1em;
            max-width: 400px;
            margin: 0 auto;
        }
        .form_ div {
            width: 100%;
        }
        .input_remember{
            color: #fff;
            padding: 0 !important;
            font-weight: 300;
        }
        .input_remember label{
            padding: 0;
        }
        .input_remember input{
            background: #fff;
        }
        .input-group span i{
            color: rgba(0,0,0, .3);
        }
        .login_header h3{
            padding: 0;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: .1em;
            color: #fff;
        }
        .lost_pass{
            color: #fff;
        }
        .login_header p{
            margin: 0;
        }
        .input-group{
            margin: 0;
        }
        .wallet-button{
            border-radius: 22px;
            padding: .5em 3em;
        }

        .wallet-button-positive{
            background: #00C8FF;
            color: #fff;
        }

        .wallet-button-negative{

        }
        .custom-form-group label{
            color: <?php print $arrColors["color-menu-text"]["color"]; ?>;
        }
        .custom-form-group input, .custom-form-group select{
            border-radius: 22px !important;
            background: rgb(235, 235, 235);
        }
        .logo_container{
            background: url("<?php print $image_background_souce; ?>") !important;
            background-color: <?php $image_background_color ? print $image_background_color : print 'black'; ?> !important;
            <?php $image_background_color ?
                    print "background-repeat: repeat !important;" :
                    print "background-repeat: no-repeat !important; background-size: cover !important;" ?>
            height: 100%;
        }
        .color-menu-text{
            color: <?php print $arrColors["color-menu-text"]["color"]; ?>;
        }
        .add-to-screen{
            margin: 1em 0;
        }
        .logo_src{
            height: 100%;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
        }
        .alink{
            font-size: 1.3em;
            color: <?php print $arrColors["color-menu-text"]["color"]; ?>;
        }
        .logo_src img{
            max-height: 100%;
            max-width: 80%;
            animation-name: img-ani;
            animation-duration: .3s;
            animation-timing-function: ease-in;
        }
        input, button, label, h3, span{
            animation-name: img-ani;
            animation-duration: .3s;
            animation-timing-function: ease-in;
        }
        @keyframes img-ani {
            from{opacity:0;}
            to{opacity: 1;}
        }
        @media (max-width: 520px){
            #fPTB{
                height: 50%;
            }
            .buttonLogin{
                margin-top: 0;
            }
            .formLogin{
                margin-bottom: 0;
            }
        }
    </style>
    <body>
    <!--$consulta["path"] ? print "" : print "fPTB"-->
    <div class="col-md-8 logo_container">
        <div class="logo_src">
            <img src="<?php file_exists($source) ?
                            print $source :
                            (
                                file_exists("profiles/".$cfg["core"]["site_profile"]."/images/theme_image_login_logo.png") ?
                                print "profiles/".$cfg["core"]["site_profile"]."/images/theme_image_login_logo.png" :
                                print "themes/".$cfg["core"]["theme"]."/images/theme_image_login_logo.png"
                            );
                        ?>">
        </div>
    </div>

    <div class="col-md-4 login_container login_content">
        <form action="<?php print $strTargetLink;?>?login=1" name="login_form" id="login_form" class="formLogin" method="post">
            <div class="form_">
                <p class="textDanger" style="text-align: center; color: #F2C02A;">
                    <?php
                    if((isset($_SESSION["wt"]["error"]) && strlen($_SESSION["wt"]["error"])>0)){
                        print $_SESSION["wt"]["error"];
                        unset($_SESSION["wt"]["error"]);
                    }
                    ?>
                </p>
                <div class="login_header">
                    <h3 class="login_title-principal">Inicio de sesión</h3>
                    <p class="login_title-optional"></p>
                </div>
                <div class="login_body">
                    <div class="form-group">
                        <div class="input-group checkLogin custom-form-group">
                            <!--<span class="input-group-addon contIcons"><i class="glyphicon glyphicon-user loginIcons"></i></span>-->
                            <label for=""><?php print $lang["LOGIN_NAME"]; ?></label>
                            <input type="text" class="form-control" style="border-radius: 0; border: none;" id="login_name" name="login_name" value="<?php print htmlSafePrint($strLogin); ?>" placeholder="<?php print $lang["LOGIN_NAME"]; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group checkLogin custom-form-group">
                            <!--<span class="input-group-addon contIcons"><i class="glyphicon glyphicon-lock loginIcons"></i></span>-->
                            <label for=""><?php print $lang["LOGIN_PASSWD"];?></label>
                            <input type="password" class="form-control" style="border-radius: 0; border: none;" id="login_passwd" name="login_passwd" placeholder="Contraseña">
                        </div>
                    </div>
                </div>
                <?php
                if (isset($_SESSION["wt"]["boolAlreadyConected"])) {
                    ?>
                    <?php
                }
                ?>
                <?php
                if (!isset($cfg["core"]["lostPWD"]) || !$cfg["core"]["lostPWD"]) {
                    ?>
                    <div class="text-right">
                        <button type="button" class="btn btn-link lost_pass" tabindex="-1" onclick="gotolostpass();"><?php echo $lang["LOGIN_LOST_PWD"];?></button>
                    </div>
                    <?php
                }
                ?>
                <div class="form-group text-center">
                    <input type="hidden" name="screenInfo">
                    <input type="hidden" name="submit_login" value="1">
                </div>
                <div class="checkbox input_remember">
                    <label>
                        <?php print $lang["LOGIN_AUTOLOGIN"];?><input type="checkbox" style="margin: 6px 15px; background: transparent;" name="login_auto" value="1">
                    </label>
                </div>
                <?php
                if (isset($_SESSION["wt"]["boolAlreadyConected"])){
                    ?>
                    <div class="checkbox desconectarUser">
                        <label>
                            <?php print $lang["LOGIN_FORCEDISCONNECT"]; ?><input type="checkbox" style="margin: 6px 15px;" name="force_disconect" value="1">
                        </label>
                    </div>
                    <?php
                }
                ?>
                <?php
                    if(isset($_SESSION["registered"])){
                        $strMessage = $_SESSION["registered"];
                        print "<p class='color-menu-text'>$strMessage</p>";
                        unset($_SESSION["registered"]);
                    }
                ?>
                <div class="col-sm-12 text-center">
                    <button type="submit" class="btn wallet-button buttonLogin button_login" ><?php echo $lang["LOGIN_BUTTON"]; ?></button>
                </div>
                <?php
                    if (isset($cfg["core"]["AccountRequest"]) && $cfg["core"]["AccountRequest"]) {
                        ?>
                        <div class="text-center">
                            <button type="button" class="btn btn-link alink" onclick="toRegister()"><?php echo $lang["NEW_USER_LINK"];?></button>
                        </div>
                        <?php
                    }
                ?>
            </div>
        </form>
        <div class="text-center color-menu-text">
            <?php echo str_replace("|", "<br>", $loginCaption["content"]) ?>
        </div>
        <div class="add-to-app">
            <button class="add-to-screen btn wallet-button">Instalar WebApp</button>
        </div>
        <script src="/triggerServiceWorker.js"></script>
        <script defer>
            let dw = new drawWidgets();
            function redirectTo(){
                document.location.href='adm_main.php?mde=users&wdw=register';
            }
            let lang = {
                'REGISTER_CONDITION': '<?php print $lang["REGISTER_CONDITION"]; ?>',
                'REGISTER_LAW': '<?php print $lang["REGISTER_LAW"]; ?>'
            };
            function toRegister(){
                let temp = `<div>
                                <div>
                                    <p>${lang.REGISTER_CONDITION}</p>
                                    <p>${lang.REGISTER_LAW}</p>
                                </div>
                                <div class="col-sm-12 text-center">
                                    <button type="button" class="btn wallet-button wallet-button" onclick="dw.closeDialog();">Cerrar</button>
                                    <button type="button" class="btn wallet-button wallet-button-positive" onclick="redirectTo()">Acepto</button>
                                </div>
                            </div>`;
                dw.alertDialog(temp, `Solo para comercios`);
                setTimeout(()=>{
                    let domAlertModal = document.querySelector(`.modal-hml-content-sm`);
                    let domAlertModalBody = document.querySelector(`.modal-hml-body`);
                    domAlertModal.style.width = '340px';
                    domAlertModal.style.maxWidth = '96%';
                    domAlertModalBody.style.margin = '0 auto';
                }, 100);
            }
        </script>
        <script defer>
            setTimeout(()=>document.querySelector('.login_content').scrollIntoView({ behavior: 'smooth', block: 'center' }), 200);
        </script>
    </div>
    </body>
    </html>
	<?php
	if (isset($_SESSION["wt"]["boolAlreadyConected"])) unset($_SESSION["wt"]["boolAlreadyConected"]);
}

function showDetailUser($uid = 0, $strSRC){
	global $cfg;

	if(!$uid) return false;
	if(empty($strSRC)) $strSRC = "themes/geniusAdminLTE/images/user_Male.jpg";
	$strQuery = "SELECT * FROM wt_users WHERE uid = {$uid}";
	$qTMP = db_query($strQuery);
	if($rTMP = db_fetch_array($qTMP)){
		?>
        <div style="text-align: center;color: white;font-size: 20px;">
            <label>Perfil de Usuario</label><br><br>
        </div>
        <div style="text-align: center;">
            <div style="width:100px;margin-left: auto;margin-right: auto;">
                <img src="<?php print $strSRC; ?>" width="100" title="Opciones" class="circular">
            </div>
        </div>
        <div>
            <label>Nombre completo:</label><br>
			<?php print $rTMP['apellidos'].','.$rTMP['nombres']; ?><br><br>
        </div>
        <div>
            <label>Nombre usual:</label><br>
			<?php print $rTMP['nickname']; ?><br><br>
        </div>
        <div>
            <label>Total de visitas:</label><br>
			<?php print $rTMP['logins']; ?><br><br>
        </div>
        <div>
            <label>Total de visitas:</label><br>
			<?php print show_date($rTMP["lastvisit"], false); ?><br><br>
        </div>
        <div>
            <label>País</label><br>
			<?php print $rTMP["country"]; ?><br><br>
        </div>
		<?php
	}
	db_free_result($qTMP);
}

function draw_menu_horizontal_default($intLinks = 5){
	global $config,$cfg;
	if ($_SESSION["wt"]["logged"]) {
		$strName = (isset($_SESSION['wt']['name']))?$_SESSION['wt']['name']:"";
		$strSRC = strGetCoreImageWithPath('user_Male.jpg');
		if(isset($_SESSION['wt']['uid'])){
			$strSex = sqlGetValueFromKey("SELECT LENGTH(avatar) AS imagen,sex FROM wt_users WHERE uid = {$_SESSION['wt']['uid']}");
			if(is_array($strSex)){
				if(!empty($strSex['sex'])){
					$strSRC = strGetCoreImageWithPath("user_{$strSex['sex']}.jpg");
				}
			}
		}

		$arrSortedArray = prepare_admin_menu_data();
		$admmenu = array();
		$arrMI = array();
		while ($arrModule = each($arrSortedArray)) {
			ksort($arrSortedArray[$arrModule["key"]]);
			if (isset($config["admmenu"][$arrModule["key"]])) {
				while ($entry = each($arrModule["value"])) {
					$arrMI["link"] = $entry["value"];
					$arrMI["text"] = $entry["key"];
					$admmenu[] = $arrMI;
				}
			}
			else {
				if (!isset($arrModule['value']['groups']) && count($arrModule["value"]) == 1) {
					$strTMP = current($arrModule["value"]);
					$arrTMP = each($arrModule["value"]);
					$strLinkText = (strlen($arrTMP["key"]) < 20) ? $arrTMP["key"] : $arrModule["key"];
					$arrMI["link"] = $strTMP;
					$arrMI["text"] = $strLinkText;
					$admmenu[] = $arrMI;
				}
				else {
					$arrMI["link"] = "admin_menu.php?module={$arrModule["key"]}";
					$arrMI["text"] = $arrModule["key"];
					$admmenu[] = $arrMI;
				}
			}
		}

		$_SESSION["wt"]["admin_menu_draw"] = array();
		if (count($admmenu)) {
			$_SESSION["wt"]["admin_menu_draw"] = $arrSortedArray;
		}

		?>
        <nav class="navbar navbar-default" role="navigation">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.php"><?php print $cfg["core"]["title"] ?></a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
						<?php
						foreach($_SESSION["wt"]["admin_menu_draw"] AS $key => $val){
							?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
									<?php print ucwords(str_replace("_","",$key)); ?> <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
									<?php
									foreach($val AS $title => $link){
										if(is_array($link)){
											foreach($link AS $key1 => $key2){
												?>
                                                <li class="dropdown-submenu">
                                                    <a href="#" tabindex="-1">
														<?php print $key2["name"]; ?>
                                                    </a>
                                                    <ul class="dropdown-menu">
														<?php
														foreach($key2["elements"] AS $link1 => $link2){
															?>
                                                            <li><a href="<?php print $link2["file"]; ?>"><?php print $link2["name"]; ?></a></li>
															<?php
															unset($link2);
															unset($link1);
														}
														?>
                                                    </ul>
                                                </li>
												<?php

												unset($key2);
												unset($key1);
											}
										}
										else{
											?>
                                            <li><a href="<?php print $link; ?>"><?php print $title; ?></a></li>
											<?php
										}
										unset($link);
										unset($title);
									}
									?>
                                </ul>
                            </li>
							<?php
							unset($val);
							unset($key);
						}
						?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <img src="adm_main.php?mde=users&wdw=myaccount&op=avatar&uid=<?php print $_SESSION["wt"]["uid"]; ?>" class="circular">
								<?php print $strName; ?> <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="adm_main.php?mde=users&wdw=myaccount">Mi cuenta</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="index.php?act=logout">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>
		<?php
	}
}

function draw_menu_vertical(){
    jquery_includeLibrary("scrollbar");
	if ($_SESSION["wt"]["logged"]) {
		$arrMenu = prepare_admin_menu_data();
        $strDir = basename($_SERVER["PHP_SELF"]) . ((empty($_SERVER["QUERY_STRING"])) ? "" : "?{$_SERVER["QUERY_STRING"]}");
		?>
        <div class="nav-side-menu">
            <div class="brand">
                <img src="themes/geniusAdminLTE/images/theme_image_principal.png" onclick="document.location.href='index.php'">
            </div>
            <i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>
            <div id="cntMenuList" class="menu-list scrollbar-inner" style="font-size: 15px; height: 150px !important;">
                <ul id="menu-content" class="menu-content collapse out">
                    <li class="collapsed active">
                        <a  href="index.php">Página principal</a>
                    </li>
                    <?php
                    if(count($arrMenu) == 1){
                        foreach($arrMenu AS $module => $links){
                            foreach($links AS $name => $link){
                                ?>
                                <li class="collapsed active">
                                    <a  href="<?php print $link ?>"><?php print $name; ?></a>
                                </li>
                                <?php
                                unset($link);
                                unset($name);
                            }
                            unset($links);
                            unset($module);
                        }
                    }
                    else{
                        foreach($arrMenu AS $module => $links){
                            $strId = striptildes(str_replace(" ","",strtolower($module)));
                            ?>
                            <li data-toggle="collapse" data-target="#<?php print $strId; ?>" class="collapsed active">
                                <?php print $module; ?> <span class="arrow"></span>
                            </li>
                            <ul class="sub-menu collapse" id="<?php print $strId; ?>">
                                <?php
                                foreach($links AS $name => $link){
                                    ?>
                                    <li><a href="<?php print $link ?>"><?php print $name; ?></a></li>
                                    <?php
                                    unset($link);
                                    unset($name);
                                }
                                ?>
                            </ul>
                            <?php
                            unset($links);
                            unset($module);
                        }
                    }
                    ?>
                    <li data-toggle="collapse" data-target="#li-user" class="collapsed active menu-user">
                        <?php print $_SESSION["wt"]["name"]; ?> <span class="arrow"></span>
                    </li>
                    <ul class="sub-menu collapse" id="li-user">
                        <li><a href="adm_main.php?mde=users&wdw=myaccount">Mi cuenta</a></li>
                        <li><a href="index.php?act=logout">Cerrar sesión</a></li>
                    </ul>
                </ul>
            </div>
            <div class="box-user" onclick="$(this).find('.box-links').toggleClass('hide')">
                <div class="box-links hide">
                    <a href="adm_main.php?mde=users&wdw=myaccount" class="fa fa-user" style="font-size: 15px;"> Mi cuenta</a>
                    <a href="index.php?act=logout" class="fa fa-sign-out" style="font-size: 15px;"> Cerrar sesión</a>
                </div>
                <div class="content-img">
                    <img src="<?php print "adm_main.php?mde=users&wdw=myaccount&op=avatar&uid={$_SESSION["wt"]["uid"]}"; ?>" width="50px" height="50px">
                </div>
                <div class="content-info" style="font-size: 15px;">
                    <?php print $_SESSION["wt"]["name"] . "<br>"; ?>
                    <?php print $_SESSION["wt"]["nombres"] . " " . $_SESSION["wt"]["apellidos"]; ?>
                </div>
            </div>
        </div>
        <script>
            let strDir = "<?php print $strDir; ?>";
            let arrDir = strDir.split("&");
            let strFromLS = localStorage.getItem("from");
            if(strFromLS == "analysis"){
                if(arrDir[1] == "wdw=proposal"){
                    $.each(arrDir, (key, val) => {
                        if (val == "meth=analysis") {
                            /*aún sin acciones, pero está para evitar entrar a la sentencia de abajo*/
                        }
                    });
                }
                else if( arrDir[1] != "wdw=payback" ){
                    if( arrDir[1] != "wdw=billing" ){
                        localStorage.removeItem('carrito');
                        localStorage.removeItem('carritoID');
                        localStorage.removeItem('carritoClient');
                        localStorage.removeItem("carritoCorrelative");
                    }
                }
            }


            $(document).ready(function() {
                $(".scrollbar-inner").scrollbar();
                doneResizingWindow();
            });
            $(window).resize(function () {
                var id;
                clearTimeout(id);
                id = setTimeout(doneResizingWindow, 50);
            });
            function doneResizingWindow(){
                var height = $(window).height();
                var width = $(window).width();
                if(height <= 370){
                    $(".scroll-wrapper").removeAttr("style");
                    $(".scroll-wrapper").attr({
                        "style": "height: 30% !important"
                    });
                }
                else if(height <= 490){
                    $(".scroll-wrapper").removeAttr("style");
                    $(".scroll-wrapper").attr({
                        "style": "height: 40% !important"
                    });
                }
                else if(height <= 560){
                    $(".scroll-wrapper").removeAttr("style");
                    $(".scroll-wrapper").attr({
                        "style": "height: 50% !important"
                    });
                }
                else if(height <= 620){
                    $(".scroll-wrapper").removeAttr("style");
                    $(".scroll-wrapper").attr({
                        "style": "height: 160% !important"
                    });
                }
                else if(height <= 750){
                    $(".scroll-wrapper").removeAttr("style");
                    $(".scroll-wrapper").attr({
                        "style": "height: 65% !important"
                    });
                }
                else if(height >= 751){
                    $(".scroll-wrapper").removeAttr("style");
                    $(".scroll-wrapper").attr({
                        "style": "height: 60% !important"
                    });
                }

                if(width > 766){
                    $("#li-user").removeClass("in");
                }
            }
        </script>
		<?php
	}
}

function draw_menu_adminlte()
{
    global $cfg;
	if ($_SESSION["wt"]["logged"]) {
		$arrMenu = prepare_admin_menu_data();
		//dump($arrMenu);
        $consulta = db_query("SELECT path, specified, title FROM wt_profile_configuration WHERE `type` = 'image';");
        $consultaMenuLinks = db_query("SELECT * FROM wt_menu_links where id > 0 order by order_menu;");
        $homeCaption = sqlGetValueFromKey("Select * from wt_caption_info where caption_position = 'caption-dashboard'");
        $arrImg = [];
        foreach($consulta as $image){
            $arrImg[$image["specified"]] = $image;
        }

        $arrMenuLinks = [];
        foreach($consultaMenuLinks as $menuLink){
            $arrMenuLinks[] = $menuLink;
        }
        $primary = $arrImg["img-menu-exp"];
        $secondary = $arrImg["img-menu-min"];
        $menu_primary = $primary["path"] && $primary["specified"] == "img-menu-exp" ?
            $primary["path"] :
            (
                    file_exists("profiles/".$cfg["core"]["site_profile"]."/images/theme_image_principal_desp.png") ?
                    "profiles/".$cfg["core"]["site_profile"]."/images/theme_image_principal_desp.png" :
                    "themes/".$cfg["core"]["theme"]."/images/theme_image_principal_desp.png"
            );

        $menu_secondary = $secondary["path"] ? $secondary["path"] :
            (
                    file_exists("profiles/".$cfg["core"]["site_profile"]."/images/theme_image_principal_mini.png") ?
                    "profiles/".$cfg["core"]["site_profile"]."/images/theme_image_principal_mini.png" :
                    "themes/".$cfg["core"]["theme"]."/images/theme_image_principal_mini.png"
            );
        $strDir = basename($_SERVER["PHP_SELF"]) . ((empty($_SERVER["QUERY_STRING"])) ? "" : "?{$_SERVER["QUERY_STRING"]}");
		?>
        <header class="main-header">
            <a href="index.php" class="logo" style="position: fixed">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini">
                <img src="<?php print $menu_secondary; ?>" style="width: 40%; " onclick="document.location.href='index.php'">
                </span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg">
                    <img src="<?php print $menu_primary; ?>" style="width: 80%;" onclick="document.location.href='index.php'">
                </span>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <!-- menu prueba
            <nav class="navbar-static-top">
                <div class="notpizza">
                    <a href="#" class="fa fa-bars" style="color: white; background: #0D3349; position: fixed; top: 12px;"   data-toggle="push-menu" role="button" >
                        <span class="sr-only">Toggle navigation</span>
                    </a>
                </div>
                <div class="menu_center">
                    <script defer>
                        if(window.innerWidth <= 600){
                            let img = document.querySelector('.menu_center');
                            img.innerHTML = `<span class="">
                    <img src="themes/geniusAdminLTE/images/theme_image_principal.png" style="width: 30%;" onclick="document.location.href='index.php'">
                </span>`;
                        }
                    </script>
                </div>
                <div class="user_options_top">
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="dropdown messages-menu">
                                <a href="#" class="dropdown-toggle"  data-toggle="dropdown">
                                    <i class="fa fa-users"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="header"><h4>Usuarios conectados</h4></li>
                                    <li>

                                        <ul class="menu">
                                            <li>
                                                <div style="padding: 10px;">
                                                    <div class="pull-left">
                                                        <i class="fa fa-users fa-2x"></i>
                                                    </div>
                                                    <div class="pull-left">
                                                        <h4 style="padding-left: 5px;">
                                                            <?php /*draw_users_online(true); */?>
                                                        </h4>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="footer"><a href="#"></a></li>
                                </ul>
                            </li>
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" style="height: 40px" data-toggle="dropdown">
                                    <img src="<?php /*print "adm_main.php?mde=users&wdw=myaccount&op=avatar&uid={$_SESSION["wt"]["uid"]}"; */?>" width="50px" height="50px" class="user-image" alt="User Image">
                                    <span class="hidden-xs"><?php /*print $_SESSION["wt"]["nombres"] . " " . $_SESSION["wt"]["apellidos"]; */?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="user-header">
                                        <img src="<?php /*print "adm_main.php?mde=users&wdw=myaccount&op=avatar&uid={$_SESSION["wt"]["uid"]}"; */?>" width="50px" height="50px" class="img-circle" alt="User Image">
                                        <p>
                                            <?php /*print $_SESSION["wt"]["nombres"] . " " . $_SESSION["wt"]["apellidos"]; */?>
                                            <small>
                                                <a href="#"><i class="fa fa-circle text-success"></i> Online</a><br/>
                                            </small>

                                        </p>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="adm_main.php?mde=users&wdw=myaccount" class="btn btn-default btn-flat" style="font-size: 15px;">
                                                <i class="fa fa-user"></i>
                                                Mi cuenta
                                            </a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="index.php?act=logout" class="btn btn-danger btn-flat" style="font-size: 15px;">
                                                <i class="fa fa-sign-out"></i>
                                                Cerrar sesión
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            -->
            <nav class="navbar navbar-static-top" style="" >
                <!-- Sidebar toggle button-->
                <div class="menu_container">
                    <div class="bars_container"><a href="#" class="fa fa-bars" style=""   data-toggle="push-menu" role="button" >
                            <span class="sr-only">Toggle navigation</span>
                        </a></div>

                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <?php
                            if(check_user_class("admin")){
                                ?>
                                <li class="dropdown messages-menu">
                                    <a href="#" class="dropdown-toggle"  data-toggle="dropdown">
                                        <i class="fa fa-users"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="header"><h4>Usuarios conectados</h4></li>
                                        <li>
                                            <!-- inner menu: contains the actual data -->
                                            <ul class="menu">
                                                <li>
                                                    <div style="">
                                                        <div class="pull-left">
                                                            <i class="fa fa-users fa-2x"></i>
                                                        </div>
                                                        <div class="pull-left">
                                                            <h4 style="">
                                                                <?php draw_users_online(true); ?>
                                                            </h4>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </li>
                                        <li class="footer"><a href="#"></a></li>
                                    </ul>
                                </li>
                                <?php
                            }
                            ?>
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" style="" data-toggle="dropdown">
                                    <img src="<?php print "adm_main.php?mde=users&wdw=myaccount&op=avatar&uid={$_SESSION["wt"]["uid"]}"; ?>" width="50px" height="50px" class="user-image" alt="User Image">
                                    <span class="hidden-xs"><?php print $_SESSION["wt"]["nombres"] . " " . $_SESSION["wt"]["apellidos"]; ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="user-header">
                                        <img src="<?php print "adm_main.php?mde=users&wdw=myaccount&op=avatar&uid={$_SESSION["wt"]["uid"]}"; ?>" width="50px" height="50px" class="img-circle" alt="User Image">
                                        <p>
                                            <?php print $_SESSION["wt"]["nombres"] . " " . $_SESSION["wt"]["apellidos"]; ?>
                                            <small>
                                                <a href="#"><i class="fa fa-circle text-success"></i> Online</a><br/>
                                            </small>
                                            <!--<small>Member since Nov. 2012</small>-->
                                        </p>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="adm_main.php?mde=users&wdw=myaccount" class="btn btn-default btn-flat" style="font-size: 15px;">
                                                <i class="fa fa-user"></i>
                                                Mi cuenta
                                            </a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="index.php?act=logout" class="btn btn-danger btn-flat" style="font-size: 15px;">
                                                <i class="fa fa-sign-out"></i>
                                                Cerrar sesión
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <aside class="main-sidebar">

            <section class="sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <ul class="sidebar-menu" data-widget="tree">
                        <li class="treeview" onclick="location.href='index.php'">
                            <a class="text-left" href="index.php" >
                                <i class="fa fa-home pull-left"></i>
                                <span>Página principal</span>
                            </a>
                        </li>
                        <?php
                        draw_item_menuAdminLte($arrMenu);
                        if($arrMenuLinks){
                            if(count($arrMenuLinks) >= 5){
                                $data = "";
                                foreach ($arrMenuLinks as $menu_link) {
                                    if ($menu_link["available"]) {
                                        $blank_self = $menu_link["blank"] ? '_blank' : '_self';
                                        $data .= <<<EOD
                                            <li class="treeview" onclick="window.open('{$menu_link["url"]}', '{$blank_self}');">
                                                <a class="text-left" href="{$menu_link["url"]}" {$blank_self}>
                                                    <i class="{$menu_link["icon"]}"></i><span>{$menu_link["title"]}</span>
                                                </a>
                                            </li>
EOD;
                                    }
                                }
                                echo <<<EOD
                                        <li class="treeview">
                                                <a class="text-left" href="#">
                                                    <i class="fa fa-link"></i><span>Links</span><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
                                                </a>
                                                <ul class="treeview-menu">
                                                    $data
                                                </ul>
                                        </li>
EOD;
;
                            }else {
                                foreach ($arrMenuLinks as $menu_link) {
                                    if ($menu_link["available"]) {
                                        $blank_self = $menu_link["blank"] ? '_blank' : '_self';
                                        echo <<<EOD
                                            <li class="treeview" onclick="window.open('{$menu_link["url"]}', '{$blank_self}');">
                                                <a class="text-left" href="{$menu_link["url"]}" {$blank_self}>
                                                    <i class="{$menu_link["icon"]}"></i><span>{$menu_link["title"]}</span>
                                                </a>
                                            </li>
EOD;
                                    }
                                }
                            }
                        }
                        ?>
                    </ul>
                    <div class="relative_container" style="position: absolute; bottom: 0; width: 100%;">
                        <div id="myDIV" class="divAlert" style="color: #ffffff; position: absolute; bottom: 25px; left: 0; right: 0; text-align: center;">
                            <?php echo str_replace("|", "<br>", $homeCaption["content"]) ?>
                        </div>
                    </div>
                </section>
            </section>
        </aside>
        <script type="application/javascript">

            let strDir = "<?php print $strDir; ?>";
            let arrDir = strDir.split("&");
            let strFromLS = localStorage.getItem("from");
            if(strFromLS == "analysis"){
                if(arrDir[1] == "wdw=proposal"){
                    $.each(arrDir, (key, val) => {
                        if (val == "meth=analysis") {
                            /*aún sin acciones, pero está para evitar entrar a la sentencia de abajo*/
                        }
                    });
                }
                else if( arrDir[1] != "wdw=payback" ){
                    if( arrDir[1] != "wdw=billing" ){
                        localStorage.removeItem('carrito');
                        localStorage.removeItem('carritoID');
                        localStorage.removeItem('carritoClient');
                        localStorage.removeItem("carritoCorrelative");
                    }
                }
            }

            let menu = localStorage.getItem("menu");
            if(typeof menu == "undefined"){
                localStorage.setItem("menu", "expand");
            }

            $('[data-toggle="push-menu"]').pushMenu()
            let $pushMenu = $('[data-toggle="push-menu"]').data('lte.pushmenu')
            let $layout = $('body').data('lte.layout')
            $(window).on('load', function() {
                // Reinitialize variables on load
                $pushMenu = $('[data-toggle="push-menu"]').data('lte.pushmenu')
                $layout = $('body').data('lte.layout')
            });


            $(document).on('expanded.pushMenu', ()=>{
                localStorage.setItem("menu", "expand");
            }).on('collapsed.pushMenu',()=>{
                localStorage.setItem("menu", "collapse");
            });
            let $menuDisplay = localStorage.getItem("menu");
            if($menuDisplay == "collapse")
                $pushMenu.collapse();
        </script>

        <?php
	}
}

function draw_item_menuAdminLte($menu,$boolGroup = false)
{
    global $config, $lang;
	foreach ($menu AS $key => $value){
		if(is_array($value) && count($value)>0){
            if($boolGroup){
                if(is_array($value) && count($value)>0){
                    ?>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa <?php print global_function::getParam($value,"icon","fa-circle-o");  ?>"></i>
                            <span><?php print $value["name"]; ?></span>
                            <?php
                            if(!empty($value["new"])){
                                $dateLimit = strtotime($value["new"]);
                                $dateToDay = strtotime(date("Y-m-d H:i:00",time()));
                                if(!empty($dateToDay)){
                                    if($dateToDay < $dateLimit){
                                        ?>
                                        <span class="newElementToAccess floatRightMargin"><?php print $lang["NEW_CHARACTERISTIC"] ?></span>
                                        <?php
                                    }
                                }
                            }
                            ?>

                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <?php
                            draw_item_menuAdminLte($value["elements"]);
                            ?>
                        </ul>
                    </li>
                    <?php
                }
            }
            else{
                ?>
                <li class="treeview">
                    <a href="#">
                        <i class="fa <?php print global_function::getParam($config["admmenu"][$key],"icon","fa-circle-o");  ?>"></i>
                        <span><?php print $key; ?></span>
                        <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <?php
                            if(!empty($value["groups"])){
	                            draw_item_menuAdminLte($value["groups"],true);
                            }
                            else{
	                            draw_item_menuAdminLte($value);
                            }
                        ?>
                    </ul>
                </li>
                <?php
            }
		}
		else{
			?>
            <li><a href="<?php echo $value ?>"><i class="fa <?php print global_function::getParam($config["admmenu"][$key],"icon","fa-circle-o");  ?>"></i> <?php echo $key; ?></a></li>
			<?php
		}
		?>
		<?php
	}
	?>
	<?php
}
