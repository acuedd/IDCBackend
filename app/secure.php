<?php
include_once("core/main.php");
global $lang;
$index_page = true;
$page_name = $lang["HOME_TITLE"];

	if (isset($_GET["link"]) && isset($_GET["vars"])) {
		$strLink = user_input_delmagic($_GET["link"]);
		$strLink = str_replace(array("*", "|"), array("?", "&"), $strLink);
		$strVars = user_input_delmagic($_GET["vars"]);
		$strVars = str_replace(array("~","|"), array("&","="), $strVars);
		
		if (!$_SESSION["wt"]["logged"]) {
	   		core_show_login_only($strLink, $strVars);
		}
		else{
			$arrPost = (isset($_GET["post"]) && !empty($_GET["post"]))?unserialize(user_input_delmagic($_GET["post"])):array();
			if (count($arrPost)) {
				$strTarget = (empty($strVars))?$strLink:"{$strLink}?{$strVars}";
				?>
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
			    <html>
			    <?php draw_header_tag(); ?>
			    <body style="background-color:white;" id="PageBody" tabindex="-1">
			    <form method="POST" id="frmAutoPost" name="frmAutoPost" action="<?php print $strTarget;?>">
			    	<?php 
			    	while ($arrItem = each($arrPost)) {
			    		?>
			    		<input type="hidden" name="<?php print $arrItem["key"];?>" value="<?php htmlSafePrint($arrItem["value"]);?>">
			    		<?php 
			    	}
			    	?>
			    </form>
			    </body>
			    <script language="Javascript" type="text/javascript" for="window" event="onload">
			    	document.frmAutoPost.submit();
				</script>
			    </html>
				<?php
			}
			else {
				if (!empty($strVars)) {
			    	header("Location: ./{$strLink}?{$strVars}");
				}
				else {
					header("Location: ./{$strLink}");
				}
			}
		}
	}
	else {
		header("Location: ./index.php");
	}
?>