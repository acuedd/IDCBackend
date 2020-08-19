<?php
if (strstr($_SERVER["PHP_SELF"], "/modules/"))  die ("You can't access this file directly...");
global $lang, $cfg;
$entertainment_basePath = "modules/mm_resources";
include_once("{$entertainment_basePath}/lang/msg_".check_lang($cfg["core"]["lang"]).".php" );
include_once("{$entertainment_basePath}/module_info.php");
include_once("{$entertainment_basePath}/functions.php");
