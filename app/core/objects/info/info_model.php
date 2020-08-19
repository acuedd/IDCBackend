<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 30/07/18
 * Time: 16:07
 */
include_once "core/global_config.php";

class info_model extends global_config implements window_model{

	private static $_instance;
	public function __construct($arrParams)
	{
		parent::__construct($arrParams);
	}
	public static function getInstance($arrParams)
	{
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($arrParams);
		}
		return self::$_instance;
	}
}