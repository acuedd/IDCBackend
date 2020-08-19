<?php
include_once("core/main.php");

$index_page = true;
$page_name = $lang["HOME_TITLE"];

	if (!$_SESSION["wt"]["logged"]) {
	    core_show_login_only("login.php");
	}
	else if ($cfg["core"]["SSO_identityP"] && isset($_REQUEST["SAMLRequest"]) && isset($_REQUEST["RelayState"])) {
		require_once("core/SSO/functions.php");
		sso_do_remote_login();
	}
	else {
	    header("location: index.php");
	}
?>