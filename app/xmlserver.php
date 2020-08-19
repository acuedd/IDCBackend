<?php
// Este archivo recibe y devuelve el XML de y para Flash.
include_once("core/miniMain.php");
include_once("core/functions_core.php");
include_once("core/xmlfunctions.php");

	$XMLString = "";
	if (count($_POST)) {
		$XMLString = getXMLPostFromFlash();
		if (!empty($XMLString) && function_exists("theme_xml_interpreter")) {
			$XMLFromFlash = new XMLObject($XMLString);
			$XMLToFlash = theme_xml_interpreter($XMLFromFlash);
			if ($XMLToFlash) {
				header("Content-Type: text/xml");
				print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
	        	print $XMLToFlash->toString();
			}
		}
	}
?>