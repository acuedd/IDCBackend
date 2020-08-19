<?php
/**
 * Created by PhpStorm.
 * User: NelsonMatul
 * Date: 26/09/2017
 * Time: 14:57
 */

class user_roles_view extends global_config implements window_view
{

    private static $_instance;
    private $strAction = "";

    public function setStrAction($strAction)
    {
        $this->strAction = $strAction;
    }

    public static function getInstance($arrParams)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function __construct($arrParams)
    {
        parent::__construct($arrParams);
    }

    public function draw()
    {
        jquery_includeLibrary("organizedchart");
        draw_header($this->lang["USER_ROLES"]);
        ?>
            <style>
                /*===== ===== =====*/
                .loaderChangeRole {
                    color: #1c2637;
                    font-size: 20px;
                    margin: 100px auto;
                    width: 1px;
                    height: 1px;
                    border-radius: 50%;
                    position: relative;
                    text-indent: -9999em;
                    -webkit-animation: load4 1.3s infinite linear;
                    animation: load4 1.3s infinite linear;
                    -webkit-transform: translateZ(0);
                    -ms-transform: translateZ(0);
                    transform: translateZ(0);
                }
                @-webkit-keyframes load4 {
                    0%,
                    10% {
                        box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;
                    }
                    12.5% {
                        box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
                    }
                    25% {
                        box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
                    }
                    37.5% {
                        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;
                    }
                    50% {
                        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;
                    }
                    62.5% {
                        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;
                    }
                    75% {
                        box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;
                    }
                    87.5% {
                        box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;
                    }
                }
                @keyframes load4 {
                    0%,
                    100% {
                        box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;
                    }
                    12.5% {
                        box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
                    }
                    25% {
                        box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;
                    }
                    37.5% {
                        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;
                    }
                    50% {
                        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;
                    }
                    62.5% {
                        box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;
                    }
                    75% {
                        box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;
                    }
                    87.5% {
                        box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;
                    }
                }
                /*===== ===== =====*/
                #namelbl{
                    padding: 5px;
                }
                #namelbl:focus{
                    outline: none;
                }
                #colorPicker{
                    margin-bottom: 55px;
                }
                .titleModalEdit{
                    color: black;
                    font-weight: 600;
                }
                #newName{
                    width: 60%;
                }
                #cntLblCreateRols{
                    margin-top: 30px;
                }
                #cntLblCreateRols > div > label{
                    width: 60%;
                    margin-left: 20%;
                    border: 1px solid;
                    border-radius: 4px;
                }
                .inputAddRols{
                    width: 90%;
                    border-radius: 4px;
                    border: none;
                    height: 35px;
                }
                .google-visualization-orgchart-node-medium{
                    background: white;
                    border-radius: 4px;
                    padding:5px 15px;
                }
                .google-visualization-orgchart-node-medium:hover{
                    cursor: pointer;
                }
                .google-visualization-orgchart-node {
                    border: none;
                    -webkit-box-shadow: rgba(0, 0, 0, 0.2) 1px 1px 1px 1px;
                }
            </style>
            <div class="col-lg-6 col-lg-offset-3" id="cntLblCreateRols">
                <div class="form-group">
                    <!--<input class="inputAddRols" name="namelbl" id="namelbl" value="">-->
                    <button class="btn btn-primary" type="button" onclick="saveLabel('','',false,true)">
                        <i class="fa fa-plus"></i>
                        Agregar nuevo rol
                    </button>
                </div>
            </div>
            <div class="col-lg-12">
                <div id="chart_div"></div>
            </div>

            <div class="modal fade" id="mdlTags" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <form id="frmUpdateRol">
                                <div id="cntElmntCreate"></div>
                                <div id="cntElmntEdit">
                                    <h4 class="titleModalEdit">Defina el nuevo nombre</h4>
                                    <input name="newName" id="newName" class="form-control">
                                </div>
                                <h4 class="titleModalEdit">Defina un color</h4>
                                <div class="col-xs-offset-4">
                                    <div id="colorPicker" class="colorPicker_select">
                                        <div></div>
                                    </div>
                                </div>
                                <div class="form-group" align="center" id="cntBtnActionForm"></div>
                                <div class="form-group" align="center" id="cntAlertEditDelete"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        $this->scripts();
        jquery_includeLibrary("colorpicker");
    }

    public function scripts(){
        ?>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script type="text/javascript">
            var dw = new drawWidgets();
            var colorDefined = "";
            var arrSet = {};
            var objAllRoles = {};
            var objRolSelected = "";
            var intCountRolesByRelation = 0;
            var objRolesAux = {};

            $(document).ready(function(){
                google.charts.load('current', {packages:["orgchart"]});
                google.charts.setOnLoadCallback(getRolsData);

                $("#colorPicker").ColorPicker({
                    color: '#0000ff',
                    onShow: function (colpkr) {
                        $(colpkr).fadeIn(500);
                        return false;
                    },
                    onHide: function (colpkr) {
                        $(colpkr).fadeOut(500);
                        return false;
                    },
                    onChange: function (hsb, hex, rgb) {
                        $('#colorPicker div').css('backgroundColor', '#' + hex);
                        colorDefined = hex;
                    }
                });
            });

            function saveNewNameRol(boolNewRol){
                var params = {};
                if(boolNewRol){
                    params = {
                        "descr": $("#iNewName").val(),
                        "color": colorDefined,
                        "boolNew": true,
                        "name": $("#iUniqueName").val()
                    };
                }
                else{
                    var strBranch = objRolSelected.branch;
                    if(objRolSelected.branch == null){
                        strBranch = $("#iBranch").val();
                    }
                    params = {
                        "nameAfected": objRolSelected.name,
                        "newName": objRolSelected.descr,
                        "color": colorDefined,
                        "branch": strBranch,
                        "idBranch": objRolSelected.branchId,
                        "boolNew": false,
                        "roleAux": $("#iAuxRole").val()
                    };
                }
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=updateNameRol",
                    type: "POST",
                    data: params,
                    success: function(response){
                        if(response.status == "ok"){
                            $(".close").click();
                            getRolsData();
                        }
                        else{
                            return false;
                        }
                    }
                });
            }

            function deleteRol(){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=deleteRol",
                    type: "POST",
                    data: {
                        beforeName: objRolSelected.name,
                    },
                    success: function(){
                        getRolsData();
                        $(".close").click();
                    }
                });
            }

            function getUsersExistRolDelete(){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getUsersExistBeforeDelete",
                    type: "POST",
                    data: {
                        beforeName: objRolSelected.name
                    },
                    success: function(data){
                        if(data.status == "ok"){
                            var intResult = parseInt(data.total);
                            if(intResult > 0){
                                var divCnt = $("<div></div>");
                                $("#cntAlertEditDelete").html("");
                                $("#cntAlertEditDelete").append(divCnt);
                                var str = $("<p>El rol lo usan al menos "+ intResult +" usuario(s), ¿Desea continuar?</p>").attr("class","strAlertDelete");
                                divCnt.append(str);
                                var btnContinue = $("<button> Aceptar</button>").attr({
                                    "class":"btn btn-danger",
                                    "type": "button"
                                }).on("click",function(){
                                    deleteRol();
                                }); divCnt.append(btnContinue);
                                var btnDenied = $("<button> Cancelar</button>").attr({
                                    "class": "btn btn-default",
                                    "type": "button"
                                }).on("click",function(){
                                    $(".close").click();
                                }); divCnt.append(btnDenied)
                            }
                            else{
                                deleteRol();
                            }
                        }
                    }
                });
            }

            function removeFather(){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=removeFather",
                    type: "POST",
                    data: {
                        name: objRolSelected.name,
                    },
                    success: function(){
                        getRolsData();
                        $(".close").click();
                    }
                });
            }

            function editRolExist(element){
                intCountRolesByRelation = 0;
                var uniqueName = $(element).attr("id");
                $.each(objAllRoles,function(key,val){
                    if(uniqueName == key){
                        objRolSelected = val;
                        setNameBranch();
                    }
                });
            }

            function setNameBranch(){
                var nameRol = objRolSelected.name;
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=fatherBranch",
                    type: "POST",
                    datatype: "JSON",
                    data: {
                        uniqueName: nameRol
                    },
                    beforeSend: function (){
                        $('#colorPicker div').css('backgroundColor', 'white');
                    },
                    success: function(data){
                        $('#colorPicker div').css('backgroundColor', '#' + objRolSelected.color);
                        if(data.status == "ok"){
                            if(data.father != ""){
                                intCountRolesByRelation++;
                            }
                            if(data.childs != "0"){
                                intCountRolesByRelation++;
                            }
                            modalEditRol(data.father);
                        }
                    }
                })
            }

            function modalEditRol(strLeader){
                $("#cntAlertEditDelete").html("");
                var rolAfected = objRolSelected.name;
                $("#newName").val(objRolSelected.descr);
                $("#mdlTags").modal("show");
                var cntCreateRol = $("#cntElmntCreate");
                cntCreateRol.html("");
                $("#cntElmntEdit").removeClass("hide");
                var cntButtons = $("#cntBtnActionForm").html("");
                var btnUpdateRol = $("<button> Guardar</button>").attr({
                    "class": "btn btn-primary fa fa-floppy-o",
                    "type": "button",
                    "id": "btnUpdateRol",
                    "onclick": "saveNewNameRol(false)"
                }).css("margin","5px");
                var btnNewRol = $("<button> Guardar</button>").attr({
                    "class": "btn btn-primary fa fa-floppy-o",
                    "type": "button",
                    "id": "btnNewRol",
                    "onclick": "saveNewNameRol(true)"
                }).css("margin","5px");
                var btnDeleteRol = $("<button> Eliminar</button>").attr({
                    "class": "btn btn-danger fa fa-trash-o",
                    "type": "button",
                    "onclick": "getUsersExistRolDelete()"
                }).css("margin","5px");
                var btnRemoveFather = $("<button> Desligar</button>").attr({
                    "class": "btn btn-warning fa fa-scissors",
                    "type": "button",
                    "onclick": "removeFather()"
                });

                if(strLeader == ""){
                    var strTitleSuggestion = $("<h4>Defina nombre a la rama</h4>").attr("class","titleModalEdit"); cntCreateRol.append(strTitleSuggestion);
                    var inputUnique = $("<input>").attr({
                        "type": "text",
                        "class": "form-control",
                        "id": "iBranch",
                        "name": "iBranch"
                    }).css("width","60%"); cntCreateRol.append(inputUnique);
                    var inputUnique = $("<input>").attr({
                        "type": "text",
                        "class": "form-control hide",
                        "id": "intIdBranch",
                        "name": "intIdBranch",
                        "value": objRolSelected.branchId
                    }).css("width","60%"); cntCreateRol.append(inputUnique);
                    if(objRolSelected.branch){
                        $("#iBranch").val(objRolSelected.branch).attr("disabled","disabled");
                    }
                }

                if(intCountRolesByRelation > 0){
                    var strTitleSuggestion = $("<h4>Rol Auxiliar</h4>").attr("class","titleModalEdit"); cntCreateRol.append(strTitleSuggestion);
                    var select = $("<select></select>").attr({
                        "type": "text",
                        "class": "form-control",
                        "id": "iAuxRole",
                        "name": "iAuxRole"
                    }).css("width","60%"); cntCreateRol.append(select);
                    var option = $("<option></option>").attr({
                        "value": "0",
                        "id": "opt_0"
                    }); select.append(option);
                    $.each(objRolesAux,function(keyRolesAux, valRolesAux){
                        var option = $("<option>" + valRolesAux.descr + "</option>").attr({
                            "value": valRolesAux.name,
                            "id": "opt_" + valRolesAux.name
                        }); select.append(option);
                    });
                    select.on("change",function(){
                        objRolSelected.role_aux = $(this).val();
                    });

                    if(objRolSelected.roleAux !=  null){
                        $("#iAuxRole").val(objRolSelected.roleAux);
                    }
                    objRolSelected.role_aux = select.val();
                }

                var cntName = $("<div></div>"); cntCreateRol.append(cntName);
                var strTitleSuggestion = $("<h4>ID Rol</h4>").attr("class","titleModalEdit"); cntName.append(strTitleSuggestion);
                var inputUnique = $("<input>").attr({
                    "type": "text",
                    "class": "form-control",
                    "id": "iUniqueName",
                    "disabled": "disabled",
                    "name": "iUniqueName"
                }).css("width","60%");
                inputUnique.val(rolAfected);
                if(rolAfected == "" || rolAfected == undefined){
                    cntName.append(inputUnique);
                    $("#cntElmntEdit").addClass("hide");
                    var strTitle = $("<h4>Nombre del Rol</h4>").attr("class","titleModalEdit"); cntName.append(strTitle);
                    var inputTitle = $("<input>").attr({
                        "type": "text",
                        "class": "form-control",
                        "id": "iNewName",
                        "name": "iNewName"
                    }).css("width","60%"); cntName.append(inputTitle);
                    inputUnique.val();
                    inputTitle.val();
                    $('#colorPicker div').css('backgroundColor', 'white');
                    inputTitle.on("keyup",function(){
                        getSuggestion();
                    });
                    cntButtons.append(btnNewRol);
                }
                else{
                    cntName.append(inputUnique);
                    cntButtons.append(btnUpdateRol);
                    cntButtons.append(btnDeleteRol);
                    cntButtons.append(btnRemoveFather);
                }
            }

            function getSuggestion(){
                var xhr;
                if(xhr) xhr.abort();
                xhr = $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getSuggestion",
                    type: "POST",
                    datatype: "JSON",
                    data: {
                        rolName: $("#iNewName").val()
                    },
                    success: function(data){
                        if(data.status == "ok"){
                            $("#iUniqueName").val(data.uniqueRolName);
                        }
                    }
                });
            }

            function initChart(){
                var data = new google.visualization.DataTable();
                data.addColumn('string', 'Name');
                data.addColumn('string', 'Manager');
                data.addColumn('string', 'Description');
                //data.addColumn('string', 'ToolTip');
                return data;
            }

            var xhrRols;
            function getRolsData(){
                xhrRols = $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getRoles",
                    type: "POST",
                    beforeSend: function(){
                        if(xhrRols) xhrRols.abort();
                        //dw.openLoading();
                    },
                    success: function(response){
                        objRolesAux = {};
                        //dw.closeLoading();
                        var dataTable = initChart();
                        $.each(response.roles, function(keyRoles,valRoles){
                            if(valRoles.father == ""){
                                objRolesAux[keyRoles] = valRoles;
                            }
                        });
                        addRowChart(dataTable, response.roles);
                        xhrRols = null;
                    },
                    error: function(){
                        dw.alertDialog("Ocurrió un problema al obtener los roles");
                        //dw.closeLoading();
                    }
                });
            }

            var arrColorBack = {};

            function addRowChart(datatable, value){
                var valueRol = {};
                $.each(value,function(key_rol,val){
                    delete objRolesAux[val.father];

                    var key = key_rol;
                    var name = val.name;
                    var color = val.color;
                    var description = val.descr;
                    var father = val.father;
                    var branch = val.branch;
                    var branchId = val.branchId;
                    var roleAux = val.role_auxiliar;
                    objAllRoles[key] = {
                        name : name,
                        color : color,
                        descr: description,
                        father: father,
                        branch: branch,
                        branchId: branchId,
                        roleAux: roleAux
                    };

                    arrColorBack[name] = color;
                    valueRol = val;
                    datatable.addRows([
                        [val.name, val.father, val.descr]
                    ]);
                });
                var chart = new google.visualization.OrgChart(getDocumentLayer('chart_div'));
                chart.draw(datatable, {allowHtml:true});
                addProperties();
            }

            function addProperties(){
                $(".google-visualization-orgchart-node-medium").each(function(key,val){
                    var label = $(this).html();
                    var strIdentify = $(this).attr("title");
                    $(this).attr({
                        "draggable":true,
                        "ondragstart":"drag(event,this)",
                        "ondrop":"drop(this,event)",
                        "ondragover":"allowDrop(event)",
                        "ondblclick": "editRolExist(this)",
                        "id": label
                    });
                    $(this).html(strIdentify);
                    if(arrColorBack[label]){
                        $(this).css({
                            "background":"#"+arrColorBack[label],
                            "color": "white"
                        });
                    }
                });
            }

            function saveLabel(strChild, strFather, boolChange, boolNewRol){
                intCountRolesByRelation = 0;
                if(boolChange != true){
                    if(boolNewRol){
                        objRolSelected = "";
                    }
                    modalEditRol("");
                }
                else{
                    sendRoles(strChild, strFather, boolChange);
                }
            }

            function sendRoles( strChild, strFather, boolChange){
                if(!(strFather)) strFather = false;
                var params = {
                    "child": strChild,
                    "newFather": (!strFather)?"":strFather,
                    "boolChange": boolChange
                };
                var loader = $("<div></div>").attr("class","loaderChangeRole").css({
                    "z-index": "2",
                    "margin-top": "-50px"
                });
                $("#chart_div").append(loader);
                $.ajax({
                    url:"<?php print $this->strAction; ?>&op=updateRoles",
                    type:"POST",
                    data: params,
                    success: function(data){
                        if(data.status == "ok"){
                            getRolsData();
                        }
                        else{
                            dw.alertDialog(data.msj,"Mensaje del Sistema",true);
                        }

                    },
                    error: function(){
                        dw.alertDialog("Desligue el rol para poder asignarlo.");
                    }
                });
            }

            function allowDrop(ev){
                ev.preventDefault();
            }

            var elementChild = "";
            function drag(ev,element){
                ev.dataTransfer.setData("text", ev.target.id);
                var thisElement = $(element).attr("id");
                elementChild = thisElement;
            }

            function drop(element,ev){
                ev.preventDefault();
                var thisElement = $(element).attr("id");
                saveLabel(elementChild,thisElement,true,false);
            }

        </script>
        <?php
    }
}