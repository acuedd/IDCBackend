<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 16/02/2017
 * Time: 12:19
 */
class users_view extends global_config implements window_view{

    private static $_instance;
    private $strAction = "";
    private $boolGerente = false;
    private $boolOthers = false;
    private $arrProfiles = array();

    public function __construct($arrParams){
        parent::__construct($arrParams);
        $this->boolGerente = check_user_class("create_gerentes");
        $this->boolOthers = check_user_class("create_others");
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

    public function styles(){
        ?>

        <style>
            #contTypeClient{
                max-height: 100px;
                overflow: auto;
            }
            .cntElmntRol:hover{
                background: #0078D7;
                color: white;
            }
            #srh-tags > div:hover{
                background: #0078D7;
                color: white;
            }
            input[type="checkbox"], input[type="radio"], #sltProfiles {
                cursor: pointer;
            }
            .strSuggestionPass {
                color: #3598DC;
                margin: 0 0 0 5px;
                padding: 0;
                float: right;
            }
            #cntParentsUser{
                background: rgb(242, 242, 242);
                margin: 0;
                padding: 0;
            }
            #searchTags{
                width: 80%;
                border: none;
                border-bottom: 1px solid;
                margin-left: 10%;
                margin-bottom: 20px;
            }
            #tbl-report > tbody > tr:hover{
                cursor: pointer;
                background: #8c8c8c;
                color: white;
            }
            #cntReportUserExist{
                margin-top: 60px;
                margin-bottom: 100px;
            }
            input[type='search']{
                border: 1px solid;
                border-radius: 4px;
                margin: 5px;
            }
            #tbl-report > thead{
                background: #D0DEF8;
            }
            #tbl-report > thead > tr > th{
                border-right: 1px solid white;
                border-left: 1px solid white;
            }
            .buttons-html5{
                background: transparent !important;
            }
            .dataTables_wrapper .dataTables_filter input{
                margin-left: 0;
            }
            .paginate_button{
                background: transparent !important;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
                color: black !important;
            }
            table.dataTable.no-footer{
                border-bottom: none !important;
            }
            .chkSexFormUser{
                padding: 0;
                float: left;
                color: #7C7C7C;
                margin: 0 10px;
            }
            .cntChkActiveUser{
                margin-top: 23px;
            }
            .strHideFormUser{
                margin: 0;
                padding: 0;
            }
            #selectRolUser:hover{
                cursor: pointer;
            }
            #btnTags{
                width: 100%;
            }
            .cntIndividualTag:hover{
                cursor: pointer;
                background: #f1f1f1;
            }
            #cntParents{
                margin-top: 25px;
                padding: 0;
            }
        </style>

        <?php
    }

    public function scripts(){
        ?>

        <script type="application/javascript">

            var dw = new drawWidgets();
            var uIdSelected = 0;
            var objProfilesAccess = "";
            var objRoles = "";
            var objRolesCharge = {};
            var objTags = {};
            var objRolesAsign= {};
            var tableReport = null;
            var strPass = "";
            var boolUserExist = false;
            var arrCntTypeSelected = [];
            var arrUserAsign = [];
            var strFatherAsign = "";
            var arrUsersFather = [];
            let arrDataUsers = null;
            let boolFilterInactive = false;
            let boolFilterMonthInactive = false;
            let strBtnDefault = "btn-default";
            let strBtnFilterActive = "btn-primary";

            $(document).ready(function(){
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
                        $("#iTagColor").val(hex);
                    }
                });
                drawBtnAddUserOrSelected();
                getProfilesAccess();
                drawAllUserExist();
                getRolesUser();
            });

            $(window).resize(function () {
                var id;
                clearTimeout(id);
                id = setTimeout(doneResizing, 50);
            });
            function doneResizing(){
                var height = $(window).height();
                var heightLost = 70;
                var total = height-heightLost;
                $("#cntParentsUser").css({
                    "background": "rgb(242, 242, 242)",
                    "margin": "0",
                    "padding": "0",
                    "min-height": "499px",//esta es la altura del form
                    "height" : ""+ total
                });
            }

            function drawBtnAddUserOrSelected(){
                var cntBtn = $("#cntBtnSelectedAct");
                var divCntCenter = $("<div></div>").attr("class","col-xs-12 col-md-4 col-md-offset-4"); cntBtn.append(divCntCenter);
                var cntFirstBtn = $("<div></div>").attr("class","col-md-6"); divCntCenter.append(cntFirstBtn);
                var btnAddUser = $("<button><i class='fa fa-plus'></i> Agregar</button>").attr({
                    "class": "btn btn-warning",
                    "type": "button",
                    "onclick": "drawFormCreateNewUser()"
                }); cntFirstBtn.append(btnAddUser);
                var cntSecondBtn = $("<div></div>").attr("class","col-md-6"); divCntCenter.append(cntSecondBtn);
                var btnViewUsers = $("<button><i class='fa fa-search'></i> Buscar</button>").attr({
                    "class": "btn btn-warning",
                    "type": "button"
                }).on("click",function(){
                    window.location.reload();
                }); cntSecondBtn.append(btnViewUsers);
            }

            function getProfilesAccess(){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=profiles",
                    type: "POST",
                    dataType: "JSON",
                    success: function(data){
                        if(data.status == "ok"){
                            objProfilesAccess = data.profiles;
                        }
                    }
                });
            }

            function getRolesUser(){
                //revisar de donde proviene la info
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=roles",
                    type: "POST",
                    dataType: "JSON",
                    success: function(data){
                        if(data.status == "ok"){
                            objRoles = data.roles;
                        }
                    }
                });
            }

            function drawFormCreateNewUser(boolEdit){
                arrUserAsign = [];
                arrUsersFather = [];
                objRolesCharge = {};
                if(!boolEdit){
                    uIdSelected = 0;
                }

                $("#cntFormSelected").removeClass("hide");
                $("#cntReportUser").addClass("hide");
                $("#tbl-report").fadeOut("fast");
                var cntElements = $("#cntFormSelected");
                cntElements.html("");
                var formUsers = $("<form></form>").attr("id","frmUsers");

                cntElements.append(formUsers);
                var cntFormAddDataUser = $("<div></div>").attr({
                    "id": "cntFormAddUser",
                    "class": "col-xs-12 col-md-9"
                });
                formUsers.append(cntFormAddDataUser);
                drawFormDataUser(cntFormAddDataUser);

                var cntFormAddParentsUser = $("<div></div>").attr({
                    "id": "cntParentsUser",
                    "class": "col-xs-12 col-md-3"
                });
                formUsers.append(cntFormAddParentsUser);
                var height = $(window).height();
                cntFormAddParentsUser.css({
                    "min-height": "499px",//esta es la altura del form
                    "height" : height
                });
                drawFormDataParentsUser(cntFormAddParentsUser);
            }

            function drawFormDataUser(cntForm){
                var titleForm = $("<h3>CREAR PERSONA</h3>"); cntForm.append(titleForm);
                /* DRAW PERSONAL INFO OF THE USER */
                var rowInfo = $("<div></div>").attr("class","row").css("margin-top","30px"); cntForm.append(rowInfo);
                var elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6"); rowInfo.append(elmntInto);
                var strTitleInputForm = $("<p>Nombres</p>").attr("class","strHideFormUser"); elmntInto.append(strTitleInputForm);
                var inputForm = $("<input>").attr({
                    "type": "text",
                    "class": " form-control",
                    "name": "iNames",
                    "id": "iNames",
                    "placeholder": "Nombres",
                    "required": "required",
                    "onBlur": "getUserPass()"
                }); elmntInto.append(inputForm);
                elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6"); rowInfo.append(elmntInto);
                strTitleInputForm = $("<p>Apellidos</p>").attr("class","strHideFormUser"); elmntInto.append(strTitleInputForm);
                inputForm = $("<input>").attr({
                    "type": "text",
                    "class": " form-control",
                    "name": "iLast",
                    "id": "iLast",
                    "placeholder": "Apellidos",
                    "required": "required",
                    "onBlur": "getUserPass()"
                }); elmntInto.append(inputForm);

                rowInfo = $("<div></div>").attr("class","row").css("margin-top","30px"); cntForm.append(rowInfo);
                elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6"); rowInfo.append(elmntInto);
                strTitleInputForm = $("<p>Teléfono Celular</p>").attr("class","strHideFormUser"); elmntInto.append(strTitleInputForm);
                inputForm = $("<input>").attr({
                    "type": "tel",
                    "class": " form-control",
                    "name": "iPhone",
                    "id": "iPhone",
                    "placeholder": "Teléfono Celular",
                    "required": "required",
                    "onkeypress": "return justNumbers(event,this)"
                }); elmntInto.append(inputForm);
                elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6"); rowInfo.append(elmntInto);
                strTitleInputForm = $("<p>Correo Electrónico</p>").attr("class","strHideFormUser"); elmntInto.append(strTitleInputForm);
                inputForm = $("<input>").attr({
                    "type": "email",
                    "class": " form-control",
                    "name": "iMail",
                    "id": "iMail",
                    "placeholder": "Correo Electrónico",
                    "required": "required"
                }); elmntInto.append(inputForm);

                /*DRAW SEX*/
                rowInfo = $("<div></div>").attr("class","row").css("margin-top","30px"); cntForm.append(rowInfo);
                elmntInto = $("<div></div>").attr("class","col-xs-12"); rowInfo.append(elmntInto);
                var labelTitleSex = $("<label></label>").attr("class","col-md-1");elmntInto.append(labelTitleSex);
                var strSex = $("<h4>Sexo</h4>").attr("class","chkSexFormUser"); labelTitleSex.append(strSex);

                labelTitleSex = $("<label></label>").attr("class","col-md-2 col-xs-12");elmntInto.append(labelTitleSex);
                strSex = $("<p>Femenino</p>").attr("class","chkSexFormUser"); labelTitleSex.append(strSex);
                var inputChk = $("<input>").attr({
                    "type": "radio",
                    "name": "chkSex",
                    "value": "Female",
                    "id": "chkFem"
                }); labelTitleSex.append(inputChk);

                labelTitleSex = $("<label></label>").attr("class","col-md-2 col-xs-12");elmntInto.append(labelTitleSex);
                strSex = $("<p>Masculino</p>").attr("class","chkSexFormUser"); labelTitleSex.append(strSex);
                inputChk = $("<input>").attr({
                    "type": "radio",
                    "name": "chkSex",
                    "value": "Male",
                    "id": "chkMas"
                }); labelTitleSex.append(inputChk);

                /* DRAW USER AND PASS */
                drawRowUserPass(cntForm);

                /* DRAW INPUT USERID AND ACTIVE USER */
                rowInfo = $("<div></div>").attr("class","row").css("margin-top","30px"); cntForm.append(rowInfo);
                elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6"); rowInfo.append(elmntInto);
                strTitleInputForm = $("<p>User ID</p>").attr("class","strHideFormUser"); elmntInto.append(strTitleInputForm);
                inputForm = $("<input>").attr({
                    "type": "text",
                    "class": " form-control",
                    "name": "iUserid",
                    "id": "iUserid",
                    "placeholder": "User ID",
                    "value": "0",
                    "readonly": "readonly"
                }); elmntInto.append(inputForm);

                elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6"); rowInfo.append(elmntInto);
                labelTitleSex = $("<label></label>").attr("class","col-md-6 col-xs-12");elmntInto.append(labelTitleSex);
                strSex = $("<p>Activar</p>").attr("class","chkSexFormUser"); labelTitleSex.append(strSex);
                inputChk = $("<input>").attr({
                    "type": "radio",
                    "name": "chkActive",
                    "value": "Y",
                    "id": "chkActive"
                }); labelTitleSex.append(inputChk);

                labelTitleSex = $("<label></label>").attr("class","col-md-6 col-xs-12");elmntInto.append(labelTitleSex);
                strSex = $("<p>Desactivar</p>").attr("class","chkSexFormUser"); labelTitleSex.append(strSex);
                inputChk = $("<input>").attr({
                    "type": "radio",
                    "name": "chkActive",
                    "value": "N",
                    "id": "chkDefuse"
                }); labelTitleSex.append(inputChk);

                /* DRAW SELECT PROFILE AND MULTISESION */
                rowInfo = $("<div></div>").attr("class","row").css("margin-top","30px"); cntForm.append(rowInfo);
                elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6"); rowInfo.append(elmntInto);
                strTitleInputForm = $("<p>Seleccione Perfil de Acceso</p>").attr("class","strHideFormUser"); elmntInto.append(strTitleInputForm);
                var selectProfAccess = $("<select></select>").attr({
                    "class":"form-control",
                    "name": "sltProfiles",
                    "id": "sltProfiles",
                    "required": "required"
                });
                elmntInto.append(selectProfAccess);
                var optionDefault = $("<option></option>").attr({
                    "value": "0"
                }).css("cursor","pointer"); selectProfAccess.append(optionDefault);
                $.each(objProfilesAccess,function(key_profileA,val_profileA){
                    setOptionProfileAccess(selectProfAccess,key_profileA,val_profileA);
                });

                elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6"); rowInfo.append(elmntInto);
                labelTitleSex = $("<label></label>").attr("class","col-md-6 col-xs-12");elmntInto.append(labelTitleSex);
                var strTitleInputForm = "Multisesión"; labelTitleSex.append(strTitleInputForm);
                var chkMultisesion = $("<input>").attr({
                    "id": "chkMulti",
                    "type": "checkbox",
                    "name": "chkMulti"
                }).css("float","right"); labelTitleSex.append(chkMultisesion);

                rowInfo = $("<div></div>").attr("class","row").css({
                    "margin-top":"30px",
                    "margin-bottom": "50px",
                    "text-align": "center"
                }); cntForm.append(rowInfo);
                var btnSaveUser = $("<button><i class='fa fa-save'></i> Guardar</button>").attr({
                    "type": "button",
                    "class": "btn btn-primary"
                }); rowInfo.append(btnSaveUser);
                btnSaveUser.on("click",function(){
                    saveUser();
                });

                var cntHiden = $("<div></div>").attr("id","cntElmnHiden"); $("#cntFormAddUser").append(cntHiden);
            }

            function drawRowUserPass(cntForm){
                var rowInfo = $("<div></div>").attr({
                    "class":"row",
                    "id": "cntChangePassw"
                }).css("margin-top","30px"); cntForm.append(rowInfo);
                var elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6"); rowInfo.append(elmntInto);
                var strTitleInputForm = $("<p>Usuario</p>").attr("class","strUserChangePass strHideFormUser"); elmntInto.append(strTitleInputForm);
                var inputForm = $("<input>").attr({
                    "type": "text",
                    "class": " form-control",
                    "name": "iUser",
                    "id": "iUser",
                    "placeholder": "Usuario",
                    "required": "required"
                }); elmntInto.append(inputForm);

                /* SENTENCIAS PARA MOSTRAR EL CAMBIO DE CONTRASEÑA*/
                if(uIdSelected > 0){
                    drawPassOrChangePass(rowInfo,true);
                }
                else{
                    drawPassOrChangePass(rowInfo,false);
                }
            }

            function drawPassOrChangePass(cntRowForm,boolDrawChange){
                var elmntInto = $("<div></div>").attr("class","col-xs-12 col-md-6");
                elmntInto.html("");
                if(boolDrawChange == true){/*si hay usuario*/
                    cntRowForm.append(elmntInto);
                    var lblChangePass = $("<label></label>"); elmntInto.append(lblChangePass);
                    var strTitleInputPass = $("<p> Cambiar Contraseña</p>").css({
                        "margin": "0 5px 0 0",
                        "padding": "0",
                        "float": "left"
                    });
                    var chkChangePass = $("<input>").attr({
                        "id": "changePass",
                        "type": "checkbox",
                        "name": "changePass"
                    }).click(function (){
                        if( $(this).is(":checked") ){
                            drawInputPass(true,elmntInto);
                        }
                        else{
                            drawInputPass(false,elmntInto);
                        }
                    });
                    lblChangePass.append(strTitleInputPass);
                    lblChangePass.append(chkChangePass);
                }
                else if(boolDrawChange == false){/* no hay usuario */
                    cntRowForm.append(elmntInto);
                    drawInputPass(true,elmntInto);
                }
            }

            function drawInputPass(boolDrawChange,cntElement){
                var strTitleInputFormPass = $("<p>Contraseña </p>").attr({
                    "class":"strChangePass strHideFormUser",
                    "id": "cntStrPassSug"
                }).css("float","left");
                var labelViewPass = $("<label></label>").attr("class","lblCntInfoPass");
                var inputForm = $("<input>").attr({
                    "type": "password",
                    "class": " form-control",
                    "name": "iPass",
                    "id": "iPass",
                    "placeholder": "Contraseña",
                    "required": "required"
                });
                var chkView = $("<span></span>").attr({
                    "type":"checkbox",
                    "id": "inputViewPassWeb",
                    "type": "button",
                    "class": "input-group-addon contIcons"
                });
                var iEye = $("<i></i>").attr({
                    "class": "fa fa-eye"
                });
                chkView.on("click",function(){
                    if(inputForm.attr("type") == "password"){
                        inputForm.attr("type","text");
                        iEye.attr("class","fa fa-eye-slash");
                    }
                    else{
                        inputForm.attr("type","password");
                        iEye.attr("class","fa fa-eye");
                    }
                });
                inputForm.on("keydown",function(){
                    strTitleInputFormPass.html("Contraseña");
                });
                if(boolDrawChange == true){ /* está checkeado changePass ó no hay usuario osea se está creando uno */
                    cntElement.append(strTitleInputFormPass);
                    cntElement.append(labelViewPass);
                    labelViewPass.append(inputForm);
                    labelViewPass.append(chkView);
                    chkView.append(iEye);
                    labelViewPass.css("width","100%");
                    inputForm.css({
                        "width": "90%",
                        "float": "left"
                    });
                    chkView.css({
                        "width": "10%",
                        "float": "right",
                        "cursor": "pointer",
                        "background": "transparent",
                        "padding": "8px",
                        "border": "none"
                    });
                    getUserPass(true)
                }
                else if(boolDrawChange == false){ /* se quita el check para cambiar contraseña y se remueven los elementos */
                    $(".strUserChangePass").css({
                        "margin-top": "0"
                    });
                    $("label").remove(".lblCntInfoPass");
                    $("p").remove(".strChangePass");
                }
            }

            function setOptionProfileAccess(cnt,key,val){
                var option = $("<option>"+val.nombre+"</option>").attr({
                    "value": key
                }).css("cursor","pointer"); cnt.append(option);
            }

            function drawFormDataParentsUser(cntForm){
                var cntSelectOptional = $("<div></div>").attr("class","col-xs-12").css({
                    "margin-bottom": "0"
                }); cntForm.append(cntSelectOptional);
                var strTitleOptional = $("<h4>Puesto</h4>").attr("class","strHideFormUser");cntSelectOptional.append(strTitleOptional);
                var selectRolUser = $("<select></select>").attr({
                    "class": "form-control",
                    "id": "selectRolUser",
                    "name": "selectRolUser"
                }); cntSelectOptional.append(selectRolUser);
                var optionDefault = $("<option></option>").attr("value", "0"); selectRolUser.append(optionDefault);
                $.each(objRoles,function(key,data){
                    var optionRol = $("<option>"+data.name+"</option>").attr({
                        "value": data.id_usertype
                    }); selectRolUser.append(optionRol);
                });

                selectRolUser.on("change",function () {
                    arrUsersFather = [];
                    rolAsign(this);
                });

                cntSelectOptional = $("<div></div>").attr("class","col-xs-12").css({
                    "margin-top": "30px",
                    "margin-bottom": "0"
                }); cntForm.append(cntSelectOptional);
                strTitleOptional = $("<h4>Seleccione tipo de Cliente</h4>").attr("class","strHideFormUser");cntSelectOptional.append(strTitleOptional);
                var btnViewTags = $("<button></button>").attr({
                    "id": "btnTags",
                    "type": "button",
                    "class": "btn btn-default"
                }); cntSelectOptional.append(btnViewTags);
                var span = $("<span>Tipo de Cliente</span>").css({
                    "margin": "0",
                    "padding": "0",
                    "float": "left"
                }); btnViewTags.append(span);
                var span = $("<span></span>").attr(
                    "class", "fa fa-sort"
                ).css({
                    "float":"right",
                    "margin-top": "3px"
                }); btnViewTags.append(span);

                var objResultTypeClient = $("<div></div>").attr({
                    "id": "srh-tags",
                    "class": "row div-result widhtResponsive"
                });
                cntSelectOptional.append(objResultTypeClient);
                btnViewTags.on("click",function(){
                    drawTagsExist(objResultTypeClient,false);
                });

                var cntResult = $("<div></div>").attr("id","contTypeClient");
                cntSelectOptional.append(cntResult);

                var cntParentsAdd = $("<div></div>").attr({
                    "class":"col-xs-12",
                    "id": "cntAddParents"
                }); cntForm.append(cntParentsAdd);
            }

            function drawTagsExist(cntAppendResult,boolModal,obj){
                $("#searchTags").on("keyup",function(){
                    drawTagsExist($("#mdl-seach-tag"),true, this)
                });
                var params = {};
                if(obj){
                    params = {
                        term : $(obj).val()
                    };
                }
                if(xhrTags) xhrTags.abort();
                var xhrTags = $.ajax({
                    url : "<?php print $this->strAction; ?>&op=tags",
                    type : "POST",
                    dataType : "JSON",
                    data : params,
                    success : function(data){
                        if(data.status == "ok"){
                            cntAppendResult.html("");
                            $.each(data.tags, function(key,val){
                                loadTags(cntAppendResult, val, boolModal);
                            });
                            if(!boolModal){
                                cntAppendResult.css({
                                    "border": "1px solid #d9d9d9",
                                    "border-radius": "4px",
                                    "width": "89%",
                                    "margin-left": "1%",
                                    "max-height": "300px",
                                    "overflow-y": "auto",
                                    "position": "absolute",
                                    "z-index": "2",
                                    "background": "white"
                                });
                                var content = $("<div></div>").css("cursor","pointer");cntAppendResult.append(content);
                                var user = $("<div>Agregar o editar</div>");content.append(user);
                                content.on("click",function(){
                                    $("#mdlTags").modal("show");
                                    //Tengo que cargar los tags en el modal
                                    drawTagsExist($("#mdl-seach-tag"),true);
                                    setColor("ffffff");
                                });
                            }
                            cntAppendResult.removeClass("hide");
                        }
                    }
                });
            }

            function loadTags(principal, val, boolModal){
                var content = $("<div></div>").attr("class","col-xs-12").css({
                    "border-bottom": "1px solid #d9d9d9",
                    "cursor": "pointer"
                });principal.append(content);
                //Selecciono tag si ya existe
                //if(val.id in objTags && !boolModal)content.addClass("selected");
                var divColor = $("<div></div>");content.append(divColor);
                var color = $("<div></div>").css({
                    "width":"20px",
                    "height":"20px",
                    "background-color":"#"+val.color,
                    "border":"1px solid #eef1f4",
                    "float": "left"
                });divColor.append(color);
                //Nombre
                var tagName = $("<p>"+val.tag+"</p>").css({
                    "margin": "0",
                    "padding": "0",
                    "float": "left"
                });divColor.append(tagName);
                //Editar
                if(boolModal){
                    var divEdit = $("<div></div>").css({
                        "width":"40px",
                        "height":"40px",
                        "float":"right"
                    });content.append(divEdit);
                    var edit = $("<i class='fa fa-pencil' aria-hidden='true'></i>");divEdit.append(edit);

                    divEdit.on("click",function(){
                        $("#iTag").val(val.tag);
                        $("#idTag").val(val.id);
                        setColor(val.color);
                    });
                }
                else{
                    content.on("click",function(){
                        if(content.hasClass("selected")){
                            content.removeClass("selected");
                            $("#strBubble_"+val.id).addClass("hide");
                            $(".getTagsDiv").removeClass("hide");
                            delete objTags[val.id];
                        }
                        else{
                            var idSelected = val.id;
                            var typeSelected = arrCntTypeSelected.includes(idSelected);
                            if(typeSelected == true){
                                principal.addClass("hide");
                                return false;
                            }
                            arrCntTypeSelected.push(idSelected);

                            content.addClass("selected");
                            $("#strBubble_"+val.id).removeClass("hide");
                            $(".getTagsDiv").removeClass("hide");
                            objTags[val.id] = true;
                            showTags(val,content)
                        }
                    });
                }

                $(document).click(function(){
                    $("#srh-tags").addClass("hide");
                });
            }

            function removeItemFromArr ( arr, item ) {
                var i = arr.indexOf( item );
                if ( i !== -1 ) {
                    arr.splice( i, 1 );
                }
            }

            function showTags(val,content){
                var strContTags = $("#contTypeClient");
                var bubbleColor = $("<div></div>").attr({
                    "id": "strBubble_" + val.id,
                    "class": "strBubble",
                }).css({
                    "background": "#" + val.color,
                    "max-height": "25px",
                    "border-radius": "8px",
                    "margin-top": "10px"
                }); strContTags.append(bubbleColor);
                var strBtnCloseTag = $("<button>x</button>").attr({
                    "name": "btnCloseTag",
                    "type": "button",
                    "id": "strBtnCloseTag_" + val.id,
                    "class": "strBtnCloseTag"
                }).css({
                    "float": "right",
                    "background": "transparent",
                    "border": "none",
                    "margin-right": "5px"
                }); bubbleColor.append(strBtnCloseTag);
                var strNameTag = $("<div>" + val.tag + "</div>").attr("class", "strTxtBubbleTag").css("padding","0 0 0 15px"); bubbleColor.append(strNameTag);

                $("#strBtnCloseTag_"+val.id).on("click",function(){
                    removeItemFromArr( arrCntTypeSelected, val.id );
                    delete objTags[val.id];
                    $("#strBubble_"+val.id).addClass("hide");
                    if(content.hasClass("selected")){
                        content.removeClass("selected");
                        bubbleColor.remove();
                        bubbleColor.addClass("seleccionado");
                        delete objTags[val.id];
                    }
                    else {
                        content.addClass("selected");
                        bubbleColor.removeClass("seleccionado");
                        objTags[val.id] = true;
                    }
                    createInputTags();
                });
                createInputTags();
            }

            function createInputTags(){
                $("#cntElmnHiden").html("");
                $.each(objTags,function(key,val) {
                    var input = $("<input />").attr({
                        "type":"hidden",
                        "name":"txtTags[]",
                        "value": key,
                        "class":"input-tags"
                    });
                    $("#cntElmnHiden").append(input);
                });

                $.each(objRolesAsign,function(key,val){
                    var intUser = $("<input>").attr({
                        "type": "hidden",
                        "name": "saveRolAsignUser[]",
                        "value": key,
                        "class":"input-tags"
                    }); $("#cntElmnHiden").append(intUser);
                });
            }

            function setColor(hex){
                $('#colorPicker div').css('backgroundColor', '#' + hex);
                $('#colorPicker').ColorPickerSetColor(hex);
            }

            function cleanFrmTag(){
                $("#iTag").val("");
                $("#itagcolor").val("ffffff");
                $("#idTag").val(0);
                $('#colorPicker div').css('backgroundColor', '#ffffff');
            }

            function saveTag(){
                var strName = $("#iTag").val();
                if(strName.trim() != ""){
                    $.ajax({
                        type : "POST",
                        url : "<?php print $this->strAction; ?>&op=saveTag",
                        data : {
                            idTag : $("#idTag").val(),
                            name : strName,
                            color : $("#iTagColor").val()
                        },
                        dataType : "JSON",
                        beforeSend : function(){
                            dw.openLoading();
                        },
                        success : function(data){
                            dw.closeLoading();
                            if(data.status == "ok"){
                                drawTagsExist($("#mdl-seach-tag"),true);
                                $("#iTag").val("");
                                $("#itagcolor").val("ffffff");
                                $("#idTag").val(0);
                                $('#colorPicker div').css('backgroundColor', '#ffffff');
                            }
                            else{
                                dw.alertDialog(data.msj);
                            }
                        },
                        error : function(){
                            dw.closeLoading();
                        }
                    });
                }
            }

            jQuery.extend( jQuery.fn.dataTableExt.oSort, {
                "date-uk-pre": function ( a ) {
                    if (a == null || a == "") {
                        return 0;
                    }
                    var ukDatea = a.split('/');
                    return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
                },

                "date-uk-asc": function ( a, b ) {
                    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
                },

                "date-uk-desc": function ( a, b ) {
                    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
                }
            } );

            function applyFilterDateNull(){
                let btnNoAccess = $("#btnFilterUserNoAccess");
                let btnMonthInactive = $("#btnFilterMonthInactive");
                if(tableReport !== null){
                    drawTable();
                    let strVal = '';
                    if(!boolFilterInactive){
                        boolFilterInactive = true;
                        boolFilterMonthInactive = false;
                        $(btnNoAccess).removeClass(strBtnDefault).addClass(strBtnFilterActive);
                        $(btnMonthInactive).removeClass(strBtnFilterActive).addClass(strBtnDefault);

                        strVal = "00/00/0000";
                    }
                    else{
                        $(btnNoAccess).removeClass(strBtnFilterActive).addClass(strBtnDefault);
                        boolFilterInactive = false;
                    }
                    tableReport.search(strVal).draw();
                }
            }

            function drawTable(filter = false){
                let btnNoAccess = $("#btnFilterUserNoAccess");
                let btnMonthInactive = $("#btnFilterMonthInactive");

                if(filter){   /*visualmente ya está, solo falta ver como cancelar el filtro de más de dos mese de inactividad*/
                    if(!boolFilterMonthInactive){
                        boolFilterMonthInactive = true;
                        boolFilterInactive = false;
                        $(btnMonthInactive).removeClass(strBtnDefault).addClass(strBtnFilterActive);
                        $(btnNoAccess).removeClass(strBtnFilterActive).addClass(strBtnDefault);
                    }
                    else{
                        filter = false;
                        $(btnMonthInactive).removeClass(strBtnFilterActive).addClass(strBtnDefault);
                        boolFilterMonthInactive = false;
                    }
                }

                if(arrDataUsers !== null){

                    if(tableReport !== null)tableReport.destroy();

                    const dateNow = new Date();
                    dateNow.setMonth(dateNow.getMonth() - 2);

                    const cnt = $("#tbl-report").find("tbody");
                    cnt.html('');
                    $.each(arrDataUsers,function(key,val){
                        const arrDate = val.lastvisit.split('/');
                        const dateLast = new Date(`${arrDate[1]}-${arrDate[0]}-${arrDate[2]}`);

                        if(!filter){
                            drawInfoReportInTable(cnt, val);
                        }
                        else if(!isNaN(dateLast.getTime())){
                            if(dateLast.getTime() <= dateNow.getTime()){
                                drawInfoReportInTable(cnt, val);
                            }
                        }
                    });

                    jQuery.extend( jQuery.fn.dataTableExt.oSort, {
                        "portugues-pre": function ( data ) {
                            var a = 'a';
                            var e = 'e';
                            var i = 'i';
                            var o = 'o';
                            var u = 'u';
                            var c = 'c';
                            var special_letters = {
                                "Á": a, "á": a, "Ã": a, "ã": a, "À": a, "à": a,
                                "É": e, "é": e, "Ê": e, "ê": e,
                                "Í": i, "í": i, "Î": i, "î": i,
                                "Ó": o, "ó": o, "Õ": o, "õ": o, "Ô": o, "ô": o,
                                "Ú": u, "ú": u, "Ü": u, "ü": u,
                                "ç": c, "Ç": c
                            };
                            for (var val in special_letters)
                                data = data.split(val).join(special_letters[val]).toLowerCase();
                            return data;
                        },
                        "portugues-asc": function ( a, b ) {
                            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
                        },
                        "portugues-desc": function ( a, b ) {
                            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
                        }
                    } );

                    tableReport = $("#tbl-report").DataTable({
                        "columnDefs": [
                            {type: 'portugues', targets: [0,1,2,3,4]},
                            {type: 'date-uk', targets: 8}
                        ],
                        "language": {
                            "zeroRecords": "No se encontraron resultados"
                        },
                        dom: 'Bfrtip',
                        "pageLength": 15,
                        buttons: [{
                            extend: 'copyHtml5',
                            text: '<i class="fa fa-files-o"></i>',
                            titleAttr: 'Copy'
                        }, {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i>',
                            titleAttr: 'Excel'
                        }, {
                            extend: 'csvHtml5',
                            text: '<i class="fa fa-file-text-o"></i>',
                            titleAttr: 'CSV'
                        }, {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o"></i>',
                            titleAttr: 'PDF',
                            orientation: 'landscape',
                            pageSize: 'LEGAL'
                        }],
                        "fnDrawCallback": function( oSettings ) {
                            $("#tbl-report").fadeIn("fast");
                        }
                    });
                    tableReport.columns().every( function () {
                        var that = this;
                        $('input', this.header()).on('keyup change',function(){
                            if(that.search() !== this.value){
                                that
                                    .search( this.value )
                                    .draw();
                            }
                        });
                    } );
                }
            }

            function drawAllUserExist(){
                $("#tbl-report").fadeOut("fast");
                $.ajax({
                    url: "<?php print $this->strAction; ?>&data=true",
                    type: "POST",
                    beforesend:function(){
                        dw.openLoading();
                    },
                    success: function(response){
                        dw.closeLoading();
                        arrDataUsers = response.users;
                        drawTable();
                    },
                    error: function(){
                        dw.alertDialog("Ocurrió un problema al cargar los usuarios porfavor intentelo de nuevo");
                        dw.closeLoading();
                    }
                });
            }

            function reloadAjaxTable(){
                if(tableReport != null){
                    $('#tbl-report').dataTable().fnDestroy();
                    drawAllUserExist();
                }
            }

            function getInfoUserSelected(idSelected){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getUserSelected",
                    type: "POST",
                    data: {
                        uidSelected: idSelected
                    },
                    beforesend: function(){
                        dw.openLoading();
                    },
                    success: function(response){
                        dw.closeLoading();
                        $.each(response.users,function(keyUser,valUser){
                            var tags = valUser.tags;
                            uIdSelected = valUser.uid;
                            chargeUser(valUser,tags);
                        });
                    },
                    error: function(){
                        dw.alertDialog("Ha ocurrido un problema porfavor intentelo de nuevo");
                        dw.closeLoading();
                    }
                })
            }

            function chargeUser(data,tags){
                boolUserExist = true;
                drawFormCreateNewUser(true);
                // =====Cargamos datos del usuario seleccionado
                $("input[name='iNames']").val(data.nombres);
                $("input[name='iLast']").val(data.apellidos);
                $("input[name='iPhone']").val(data.tel_cel);
                $("input[name='iMail']").val(data.email);
                $("input[name='chkSex']").each(function(){
                    if($(this).val() == data.sex){
                        $(this).prop("checked",true);
                    }
                });
                $("input[name='iUser']").val(data.name);
                $("input[name='iUserid']").val(data.uid);
                $("input[name='chkActive']").each(function(){
                    if($(this).val() == data.active){
                        $(this).prop("checked",true);
                    }
                });
                if(data.allow_multi_session != "N"){
                    $("input[name='chkMulti']").attr("checked","checked");
                }
                if(data.profile_id) $("select[name='sltProfiles']").val(data.profile_id);
                if(data.rol_id){
                    $("select[name='selectRolUser']").val(data.rol_id);
                    rolAsign($("#selectRolUser"));
                }

                $.each(tags,function(key,val){
                    var strContTags = $("#contTypeClient");
                    var bubbleColor = $("<div></div>").attr({
                        "id": "strBubble_" + val.id,
                        "class": "strBubble",
                    }).css({
                        "background": "#" + val.color,
                        "max-height": "25px",
                        "border-radius": "8px",
                        "margin-top": "10px"
                    }); strContTags.append(bubbleColor);
                    var strBtnCloseTag = $("<button>x</button>").attr({
                        "name": "btnCloseTag",
                        "type": "button",
                        "id": "strBtnCloseTag_" + val.id,
                        "class": "strBtnCloseTag"
                    }).css({
                        "float": "right",
                        "background": "transparent",
                        "border": "none",
                        "margin-right": "5px"
                    }); bubbleColor.append(strBtnCloseTag);
                    var strNameTag = $("<div>" + val.tag + "</div>").attr("class", "strTxtBubbleTag").css("padding","0 0 0 15px"); bubbleColor.append(strNameTag);
                    objTags[val.id] = true;
                    createInputTags();
                    $("#strBtnCloseTag_"+val.id).on("click",function(){
                        $("#strBubble_"+val.id).addClass("hide");
                        bubbleColor.remove();
                        delete objTags[val.id];
                        createInputTags();
                    });

                });

                objRolesCharge.father = data.father;
                objRolesCharge.childs = data.hijos;
                objRolesAsign[objRolesCharge.father.uid] = true;
                if(objRolesCharge.father.uid){
                    arrUsersFather.push(objRolesCharge.father.uid);
                }
                $.each(objRolesCharge.childs,function(key,val){
                    arrUserAsign.push(val.uid);
                    objRolesAsign[val.uid] = true;
                    createInputTags();
                })
            }

            function drawInfoReportInTable(cnt,valUsers) {
                var tr = $("<tr></tr>"); cnt.append(tr);
                tr.on("click",function(){
                    $("#cntFormSelected").removeClass("hide");
                    $("#cntReportUser").addClass("hide");
                    $("#tbl-report").addClass("hide");
                    getInfoUserSelected(valUsers.uid);
                });
                var td = $("<td>"+valUsers.name+"</td>"); tr.append(td);
                td = $("<td>"+valUsers.nombres+"</td>"); tr.append(td);
                td = $("<td>"+valUsers.apellidos+"</td>"); tr.append(td);
                td = $("<td>"+valUsers.email+"</td>"); tr.append(td);

                if(valUsers.profile_access){
                    var td = $("<td>"+valUsers.profile_access+"</td>"); tr.append(td);
                }
                else{
                    var td = $("<td>~ No definido ~</td>"); tr.append(td);
                }
                if(valUsers.rol_name){
                    var td = $("<td>"+valUsers.rol_name+"</td>"); tr.append(td);
                }
                else{
                    var td = $("<td>~ No definido ~</td>"); tr.append(td);
                }
                if(valUsers.tags){
                    var td = $("<td>"+valUsers.tags+"</td>"); tr.append(td);
                }
                else{
                    var td = $("<td>~ No definido ~</td>"); tr.append(td);
                }

                td = $("<td>"+valUsers.active+"</td>"); tr.append(td);


                td = $("<td>"+valUsers.lastvisit+"</td>"); tr.append(td);
                td = $("<td>"+valUsers.logs+"</td>"); tr.append(td);

            }

            function getUserPass(boolChangePass){
                var name = $("#iNames").val();
                var lastname = $("#iLast").val();
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=suggestion",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        name: name,
                        lastname: lastname
                    },
                    success: function(data){
                        if(data.status == "ok"){
                            if(boolChangePass == true){
                                $("#iPass").val(data.password);
                                $("#cntStrPassSug").html("Contraseña Sugerida: <p class='strSuggestionPass'>" + data.password + "</p>");
                                $("#cntStrPassSug").css("float","none");
                                $(".strUserChangePass").css({
                                    "margin-top": "29px"
                                 });
                            }
                            else{
                                strPass = data.password;
                                $("#iUser").val(data.username);
                                $("#iPass").val(data.password);
                                $("#cntStrPassSug").html("Contraseña Sugerida: <p class='strSuggestionPass'>" + data.password + "</p>");
                            }
                        }
                    }
                });
            }

            function saveUser(){
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=saveUser",
                    dataType: "JSON",
                    type: "POST",
                    data: $("#frmUsers").serialize() + "&boolEdit=" + boolUserExist,
                    beforeSend: function(){
                        dw.openLoading();
                    },
                    success: function(data){
                        dw.closeLoading();
                        if(data.status == "ok"){
                            dw.alertDialog(data.msj,"",true);
                        }
                    },
                    error: function(){
                        dw.closeLoading();
                    }
                });
            }

            function rolAsign(elmnt){
                var rolAsign = $(elmnt).val();
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=rolAsign",
                    type: "POST",
                    data: {
                        rol: rolAsign
                    },
                    beforeSend:function(){
                        dw.openLoading();
                    },
                    success: function(data){
                        dw.closeLoading();
                        if(data.status == "ok"){
                            var divCnt = $("<div></div>").attr({
                                "class": "col-xs-12",
                                "id": "cntParents"
                            }); $("#cntAddParents").html(divCnt);
                            drawSelectFatherUser(data.father,divCnt);
                            drawSelectChildUser(data.childs,divCnt);
                        }
                    },
                    error: function(){
                        dw.closeLoading();
                    }
                })
            }

            function drawSelectFatherUser(objFather,cnt){
                var divCntSearch = $("<div></div>").attr({
                    "class": "col-xs-12"
                }).css({
                    "margin": "0",
                    "padding": "0"
                }); cnt.append(divCntSearch);
                $.each(objFather,function(key,val){
                    var inputSearch = $("<input>").attr({
                        "type": "search",
                        "class": "form-control",
                        "id": "inputSrch_"+val.father,
                        "placeholder": "Asignar "+val.father,
                        "onkeyup": "drawSrchUserRol(this)"
                    });
                    var cntResponse = $("<div></div>").attr({
                        "id": "cntResult_"+val.father,
                        "class": "hide"
                    }).css({
                        "max-height": "130px",
                        "overflow-y": "auto",
                        "border": "1px solid",
                        "margin-left": "3%",
                        "width": "98%"
                    });
                    var cntTable = $("<section></section>").css({
                        "max-height": "110px",
                        "overflow": "auto",
                        "width": "98%",
                        "margin-left": "4%"
                    });
                    var table = $("<table></table>").attr("id", "tblResult_"+val.father);
                    if(val.father != ""){
                        strFatherAsign = val.father;
                        divCntSearch.append(inputSearch);
                        divCntSearch.append(cntResponse);
                        divCntSearch.append(cntTable);
                        cntTable.append(table);
                        inputSearch.on("click",function(){
                            cntResponse.removeClass("hide");
                            drawSrchUserRol($(this));
                        });
                    }
                });

            }

            function drawSelectChildUser(objChilds,cnt){
                var divCntSearch = $("<div></div>").attr({
                    "class": "col-xs-12"
                }).css({
                    "margin": "0",
                    "padding": "0"
                }); cnt.append(divCntSearch);
                $.each(objChilds,function(key,val){
                    var cntInputSrchRol = $("<div></div>").attr("class","col-xs-12").css({
                        "margin": "0",
                        "padding": "0"
                    });
                    divCntSearch.append(cntInputSrchRol);
                    var inputSearch = $("<input>").attr({
                        "type": "search",
                        "class": "form-control",
                        "id": "inputSrch_"+val.name,
                        "placeholder": "Asignar "+val.name,
                        "onkeyup": "drawSrchUserRol(this)"
                    }).css({
                        "margin-top":"50px"
                    });
                    cntInputSrchRol.append(inputSearch);
                    var cntResponse = $("<div></div>").attr({
                        "id": "cntResult_"+val.name,
                        "class": "hide"
                    }).css({
                        "max-height": "130px",
                        "overflow-y": "auto",
                        "border": "1px solid",
                        "margin-left": "3%",
                        "width": "98%",
                        "cursor": "pointer"
                    });
                    cntInputSrchRol.append(cntResponse);
                    var cntTable = $("<section></section>").css({
                        "max-height": "110px",
                        "overflow": "auto",
                        "width": "98%",
                        "margin-left": "4%"
                    });
                    var table = $("<table></table>").attr("id", "tblResult_"+val.name,);
                    cntInputSrchRol.append(cntTable);
                    cntTable.append(table);

                    inputSearch.on("click",function(){
                        cntResponse.removeClass("hide");
                        drawSrchUserRol($(this));
                    });
                    inputSearch.on("change",function(){
                        cntResponse.addClass("hide");
                    });
                });
                assignRol();
            }

            function drawSrchUserRol(elmnt){
                var strCode = $(elmnt).attr("id");
                var remove = 10;
                var limit = 100;
                var strRol = strCode.substring(remove,limit);
                if(xhrRol) xhrRol.abort();
                var xhrRol = $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getRolSpecific",
                    type: "POST",
                    dataType: "JSON",
                    data: {
                        srchRol: strRol,
                        cointidity: $(elmnt).val()
                    },
                    success: function (data){
                        if(data.status == "ok"){
                            var cntResponseUsers = $(elmnt).parent().find("div");
                            cntResponseUsers.html("").removeClass("hide");
                            drawUserExistToRol(elmnt,data.users);
                        }
                    }
                })
            }

            function drawUserExistToRol(elmnt,dataUsers) {
                var cntResponseUsers = $(elmnt).parent().find("div");
                var divCntUsers = $("<div></div>"); cntResponseUsers.append(divCntUsers);
                if(dataUsers.length != 0){
                    $.each(dataUsers,function(keyUser,valUser){
                        // =========================== ESTE TIENE QUE TENER UNA CLASE DISTINTA SI YA SE ENCUENTRA DENTRO DEL OBJETO QUE DIBUJA LOS ELEMENTOS
                        var cntUserIndividual = $("<div></div>").attr({
                            "class": "col-xs-12 cntElmntRol",
                            "id": "cntUserRol_"+keyUser
                        }).css({
                            "border-bottom": "1px solid #d9d9d9",
                            "cursor": "pointer"
                        }); divCntUsers.append(cntUserIndividual);
                        var strUser = $("<p>"+valUser.nombres+" "+valUser.apellidos+"</p>").css({
                            "margin": "0",
                            "padding": "0",
                            "float": "left"
                        }); cntUserIndividual.append(strUser);
                        cntUserIndividual.on("click",function(){
                            var idSelected = valUser.uid;
                            if(valUser.swusertype == strFatherAsign){
                                if(arrUsersFather.length > 0){
                                    cntResponseUsers.addClass("hide");
                                    return false;
                                }
                                else {
                                    arrUsersFather.push(idSelected);
                                }
                            }
                            var typeSelected = arrUserAsign.includes(idSelected);
                            if(typeSelected == true){
                                cntResponseUsers.addClass("hide");
                                return false;
                            }

                            arrUserAsign.push(idSelected);
                            assigUserByRol(valUser,elmnt);
                        });
                        $("#inputSrch_"+valUser.swusertype).on("change",function(){
                            if(!cntUserIndividual.hasClass("hide")){
                                $("#cntResult_"+valUser.swusertype).addClass("hide");
                                cntUserIndividual.toggleClass("hide");
                            }
                        });
                        $(document).click(function(){
                            if(!cntUserIndividual.hasClass("hide")){
                                $("#cntResult_"+valUser.swusertype).addClass("hide");
                                cntUserIndividual.toggleClass("hide");
                            }
                        });
                    });
                }
                else{
                    cntResponseUsers.removeClass("hide");
                    var cntUserIndividual = $("<div></div>").attr("class", "col-xs-12").css("border-bottom", "1px solid #d9d9d9"); divCntUsers.append(cntUserIndividual);
                    var strUser = $("<p>No se encontraron resultados</p>").css({
                        "margin": "0",
                        "padding": "0",
                        "float": "left"
                    }); cntUserIndividual.append(strUser);
                    $(elmnt).on("change",function(){
                        cntResponseUsers.addClass("hide");
                        cntUserIndividual.toggleClass("hide");
                    });
                    $(document).click(function(){
                        cntResponseUsers.addClass("hide");
                        cntUserIndividual.toggleClass("hide");
                    });
                }
            }

            function assignRol(){
                if(objRolesCharge.father != undefined){
                    var valUser = objRolesCharge.father;
                    var cntTable = $("#tblResult_"+objRolesCharge.father.swusertype);
                    drawRolsAsign(valUser,cntTable);

                    $.each(objRolesCharge.childs,function(key,valUser){
                        var cntTableChilds = $("#tblResult_"+valUser.swusertype);
                        drawRolsAsign(valUser,cntTableChilds);
                    });
                }
                else{
                    return false;
                }
            }

            function drawRolsAsign(valUser,cnt){
                cnt.css("width","100%");
                objRolesAsign[valUser.uid] = true;
                var tr = $("<tr></tr>").attr({
                    "id": "trSelected_"+valUser.uid
                }).css({
                    "border": "2px solid #b7b2b2",
                    "border-radius": "4px",
                    "margin-top": "5px"
                }); cnt.append(tr);
                var td = $("<td></td>"); tr.append(td);
                var imgUser = $("<img />").attr({
                    "title": "" + valUser.nombres + " " + valUser.apellidos,
                    "src": "adm_main.php?mde=users&wdw=myaccount&op=avatar&uid=" + valUser.uid
                }).css({
                    "height": "25px",
                    "margin": "0 5px",
                    "width": "25px",
                    "border-radius": "50%",
                    "border": "1px solid #3598dc",
                    "float": "left"
                }); td.append(imgUser);
                td = $("<td></td>"); tr.append(td);
                var strUser = $("<p>"+valUser.nombres+ " " + valUser.apellidos+"</p>").css({
                    "margin":"0",
                    "padding":"0"
                }); td.append(strUser);
                var intUser = $("<input>").attr({
                    "type": "text",
                    "class": "hide",
                    "value": valUser.uid,
                    "name": "saveRolAsignUser[]"
                }).css({
                    "margin": "0",
                    "background": "transparent",
                    "border": "none"
                }); td.append(intUser);
                td = $("<td></td>"); tr.append(td);
                var btnClose = $("<button></button>").attr({
                    "class": "btn btn-danger fa fa-close",
                    "type": "button"
                }).css("float","right"); td.append(btnClose);
                btnClose.on("click",function(){
                    if(valUser.swusertype == strFatherAsign){
                        arrUsersFather = [];
                    }
                    delete objRolesAsign[valUser.uid];
                    tr.remove();
                    createInputTags();
                });
            }

            function assigUserByRol(valUser,cnt){
                // =========================== APENDIZAR SOLO SI NO SE ENCUENTRA DENTRO DEL OBJETO QUE YA SE ENVIARÁ
                var cntResponse = $(cnt).parent().find("div");
                $(cntResponse).addClass("hide");
                var cntTable = $(cnt).parent().find("table");
                cntTable.css("width","100%");
                objRolesAsign[valUser.uid] = true;
                var tr = $("<tr></tr>").attr({
                    "id": "trSelected_"+valUser.uid
                }).css({
                    "border": "2px solid #b7b2b2",
                    "border-radius": "4px",
                    "margin-top": "5px"
                }); cntTable.append(tr);
                var td = $("<td></td>"); tr.append(td);
                var imgUser = $("<img />").attr({
                    "title": "" + valUser.nombres + " " + valUser.apellidos,
                    "src": "adm_main.php?mde=users&wdw=myaccount&op=avatar&uid=" + valUser.uid
                }).css({
                    "height": "25px",
                    "margin": "0 5px",
                    "width": "25px",
                    "border-radius": "50%",
                    "border": "1px solid #3598dc",
                    "float": "left"
                }); td.append(imgUser);
                td = $("<td></td>"); tr.append(td);
                var strUser = $("<p>"+valUser.nombres+ " " + valUser.apellidos+"</p>").css({
                    "margin":"0",
                    "padding":"0"
                }); td.append(strUser);
                var intUser = $("<input>").attr({
                    "type": "text",
                    "class": "hide",
                    "value": valUser.uid,
                    "name": "saveRolAsignUser[]"
                }).css({
                    "margin": "0",
                    "background": "transparent",
                    "border": "none"
                }); td.append(intUser);
                td = $("<td></td>"); tr.append(td);
                var btnClose = $("<button></button>").attr({
                    "class": "btn btn-danger" +
                    " fa fa-close",
                    "type": "button"
                }).css("float","right"); td.append(btnClose);
                btnClose.on("click",function(){
                    if(valUser.swusertype == strFatherAsign){
                        arrUsersFather = [];
                    }
                    removeItemFromArr( arrUserAsign, valUser.uid );
                    delete objRolesAsign[valUser.uid];
                    tr.remove();
                    createInputTags();
                });
            }

            function justNumbers(e,elmnt){
                var keynum = window.event ? window.event.keyCode : e.which;
                if ((keynum == 8) || (keynum == 46))
                    return true;
                if(elmnt){
                    if(e.which == 13){
                        $(elmnt).blur();
                    }
                }
                return /\d/.test(String.fromCharCode(keynum));
            }

        </script>

        <?php
    }

    public function draw(){
        draw_header($this->lang["ADM_USERS"]);
        theme_draw_centerbox_open($this->lang["ADM_USERS"]);
        jquery_includeLibrary("datatables");
        $this->styles();
        ?>
        <link rel="stylesheet" type="text/css" href="core/jquery/datatables/ext/buttons.dataTables.min.css" />
        <script type="text/javascript" src="core/jquery/datatables/ext/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/buttons.flash.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/jszip.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/pdfmake.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/vfs_fonts.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/buttons.html5.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/buttons.print.min.js"></script>

        <div id="cntBtnSelectedAct" class="col-xs-12"></div>

        <div class="col-xs-12" id="cntFormSelected"></div>
        <div class="col-xs-12 " id="cntReportUser">
            <button type="button" class="btn btn-default" style="float: right;min-width: 180px;" onclick="applyFilterDateNull()" id="btnFilterUserNoAccess">Usuarios sin Ingreso</button>
            <br><br>
            <button type="button" class="btn btn-default" style="float: right;min-width: 180px;" onclick="drawTable(true)" id="btnFilterMonthInactive">Usuarios + de 2M inactivos</button>
            <table class="table table-striped" id="tbl-report">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Perfil de Acceso <br><input class="form-control" type="text"></th>
                        <th>Puesto <br><input class="form-control"  type="text"></th>
                        <th>Tipo de Cliente <br><input class="form-control"  type="text"></th>
                        <th>Activo <br><input class="form-control"  type="text"></th>
                        <th>Fecha ultimo ingreso <br></th>
                        <th>Logs <br><input class="form-control"  type="text"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <!-- Modal para los tags -->
        <div class="modal fade" id="mdlTags" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="btn-search">
                                    <input type="text" placeholder="Buscar categoría" id="searchTags">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </div>
                                <div class="col-lg-12 div-search-tags div-result" id="mdl-seach-tag"></div>
                                <div class="col-lg-12 text-center">
                                    <button type="button" class="btn btn-primary" onclick="cleanFrmTag();">Crear una categoría nueva</button>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <form>
                                    <div class="form-group">
                                        <label for="iTags">Nombre</label>
                                        <input type="text" class="form-control" name="iTag" id="iTag" required autocomplete="off">
                                        <input type="hidden" id="iTagColor" value="">
                                        <input type="hidden" id="idTag" value="0">
                                    </div>
                                    <div class="form-group">
                                        <div id="colorPicker" class="colorPicker_select">
                                            <div></div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-12 text-center">
                                <button type="button" class="btn btn-primary" onclick="saveTag();">GUARDAR</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        jquery_includeLibrary("colorpicker");
        $this->scripts();
        theme_draw_centerbox_close();
        draw_footer();
    }

    public function drawOnline($arrUsers = array()){
	    draw_header();
	    if (!$_SESSION["wt"]["logged"]) {
		    theme_draw_centerbox_open( $this->lang["USERS_ONLINE"] );
		    echo "<span class=\"error\">".$this->lang["NOT_LOGGED_ONLINE"]."</span>";
		    theme_draw_centerbox_close();
	    }
	    else{
		    theme_draw_centerbox_open( $this->lang["USERS_ONLINE"] );
		    ?>
            <div class="row">
                <div class="col-lg-12">
                    <ul class="list-group">
                        <?php
                        if(count($arrUsers)>0){
                            foreach($arrUsers AS $key => $user ){
                                ?>
                                <li class="list-group-item col-sm-6 col-md-3 col-lg-3">
                                    <?php
                                    echo $user["name"];
                                    ?>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
		    <?php
		    theme_draw_centerbox_close();
        }
	    draw_footer();
    }
}