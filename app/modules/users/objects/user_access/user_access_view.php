<?php

/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 07/06/17
 * Time: 09:28
 */
class user_access_view extends global_config implements window_view
{
	private static $_instance;
	private $strAction;
	private $intUid = 0;
	private $strUserName = "";
	private $arrModules = array();
	private $arrSortedFields = array();
	private $arrProfilePerUser = array();

	public static function getInstance($arrParams){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self($arrParams);
		}
		return self::$_instance;
	}

	public function __construct($arrParams)
	{
		parent::__construct($arrParams);
	}

	/**
	 * @param mixed $intUid
	 */
	public function setIntUid($intUid)
	{
		$this->intUid = $intUid;
	}

	/**
	 * @param mixed $arrModules
	 */
	public function setArrModules($arrModules)
	{
		$this->arrModules = $arrModules;
	}

	/**
	 * @param mixed $arrSortedFields
	 */
	public function setArrSortedFields($arrSortedFields)
	{
		$this->arrSortedFields = $arrSortedFields;
	}

	public function setStrAction($strAction)
	{
		$this->strAction = $strAction;
	}

	/**
	 * @param array $arrProfilePerUser
	 */
	public function setArrProfilePerUser($arrProfilePerUser)
	{
		$this->arrProfilePerUser = $arrProfilePerUser;
	}

	/**
	 * @param string $strUserName
	 */
	public function setStrUserName($strUserName)
	{
		$this->strUserName = $strUserName;
	}

	public function draw()
	{
		draw_header($this->lang["ADM_USERACCESS"]);
		theme_draw_centerbox_open($this->lang["ADM_USERACCESS"]);
        global_function::clearBrowserCache();
		$this->scripts();
		$this->getReport();

		if($this->intUid!=0){
			?>
            <div class="row">
                <div class="col-lg-12">
                    <form name="frmAccess" method="post" action="<?php print $this->strAction; ?>" >
                        <input type="hidden" name="frmAccess_save" value="1">
                        <input type="hidden" name="intuid" value="<?php print $this->intUid; ?>">
                        <div class="bs-callout bs-callout-info" id="callout-helper-context-color-specificity">
                            <h4><?php print $this->strUserName; ?></h4>
                        </div>
                        <table width="100%" cellspacing="0" cellpadding="2" border="0">
                            <?php
                            $arrValidProfiles = core_getValidAccessProfiles();
                            if (check_user_class("accesos/profiles/asign") && is_array($arrValidProfiles) && count($arrValidProfiles)) {
                                ?>

                                <?php
                            }
                            $intModuleCounter = 0;
                            foreach($this->arrSortedFields AS $key => $value){
                                $arrModule["key"] = $key;
                                $arrModule["value"] = $value;
                                $arrModuleDefaultStatus[$intModuleCounter] = false;
                                ?>
                                <tr style="cursor:pointer;" onclick="module_Toggle(<?php print $intModuleCounter;?>);">
                                    <td class="rowgroup">
                                        <input type="checkbox" class="field_checkbox" name="chk_<?php print $intModuleCounter;?>"
                                               onclick="module_checked(<?php print $intModuleCounter;?>, this);">
                                        <?php htmlSafePrint($arrModule["key"]);?>
                                    </td>
                                    <td class="rowgroup" width="1%" id="indicator_<?php print $intModuleCounter;?>">+</td>
                                </tr>
                                <tr>
                                    <td colspan="2" id="tbl_<?php print $intModuleCounter;?>" style="display:none;">
                                        <table width="100%" cellspacing="0" cellpadding="2" border="0" class="table table-striped">
                                            <?php
                                            $intCellCounter = 1;
                                            foreach($arrModule["value"] AS $arrAccess["key"] => $arrAccess["value"] ){
                                                ?>
                                                <tr>
                                                    <td id="cell_<?php print $intModuleCounter;?>_<?php print $intCellCounter;?>" width="5%" valign="middle" nowrap>
                                                        <input type="checkbox" value="<?php print $this->checkParam("className",$arrAccess["value"]); ?>"
                                                               name="chk_access_<?php print $intModuleCounter; ?>_<?php print $intCellCounter; ?>"
                                                                <?php print ($this->checkParam($this->checkParam("className",$arrAccess["value"]), $this->arrModules))?"checked":"" ?>/>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        foreach($arrAccess["value"]["Links"] AS $value2){
                                                            $arrLink["value"] = $value;
                                                            ?>
                                                            <li><?php print $arrLink["value"];?></li>
                                                            <?php
                                                        }
                                                        ?>
                                                    </td>
                                                </tr>
                                                <?php
                                                $intCellCounter++;
                                            }
                                            ?>
                                        </table>
                                    </td>
                                </tr>
                                <?php
                                $intModuleCounter++;
                            }
                            reset($this->arrSortedFields);
                            ?>
                            <tr>
                                <td colspan="2" align="center">
                                    <button type="submit" class="btn btn-info">
                                        <i class="fa fa-search" aria-hidden="true"></i>
                                        <?php print $this->lang["SAVE"]; ?>
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>
			<?php
		}

		theme_draw_centerbox_close();
		draw_footer();
	}

	public function scripts(){
	    ?>
        <style type="text/css">
            .bs-callout {
                padding: 20px;
                margin: 20px 0;
                border: 1px solid #eee;
                border-left-width: 5px;
                border-radius: 3px;
            }
            .bs-callout-info {
                border-left-color: #1b809e;
            }
        </style>
		<script language="Javascript" type="text/javascript">
            function module_checked(intModuleID, objMainBox) {
                var boolExists = true;
                var intCell = 1;
                var strLookFor = "";
                var objCheckBox = false;
                var strCheckBoxName = "";
                var intTMP = 0;

                while (boolExists) {
                    strLookFor = "cell_" + intModuleID + "_" + intCell;
                    var objCell = $("#"+strLookFor);

                    if (objCell.length > 0) {
                        boolExists = true;
                        objCheckBox = objCell.find("input:checkbox");

                        strCheckBoxName = objCheckBox.attr("name");
                        intTMP = strCheckBoxName.indexOf("homeland", 0);
                        if (intTMP == -1) {
                            objCheckBox.prop("checked",objMainBox.checked);
                        }
                        else {
                            objCheckBox.is(":checked",false);
                        }
                    }
                    else {
                        boolExists = false;
                    }
                    intCell++;
                }

                if (objMainBox.checked) {
                    module_ShowAndHide(intModuleID, false);
                }
            }

            function module_ShowAndHide(intModuleID, boolShow) {
                var objTable = getDocumentLayer("tbl_" + intModuleID);
                var objIndicator = getDocumentLayer("indicator_" + intModuleID);

                if (boolShow) {
                    objTable.boolShown = true;
                    objTable.style.display = "";
                    objIndicator.innerHTML = "-";
                }
                else {
                    objTable.boolShown = false;
                    objTable.style.display = "none";
                    objIndicator.innerHTML = "+";
                }
            }

            function module_Toggle(intModuleID) {
                var objTable = getDocumentLayer("tbl_" + intModuleID);
                var objIndicator = getDocumentLayer("indicator_" + intModuleID);

                if (objTable.boolShown) {
                    module_ShowAndHide(intModuleID, false);
                }
                else {
                    module_ShowAndHide(intModuleID, true);
                }
            }

        </script>
        <?php
    }

	public function getReport(){
		?>
		<div id="mdlUsers" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						Busqueda
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button>
					</div>
					<div class="modal-body"></div>
					<div class="modal-footer"></div>
				</div>
			</div>
		</div>
		<div class="col-lg-12 text-center">
			<button type="button" class="btn btn-warning" onclick="getUsers();">
				<i class="fa fa-search" aria-hidden="true"></i>
				<?php print $this->lang["ADM_USERACCESS_VIEW"]; ?>
			</button>
		</div>
		<script>
            function getUsers() {
                var objDw = new drawWidgets();
                $.ajax({
                    url : "<?php print $this->strAction ?>&boolReport=true",
                    type : "GET",
                    dataType : "HTML",
                    beforeSend: function(){
                        objDw.openLoading();
                        $("#mdlUsers .modal-body").html("");
                    },
                    success: function(data){
                        objDw.closeLoading();
                        $("#mdlUsers").find(".modal-body").html(data);
                        $("#mdlUsers").modal("show");
                    },
                    error: function(){
                        objDw.closeLoading();
                        objDw.alertDialog("Hubo un problema con la comunicación, intente de nuevo")
                    }
                });
            }

            function setUser(uid,name,lastname){
                $("#mdlUsers").modal("hide");
				location.href = '<?php print $this->strAction; ?>&intuid='+uid+"&name="+name+"&lastname="+lastname;
            }
		</script>
		<?php
	}
}