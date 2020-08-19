<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 8/02/2017
 * Time: 09:06
 */
include_once("core/global_config.php");
class myaccount_view extends global_config implements window_view {
    private static $_instance;
    private $strAction;
    private $intUid = 0;
    private $infoUser = array();
    private $arrPaises = array();
    private $arrDevices = array();

    public function __construct($arrParams){
        parent::__construct($arrParams);
        $this->intUid = $_SESSION["wt"]["uid"];
    }

    public function setIntUid($intUid){
        $this->intUid = $intUid;
    }

    public function setArrPaises($arrPaises){
        $this->arrPaises = $arrPaises;
    }

    public function setArrDevices($arrDevices){
        $this->arrDevices = $arrDevices;
    }

    public function setInfoUser($infoUser){
        $this->infoUser = $infoUser;
    }

    public static function getInstance($arrParams){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }
    public function setStrAction($strAction){
        $this->strAction = $strAction;
    }

    public function draw(){
        draw_header($this->lang["MYACCT_TITLE"]);
        $strName = (isset($_SESSION['wt']['name']))?$_SESSION['wt']['name']:"";
        theme_draw_centerbox_open($this->lang["MYACCT_TITLE"]);
        $this->headerScripts();
        ?>
        <style type="text/css">
            .strUserAux{
                border: 2px solid rgb(199, 199, 199);
                border-radius: 4px;
                padding-bottom: 10px;
            }
            #chart_div{
                overflow: auto;
            }
            .cntChart{
                margin-top: 85px;
            }
            #cntTitleMyGroup{
                padding: 0 10px;
            }
            .btn-width{
                min-width: 100px;
            }
            input[type="search"]{
                padding: .3em .6em;
                outline: none;
                border-radius: 22px;
                border: 1px solid rgba(0,0,0, .3);
            }
            .pagination>.active>a{
                border: 1px solid rgba(0,0,0, .3) !important;
                color: #000;
                background: transparent !important;
            }
            .imgUserLogin{
                width: 100%;
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
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 custom-center bhoechie-tab-container">
            <div class="col-lg-2 col-md-2 col-sm-3 col-xs-12 bhoechie-tab-menu">
                <div class="list-group">
                    <a href="#" class="list-group-item active text-center">
                        <span class="menu-content">
                            <i class="fa fa-address-card-o fa-3x" aria-hidden="true"></i>
                            <span class="">General</span>
                        </span>
                    </a>
                    <?php
                    if($this->cfg["core"]["allow_webservice_devices"]){
                    ?>
                    <a href="#" class="list-group-item text-center">
                        <span class="menu-content">
                            <i class="fa fa-mobile fa-3x" aria-hidden="true"></i>
                            <span class="">Mis dispositivos móviles</span>
                        </span>
                    </a>
                        <?php
                    }
                    ?>
                    <a href="#" class="list-group-item text-center">
                        <span class="menu-content">
                            <i class="fa fa-file-image-o fa-3x" aria-hidden="true"></i>
                            <span class="">Fotografía personal</span>
                        </span>
                    </a>
                    <a href="#" class="list-group-item text-center">
                        <span class="menu-content">
                            <i class="fa fa-key fa-3x" aria-hidden="true"></i>
                            <span class="">Cambiar contraseña</span>
                        </span>
                    </a>
                    <a href="#" class="list-group-item text-center">
                        <span class="menu-content">
                            <i class="fa fa-users fa-3x" aria-hidden="true"></i>
                            <span class="">Mi Grupo</span>
                        </span>
                    </a>
                </div>
            </div>
            <div class="col-lg-10 col-md-10 col-sm-9 col-xs-12 bhoechie-tab">
                <!-- general section -->
                <div class="bhoechie-tab-content active">
                    <form id="frmGeneral">
                        <div class="col-sm-12">
                            <?php
                            if(function_exists('getUserMerchanting')){
                                print getUserMerchanting();
                            }
                            ?>
                        </div>
                        <div class="form-group custom-form-group col-lg-6">
                            <input type="hidden" name="uid" value="<?php print $this->intUid; ?>">
                            <label for="iNombres">Nombres</label>
                            <input type="text" class="form-control" required id="iNombres" name="iNombres">
                        </div>
                        <div class="form-group custom-form-group col-lg-6">
                            <label for="iApellidos">Apellidos</label>
                            <input type="text" class="form-control" required id="iApellidos" name="iApellidos">
                        </div>
                        <div class="form-group custom-form-group col-lg-6">
                            <label for="iUsual">Nombre usual</label>
                            <input type="text" class="form-control" required id="iUsual" name="iUsual">
                        </div>
                        <div class="form-group custom-form-group col-lg-6">
                            <label for="iCorreo">Correo</label>
                            <input type="email" class="form-control" required id="iCorreo" name="iCorreo" data-toggle="tooltip" data-placement="top" title="Cuando olvide o pierda su contraseña se estara enviando a este correo">
                        </div>
                        <div class="form-group custom-form-group col-lg-6">
                            <label for="sSexo">Sexo</label>
                            <select class="form-control" required name="sSexo" id="sSexo">
                                <option value="Male">Masculino</option>
                                <option value="Female">Femenino</option>
                            </select>
                        </div>
                        <div class="form-group custom-form-group col-lg-6">
                            <label for="sPais">País</label>
                            <select class="form-control" name="sPais" id="sPais">
                                <?php
                                foreach($this->arrPaises AS $pais){
                                    $selected = "";
                                    if($pais["default"] == "Y")$selected = "selected";
                                    ?>
                                    <option value="<?php print $pais["nombre"]; ?>" <?php print $selected; ?>><?php print $pais["nombre"]; ?></option>
                                    <?php
                                    unset($pais);
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group custom-form-group col-lg-6">
                            <label for="iCelular">Celular</label>
                            <input type="text" class="form-control" required id="iCelular" name="iCelular">
                        </div>
                        <div class="form-group custom-form-group col-lg-12 text-center">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-floppy-o" aria-hidden="true"></i>&nbsp;Guardar
                            </button>
                        </div>
                    </form>
                </div>
                <?php
                if($this->cfg["core"]["allow_webservice_devices"]){
                ?>
                <!-- dispositivos section -->
                <div class="bhoechie-tab-content">
                    <div>
                        <table id="table_device" class="table table-striped table-bordered nowrap" style="width:100%">
                            <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>Alias</th>
                                <th>Teléfono</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Ultima fecha y hora</th>
                                <th>Opciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach($this->arrDevices as $device){
                                ?>
                                <tr>
                                    <td>
                                        <div class="sk-circle hide" id="spinner_<?php print $device["id"]; ?>">
                                            <div class="sk-circle1 sk-child"></div>
                                            <div class="sk-circle2 sk-child"></div>
                                            <div class="sk-circle3 sk-child"></div>
                                            <div class="sk-circle4 sk-child"></div>
                                            <div class="sk-circle5 sk-child"></div>
                                            <div class="sk-circle6 sk-child"></div>
                                            <div class="sk-circle7 sk-child"></div>
                                            <div class="sk-circle8 sk-child"></div>
                                            <div class="sk-circle9 sk-child"></div>
                                            <div class="sk-circle10 sk-child"></div>
                                            <div class="sk-circle11 sk-child"></div>
                                            <div class="sk-circle12 sk-child"></div>
                                        </div>
                                        <i class="fa fa-check fa-2x text-success hide" aria-hidden="true" id="check_<?php print $device["id"]; ?>"></i>
                                        <i class="fa fa-exclamation fa-2x text-danger hide" aria-hidden="true" id="error_<?php print $device["id"]; ?>"></i>
                                    </td>
                                    <td>
                                        <div>
                                            <input type="text" class="form-control" value="<?php print $device["nombre_p"]; ?>" column="nombre_p"
                                                   onchange="save_device('<?php print $device["id"]; ?>',this)">
                                        </div>
                                    </td>
                                    <td><?php print $device["telefono"]; ?></td>
                                    <td><?php print $device["marca"]; ?></td>
                                    <td><?php print $device["modelo"]; ?></td>
                                    <td><?php print $device["last_use"]; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-width <?php print ($device["activo"] == "Y")?"":"hide"; ?>" value="N" column="activo"
                                                onclick="save_device('<?php print $device["id"]; ?>',this)">Desactivar</button>
                                        <button type="button" class="btn btn-primary btn-width <?php print ($device["activo"] == "Y")?"hide":""; ?>" value="Y" column="activo"
                                                onclick="save_device('<?php print $device["id"]; ?>',this)">Activar</button>
                                        <button type="button" class="btn btn-danger btn-width" value="Y" column="eliminado"
                                                onclick="save_device('<?php print $device["id"]; ?>',this)">Eliminar</button>

                                    </td>
                                </tr>
                                <?php
                                unset($device);
                            }
                            ?>
                            </tbody>
                            <tfoot>

                            </tfoot>
                        </table>
                    </div>
                </div>
                <?php
                }
                ?>
                <!-- fotografia section -->
                <div class="bhoechie-tab-content">
                    <form id="frmAvatar">
                        <div class="form-group custom-form-group">
                            <label for="iImage">Imagen</label>
                            <input type="file" id="iImage" name="iImage">
                            <p class="help-block">Subir archivo jpg ó gif ó png (Tamaño máximo 2MB)</p>
                        </div>
                        <div class="form-group custom-form-group text-center select-avatar">
                            <img src="<?php print $this->strAction; ?>&op=avatar&uid=<?php print $this->intUid; ?>" alt="Imagen de perfil" class="img-thumbnail" id="img-user">
                        </div>
                        <div class="form-group custom-form-group col-lg-12 text-center">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-floppy-o" aria-hidden="true"></i>&nbsp;Guardar
                            </button>
                        </div>
                    </form>
                </div>
                <!-- contraseña section -->
                <div class="bhoechie-tab-content">
                    <form>
                        <div class="col-lg-6 col-md-offset-3 text-center fade" id="alertPass">
                            <p class="bg-danger">
                                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                Las contraseñas no coiciden
                            </p>
                        </div>
                        <div class="form-group custom-form-group col-lg-6">
                            <label for="iPass">Contraseña</label>
                            <input type="password" class="form-control" id="iPass" name="iPass">
                        </div>
                        <div class="form-group custom-form-group col-lg-6">
                            <label for="iPassConfirm">Confirme contraseña</label>
                            <input type="password" class="form-control" id="iPassConfirm" name="iPassConfirm">
                        </div>
                        <div class="form-group custom-form-group col-lg-12 text-center">
                            <button type="button" class="btn btn-success disabled" id="btnPass" disabled onclick="save_pass();">
                                <i class="fa fa-floppy-o" aria-hidden="true"></i>&nbsp;Guardar
                            </button>
                        </div>
                    </form>
                </div>
                <!-- my family section -->
                <div class="bhoechie-tab-content">
                    <form>
                        <div class="bs-callout bs-callout-info" id="cntTitleMyGroup">
                            <h4 id="strTitleMyFamily"></h4>
                        </div>
                        <div class="col-lg-6 col-md-offset-3 text-center fade" id="alertAssignFamily">
                            <p class="bg-danger">
                                <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                El usuario ya se encuentra asignado a otra familia
                            </p>
                        </div>
                        <div class="form-group custom-form-group col-lg-12" id="cntMenuRolesAdmFamily"></div>
                    </form>
                    <div id="cntUserAuxFather"></div>
                    <div class="col-lg-12 cntChart">
                        <h3 align="center">Mi Grupo</h3>
                        <div id="chart_div"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
	    //debug::drawdebug($this->infoUser);
	    //debug::drawdebug(json_encode($this->infoUser));
        ?>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
            let wd = new drawWidgets();
            let roleUser = null;
            let objRoleAux = null;
            let objRolesChild = null;
            let boolExitChild = true;
            let boolExitRolAux = true;
            let objCntAllRoles = {};
            let elementContentSearch = null;
            let userID = null;
            let strRoleAux = "";
            let objDataRoleAuxExist = {};
            /*let arrColores = [
                '#A9CCE3', '#bfab71', '#6093ad', '#3a5468', '#545659','#39c2a8', '#512E5F',
                '#A9CCE3', '#bfab71', '#6093ad', '#3a5468', '#545659','#39c2a8', '#512E5F',
                '#A9CCE3', '#bfab71', '#6093ad', '#3a5468', '#545659','#39c2a8', '#512E5F'];*/
            let boolAuxRol = false;
            $(document).ready(function() {
                $("#iPass").keyup(verifyPass);
                $("#iPassConfirm").keyup(verifyPass);

                $("#iImage").on("change",function(){
                    loadImg(this,"img-user");
                });

                $('[data-toggle="tooltip"]').tooltip();
                $("div.bhoechie-tab-menu>div.list-group>a").click(function(e) {
                    e.preventDefault();
                    $(this).siblings('a.active').removeClass("active");
                    $(this).addClass("active");
                    let index = $(this).index();
                    $("div.bhoechie-tab>div.bhoechie-tab-content").removeClass("active");
                    $("div.bhoechie-tab>div.bhoechie-tab-content").eq(index).addClass("active");
                });
                setDataUser(<?php print json_encode($this->infoUser); ?>);


                $("#frmGeneral").on('submit',(function(e){
                    e.preventDefault();

                    let expReg = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
                    if (!expReg.test($("#iCorreo").val())) {
                        wd.alertDialog('Correo incorrecto, intente nuevamente ingresando uno válido');
                    } else {
                        $.ajax({
                            url: "<?php print $this->strAction; ?>&op=save",
                            type: "POST",
                            data:  new FormData(this),
                            contentType: false,
                            cache: false,
                            processData:false,
                            beforeSend: function(){
                                wd.openLoading();
                            },
                            success: function(data){
                                wd.closeLoading();
                                wd.alertDialog(data.msj);
                            },
                            error: function(){
                                wd.closeLoading();
                            }
                        });
                    }
                }));

                $("#frmAvatar").on('submit',(function(e){
                    e.preventDefault();
                    $.ajax({
                        url: "<?php print $this->strAction; ?>&op=sAvatar",
                        type: "POST",
                        data:  new FormData(this),
                        contentType: false,
                        cache: false,
                        processData:false,
                        beforeSend: function(){
                            wd.openLoading();
                        },
                        success: function(data){
                            wd.closeLoading();
                            wd.alertDialog(data.msj);
                        },
                        error: function(){
                            wd.closeLoading();
                        }
                    });
                }));

                getFamilyRoles();
            });

            /* GOOGLE CHART */
            function initChart(){
                let data = new google.visualization.DataTable();
                data.addColumn('string', 'Name');
                data.addColumn('string', 'Manager');
                data.addColumn('string', 'Description');
                //data.addColumn('string', 'ToolTip');
                return data;
            }

            function getRolsData(){
                if(boolAuxRol){
                    getFamilyByRolAux();
                }
                else{
                    getFamilyByRole();
                }
            }

            function addRowChart(datatable, value){
                $.each(value,function (key,val) {
                    let nameUser = val.nombres + " " + val.apellidos;
                    datatable.addRows([
                        [val.uid, val.father, nameUser]
                    ]);
                });
                let chart = new google.visualization.OrgChart(getDocumentLayer('chart_div'));
                chart.draw(datatable, {allowHtml:true});
                addProperties();
            }

            function addProperties(){
                let count = 0;
                $(".google-visualization-orgchart-node-medium").each(function(key,val){
                    let strIdentify = $(this).attr("title");
                    let intIDUser = $(this).text();
                    $(this).attr("cntId", intIDUser).css("color", "white");
                    <?php
                    if(check_user_class("unassign_family")){
                        ?>
                        $(this).attr({
                            "onmouseover": "addRemoveButton(this)",
                            "onmouseleave": "quitRemoveButton(this)"
                        });
                        <?php
                    }
                    ?>
                    if($(this).attr("cntID") == userID){
                        $(this).css("background","#FFc530")
                    }
                    else{
                        $(this).css("background","#396BCE")
                    }
                    $(this).html(strIdentify);
                    count++;
                });
            }

            function addRemoveButton(element){
                let buttonClose = $("<button>X</button>").css({
                    "border": "none",
                    "color": "white",
                    "background": "red",
                    "border-radius": "5px",
                    "padding": "2px 3px",
                    "float": "right",
                    "position": "relative",
                    "top": "-20px",
                    "right": "-17px",
                    "font-size": "10px"
                }).attr({
                    "type": "button"
                }).on("click",function(){
                    unassingUser(element);
                });
                if($(element).find("button").length == 0){
                    if(userID != $(element).attr("cntID")){
                        $(element).append(buttonClose);
                    }
                }
            }

            function quitRemoveButton(element){
                if($(element).find("button")){
                    $(element).find("button").remove();
                }
            }

            function unassingUser(element){
                let userID = $(element).attr("cntId");
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=deleteUserForFather",
                    type: "POST",
                    data: {
                        uid: userID
                    },
                    beforeSend: function(){
                        wd.openLoading();
                    },
                    success: function(data){
                        wd.closeLoading();
                        if(data.status == "ok"){
                            getRolsData();
                        }
                    },
                    error: function(){
                        wd.closeLoading();
                    }
                });
            }
            /* =========== */

            let xhrRols;
            let xhr;
            function getFamilyByRole(){
                xhrRols = $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getFamily",
                    type: "POST",
                    beforeSend: function(){
                        if(xhrRols) xhrRols.abort();
                    },
                    success: function(response){
                        //dw.closeLoading();
                        let dataTable = initChart();
                        let objAllUsersFamily = {};
                        userID = response.iAm.uid;

                        /*SE ARMA EL ARREGLO DONDE SE ENCUENTRA TODA LA FAMIIA
                        * PARA LOS QUE SE IGUALAN A VACÍO ES PARA QUE NO MUESTRE PAPÁ 0*/
                        response.father.father = "";
                        if(response.iAm.father == "0"){
                            response.iAm.father = "";
                        }
                        objAllUsersFamily[response.iAm.uid] = response.iAm;
                        objAllUsersFamily[response.father.uid] = response.father;
                        if(Object.keys(response.childs).length > 0){
                            $.each(response.childs,function(key,val){
                                objAllUsersFamily[val.uid] = val;
                            });
                        }
                        if(response.brothers){
                            if(Object.keys(response.brothers).length > 0){
                                $.each(response.brothers,function(key,val){
                                    objAllUsersFamily[val.uid] = val;
                                });
                            }
                        }

                        if(response.userAuxMyFather){
                            drawUserAuxMyFather(response.userAuxMyFather);
                        }

                        /* END SET ARRAY FAMILY */


                        addRowChart(dataTable, objAllUsersFamily);
                        xhrRols = null;
                    },
                    error: function(){
                        wd.alertDialog("Ocurrió un problema al obtener usuarios.");
                    }
                });
            }

            function drawUserAuxMyFather(dataUserAux){
                let cntInfo = $("#cntUserAuxFather");
                let cntUserAuxData = $("<div></div>").attr({
                    "class": "col-xs-12 col-md-6"
                });
                cntInfo.append(cntUserAuxData);
                let strTitle = $("<h4>Persona Auxiliar</h4>");
                cntUserAuxData.append(strTitle);
                let strUserAux = $("<p>" + dataUserAux.nombres + " " + dataUserAux.apellidos + "</p>").attr("class", "strUserAux");
                cntUserAuxData.append(strUserAux);
            }

            function getFamilyByRolAux(){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getFamilyByRolAux",
                    type: "POST",
                    dataType: "JSON",
                    beforeSend: () => {
                        wd.openLoading();
                    },
                    success: (data) => {
                        wd.closeLoading();
                        if(data.status == "ok"){
                            const objFamily = {};
                            let dataTable = initChart();
                            $.each(data.family, (keyFathers, valFathers) => {
                                objFamily[valFathers.father.uid] = valFathers.father;
                                $.each(valFathers.childs, (keyChilds, valChilds) => {
                                    objFamily[valChilds.uid] = valChilds;
                                });
                            });
                            $.each(objFamily, (key, val) => {
                                let searchFather = val.father;
                                if(objFamily[searchFather]){
                                    return true;
                                }
                                else{
                                    /*remove father for not draw numbers in chart*/
                                    val.father = "";
                                }
                            });
                            addRowChart(dataTable, objFamily);
                        }
                    },
                    error: () => {
                        wd.closeLoading();
                        wd.alertDialog("Ha ocurrido un error al obtener la información de la familia del usuario.","Mensaje del sistema");
                    }
                });
            }

            function setDataUser(arrData){

                if(Object.keys(arrData).length > 0){
                    $("#iNombres").val(arrData.nombres);
                    $("#iApellidos").val(arrData.apellidos);
                    $("#iUsual").val(arrData.nickname);
                    $("#iCorreo").val(arrData.email);
                    $("#sSexo").val(arrData.sex);
                    $("#sPais").val(arrData.country);
                    $("#iCelular").val(arrData.tel_cel);
                }
                else{
                    wd.alertDialog("Error al cargar el usuario")
                }
            }

            function verifyPass() {
                $("#iPass").val($("#iPass").val().trim())
                $("#iPassConfirm").val($("#iPassConfirm").val().trim())
                let iPass = $("#iPass").val();
                let iPassConfirm = $("#iPassConfirm").val();

                if(iPass != '' && iPassConfirm != ''){
                    if(iPass != iPassConfirm){
                        $("#alertPass").addClass("in");
                        $("#btnPass").addClass("disabled");
                        $("#btnPass").attr("disabled","disabled");
                    }
                    else{
                        $("#alertPass").removeClass("in");
                        $("#btnPass").removeClass("disabled");
                        $("#btnPass").removeAttr("disabled");
                    }
                }
                else{
                    $("#alertPass").removeClass("in");
                    $("#btnPass").addClass("disabled");
                    $("#btnPass").attr("disabled","disabled");
                }
            }

            function save_device(id,obj){
                if(!id || !obj) return false;

                $("#spinner_"+id).addClass("hide");
                $("#check_"+id).addClass("hide");
                $("#error_"+id).addClass("hide");

                const params = {
                    device : id,
                    campo : $(obj).attr('column'),
                    valor : $(obj).val()
                };

                if(xhr) xhr.abort();
                xhr = $.ajax({
                    url: "<?php print $this->strAction; ?>&op=device",
                    type : "POST",
                    data : params,
                    beforeSend: function(){
                        $("#spinner_"+id).removeClass("hide");
                    },
                    success: function( _response ){
                        $("#spinner_"+id).addClass("hide");
                        if(_response.status == "ok"){
                            $("#check_"+id).removeClass("hide");
                            setTimeout(function () {
                                $("#check_"+id).addClass("hide");
                            },2000);

                            if(params.campo == "activo"){
                                if(params.valor == "Y"){
                                    $(obj).addClass("hide");
                                    $(obj).parent().find(".btn-warning").removeClass("hide");
                                }
                                else{
                                    $(obj).addClass("hide");
                                    $(obj).parent().find(".btn-primary").removeClass("hide");
                                }
                            }
                            else if(params.campo == "eliminado"){
                                $(obj).parent().parent().remove();
                            }
                        }
                        else{
                            $("#error_"+id).removeClass("hide");
                            setTimeout(function () {
                                $("#error_"+id).addClass("hide");
                            },2000);
                            wd.alertDialog(_response.msj)
                        }

                    },
                    error: function(){
                        $("#spinner_"+id).addClass("hide");
                        wd.closeLoading();
                    }
                });
            }

            function save_pass(){
                $.ajax({
                    url : "<?php print $this->strAction; ?>&op=pass",
                    type : "POST",
                    data : {
                        uid : '<?php print $this->intUid ?>',
                        pass : $("#iPass").val(),
                    },
                    beforeSend : function(){
                        wd.openLoading();
                    },
                    success : function(data){
                        wd.closeLoading();
                        wd.alertDialog(data.msj);
                        if(data.status == "ok"){
                            $("#iPass").val("");
                            $("#iPassConfirm").val("");
                            $("#btnPass").addClass("disabled");
                            $("#btnPass").attr("disabled","disabled");
                        }
                    },
                    error : function(){
                        wd.closeLoading();
                        wd.alertDialog("Hubo un problema al guardar, intente de nuevo");
                    }
                });
            }

            function getFamilyRoles(){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getRoles",
                    type: "POST",
                    beforeSend: function () {
                        wd.openLoading();
                    },
                    success: function(data){
                        wd.closeLoading();
                        if(data.status == "ok"){
                            roleUser = data.roles.roleUser;
                            objRoleAux = data.roles.roleAux;
                            objRolesChild = data.roles.rolesChild;
                            setRoleUser();
                            getInfoUserRoleAux();
                        }
                    }
                });
            }

            function setRoleUser() {
                $("#strTitleMyFamily").text("Puesto: " + roleUser);
            }

            function getInfoUserRoleAux(){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getUserAux",
                    type: "POST",
                    beforeSend: function(){
                        wd.openLoading();
                    },
                    success: function(data){
                        wd.closeLoading();
                        if(data.status == "ok"){
                            objDataRoleAuxExist = data.userAux;
                            $.each(data.roles_aux, (key, val) => {
                                if(roleUser == val.role_auxiliar){
                                    boolAuxRol = true;
                                }
                            });
                            setViewDrawRoles();
                            google.charts.load('current', {packages:["orgchart"]});
                            google.charts.setOnLoadCallback(getRolsData);
                        }
                    },
                    error: function(){
                        wd.closeLoading();
                    }
                });
            }

            function setViewDrawRoles(){
                let cntAllInfoRoles = $("#cntMenuRolesAdmFamily");
                cntAllInfoRoles.html("");
                let lengthObjChilds = Object.keys(objRolesChild).length;
                let cntFilterChilds = $("<div></div>").attr({
                    "id": "cntFilterChilds",
                    "class": "col-xs-12 col-md-6"
                }); cntAllInfoRoles.append(cntFilterChilds);
                let cntFilterRoleAux = $("<div></div>").attr({
                    "id": "cntFilterRoleAux",
                    "class": "col-xs-12 col-md-6"
                }); cntAllInfoRoles.append(cntFilterRoleAux);

                if(lengthObjChilds < 1){
                    boolExitChild = false;
                    $("#cntFilterChilds").remove();
                }
                if( objRoleAux.id_usertype == "undefined" || objRoleAux.id_usertype == undefined){
                    boolExitRolAux = false;
                    $("#cntFilterRoleAux").remove();
                }

                drawInputSearchRoles(cntFilterChilds);

                if(Object.keys(objDataRoleAuxExist).length > 0){
                    drawUserAuxExist(cntFilterRoleAux);
                }
                else{
                    drawInputSearchRoleAux(cntFilterRoleAux);
                }
            }

            function drawInputSearchRoles(cntDrawFilterChilds){
                if(boolExitChild){
                    $.each(objRolesChild,function(keyRolC,valRolC){
                        let cntByRol = $("<div></div>").attr({
                            "class": "col-xs-12",
                            "id": "cntSearchRol_" + valRolC.id_usertype
                        });
                        cntDrawFilterChilds.append(cntByRol);

                        let titleSearchRol = $("<h5>Buscar " + valRolC.descr + "</h5>");
                        cntByRol.append(titleSearchRol);
                        let inputSearchRol = $("<input>").attr({
                            "type": "text",
                            "class": "form-control inputSearchRol",
                            "name": "iSearch_" + valRolC.name,
                            "id": "iSearch_" + valRolC.name
                        });
                        /*seteo el name del rol, al id/name del input, porque así se guarda en el campo swusertype de wt_users para hacer la consulta luego*/
                        cntByRol.append(inputSearchRol);


                        let cntTable = $("<section></section>").attr("class","cntResultsUser hide").css({
                            "max-height": "110px",
                            "overflow": "auto"
                        });
                        let table = $("<table></table>").attr("id", "tblResult_"+valRolC.name).css("width","100%");
                        if(valRolC.name != ""){
                            cntByRol.append(inputSearchRol);
                            cntByRol.append(cntTable);
                            cntTable.append(table);
                            inputSearchRol.on("click",function(){
                                if($(cntTable).hasClass("hide")){
                                    $(cntTable).removeClass("hide")
                                }
                                else{
                                    $(cntTable).addClass("hide")
                                }
                                elementContentSearch = cntTable;
                                if(objCntAllRoles[valRolC.name] == undefined || objCntAllRoles[valRolC.name] == "undefined"){
                                    getDataRolByName(valRolC.name, table);
                                }
                                else{
                                    drawResponseByRol(objCntAllRoles[valRolC.name], table);
                                }
                            });
                        }

                        inputSearchRol.on("keyup",function () {
                            let strVal = $(this).val();
                            drawResponseByRol(objCntAllRoles[valRolC.name], table, strVal);
                        });

                    });
                }
            }

            function drawUserAuxExist(cntDrawFilterRoleAux){
                let cntRolAux = $("<div></div>").attr({
                    "class": "col-xs-12",
                    "id": "cntUserAux"
                });
                cntDrawFilterRoleAux.append(cntRolAux);
                let cntUserAux = $("<div></div>");
                cntRolAux.append(cntUserAux);
                let titleSearchRol = $("<h5>Persona Auxiliar</h5>");
                cntUserAux.append(titleSearchRol);

                let cntNameUserAux = $("<div></div>").css({
                    "positon": "relative",
                    "height": "40px",
                    "border": "2px solid #C7C7C7",
                    "border-radius": "4px"
                });
                cntUserAux.append(cntNameUserAux);
                let p = $("<p>" + objDataRoleAuxExist.nombres + " " + objDataRoleAuxExist.apellidos + "</p>").css({
                    "padding": "5px",
                    "float": "left"
                });
                cntNameUserAux.append(p);
                let btnDeleteRoleAux = $("<button>X</button>").attr({
                    "type": "button",
                    "class": "btn btn-danger"
                }).css({
                    "float": "right"
                });
                cntNameUserAux.append(btnDeleteRoleAux);
                btnDeleteRoleAux.on("click", function(){
                    unassingUserAux(objDataRoleAuxExist);
                });
            }

            function unassingUserAux(objDataRoleAuxExist){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=removeRoleAux",
                    type: "POST",
                    data: {
                        idUserFatherAux: objDataRoleAuxExist.uid
                    },
                    beforeSend: function(){
                        wd.closeLoading();
                    },
                    success: function(data){
                        wd.closeLoading();
                        if(data.status == "ok"){
                            objCntAllRoles = {};
                            objDataRoleAuxExist = {};
                            getFamilyRoles();
                        }
                    },
                    error: function(){
                        wd.closeLoading();
                    }
                });
            }

            function drawInputSearchRoleAux(cntDrawFilterRoleAux){
                if(boolExitRolAux){
                    let cntRolAux = $("<div></div>").attr({
                        "class": "col-xs-12",
                        "id": "cntSearchRolAux"
                    });
                    cntDrawFilterRoleAux.append(cntRolAux);

                    let titleSearchRol = $("<h5>Buscar " + objRoleAux.descr + "</h5>");
                    cntRolAux.append(titleSearchRol);
                    let inputSearchRol = $("<input>").attr({
                        "type": "text",
                        "class": "form-control inputSearchRol",
                        "name": "iSearch_" + objRoleAux.name,
                        "id": "iSearch_" + objRoleAux.name
                    });
                    cntRolAux.append(inputSearchRol);

                    let cntTable = $("<section></section>").attr("class","cntResultsUser hide").css({
                        "max-height": "110px",
                        "overflow": "auto"
                    });
                    let table = $("<table></table>").attr("id", "tblResult_"+objRoleAux.name).css("width","100%");
                    if(objRoleAux.name != ""){
                        cntRolAux.append(inputSearchRol);
                        cntRolAux.append(cntTable);
                        cntTable.append(table);
                        inputSearchRol.on("click",function(){
                            if($(cntTable).hasClass("hide")){
                                $(cntTable).removeClass("hide")
                            }
                            else{
                                $(cntTable).addClass("hide")
                            }
                            elementContentSearch = cntTable;
                            if(objCntAllRoles[objRoleAux.name] == undefined || objCntAllRoles[objRoleAux.name] == "undefined"){
                                getDataRolByName(objRoleAux.name, table);
                                strRoleAux = objRoleAux.name;
                            }
                            else{
                                drawResponseByRol(objCntAllRoles[objRoleAux.name], table, "");
                            }
                        });
                    }

                    inputSearchRol.on("keyup",function () {
                        let strVal = $(this).val();
                        drawResponseByRol(objCntAllRoles[objRoleAux.name], table, strVal);
                    });
                }
            }

            function getDataRolByName(nameRol, elment){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getUsersByRol",
                    type: "POST",
                    data: {
                        nameRol: nameRol
                    },
                    beforeSend: function(){
                        /*wd.openLoading();*/
                    },
                    success: function(data){
                        /*wd.closeLoading();*/
                        if(data.status == "ok"){
                            $.each(data.usersByRol,function(keyRol,valRol){/*recorro solo para setear el key, al objeto que me contiene toda la info*/
                                objCntAllRoles[keyRol] = valRol;
                            });
                            drawResponseByRol(objCntAllRoles[nameRol],elment);
                        }
                    },
                    error: function(){
                        wd.closeLoading();
                        wd.alertDialog("Ocurrió un error en obtener la información de este rol.");
                    }

                });
            }

            function drawResponseByRol(objDraw, cntDiv, strSearch){
                /*el booleano aquí no me sirve, porque no estoy identificando nada, solo veo en el objeto
                * lo que hay que hacer es ver el proceso y desde antes de la consulta enviar un parámetro para indicar que es el rol auxiliar*/
                cntDiv.html("");
                $.each(objDraw,function(keyDataRol,valDataRol){
                    let userName = valDataRol.nombres + " " + valDataRol.apellidos;
                    let newStrSearch = "";
                    if(strSearch){
                        newStrSearch = removeCharacter(strSearch);
                    }
                    let newUserName = removeCharacter(userName);

                    if(newUserName.match(newStrSearch)){
                        let tr = $("<tr></tr>").css({
                            "border": "1px solid #C7C7C7",
                            "cursor": "pointer"
                        }).on("mouseover",function(){
                            $(this).css("background","#c7c7c7")
                        }).on("mouseleave",function(){
                            $(this).css("background","white")
                        });
                        cntDiv.append(tr);
                        let p = $("<p>" + userName + "</p>").css({
                            "margin": "0",
                            "padding": "10px"
                        });
                        tr.append(p);

                        tr.on("click",function(){
                            if(strRoleAux == valDataRol.swusertype){
                                sendRolAuxToAsiggnUser(valDataRol);/*aquí se asigna el rol auxiliar*/
                            }
                            else{
                                if(valDataRol.father != "0" || valDataRol.father != 0){
                                    $("#alertAssignFamily").addClass("in");
                                    setTimeout(function(){
                                        $("#alertAssignFamily").removeClass("in");
                                    },1000);
                                }
                                else{
                                    sendRolToAsiggnUserFather(valDataRol);
                                }
                            }
                        });
                    }
                });
            }

            function sendRolToAsiggnUserFather(dataUserSel){
                $.ajax({
                        url: "<?php print $this->strAction; ?>&op=saveChild",
                        type: "POST",
                        data: {
                            uidChild: dataUserSel.uid,
                        },
                        beforeSend: function(){
                            wd.openLoading();
                        },
                        success: function(data){
                            wd.closeLoading();
                            if(data.status == "ok"){
                                getRolsData();
                                setViewDrawRoles();
                                /*NELSON
                                * aquí tenés que ver también que se recarge o se reemplaze el objeto de los usuarios disponibles*/
                            }
                        },
                        error: function(){
                            wd.closeLoading();
                        }
                    })
                /*
                * PARA EL ORGANIGRAMA
                * tener un objeto que será la familia a dibujar
                * al guardar o dar click a un elemento, recargar la ventana?
                * *  si se recarga la ventana, la función que me llena el objeto al inicio, funcionaría para el organigrama
                * * * lo malo es que no me dejaría en la opción de "MI GRUPO"
                * si no se recarga
                * * guardar los datos, asignando al hijo a su nuevo papá
                * * llamar de nuevo la función que me consulta quienes son los hijos de esta persona
                * * dibujar nuevamente el organigrama
                * * * puedo añadir un Loading a este div? para que el usuario vea que está trabajando, o se está haciendo el cambio
                */
            }

            function sendRolAuxToAsiggnUser(dataUserRol){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=asiggnRoleAux",
                    data: {
                        idUserRolAux: dataUserRol.uid
                    },
                    type: "POST",
                    beforeSend: function(){
                        wd.openLoading();
                    },
                    success: function(data){
                        wd.closeLoading();
                        if(data.status == "ok"){
                            getFamilyRoles();
                        }
                    },
                    error: function(){
                        wd.closeLoading();
                        wd.alertDialog("Ocurrió un problema para asignar el rol");
                    }
                });
            }

        </script>
        <?php
        $this->scripts();
        theme_draw_centerbox_close();
        draw_footer();
    }

    public function headerScripts(){
        ?>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.6/css/fixedHeader.bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">

        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>
        <?php
    }

    public function scripts(){
        ?>
            <script src="/modules/users/objects/myaccount/js/account.js" defer></script>
        <?php
    }
}