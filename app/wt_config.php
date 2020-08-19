<?php
//global $config;
require_once __DIR__ . '/vendor/autoload.php';
require_once "core/objects/configCore/configCore.php";

use Dotenv\Dotenv;

$config = array();

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();
$config = [
	"dbtype" => getenv("HML_DBTYPE"),
	"host"   => getenv("HML_HOST"),
	"database"   => getenv("HML_DATABASE"),
	"prefix" => getenv("HML_PREFIX"),
	"user" => getenv("HML_USER"),
	"password" => getenv("HML_PASS"),
	"enviroment" => getenv("HML_ENVIROMENT"),
	"debug" => getenv("DEBUG"),
	"timezone" => getenv("HML_TIMEZONE"),
    "appname" => getenv("HML_APPNAME"),
	"version" => getenv("HML_VERSION_APP"),
	"engine" => getenv("HML_DBENGINE"),
];
configCore::setDebug(false);
configCore::init($config);
configCore::getConfig($config);
if($config["enviroment"] == "local"){
	configCore::setDebug(true);
	include_once("wt_config_local.php");
}
else{
	configCore::getConfig($config);
}