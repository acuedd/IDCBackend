<?php
include_once("core/main.php");
$index_page = false;

$strModule = "";
$boolFulAdmin = false;
$boolFulNormal = false;

if (isset($_GET["module"])){
    $strModule = $_GET["module"];
}
elseif (isset($_GET["full_admin"])) {
	$strModule = $lang["ADM_MENU_ADMIN"];
	$boolFulAdmin = true;
}
elseif (isset($_GET["full_normal"])) {
	$strModule = $lang["ADM_MENU"];
	$boolFulNormal = true;
}
$page_name = $strModule;

draw_header();

	if ($boolFulNormal) {
		$arr_menu = $config["menu"];
	    ksort($arr_menu, SORT_NUMERIC);
	    reset($arr_menu);

	    $arrDrawThisArray = array();
	    $intMaxLen = 0;
	    while($entry = each( $arr_menu )){
	        if (isset($entry["value"]["moduleID"])) {
	            if (!check_module($entry["value"]["moduleID"], false, $entry["value"]["type"])) {
	            	continue;
	            }
	            else {
	            	if(isset($entry["value"]["type"]) && $entry["value"]["type"] != "A") {
		                if($entry["value"]["type"] == "L") {
		                    if( !$_SESSION["wt"]["logged"] ) continue;
		                } else if(  $entry["value"]["type"] == "N" ) {
		                    if( $_SESSION["wt"]["logged"] ) continue;
		                } else {
		                    continue;
		                }
		            }
	            }
	        }
	        else {
	            if($entry["value"]["type"] != "A") {
	                if($entry["value"]["type"] == "L") {
	                    if( !$_SESSION["wt"]["logged"] ) continue;
	                } else if(  $entry["value"]["type"] == "N" ) {
	                    if( $_SESSION["wt"]["logged"] ) continue;
	                } else {
	                    continue;
	                }
	            }
	        }
	        $arrDrawThisArray[$entry["key"]] = $entry["value"];
	        if (strlen($entry["value"]["title"]) > $intMaxLen) $intMaxLen = strlen($entry["value"]["title"]);
	    }

	    if (count($arrDrawThisArray)) {
	    	theme_draw_centerbox_open($page_name);
	    	?>
		    <table border="0" cellspacing="0" cellpadding="3" width="100%">
			    <?php
			    $strClass = "row1";
			    while($entry = each($arrDrawThisArray)){
	                if (isset($entry["value"]["title"])) {
	                    ?>
	                    <tr>
			                <td align="left" class="<?php print $strClass;?>" colspan="2">
			                    <a href="<?php print $entry["value"]["file"];?>"><?php print $entry["value"]["title"];?></a>
			                </td>
			            </tr>
	                    <?php
	                }
	                // subitems solo por si los trae, es mas un soporte para el futuro
	                if (isset($entry["value"]["subitems"])) {
	                    reset($entry["value"]["subitems"]);
	                    while ($subEntry = each($entry["value"]["subitems"])) {
	                        if (!($subEntry["key"]==="type")) {
	                            print theme_draw_menu_item($subEntry["value"]["title"], $subEntry["value"]["file"], 15);
	                            ?>
	                            <tr>
				    				<td align="left" class="<?php print $strClass;?>">&nbsp;</td>
					                <td align="left" class="<?php print $strClass;?>">
					                    <a href="<?php print $subEntry["value"]["file"];?>"><?php print $subEntry["value"]["title"];?></a>
					                </td>
					            </tr>
	                            <?php
	                        }
	                    }
	                }
	                $strClass = ($strClass=="row1")?"row2":"row1";
	            }
			    ?>
		    </table>
		    <?php
            theme_draw_centerbox_close();
        }
	}
	else {
        $arrModules = array();

        if (!isset($_SESSION["wt"]["admin_menu_draw"])) $_SESSION["wt"]["admin_menu_draw"] = prepare_admin_menu_data();

		if ($boolFulAdmin) {
			$arrModules = $_SESSION["wt"]["admin_menu_draw"];
		}
		else {
			$arrModules = array();
			if ($strModule !="" && isset($_SESSION["wt"]["admin_menu_draw"][$strModule])) {
                ksort($_SESSION["wt"]["admin_menu_draw"][$strModule]);
				$arrModules[$strModule] = $_SESSION["wt"]["admin_menu_draw"][$strModule];
			}
		}
		$strFunction = "admin_menu_" . str_replace(array(" ", "-", "/"), "", $strModule);
		if (function_exists($strFunction)) {
			$strFunction($arrModules);
		}
		else {
            $strFunction = "admin_menu_draw_{$cfg["core"]["theme"]}";
            if(function_exists($strFunction)){
                $strFunction($arrModules);                
            }
            else{
                while ($arrThis = each($arrModules)) {
                    $arrLinks = array();
                    theme_draw_centerbox_open($arrThis["key"]);
                    ?>
                    <table border="0" cellspacing="0" cellpadding="3" width="100%">
                        <?php
                        if (isset($arrThis["value"]["groups"])){
                            while ($arrItem = each($arrThis["value"]['groups'])) {
                                $group = $arrItem["value"];
                                if (count($group['elements'])) {
                                    ?>
                                    <tr>
                                        <td colspan="2" class="rowgroup"><?php print $group['name'];?></td>
                                    </tr>
                                    <?php
                                    $strClass = "row1";
                                    while ($arrElement = each($group['elements'])) {
                                        $element = $arrElement["value"];
                                        if (isset($arrLinks[$element["file"]])) continue;
                                        ?>
                                        <tr>
                                            <td align="left" class="<?php print $strClass;?>">&nbsp;</td>
                                            <td align="left" class="<?php print $strClass;?>">
                                                <a href="<?php print $element["file"];?>"><?php print $element["name"];?></a>
                                            </td>
                                        </tr>
                                        <?php
                                        $strClass = ($strClass == "row1")?"row2":"row1";
                                        $arrLinks[$element["file"]] = true;
                                    }
                                }
                            }
                        }
                        else {
                            $strClass = "row1";
                            while ($entry = each($arrThis["value"])){
                                if (!isset($arrLinks[$entry["value"]])){
                                    ?>
                                    <tr>
                                        <td align="left" class="<?php print $strClass;?>" colspan="2">
                                            <a href="<?php print $entry["value"];?>"><?php print $entry["key"];?></a>
                                        </td>
                                    </tr>
                                    <?php
                                    $strClass = ($strClass == "row1")?"row2":"row1";
                                    $arrLinks[$entry["value"]] = true;
                                }
                            }
                        }
                        ?>
                    </table>
                    <?php
                    theme_draw_centerbox_close();
                }   
            }			
		}
	}

draw_footer();