<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 25/06/18
 * Time: 10:03
 */
require_once "wt_config.php";
require_once "core/objects/configCore/configCore.php";
require_once __DIR__ . '/vendor/autoload.php';
global $config;

use Pimple\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

//Enable whoops Service provider to handle errors
if ($config['debug']) {
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	//Make sure the class exists,
	//because this is a dev-dependency and may not be installed
	//Its better not enabled for mor details
	if (false && class_exists('Whoops\\Provider\\Silex\\WhoopsServiceProvider')) {

	}
}


//This is for proper time handling:
date_default_timezone_set($config['appConfig']['settings']['timezone']);

$app = new Container();
$driver = configCore::getDriver();
$app['config'] = $config["appConfig"]["database"]["connections"][$driver];
$app["db"] = function($c){
    $capsule = new Capsule;
    $capsule->addConnection($c["config"]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    return $capsule;
};







