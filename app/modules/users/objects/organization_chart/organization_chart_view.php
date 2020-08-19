<?php
/**
 * Created by PhpStorm.
 * User: NelsonMatul-DEV
 * Date: 7/05/2018
 * Time: 8:02 PM
 */
include_once("core/global_config.php");
class organization_chart_view extends global_config implements window_view{
    private static $_instance;
    private $strAction;

    public function __construct($arrParams){
        parent::__construct($arrParams);
        $this->intUid = $_SESSION["wt"]["uid"];
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

        draw_header($this->lang["USER_ORGANIZATION_CHART"]);
        theme_draw_centerbox_open($this->lang["USER_ORGANIZATION_CHART"]);
        jquery_includeLibrary("datatables");
        $this->styles();
        $this->scripts();
        ?>
        <link rel="stylesheet" type="text/css" href="core/jquery/datatables/ext/buttons.dataTables.min.css" />
        <script type="text/javascript" src="core/jquery/datatables/ext/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/buttons.flash.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/jszip.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/pdfmake.min.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/vfs_fonts.js"></script>
        <script type="text/javascript" src="core/jquery/datatables/ext/buttons.html5.min.js"></script>


        <script>
            $(".icon").click(function() {
                var icon = $(this),
                    input = icon.parent().find("#search"),
                    submit = icon.parent().find(".submit"),
                    is_submit_clicked = false;

                // Animate the input field
                input.animate({
                    "width": "165px",
                    "padding": "10px",
                    "opacity": 1
                }, 300, function() {
                    input.focus();
                });

                submit.mousedown(function() {
                    is_submit_clicked = true;
                });

                // Now, we need to hide the icon too
                icon.fadeOut(300);

                // Looks great, but what about hiding the input when it loses focus and doesnt contain any value? Lets do that too
                input.blur(function() {
                    if(!input.val() && !is_submit_clicked) {
                        input.animate({
                            "width": "0",
                            "padding": "0",
                            "opacity": 0
                        }, 200);

                        // Get the icon back
                        icon.fadeIn(200);
                    };
                });
            });
        </script>
        <div class="tutuloje" >
            <div class="titoloe" >
            <h3 class="text-title">Esquema Jerárquico de Usuario</h3>
            </div>
        </div>
            <div id="cntAllOrganizationChart" class="col-xs-12 col-md-10 col-md-offset-1">
                <h3 class="text-title">Esquema Jerárquico de Usuario</h3>
            </div>
            <div id="cntAllUsersNotHaveFather" class="col-xs-12">
                <div class="col-xs-12 col-md-12">
                    <h3 class="text-title">Roles de Usuarios</h3>
                    <div id="cntRolesList"></div>
                    <button class="btn btn-warning vertodos" onclick="serchcler()" >&nbsp Ver todos</button>

                </div>

                <div class="container">

                </div>


                <div class="desfamily" style="top: 20px;margin-bottom: 30px;">

                    <div class="row col-xs-12" style="margin-left: 18%; height: 50px">
                        <input name="remitosucursal" id="remitosucursal" class="form-control" style="width:55%; position: relative;float: left" onkeyup="buscarGetUser()" type="text" required  placeholder="Buscar" class="col-xs-6">
                        <span class="input-group-addon" style="width: 30px;height:34px;color:#f39c12;background: transparent " >
                            <i class="fa fa-search" style="position: relative; display: inline-block"></i>
                        </span>
                    </div>

                    <div style=" margin-bottom: 5px;height: 90px;">
                    <h3 class="text-title" style="padding: 45px">Usuarios sin familia</h3>
                    </div>

                    <table class="table table-striped" id="tbl-report">
                        <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Puesto</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div id="cntUserNotHaveFather" style="margin-bottom: 25px"></div>

                </div>

            </div>



        <?php

        theme_draw_centerbox_close();
        draw_footer();
    }

    public function styles(){
        ?>
            <style type="text/css">
                .tutuloje{
                    width: 100%;
                    position: relative;
                    height: 60px;
                    padding: 5px;

                }
                .titoloe{
                    width: 84%;
                    height: 50px;
                    background: #7b7e83;
                    margin: auto;
                    color:white;
                    border: 1px solid #2e4e78;
                    border-radius: 10px;
                                    }
                .titoloe h3{
                    margin-top: 11px;
                }
                .text-title{
                    text-align: center;
                }
                #cntUserNotHaveFather{
                    overflow: auto;
                    max-height: 350px;

                }

                #cntAllOrganizationChart{
                    margin-top: 5px;
                    overflow: auto;
                    border: 2px solid #2e4e78;
                    border-radius: 4px;
                }
                .google-visualization-orgchart-node-medium{
                    background: white;
                    border-radius: 4px;
                    padding:5px 15px;
                }
                .google-visualization-orgchart-node {
                    border: none;
                    -webkit-box-shadow: rgba(0, 0, 0, 0.2) 1px 1px 1px 1px;
                }
                .pointer-list{
                    text-align: center;
                    width: 20%;
                    height: 55px;
                    position: relative;
                    border-radius: 8px;
                    float: left;
                    margin: 15px;
                }
                .vertodos{
                    height: 53px;
                    width: 20%;
                    text-align: center;
                    border-radius: 8px;
                    position: relative;
                    float: left;
                    left:14px;
                    top: 14px;
                    margin:1px;
                    box-shadow:2px 2px 5px #999;
                }

            </style>
        <?php
    }

    public function scripts(){
        ?>
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <script type="application/ecmascript">
                let dw = new drawWidgets();
                let arrColores = [
                    '#A9CCE3', '#bfab71', '#6093ad', '#3a5468', '#545659','#39c2a8', '#512E5F',
                    '#A569BD', '#48C9B0', '#229954', '#D35400', '#909497','#1F618D', '#78281F'];
                let objDataUsers = null;
                let objDataUsersS = null;
                let nom = null;
                let rolls = null;
                let tableReport = null;
                let dataSet = null;
                let color = "#";
                let strSearchs = null;
                let newname = null;
                let dowinpt = null;

                $(document).ready( () => {
                    google.charts.load('current', {packages:["orgchart"]});
                    google.charts.setOnLoadCallback(getUsersOrganization);

                    getRoles();
                });

                function initChart(){

                    let data = new google.visualization.DataTable();
                    data.addColumn('string', 'Name');
                    data.addColumn('string', 'Manager');
                    data.addColumn('string', 'Description');
                    //data.addColumn('string', 'ToolTip');
                    return data;
                }

                function getUsersOrganization(){
                    $("#tbl-report").fadeOut("fast");
                    $.ajax({
                        url: "<?php print $this->strAction; ?>&op=getOrganizationChart",
                        type: "POST",
                        dataType: "JSON",
                        beforeSend: () => {
                            dw.openLoading();
                        },

                        success: (response) => {

                            dw.closeLoading();
                            if(response.status == "ok"){
                                let dataTable = initChart();
                                objDataUsers = response.users;
                                addRowChart(dataTable, response.users);
                                drawTable(strSearchs);
                            }
                        },
                        error: () => {
                            dw.closeLoading();
                            dw.alertDialog("Ocurrió un problema al obtener la información, por favor intentelo de nuevo.")
                        }
                    });

                    }

                function addRowChart(datatable, value){

                    objDataUsersS = objDataUsers;
                    dataSet = objDataUsers;
                    $.each(value,function (key,val) {

                        let intFather = val.father;

                            let nameUser = val.nombres + " " + val.apellidos + "_" + val.id_usertype;

                            if (intFather == "0") {
                                intFather = "";
                            }
                            let boolUserHaveChild = false;
                            if (intFather == "") {
                                boolUserHaveChild = userHaveChild(val.uid);
                            }
                            else {
                                boolUserHaveChild = true;
                            }

                            if (boolUserHaveChild) {

                                datatable.addRows([
                                    [val.uid, intFather, nameUser]
                                ]);
                            }
                            else {
                                drawUsersNotHaveFather(val);
                            }

                    });
                    let chart = new google.visualization.OrgChart(getDocumentLayer('cntAllOrganizationChart'));
                    chart.draw(datatable, {allowHtml:true});
                    addProperties();
                }

                function userHaveChild(userID){

                        let boolReturn = false;
                        $.each(objDataUsers, (key, val) => {
                            if (val.father == userID) {
                                boolReturn = true;
                            }
                        });
                        return boolReturn;

                    }

                function drawUsersNotHaveFather(valData){


                    let cnt = $("#cntUserNotHaveFather");
                    if (valData.color == ""){
                        valData.color = "9D9897";
                    }
                    var clos = color.concat(valData.color)

                    let cntUser = $("<div></div>").attr({
                        "class": "col-xs-6 col-md-4"
                    }).css({
                        "background": clos,
                        "border-radius": "5px",
                        "border": "1px solid #f6f6f6",
                        "color": "#f6f6f6"
                    });
                    cnt.append(cntUser);

                    if(valData.id_usertype == null){
                        $(cntUser).css({
                            "background": "red"
                        });
                    }

                    let strName = $("<p>" + valData.nombres + " " + valData.apellidos + "</p>");
                    cntUser.append(strName);

                    }

                function addProperties(){
                    $(".google-visualization-orgchart-node-medium").each(function(key,val){
                        let strIdentify = $(this).attr("title");
                        let arrIdentify = "";
                        if(strIdentify != undefined){
                            arrIdentify = strIdentify.split("_");
                        }
                        let intIDUser = $(this).text();
                        $(this).attr("id","cntUser_"+intIDUser).css({
                            "background": arrColores[arrIdentify[1]],
                            "color": "white"
                        });
                        $(this).html(arrIdentify[0]);
                    });
                }

                function getRoles(){

                    $.ajax({
                        url: "<?php print $this->strAction; ?>&op=getRoles",
                        type: "POST",
                        dataType: "JSON",
                        beforeSend: () => {
                            dw.openLoading();
                        },

                        success: (response) => {

                            dw.closeLoading();

                            if(response.status == "ok"){
                                let cntListRoles = $("#cntRolesList");
                                cntListRoles.html("");
                                let cntUserNotHaveRol = $("<button  onclick=searchRol(this)></button>").addClass("pointer-list").css({
                                    "background": "red",
                                    "color": "white"

                                }); cntListRoles.append(cntUserNotHaveRol);
                                let strRole = $("<p>Usuario sin rol</p>");
                                cntUserNotHaveRol.append(strRole);

                                $.each(response.roles, (key, val) => {

                                     var clos = color.concat(val.color)
                                    let cntIndividualRol = $("<button  id='" + val.id_usertype + "' name='" + val.descr + "' onclick=searchRol(this)></button>").addClass("pointer-list").css({
                                        "background": clos,
                                        "color": "white"
                                    });

                                    cntListRoles.append(cntIndividualRol);
                                    let strRole = $("<p>" + val.descr + "</p>");
                                    rolls =  strRole  ;
                                    cntIndividualRol.append(strRole);

                                });

                                }
                        },
                        error: () => {
                            dw.closeLoading();
                            dw.alertDialog("Ocurrió un error al obtner la información de los roles.");
                        }
                    })
                }
                function buscarGetUser(objDataUsersS, cnt,strSearch) {

                    cnt = $("#cntUserNotHaveFather");
                    cnt.html("");
                    objDataUsersS = objDataUsers;
                     nom =  $("#remitosucursal").val();

                    let strAsd =  $("#remitosucursal").val();
                    let newstrSearch = removeCharacter(strAsd);
                    newname = newstrSearch;
                    dowinpt = 1;
                    drawTable(newname);

                    $.each(objDataUsers,function(key,val){

                        const userName = val.nombres + " " + val.apellidos;
                        const strUserNameClean = removeCharacter(userName);
                        if (val.father == "0") {
                        if (strUserNameClean.match(newstrSearch)) {
                            if (val.color == "") {
                                val.color = "9D9897";
                            }
                            var clos = color.concat(val.color)
                            let cntUser = $("<div></div>").attr({
                                "class": "col-xs-6 col-md-4"
                            }).css({
                                "background": clos,
                                "border-radius": "5px",
                                "border": "1px solid #f6f6f6",
                                "color": "#f6f6f6"
                            });
                            cnt.append(cntUser);
                            if (val.id_usertype == null) {
                                $(cntUser).css({
                                    "background": "red"
                                });
                            }

                            let strName = $("<p>" + val.nombres + " " + val.apellidos + "</p>");
                            cntUser.append(strName);
                        }
                        }
                    });
                    dowinpt = 0;
                    }

                function searchRol(element) {

                    $(":text").val('');
                    dowinpt = null;
                    const btn = $(element);
                    const roolss = btn.attr("id");
                    const namePu = btn.attr("name");

                    const cnt = $("#cntUserNotHaveFather");
                    cnt.html("");

                    for(let key in objDataUsers) {

                        const rolls = objDataUsers[key].id_usertype;
                        const val = objDataUsers[key];
                        const fam = objDataUsers[key].father;
                        if (fam  == "0") {
                            if (rolls == roolss) {
                                if (val.color == "") {
                                    val.color = "9D9897";
                                }
                                const clos = color.concat(val.color);
                                let cntUser = $("<div></div>").attr({
                                    "class": "col-xs-6 col-md-4"
                                }).css({
                                    "background": clos,
                                    "border-radius": "5px",
                                    "border": "1px solid #f6f6f6",
                                    "color": "#e9e3ff",

                                });
                                cnt.append(cntUser);
                                if (val.id_usertype == null) {
                                    $(cntUser).css("background", "red");
                                }

                                let strName = $("<p>" + val.nombres + " " + val.apellidos + "</p>");
                                cntUser.append(strName);
                            }
                        }
                    }
                    drawTable(namePu);
                }
                function serchcler() {
                    clearinput();
                    buscarGetUser();


                }

                function clearinput() {
                    $(":text").val('');

                }

                function drawTable(strSearchs){

                   $("#tbl-report").addClass("hide");
                    $("#tbl-report").dataTable().fnDestroy();

                    if(objDataUsers !== null){

                    if (strSearchs === undefined) {

                            const cntdiv = $("#tbl-report").find("tbody");
                            let objTMPUsers = JSON.parse(JSON.stringify(objDataUsers));
                        cntdiv.html('');
                            for (let key in objTMPUsers) {
                                const val = objTMPUsers[key];

                                    if (val.swusertype == "") {
                                        drawInfoReportInTable(cntdiv, val);
                                    }

                            }

                        }

                      else if (strSearchs !== null)  {

                           const cntdiv = $("#tbl-report").find("tbody");
                           let objTMPUsers = JSON.parse(JSON.stringify(objDataUsers));
                           let strNewSearch = strSearchs.replace(/ /g, "_");
                           strNewSearch = strNewSearch.toLowerCase();
                           cntdiv.html('');
                           for (let key in objTMPUsers) {
                               const val = objTMPUsers[key];

                                   val.swusertype = val.swusertype.toLowerCase();
                                   var father = val.father;
                                   const userName2 = val.nombres + " " + val.apellidos;
                                   var nombres2 = removeCharacter(userName2);
                                   val.nombres = val.nombres.toLowerCase()

                                   if (val.swusertype === strNewSearch ) {
                                       if (val.father == "0") {
                                           if (dowinpt == null) {


                                           drawInfoReportInTable(cntdiv, val);
                                       }
                                       }
                                   }
                                   if (dowinpt == 1) {
                                       if (father == "0") {

                                       if (nombres2.match(strNewSearch)) {
                                           drawInfoReportInTable(cntdiv, val);

                                       }
                                   }
                                   }

                           }

                       }

                       else {
                           const cntdiv = $("#tbl-report").find("tbody");
                           let objTMPUsers = JSON.parse(JSON.stringify(objDataUsers));
                           for (let key in objTMPUsers) {

                                   const val = objTMPUsers[key];
                                  drawInfoReportInTable(cntdiv, val);

                               }

                       }
                        tableReport =  $("#tbl-report").DataTable({
                            dom: 'Bfrtip',
                            searching:false ,
                            paging: false,
                            info:false,
                            buttons: [{
                                extend: 'copyHtml5',
                                text: '<i class="fa fa-files-o"></i>',
                                titleAttr: 'Copy'
                            }, {
                                extend: 'excelHtml5',
                                text: '<i class="fa fa-file-excel-o"></i>',
                                titleAttr: 'Excel',
                                filename: 'Data export',
                                title:'Usuarios sin Familia',

                            }, {
                                extend: 'csvHtml5',
                                text: '<i class="fa fa-file-text-o"></i>',
                                titleAttr: 'CSV',
                                filename: 'Data export',
                                title:'Usuarios sin Familia',
                            }, {
                                extend: 'pdfHtml5',
                                text: '<i class="fa fa-file-pdf-o"></i>',
                                titleAttr: 'PDF',
                                pageSize: 'A4',
                                filename: 'Data export',
                                title:'Usuarios sin Familia',
                            }],
                            "fnDrawCallback": function( oSettings ) {
                                $("#tbl-report").fadeIn("fast");
                            }
                        });


                        const objInputSearch = $("#tbl-report_filter").find("label").find("input");
                        objInputSearch.trigger('focus');
                        objInputSearch.val(strSearchs);
                        //emular un change del input o hacer un filtro manual según lo que se desee buscar
                    }
                }

                function drawInfoReportInTable(cntdiv,valUsers) {

                    if(valUsers.father == 0) {

                        valUsers.nombres = valUsers.nombres.toUpperCase();
                        valUsers.apellidos = valUsers.apellidos.toUpperCase();
                        var tr = $("<tr></tr>");
                        cntdiv.append(tr);
                        var td = $("<td>" + valUsers.name + "</td>");
                        tr.append(td);
                        td = $("<td>" + valUsers.nombres + "</td>");
                        tr.append(td);
                        td = $("<td>" + valUsers.apellidos + "</td>");
                        tr.append(td);
                        td = $("<td>" + valUsers.email + "</td>");
                        tr.append(td);
                        td = $("<td>" + valUsers.swusertype + "</td>");
                        tr.append(td);

                    }
                }

            </script>
        <?php
    }
}