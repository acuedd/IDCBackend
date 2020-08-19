<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 7/02/2017
 * Time: 15:27
 */
include_once("core/global_config.php");
class emulate_view extends global_config implements window_view {
    private static $_instance;
    private $strAction = "";
    public function __construct($arrParams){
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams){
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function setStrAction($strAction){
        $this->strAction = $strAction;
    }

    public function draw(){
        draw_header($this->lang["CHANGE_USER_TO_TEST"]);
        theme_draw_centerbox_open($this->lang["CHANGE_USER_TO_TEST"]);
        global_function::clearBrowserCache();
        ?>
        <style>
            .strButton{
                font-size: 12px;
            }
            .cntIndividualBtnEmulate{
                margin: 15px 0;
            }
            .btnSelOption{
                margin: 20px;
                background: white;
                color: black;
                font-size: 23px;
                width: 200px;
                height: 130px;
                padding-top: 20px;
                border: 1px solid #2D3C53;
                border-radius: 0;
            }
            .btnSelOption:hover, .btnSelOption:focus{
                top: 0px;
                color: white;
                background: #3598DC;
                border: white;
            }
            .divInline{
                display: inline;
            }
        </style>
        <div class="col-lg-12 col-xs-12">
            <div class="col-lg-offset-3 col-lg-6">
                <p class="bg-warning">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    <?php print $this->lang["CHANGE_USER_TO_TEST_INSTRUCCIONES"]; ?>
                </p>
            </div>
        </div>
        <div class="col-lg-12 col-xs-12 text-center">
            <?php
            if (isset($_SESSION["wt"]["originalUserToTest"])) {
                ?>
                <button type="button" class="btn btn-warning" onclick="document.location.href='<?php print $this->strAction; ?>&revertUser=<?php print $_SESSION["wt"]["originalUserToTest"]; ?>'">
                    <i class="fa fa-ban" aria-hidden="true"></i>
                    <?php print $this->lang["CHANGE_USER_TO_TEST_REMOVE"]; ?>
                </button>
                <?php
            }
            else{
                ?>
                <div id="cntButtonUniqueEmulate"></div>
                <?php
            }
            ?>
        </div>
        <div class="col-lg-6 col-md-offset-3 divInline">
            <h5 id="lblUser" class="fade"></h5>
            <div class="alert alert-danger alert-dismissible fade" role="alert" id="alertUser">
                <h4>Precaución</h4>
                <p>
                    Al emular al usuario, podrá hacer operaciones haciéndose pasar por el usuario elegido. El sistema lleva un registro interno con el detalle de todo lo que opere emulando al otro usuario.
                </p>
                <form action="<?php print $this->strAction; ?>" name="frmUserToTest" method="POST">
                    <p class="text-right">
                        <input type="hidden" name="hidUserToTest" value="1">
                        <input type="hidden" name="hidUsuarioNuevo" value="<?php print (isset($_SESSION["wt"]["originalUserToTest"]))?$_SESSION["wt"]["uid"]:0;?>">
                        <button type="submit" class="btn btn-danger">Aceptar</button>
                        <button type="button" class="btn btn-default" onclick="document.location.href='<?php print $this->strAction; ?>'">Cancelar</button>
                    </p>
                </form>
            </div>
        </div>

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
        <script>
            $(document).ready(() => {
                getTypeUser();
            });
            const dw = new drawWidgets();
            function getTypeUser() {
                $.ajax({
                    url: "<?php print $this->strAction; ?>&op=getUserAux",
                    type: "POST",
                    dataType: "JSON",
                    beforeSend: () => {
                        dw.openLoading();
                    },
                    success: (response) => {
                        dw.closeLoading();
                        if(response.status == "ok"){
                            let lengthObjResponse = 0;
                            if(response.uidAux){
                                lengthObjResponse = Object.keys(response.uidAux).length;
                            }
                            let cntButton = $("#cntButtonUniqueEmulate");
                            cntButton.html("");
                            if( lengthObjResponse > 0 ){
                                $.each(response.uidAux, (keyAux, valAux) => {
                                    drawButtonsCoordination(cntButton, valAux);
                                });
                            }
                            else{
                                drawUniqueButton(cntButton);
                            }
                            /*getUsers(intUIDAux);*/
                        }
                    },
                    error: () => {
                        dw.closeLoading();
                        dw.alertDialog("Ocurrió un error al identificar el tipo de usuario.");
                    }
                })

            }

            function drawButtonsCoordination(cntButton, valAux){

                let button = $("<button></button>").attr({
                    "class": "btnSelOption btn",
                    "id": "btnSel_" + valAux.id_user,
                    "name": "btnTypeProduct",
                    "onclick": "getUsers( " + valAux.id_user + " )"
                });
                cntButton.append(button);

                let imgUser = $("<img>").attr({
                    "src": "adm_main.php?mde=users&wdw=myaccount&op=avatar&uid=" + valAux.id_user
                }).css({
                    "margin": "0",
                    "padding": "0",
                    "border-radius": "50%"
                });
                button.append(imgUser);


                let strTxtButton = $("<p></p>").addClass("strButton");
                button.append(strTxtButton);
                strTxtButton.text("Ver coordinación de "+ valAux.nombres + " " + valAux.apellidos);
            }

            function drawUniqueButton(cntButton){
                let button = $("<button></button>").attr({
                    "class": "btnSelOption btn",
                    "name": "btnTypeProduct",
                }).click(()=>{
                    getUsers(0);
                });
                var strIcon = $("<p></p>").attr({
                    "class": "fa fa-search"
                }).css({
                    "font-size": "35px",
                    "margin": "0",
                    "padding": "0"
                });
                button.append(strIcon);
                let strTxtButton = $("<p>" + '<?php print $this->lang["CHANGE_USER_TO_TEST_SELECT"]; ?>' + "</p>").addClass("strButton");
                button.append(strTxtButton);
                cntButton.append(button);
            }

            function getUsers(intUIDAux) {
                var objDw = new drawWidgets();
                $.ajax({
                    url : "<?php print $this->strAction ?>&boolReport=true",
                    type : "GET",
                    dataType : "HTML",
                    data: {
                        IDUserAux: intUIDAux
                    },
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
                $("[name=hidUsuarioNuevo]").val(uid);
                $("#lblUser").html("<b>Usuario a emular -></b> " + name + ", " + lastname);
                $("#lblUser").addClass("in");
                $("#alertUser").addClass("in");
                $("#mdlUsers").modal("hide");
            }
        </script>
        <?php
        theme_draw_centerbox_close();
        draw_footer();
    }
}