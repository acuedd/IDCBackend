<?php
/**
 * Created by PhpStorm.
 * User: edwardacu <eacu@homeland.com.gt>
 * Date: 25/06/18
 * Time: 10:12
 */

use \Phpmig\Adapter;
use Illuminate\Database\Capsule\Manager as Capsule;
require_once __DIR__ . '/bootstrap.php';
global $app,$appConf;
$app['phpmig.adapter'] = function($app){
	return new Adapter\Illuminate\Database($app["db"], 'migrations');
};

$app['phpmig.migrations_path'] = function () {
	return __DIR__ . '/core/sql/Migrations';
};
/** @var //Pimple\Container $container
 * Here put your modules path to consider run migrations
 */
$app['phpmig.migrations'] = function () {
	return array_merge(
	    /*Migrations*/
		glob(__DIR__.'/libs/xls_load/sql/Migrations/*.php'),
		glob(__DIR__.'/libs/csv_load/sql/Migrations/*.php'),
		glob(__DIR__.'/modules/sales/sql/Migrations/*.php'),
		glob(__DIR__.'/modules/customers/sql/Migrations/*.php'),
		glob(__DIR__.'/modules/users/sql/Migrations/*.php'),
        glob(__DIR__.'/modules/configuration/sql/Migrations/*.php'),
		glob(__DIR__.'/modules/credit_card/sql/Migrations/*.php'),
		glob(__DIR__.'/modules/sms/sql/Migrations/*.php'),
        /*Seeds*/
        glob(__DIR__.'/modules/sms/sql/Seeds/*.php'),
        glob(__DIR__.'/modules/configuration/sql/Seeds/*.php'),
        glob(__DIR__.'/modules/credit_card/sql/Seeds/*.php'),
        glob(__DIR__.'/modules/users/sql/Seeds/*.php'),
        glob(__DIR__.'/core/sql/Seeds/*.php')
	);
};

/** //@var -Pimple\Container $container
 * How to use sets but this is not functionaly because not run only core's migrations
$app['phpmig.sets'] = function ($container) {
	global $app;
	return array(
		'sales' => array(
			'adapter' =>  $app['phpmig.adapter'],
			new Adapter\File\Flat(__DIR__ . DIRECTORY_SEPARATOR . 'modules/sales/sql/log' . "sales" . '_migrations.log'),
			//'adapter' => new Adapter\File\Flat('modules/sales/sql/sales_migrations.log'),
			'migrations_path' => __DIR__.'/modules/sales/sql/Migrations'
		),
		'users' => array(
			'adapter' =>  $app['phpmig.adapter'],
			new Adapter\File\Flat(__DIR__ . DIRECTORY_SEPARATOR . 'modules/users/sql/log' . "users" . '_migrations.log'),
			'migrations_path' => __DIR__.'/modules/users/sql/Migrations'
		),
		'customers' => array(
			'adapter' =>  $app['phpmig.adapter'],
			new Adapter\File\Flat(__DIR__ . DIRECTORY_SEPARATOR . 'modules/customers/sql/log' . "customers" . '_migrations.log'),
			'migrations_path' => __DIR__.'/modules/customers/sql/Migrations'
		)
	);
};
*/


//I can run this directly, because Capsule is set as globally
//with $capsule->setAsGlobal(); line at /bootstrap.php
$app['schema'] = function () {
	return Capsule::schema();
};

return $app;