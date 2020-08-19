<?php
/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 7/02/2017
 * Time: 15:23
 */

use Symfony\Component\HttpFoundation\Request;

require_once("core/main.php");
require_once 'core/global_config.php';
$request = Request::createFromGlobals();
$module = $request->query->get("mde") ?? "";
$ventana = $request->query->get("wdw") ?? "";

if(!empty($module) && !empty($ventana)){

	$file = $request->query->get("file") ?? "";
	$method = $request->query->get("mth") ?? "";
	$view = $request->query->get("view") ?? "";

	$boolModule = check_module("{$module}");
	if($boolModule) {
		$strGet = "";
		if(!empty($view)){
			$strGet = "&view={$view}";
		}
		$include = ($module == "core") ? "" : "modules/";
		if (!empty($file)) {
			$path = "{$include}{$module}/objects/{$file}.php";
			if (file_exists($path)) {
				$action = "adm_main.php?file={$file}&mde={$module}&wdw={$ventana}{$strGet}";
				include_once($path);
				die;
			}
		} else {
			if (file_exists("{$include}{$module}/objects/{$ventana}/{$ventana}_controller.php")) {
				include_once("{$include}{$module}/objects/{$ventana}/{$ventana}_controller.php");

				$strClass = "{$ventana}_controller";
				if (class_exists($strClass)) {
					$action = "adm_main.php?mde={$module}&wdw={$ventana}{$strGet}";
					if(!empty($method))
						$action .= "&mth={$method}";

					$arrParamsG = $request->query->all();
                    $arrParamsP = $request->request->all();
                    $arrParams = array_merge($arrParamsG, $arrParamsP);
					$objC = new $strClass($arrParams);
					$objC->setStrAction($action);
					if(!empty($method)){
						if(method_exists($objC,$method)){
							$objC->$method();
							die;
						}
					}
					else{
						$objC->main();
						die;
					}
				}
			}
		}
	}
	else{
		Kint::dump("modulo no cargado");
	}
}
else if($request->get("tool")){
	if (strstr($_SERVER["PHP_SELF"], "/tools/"))  die ("You can't access this file directly...");
	$tool = $request->query->get("tool") ?? "";
	$module = $request->query->get("mde") ?? "";

	if(!empty($module)){
		$boolModule = check_module("{$module}");
		if(!$boolModule){
			Kint::dump("modulo no cargado");
		}
	}
	$path = "tools/_{$tool}.php";
	if(file_exists($path)){
		include_once ($path);
		die();
	}
}
include_once("404.shtml");