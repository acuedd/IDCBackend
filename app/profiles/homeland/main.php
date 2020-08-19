<?php
// Configuracion del profile e includes
$strProfile = $cfg["core"]["site_profile"];
$strTextProfile = "profiles/{$strProfile}";
$strThemeGlobalContentsWidth = "100%";
    
// Configuraciones visuales
$boolGlobalShowTabMenu = false;
$boolGlobalShowNormalMenu = true;
$boolGlobalShowMenuWidget = false;
$boolGlobalShowLeftCol = true;
$boolGlobalMaximizeInterface = true;

$config["profile_visualizacion"]["fondo_encabezado"] = "#ffffff";

// Overrides de textos, etc.
$config["theme_vars"]["Titulo"] = "&nbsp;";
$config["theme_vars"]["colorTTFrender"] = "252,255,229";

include_once "profiles/$strProfile/functions.php";