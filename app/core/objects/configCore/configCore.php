<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 25/06/18
 * Time: 15:29
 */

Class configCore{
	private static $arrConfig = array(
		"dbtype" => "mysql",
		"host"   => "127.0.0.1",
		"prefix" => "wt",
		"user" => "",
		"password" => "",
		"enviroment" => "",
		"debug" => "",
		"timezone" => "",
		"appConfig" => array(
			"settings" => array(
				"timezone" => ""
			),
			"database" => array(
				"connection" => "",
				"connections" => array(
					"mysql" => array(
						"driver" => "",
						"host" => "",
						"database" => "",
						"username" => "",
						"password" => "",
						"charset" => "latin1",
						"collation" => "latin1_swedish_ci",
						"prefix" => "",
						"strict" => false,
						"engine" => "innodb"
					)
				)
			),
			"phpmig" => array(
				"tableName" => "migrations",
				"createStatement" => "CREATE TABLE migrations ( version VARCHAR(255) NOT NULL );"
			)
		)
	);

	/**
	 * configCore constructor.
	 */
	public static function getDriver(){
		$driver = "";
		if(self::$arrConfig["dbtype"] == "mysql" || self::$arrConfig["dbtype"] == "mysqli"){
			$driver = "mysql";
		}
		else{
			$driver = self::$arrConfig["dbtype"];
		}
		return $driver;
	}
	public static function setDatabase($strDatabase){
		$driver = self::getDriver();
		self::$arrConfig["database"] = $strDatabase;
		self::$arrConfig["appConfig"]["database"]["connections"][$driver]["database"] = $strDatabase;
	}
	public static function setUser($strUser){
		$driver = self::getDriver();
		self::$arrConfig["user"] = $strUser;
		self::$arrConfig["appConfig"]["database"]["connections"][$driver]["username"] = $strUser;
	}
	public static function setPass($strPass){
		$driver = self::getDriver();
		self::$arrConfig["password"] = $strPass;
		self::$arrConfig["appConfig"]["database"]["connections"][$driver]["password"] = $strPass;
	}
	public static function setHost($strHost){
		$driver = self::getDriver();
		self::$arrConfig["host"] = $strHost;
		self::$arrConfig["appConfig"]["database"]["connections"][$driver]["host"] = $strHost;

	}
	public static function setDbType($strDbType){
		$driver = ($strDbType=="mysql" || $strDbType =="mysqli")?"mysql":$strDbType;
		self::$arrConfig["dbtype"] = $strDbType;
		self::$arrConfig["appConfig"]["database"]["connection"] = $driver;
		self::$arrConfig["appConfig"]["database"]["connections"][$driver]["driver"] = $driver;
	}
	public static function setPrefix($strPrefix){
		//$driver = self::getDriver();
		self::$arrConfig["prefix"] = $strPrefix;
		//self::$arrConfig["appConfig"]["database"]["connections"][$driver]["prefix"] = $strPrefix;
	}
	public static function setEnviroment($strEnviroment)
	{
		self::$arrConfig["enviroment"] = $strEnviroment;
	}
	public static function setDebug($boolDebug = false)
	{
		self::$arrConfig["debug"] = $boolDebug;
	}
	public static function setTimezone($strTimezone)
	{
		self::$arrConfig["timezone"] = $strTimezone;
		self::$arrConfig["appConfig"]["settings"]["timezone"] = $strTimezone;
	}
	public static function setAppConfig($key,$subKey = "",$value){
		if(!empty($subKey) && isset(self::$arrConfig[$key][$subKey]) && is_array(self::$arrConfig[$key][$subKey])){
			self::$arrConfig[$key][$subKey] = $value;
		}
		elseif(isset(self::$arrConfig[$key])){
			self::$arrConfig[$key] = $value;
		}
		else{
			print_r("Position {$key} in config array, doesn't exist");
		}
	}
	public static function setEngine($strEngine)
	{
		$driver = self::getDriver();
		self::$arrConfig["appConfig"]["database"]["connections"][$driver]["engine"] = $strEngine;
	}
	public static function getEngine()
	{
		$driver = self::getDriver();
		return self::$arrConfig["appConfig"]["database"]["connections"][$driver]["engine"];
	}
	public static function getConfig(&$config){
		self::fillConfig(self::$arrConfig, $config);
	}
	public static function isDebug()
	{
		self::$arrConfig["debug"];
	}
	public static function init(&$config)
	{
		configCore::setDbType($config["dbtype"]);
		configCore::setHost($config["host"]);
		configCore::setPrefix($config["prefix"]);
		configCore::setDatabase($config["database"]);

		configCore::setUser($config["user"]);
		configCore::setPass($config["password"]);
		configCore::setTimezone($config["timezone"]);
		configCore::setEnviroment($config["enviroment"]);
		configCore::setConfig("appname",$config["appname"]);
		configCore::setConfig("version",$config["version"]);
		configCore::setEngine($config["engine"]);
	}

	private static function fillConfig(&$objFrom, &$objTo)
	{
		if(is_array($objFrom)){
			foreach($objFrom as $key => $value){
				if(is_array($value)){
					self::fillConfig($value, $objTo[$key]);
				}
				else{
					$objTo[$key] = $value;
				}
			}
		}
	}

	public static function setConfig($key, $value)
	{
		self::$arrConfig[$key] = $value;
	}
	public static function createENV($conf)
	{
		$myfile = fopen(".env", "w") or die("Unable to open file!");
		$txt = "DEBUG={$conf["debug"]}
HML_DBTYPE='{$conf["dbtype"]}'
HML_DATABASE='{$conf["database"]}'
HML_HOST='{$conf["host"]}'
HML_PREFIX='{$conf["prefix"]}'
HML_USER='{$conf["user"]}'
HML_PASS='{$conf["password"]}'
HML_TIMEZONE='{$conf["timezone"]}'
HML_ENVIROMENT='{$conf["enviroment"]}'
HML_APPNAME='{$conf["appname"]}'
HML_VERSION_APP='{$conf["version"]}'
HML_DBENGINE='{$conf["engine"]}'
HML_LOCALREADY='OK'";
		fwrite($myfile, $txt);
		fclose($myfile);
	}
}