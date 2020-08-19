<?php
/**
 * Autor: Alexander Flores
 * Fecha: 25-08-2014
 * Descripcion: vista del theme
 */
$strThemeGlobalContentsWidth = "85%";
$intGlobalTabMenuWidth = 1000;
$boolGlobalShowTabMenu = false;
$boolGlobalShowNormalMenu = true;
$boolGlobalShowMenuWidget = false;

$boolGlobalShowLeftCol = true;
$boolGlobalMaximizeInterface = false;

$lang['bus_usage_tipo1'] = 'Completo';
$lang['bus_usage_tipo2'] = 'Medio Servicio';
$lang['bus_usage_none'] = 'No Usa';
$lang["PASSWD_TEXT"] = <<<EOD
                        <p>
                            <b style="font-size:25px;">¿Perdio su contraseña?</b>
                        </p>
                        <p>
                            <label style="font-size:16px;">Por favor ingrese el nombre de empresa y la dirección de correo electrónico que tiene registrado en su sitio.</label>
                        </p>
                        <p>
                            <label style="font-size:16px;">Esto generará una contraseña nueva para su usuario y se la enviará a la dirección especificada solamente si:</label>
                            <ul>
                                <li><b>La dirección está registrada en el sitio</b></li>
                                <li><b>La dirección registrada es una cuenta de correo que exista y es válida</b></li>
                            </ul>
                        </p>
EOD;
$lang["USERS_ONLINE_TEXT"]="Hay: <b>%d</b> %sen línea%s y <b>%d</b> visitantes";
$lang["USERS_ONLINEWOV_TEXT"]="Hay: <b>%d</b> %susuarios en línea%s";

$config["theme_vars"]["JavaMenuLine"] = "";

//$config["theme_vars"]["JavaMenuItemClassNormal"] = "themes/tigoPos/img/menuItems.swf";
$config["theme_vars"]["JavaMenuItemClassNormal"] = "menuLink";
$config["theme_vars"]["JavaMenuItemClassOver"] = "menuLink_Over";

$config["theme_vars"]["JavaSubMenuItemClassNormal"] = "subMenuLink";
$config["theme_vars"]["JavaSubMenuItemClassOver"] = "subMenuLink_Over";

$config["theme_vars"]["NotLoggedBackGround"] = "IGNORE";

// Visualizaciones que se pueden cambiar desde el profile
$config["profile_visualizacion"]["fondo_encabezado"] = "#E6E6E6";

function draw_header($page = ""){
    ?>
    <!DOCTYPE HTML>
    <html>
	<?php draw_header_tag($page); ?>
        <body class="hold-transition skin-tigo sidebar-mini">
            <link rel="stylesheet" href="core/packages/adminlte/css/AdminLTE.css">
            <link rel="stylesheet" href="themes/geniusAdminLTE/css/skin_tigo.css">

            <script src="core/packages/adminlte/js/adminlte.min.js"></script>
            <script src="themes/geniusAdminLTE/js/theme.js"></script>
            <style>
                .content-wrapper{
                    background-color: white;
                }
                p{
                    padding: 0px;
                }
                .user-panel{
                    min-height: 70px;
                }
                h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6, {
                       font-family: Cronos-Pro_12459 !important;
                }
                button {
                    font-family: Cronos-Pro_12459 !important;
                }
                table{ font-family: Cronos-Pro_12459 !important;

                }
                td{
                    font-family: Cronos-Pro_12459 !important;
                }
                <?php
                ?>
                .f_alert-container{
                    max-height: 100%;
                    position: fixed;
                    bottom: 5px;
                    z-index: 9999;
                    right: 20px;
                    padding: .2em;
                }
                .f_alert{
                    background: rgba(0,0,0, .8);
                    color: #fff;
                    padding: .3em .4em;
                    text-align: left;
                    font-weight: 300;
                    min-width: 100px;
                    font-size: 1.1em;
                    margin: 1em 0;
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    box-shadow: 0px 0px 10px rgba(0,0,0, .4);
                }
                .f_alert-success > p, h1, h2, h3, h4{
                    margin: 0;
                }
                .f_alert-success{
                    background: rgba(39, 170, 16, .9);
                }
                .f_alert-error{
                    background: rgba(170, 16, 16, .9);
                }
                .f_alert > i{
                    margin: 0 .3em;
                }
                .f_alert-warning{
                    background: rgba(220, 150, 22, .9);
                }

                .f_alert_animation-fadeIn{
                    animation-name: f_alert_animation_fadeIn;
                    animation-duration: .3s;
                    animation-timing-function: ease;
                }

                .f_alert_animation-fadeOut{
                    visibility: hidden;
                    opacity: 0;
                    transition: all .3s;
                }

                @keyframes f_alert_animation_fadeIn {
                    from{opacity: 0}
                    to{opacity: 1}
                }

                .custom-form-group input, .custom-form-group select{
                    border-radius: 22px !important;
                    padding: 1em;
                    background: rgba(200, 200, 200, .1);
                }

                .custom-form-group label{
                    font-weight: 100;
                }

                @keyframes f_alert_animation_fadeOut {
                    0%{display: block}
                    100%{display: none !important;}
                }
            </style>
            <link rel="stylesheet" href="themes/geniusAdminLTE/css/custom_styles.css">
	        <?php //draw_menu_vertical();?>
            <div class="wrapper" style="height: auto; min-height: 100%;">
                <?php draw_menu_adminlte();?>
                <div class="f_alert-container">
                    <!--<div class="f_alert">que pex</div>
                    <div class="f_alert f_alert-success"><i class="fa fa-check"></i><p>Guardado con éxito</p></div>
                    <div class="f_alert f_alert-warning">precaucion</div>
                    <div class="f_alert f_alert-error">Ocurrio un error al guardar</div>-->
                </div>
                <script >
                    function FAlert(msg, type, icon, reload = false, time){
                        let alerts_container = document.querySelector('.f_alert-container');
                        let ic = `<i class="${icon || icon}"></i>`;
                        let alert = `${ic+msg}`;
                        let element = document.createElement('div');
                        element.classList.add('f_alert');
                        element.classList.add(`f_alert-${type || ''}`);
                        element.classList.add('f_alert_animation-fadeIn');
                        let timeoutTime = time || 2000;
                        element.innerHTML = alert;
                        alerts_container.appendChild(element);
                        setTimeout(()=>{
                            element.classList.remove('f_alert_animation-fadeIn');
                            element.classList.add('f_alert_animation-fadeOut');
                        }, timeoutTime - 500);
                        setTimeout(()=>{
                            alerts_container.removeChild(element);
                            if(reload) location.reload();
                        }, timeoutTime);
                    }
                </script>
                <div class="content-wrapper">
                    <div class="ctn-main content">

	<?php
}

function draw_footer(){
	global $config;
    $version = global_function::getParam($config,"version",1);
    $appname = global_function::getParam($config, "appname","HML")
	?>
                    </div>
                </div>
            </div>
            <footer class="main-footer">
                <div class="pull-right ">
                    <i class="fa fa-code-fork"></i><span style="font-weight: bold"> <?php print $appname; ?> Version <?php print $version; ?></span>
                </div>
            </footer>
        </body>
    </html>
	<?php
}


function theme_draw_centerbox_open($title = "", $strCatalogueShow = false) {
	if(!empty($title)){
		?>
        <div class="col-lg-12 text-center title-page">
			<?php print $title; ?>
        </div><br/><br/>
        <?php
	}
    if(!empty($strCatalogueShow)){
        ?>
        <div class="bs-callout bs-callout-info" id="callout-helper-context-color-specificity">
            <h4>Información actualizada el <?php
                $arrData = global_function::getUpToDateCatalogue("{$strCatalogueShow}");
                print show_date($arrData["fecha"] . " " . $arrData["hora"],true,true,true,true,true); ?>
            </h4>
        </div>
        <?php
    }
	?>
    <div class="col-lg-12 row-main">
	<?php
}

function theme_draw_centerbox_close() {
	?>
    </div>
	<?php
}