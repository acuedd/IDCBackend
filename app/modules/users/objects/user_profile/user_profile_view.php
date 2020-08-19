<?php
/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 15/02/2017
 * Time: 09:56
 */
include_once("core/global_config.php");
class user_profile_view extends global_config implements window_view{
    private $strAction = "";
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

    public function setStrAction($strAction)
    {
        $this->strAction = $strAction;
    }

    public function draw()
    {
        draw_header($this->lang["ADM_USERACCESS_ADMIN_PROFILE"]);
        theme_draw_centerbox_open($this->lang["ADM_USERACCESS_ADMIN_PROFILE"]);
        $this->styles();
        ?>
        <div class="col-lg-10 col-md-offset-1">
            <form id="frmAccess">
                <div id="contSelect" class="col-lg-4">
                    <select id="selectB" class="form-control" name="sltProfile" onchange="getProfiles(this);">
                        <option value="0"></option>
                    </select>
                </div>
                <div id="frmContCreateNew" class="col-lg-12"></div>
                <div class="form-group col-lg-6 col-md-12">
                    <label id="" class="titleCreatePorfile hide">Nombre Perfil</label>
                    <input id="profile_name" class="form-control secCreatePorf hide" name="name" type="text" required>
                </div>
                <div class="form-group col-lg-6 col-md-12">
                    <label id="" class="titleCreatePorfile hide">Descripción</label>
                    <textarea id="profile_description" class="form-control secCreatePorf hide" name="desc". style="height: 35px;" required></textarea>
                </div>
                <div id="contAccordion" class="contProfAcc col-lg-12">
                    <div class="panel-group" id="acc-access" role="tablist" aria-multiselectable="true"></div>
                </div>
                <div class="col-lg-12 text-center">
                    <button id="btnSave" onclick="saveProfile()" type="button" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        Guardar
                    </button>
                    <button id="btnDelete" onclick="deleteProfile()" type="button" class="btn btn-danger hide">
                        <i class="fa fa-trash"></i>
                        Eliminar
                    </button>
                </div>
            </form>
        </div>
    <?php
        $this->script();
        theme_draw_centerbox_close();
        draw_footer();
    }

    public function styles()
    {
        ?>
        <style>
            #mdlWindowAccess_body{
                display: table;
            }
            .titleCreatePorfile {
                color: #2E4E78;
                font-size: 20px;
            }
            .btnCreateNewProfile{
                margin-top: 25px;
                margin-bottom: 5px;
                background: #3fa1e0;
                border: none;
                color: white;
                border-radius: 2px;
                padding: 8px 42px;
            }
            .secCreatePorf {
                border: 1px solid #B4B5B7;
                border-radius: 3px;
            }
            .panel-heading{
                border-radius: 0;
                border-left: 1px solid #ed8929;
                background: #ed8929;
                padding-top: 5px;
                padding-bottom: 5px;
            }
            .panel-default>.panel-heading {
                color: #333;
                background-color: #f4f4f4;
                border-radius: 0;
                /*border-left: 12px solid #39c2a8;*/
            }
            .titleAcordion {
                font-size: 18px;
            }
            .tabDatos {
                padding: 5px 20px;
                font-size: 17px;
            }
            .chkAll {
                margin-top: 10px;
            }
            .txtChkAll{
                margin-left: 15px;
                margin-top: 15px;
            }
            .filaProfiles:nth-child(odd){
                background: #f6f6f6;
            }
            .filaProfiles:nth-child(even){
                background: white;
            }
            .contProfAcc{
                margin-top: 30px;
            }
            @media(max-width: 1200px){
                .contAccordionUser{
                    margin-top: 170px;
                }
            }
            @media(max-width: 990px){
                .contAccordionUser{
                    margin-top: 30px;
                }
            }
            @media (max-width: 900px) {
                .btnCreateNewProfile{
                    width: 60%;
                }
            }
        </style>
        <?php
    }

    public function script()
    {
        ?>
        <script>
            let wd = new drawWidgets();
            let dataAcc = false;
            let idInt = false;
            const arrColores = [
                '#39c2a8', '#A9CCE3', '#FAD7A0', '#b388ff', '#ed8929',
                '#4598e8', '#1ABC9C', '#5B2C6F', '#2E4053', '#FDFEFE',
                '#323f76', '#F5B041', '#E6B0AA', '#F1C40F', '#F0F3F4',
                '#E6B0AA', '#1C2833', '#F5B041', '#FAD7A0', '#229954',
                '#f48fb1', '#CD6155', '#1ABC9C', '#1F618D', '#1C2833',
                '#e57373', '#EBDEF0', '#922B21', '#154360', '#FAD7A0',
                '#ba68c8', '#39c2a8', '#C39BD3', '#641E16', '#2980B9',
                '#bbdefb', '#ed8929', '#b388ff', '#9B59B6', '#E6B0AA',
                '#5c6bc0', '#e57373', '#4598e8', '#f48fb1', '#76448A',
                '#b388ff', '#323f76', '#922B21', '#39c2a8', '#512E5F'];
            const lang = {
                'ERROR_AJAX': '<?php print $this->lang["ERROR_AJAX"] ?>',
            };
            const strAction = '<?php print $this->strAction; ?>';

            $(document).ready( () => {
                getProfilesInitial(drawProfiles);
                AdmProfiles();
            });

            function getProfilesInitial(callback) {
                fetch(`${strAction}&op=profiles`)
                    .then((response) => {
                        return response.json();
                    })
                    .then((data) => {
                        if (data.status === 'ok') {
                            if(typeof drawProfiles === "function"){
                                return callback(data.profiles);
                            }
                            else{
                                return data.profiles;
                            }
                        }
                    })
                    .catch(() => {
                        return {};
                    });
            }

            function drawProfiles(objProfiles)
            {
                const container = $("#selectB");
                if(Object.keys(objProfiles).length){
                    for(let key in objProfiles){
                        const data = objProfiles[key];
                        const name = data.nombre;
                        const option = `<option value="${key}">${name}</option>`;
                        container.append(option);
                    }
                }
            }

            function AdmProfiles() {
                $.ajax({
                    url: `${strAction}&op=access`,
                    type: 'POST',
                    dataType: 'JSON',
                    beforeSend: () => {},
                    success: function (datos) {
                        if (datos.status == "ok") {
                            const frmContBtn = $("#frmContCreateNew");
                            const btnCreateNewProfile = $("<button>Crear nuevo perfil de acceso</button>").attr({
                                "type": "button",
                                "name": "btnCreateNewProfile",
                                "class": "btnCreateNewProfile",
                                "onclick": "showTextArea()"
                            });
                            frmContBtn.append(btnCreateNewProfile);
                            const objDataAccess = datos.access;

                            if(Object.keys(datos.categories).length)
                                objDataAccess['Categorias'] = datos.categories;
                            if(Object.keys(datos.categoriesMovil).length)
                                objDataAccess['Categorias Moviles'] = datos.categoriesMovil;

                            dataAcc = objDataAccess;

                            drawAccordionAccessGroup(objDataAccess);
                        }
                    },
                    error: () => {}
                });
            }

            function drawAccordionAccessGroup(objDataAccess)
            {
                let intAcc = 0;
                idInt = intAcc;
                var cntAll = $("#acc-access");
                for(let key in objDataAccess){
                    const data = objDataAccess[key];
                    const strKeyTMP = key.split(" ");
                    const strKey = strKeyTMP.join("");

                    const elementPanel = `  <div class="panel panel-default">
                                                <div class="row panel-heading" role="tab" id="acc-heading-${intAcc}" style="border-left: 12px solid ${arrColores[intAcc]}">
                                                    <label class="panel-title">
                                                        <a role="button" class="titleAcordion" data-toggle="collapse" data-parent="acc-access" href="#acc-content-${intAcc}" aria-controls="acc-content-${intAcc}">
                                                            <strong>
                                                                ${key}
                                                            </strong>
                                                        </a>
                                                    </label>
                                                </div>
                                                <div id="acc-content-${intAcc}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="acc-heading-${intAcc}">
                                                    <div>
                                                        <label id="tr_${strKey}" class="col-xs-12" name="${key}">
                                                            <div class="col-xs-1">
                                                                <input type="checkbox" class="chkAll" id="chkall_${strKey}" name="chkAll" style="margin-left: 15px; margin-top: 15px;" />
                                                            </div>
                                                            <div class="txtChkAll col-lg-4 col-xs-8" style="padding-left: 15px">
                                                                Marcar / Desmarcar Todo
                                                            </div>
                                                        </label>
                                                    </div>
                                                    <div class="panel-body" id="contTable_${intAcc}"></div>
                                                </div>
                                            </div>`;
                    $(cntAll).append(elementPanel);

                    const chkAll = $(`#chkall_${strKey}`);
                    chkAll.on('click', (e) => {
                        e = e.target;
                        const name = $(e).attr("id");
                        const arrName = name.split("_");
                        if($(e).is(":checked"))
                            $(`.profile_${arrName[1]}`).attr("checked",true);
                        else
                            $(`.profile_${arrName[1]}`).attr("checked",false);
                    });
                    const cntBody = $(`#contTable_${intAcc}`);
                    drawIndividualCheckListAccess(cntBody, data, strKey);

                    intAcc++;
                }
            }

            function drawIndividualCheckListAccess(cntBody, data, strKey)
            {
                for(let id in data){
                    const infoProfile = data[id];
                    if(infoProfile.class){
                        const elementRow = `<label id="${infoProfile.class}" class="filaProfiles col-xs-12">
                                                <div class="col-xs-1">
                                                    <input type="checkbox" class="profile_${strKey} ${infoProfile.clean}" name="access[]" value="${infoProfile.class}" />
                                                </div>
                                                <div class="tabDatos col-xs-4">
                                                    ${infoProfile.class}
                                                </div>
                                                <div class="tabDatos col-xs-7">
                                                    ${infoProfile.description}
                                                </div>
                                            </label>`;
                        $(cntBody).append(elementRow);
                    }
                    else if(infoProfile.type == "movil"){
                        const elementRow = `<label id="${infoProfile.category_code}" class="filaProfiles col-xs-12">
                                                <div class="col-xs-1">
                                                    <input type="checkbox" class="profile_${strKey} input_movil_${infoProfile.id_category}" name="categoryMovil[]" value="${infoProfile.id_category}" />
                                                </div>
                                                <div class="tabDatos col-xs-4">
                                                    ${infoProfile.category_code}
                                                </div>
                                                <div class="tabDatos col-xs-7">
                                                    ${infoProfile.category_name}
                                                </div>
                                            </label>`;
                        $(cntBody).append(elementRow);
                    }
                    else if (infoProfile.id_category){
                        const elementRow = `<label id="${infoProfile.category_code}" class="filaProfiles col-xs-12">
                                                <div class="col-xs-1">
                                                    <input type="checkbox" class="profile_${strKey} input_${infoProfile.id_category}" name="category[]" value="${infoProfile.id_category}" />
                                                </div>
                                                <div class="tabDatos col-xs-4">
                                                    ${infoProfile.category_code}
                                                </div>
                                                <div class="tabDatos col-xs-7">
                                                    ${infoProfile.category_name}
                                                </div>
                                            </label>`;
                        $(cntBody).append(elementRow);
                    }
                }
            }

            function showTextArea(){
                document.getElementById('selectB').options.selectedIndex = 0;
                $("input[type='checkbox']").attr("checked", false);
                $(".titleCreatePorfile").removeClass("hide");
                $("#profile_name").removeClass("hide");
                $("#profile_description").removeClass("hide");

                $("#contAccordion").addClass("contAccordionUser");

                $("input[type='text']").val(" ");
                $(".panel-collapse").removeClass("in");
                $("textarea").val(" ");
                $("#btnDelete").addClass("hide");
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

            function saveProfile()
            {
                $.ajax({
                    url: `${strAction}&op=save`,
                    type: "POST",
                    data: $("#frmAccess").serialize(),
                    beforeSend : () => {
                        wd.openLoading();
                    },
                    success: (data) => {
                        wd.closeLoading();
                        if(data.status === "ok")
                            wd.alertDialog(data.msj, false, true);
                        else
                            wd.alertDialog(data.msj);
                    },
                    error: () => {
                        wd.alertDialog("Hubo un problema con la comunicación, intente de nuevo")
                    }
                });
            }

            function getProfiles(obj){
                if($(obj).val() != 0){
                    $(".titleCreatePorfile").removeClass("hide");
                    $("#profile_name").removeClass("hide");
                    $("#profile_description").removeClass("hide");
                    $("#btnDelete").removeClass("hide");

                    $("#contAccordion").addClass("contAccordionUser");

                    $.ajax({
                        type : "POST",
                        url : `${strAction}&op=detail`,
                        data : {
                            id : $(obj).val()
                        },
                        dataType : "JSON",
                        beforeSend : () => {
                            wd.openLoading();
                        },
                        success : (data) => {
                            wd.closeLoading();
                            if(data.status == "ok"){
                                $("#profile_description").val(data.detail.descripcion);
                                $("#profile_name").val(data.detail.nombre);
                                if(Object.keys(data.access).length){
                                    drawCheckedExistAccess(data.access);
                                }

                                if(Object.keys(data.categories).length){
                                    drawCheckedExistCategories(data.categories);
                                }
                            }
                            else{
                                wd.alertDialog(data.msj)
                            }
                        },
                        error : () => {
                            wd.closeLoading();
                            wd.alertDialog("Hubo un problema al cargar, intente de nuevo");
                        }
                    });
                }
                else{
                    $("#btnDelete").addClass("hide");
                    $(".titleCreatePorfile").addClass("hide");
                    $("#profile_name").addClass("hide");
                    $("#profile_description").addClass("hide");
                }
            }

            function drawCheckedExistAccess(objAccess)
            {
                let strClassToIn = '';
                for(let key in objAccess){
                    const val = objAccess[key];
                    if(strClassToIn === '')
                        strClassToIn = val;
                    $("."+val).attr("checked",true);
                }
                $(`.${strClassToIn}`).parent().parent().parent().parent().addClass("in");
                $("#selectB").on("click", () => {
                    $("input[type='checkbox']").attr("checked", false)
                });
            }

            function drawCheckedExistCategories(objCategories)
            {
                let strClassToIn = '';
                for(let key in objCategories){
                    const val = objCategories[key];
                    if(val.type == "movil"){
                        strClassToIn = "movil_"+val.id_category;
                    }
                    else{
                        strClassToIn = val.id_category;
                    }
                    $(".input_"+strClassToIn).attr("checked",true);
                    $(`.input_${strClassToIn}`).parent().parent().parent().parent().addClass("in");
                }

                $("#selectB").on("click", () => {
                    $("input[type='checkbox']").attr("checked", false)
                });
            }

            function deleteProfile(){
                var slt = $("#selectB").val();
                if(slt > 0){
                    $.ajax({
                        type : 'POST',
                        url : `${strAction}&op=delete`,
                        data : {
                            id : slt
                        },
                        dataType : 'JSON',
                        beforeSend : function(){
                            wd.openLoading();
                        },
                        success : function(data){
                            wd.closeLoading();
                            if(data.status == "ok"){
                                wd.alertDialog(data.msj,false,true);
                            }
                            else{
                                wd.alertDialog(data.msj);
                            }
                        },
                        error : function(){
                            wd.closeLoading();
                            wd.alertDialog("Hubo un problema al cargar, intente de nuevo");
                        }
                    });
                }
            }

        </script>
        <?php
    }

}