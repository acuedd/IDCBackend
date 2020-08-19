<?php
require_once "core/global_config.php";

class register_view extends global_config implements window_view
{
    private $strAction = "";
    private $strKey = "";
    public function setStrAction($strAction)
    {
        $this->strAction = $strAction;
    }

    public function setSiteKey($strKey)
    {
        $this->strKey = $strKey;
    }

    public function draw()
    {
        global $cfg;
        include_once("modules/configuration/objects/profile/profile_model.php");
        $objProfile = new profile_model();
        $objRegister = new register_model();

        draw_header_tag($cfg["core"]["title"]);
        $source = "";
        $consulta = $objProfile->profileConfiguration('img-start');
        $consulta_background = $objProfile->profileConfiguration('img-start-bkg');
        $source = $consulta["path"];
        $arrColors = [];
        $colors = $objProfile->getBy('type', 'color', true);
        foreach($colors as $color){
            $arrColors[$color["specified"]] = $color;
        }
        $menu_background = strlen($arrColors["color-menu"]["color"]) >= 4 ? $arrColors["color-menu"]["color"] : "";
        $menu_color_deg = strlen($arrColors["color-deg"]["color"]) >= 4 ? $arrColors["color-deg"]["color"] : "";
        $image_background_color = strlen($arrColors["color-start-bkg"]["color"]) >= 4 ? $arrColors["color-start-bkg"]["color"] : "";
        $image_face = $menu_color_deg ? "linear-gradient($menu_background, $menu_color_deg)" : $menu_background;
        $image_background_souce = $consulta_background["path"] ? $consulta_background["path"] : "";
        $loginCaption = $objRegister->getLoginCaption();
        ?>
        <link rel="stylesheet" href="/modules/users/objects/register/css/register.css">
        <style>
            @font-face {
                font-family: Cronos-Pro_12459;
                src: url(./themes/geniusAdminLTE/fonts/Cronos-Pro_12459.ttf)format('truetype');
            }
            *{font-family: Cronos-Pro_12459;}
            /*body{background: #323C45;}*/
            #fPTB{
                height: 100%;
                margin: 0;
                /*background-image: url(./images/img_1.jpg);*/
                background-image: url(./images/tigoBusiness.png);
                background-repeat: no-repeat;
                background-size: 100% 100%;
                background-position: 0 0 !important;
            }
            .containerTitle{  padding-left: 50px;  }
            .titlePrincipal{
                font-size: 38px;
                position: absolute;
            }
            .imgPrincipalFirst{  padding-top: 22%;  }
            .fondoLogin{
                background: #1E2831;
                color: white;
                height: 100%;
            }
            .titleLogin{margin-bottom: 30px;}
            .menTemp{
                color:#43c7c7 ;
            }
            .formLogin{
                padding-top: 23%;
            }
            .loginIcons{color: #323C45;}
            .contIcons{background: white; border: 1px solid white; border-radius: 0;}
            .checkLogin{
                margin: 10px;
                color: black;
                font-size: 20px;
            }
            .passLost{  color: #F2C02A;  }
            .passLost:hover{  color: #F2C02A;  }
            .buttonLogin{
                background: #323C45;
                width: 80%;
                padding: 0 !important;
                margin-top: 13%;
                margin-left: auto;
            }
            .buttonLogin:hover{
                color: white;
            }
            .imgGTvL{
                height: 80%;
                margin-top: 10%;
            }
            /*=====================================================*/
            @media (max-width: 991px){
                .formLogin{
                    width: 80%;
                    padding-left: 25%;
                    padding-top: 10%;
                    margin-bottom: 60px;
                }
            }
            @media (max-width: 940px){
                #fPTB{
                    height: 80%;
                }
                .formLogin{
                    padding-top: 10%;
                }
            }
            .login_container{
                background: <?php $image_face ? print $image_face : print "#1c2637"; ?>;
                min-height: 100vh;
                display: flex;
                align-items: center;
            }
            .login_container{
                box-shadow: 0px 0px 9px rgba(0,0,0, .4);
                display: inline-flex;
            }
            .login_container form{
                padding: 0;
                margin: 0 auto;
                width: 100%;
            }
            .login_container form{
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .button_login{
                background: rgba(255, 255, 255, .3);
                font-size: 1.3em;
                padding: .3em;
                width: 50%;
                /*height: 100%;*/
                color: #fff;
            }
            @media (min-width: 90px) {
                .button_login {
                    height: 50px !important;
                }
            }
            @media (min-height: 90px) {
                .button_login {
                    height: 50px !important;
                }
            }
            @media (max-height: 900px) {
                .button_login {
                    height: 50px !important;
                }
            }
            @media (min-width: 800px){
                .button_login:hover{
                    box-shadow: 0px 0px 10px rgba(0,0,0, .3);
                    transform: scale(1.1);
                    transition: all .1s linear;
                }
            }
            .login_container form button{
                margin: 0;
            }
            .input_remember{
                color: #fff;
                padding: 0 !important;
                font-weight: 300;
            }
            .input_remember label{
                padding: 0;
            }
            .input_remember input{
                background: #fff;
            }
            .input-group span i{
                color: rgba(0,0,0, .3);
            }
            .login_header h3{
                padding: 0;
                margin: 0;
                text-transform: uppercase;
                font-weight: bold;
                letter-spacing: .1em;
                color: #fff;
            }
            .lost_pass{
                color: #fff;
            }
            .custom-form-group label{
                color: <?php print $arrColors["color-menu-text"]["color"]; ?>;
            }
            .custom-form-group input, .custom-form-group select{
                border-radius: 22px !important;
                background: rgb(235, 235, 235);
            }
            .set-color{
                color: <?php print $arrColors["color-menu-text"]["color"]; ?>;
            }
            .color-menu-text{
                color: <?php print $arrColors["color-menu-text"]["color"]; ?>;
            }

            .wallet-button{
                border-radius: 22px;
                padding: .5em 3em;
            }

            .add-margin{
                margin: 1em 0;
            }

            .wallet-button-positive{
                background: #00C8FF;
                color: #fff;
            }

            .wallet-button-negative{
                background: #ff0000;
                color: #fff;
            }

            .no-margin{
                margin: 0;
            }

            .login_header p{
                margin: 0;
            }
            .register-container{
                display: flex;
                align-items: stretch;
            }
            .fill{
                border: 1px solid red !important;
            }
            .relative{
                position: relative;
            }
            .input-group{
                margin: 0;
            }
            .logo_container{
                background: url("<?php print $image_background_souce; ?>") !important;
                background-color: <?php $image_background_color ? print $image_background_color : print 'black'; ?> !important;
            <?php $image_background_color ?
                    print "background-repeat: repeat !important;" :
                    print "background-repeat: no-repeat !important; background-size: cover !important;" ?>
            }
            .logo_src{
                height: 100%;
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
            }
            .g-recaptcha{
                display: flex;
                justify-content: center;
            }
            .logo_src img{
                max-height: 100%;
                max-width: 80%;
                animation-name: img-ani;
                animation-duration: .3s;
                animation-timing-function: ease-in;
            }
            input, button, label, h3, span{
                animation-name: img-ani;
                animation-duration: .3s;
                animation-timing-function: ease-in;
            }
            @keyframes img-ani {
                from{opacity:0;}
                to{opacity: 1;}
            }
            @media (max-width: 1020px){
                .register-container{
                    display: block;
                }
            }
            @media (max-width: 700px) {
                .modal-hml-content-sm {
                    width: 90% !important;
                }
            }
            .modal-hml-content-sm{
                max-width: 400px;
            }
            .terms_accept{
                font-size: 1em;
                font-weight: bold;
                cursor: pointer;
                padding: 1em;
            }
            .add-to-screen{
                margin: 1em 0;
            }
            .modal-hml-body{
                display: block;
            }
            @media (max-width: 520px){
                #fPTB{
                    height: 50%;
                }
                .buttonLogin{
                    margin-top: 0;
                }
                .formLogin{
                    margin-bottom: 0;
                }
            }
        </style>

        <div class="register-container">
            <div class="col-md-6 logo_container">
                <div class="logo_src">
                    <img src="<?php file_exists($source) ?
                        print $source :
                        (
                        file_exists("profiles/".$this->cfg["core"]["site_profile"]."/images/theme_image_login_logo.png") ?
                            print "profiles/".$this->cfg["core"]["site_profile"]."/images/theme_image_login_logo.png" :
                            print "themes/".$this->cfg["core"]["theme"]."/images/theme_image_login_logo.png"
                        );
                    ?>">
                </div>
            </div>
            <div class="col-md-6 login_container">
                <script src='https://www.google.com/recaptcha/api.js'></script>
                <div class="">
                    <form name="login_form" id="login_form" class="formLogin" method="post" enctype="multipart/form-data">
                        <div class="form_container">
                            <div class="col-sm-12 text-center">
                                <h3 class="set-color">Est� a punto de registrar un comercio, favor de brindar informacion veridica.</h3><br/><br/>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Nombre de comercio</label>
                                <input type="text" name="commerceName" id="commerce_name" class="form-control" required>
                            </div>
                            <input type="hidden" name="country" id="country" value="">
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Departamento</label>
                                <select name="commerce_address_department" class="form-control" id="commerce_address_department" required>

                                </select>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Municipio</label>
                                <select name="commerce_address_town" class="form-control" id="commerce_address_town" required>

                                </select>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Direcci�n</label>
                                <input type="text" name="address" class="form-control" id="commerce_address" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Colonia o aldea</label>
                                <input type="text" name="suburb" class="form-control" id="suburb" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Zona</label>
                                <input type="text" name="address_zone" class="form-control" id="commerce_address_zone" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Nombres</label>
                                <input type="text" name="firstName" id="commerce_user_firstname" class="form-control" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Apellidos</label>
                                <input type="text" name="lastName" id="commerce_user_lastname" class="form-control" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">G�nero</label>
                                <select name="genre" class="form-control" id="commerce_user_genre" required>
                                    <option value="0" selected disabled>Seleccione una opci�n</option>
                                    <option value="Male">Masculino</option>
                                    <option value="Female">Femenino</option>
                                </select>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Banco</label>
                                <!--<input type="text" name="bankName" id="commerce_bank" class="form-control" required>-->
                                <select name="bankName" id="commerce_bank" class="form-control">

                                </select>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Tipo de cuenta</label>
                                <select name="accountType" class="form-control" id="commerce_bank_type" required>
                                    <option value="0" selected disabled>Seleccione una opci�n</option>
                                    <option value="monetaria">Monetaria</option>
                                    <option value="ahorro">Ahorro</option>
                                </select>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">N�. Cuenta</label>
                                <input type="text" name="accountNumber" id="commerce_bank_number" min="19" max="23" class="form-control" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">N�mero de celular</label>
                                <input type="number" name="phoneNumber" id="commerce_phone" class="form-control" min="1" max="8" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Correo el�ctronico</label>
                                <input type="email" name="commerce_mail" id="commerce_mail" class="form-control" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-12">
                                <label for="">DPI</label>
                                <input type="text" name="dpi" id="commerce_user_dpi" class="form-control" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Contrase�a</label>
                                <input type="password" name="passwd" id="commerce_password" class="form-control" required>
                            </div>
                            <div class="form-group custom-form-group col-sm-6">
                                <label for="">Confirmar Contrase�a</label>
                                <input type="password" name="passwd" id="commerce_password_confirm" class="form-control" required>
                            </div>

                            <div class="additional_info col-sm-12">
                                <div class="additional_info-item color-menu-text item_dpiFront">
                                    <div class="item-sample">
                                        <img src="/themes/<?php print $cfg["core"]["theme"]; ?>/images/samples/dpi-front.png" alt="">
                                    </div>
                                    <label for="dpiFront">
                                        <div class="item-uploaded image_dpiFront">
                                            Foto de DPI (frontal)
                                        </div>
                                        <input id="dpiFront" accept="image/x-png,image/jpeg" type="file">
                                    </label>
                                    <div id="note_dpiFront"></div>
                                </div>
                                <div class="additional_info-item color-menu-text item_dpiBack">
                                    <div class="item-sample">
                                        <img src="/themes/<?php print $cfg["core"]["theme"]; ?>/images/samples/dpi-back.png" alt="">
                                    </div>
                                    <label for="dpiBack">
                                        <span class="item-uploaded image_dpiBack">
                                            Foto de DPI (reverso)
                                        </span>
                                        <input id="dpiBack" type="file" accept="image/x-png,image/jpeg">
                                    </label>
                                    <div id="note_dpiBack"></div>
                                </div>
                                <div class="additional_info-item color-menu-text item_stateAccountImage">
                                    <div class="item-sample">
                                        <img src="/themes/<?php print $cfg["core"]["theme"]; ?>/images/samples/sample_account.png" alt="">
                                    </div>
                                    <label for="stateAccountImage">
                                        <span class="item-uploaded image_stateAccountImage">
                                            Foto de soporte de cuenta, ejemplo: encabezado estado de cuenta, cheque, libreta de ahorro, etc.
                                        </span>
                                        <input id="stateAccountImage" accept="image/x-png,image/jpeg" type="file">
                                    </label>
                                    <div id="note_stateAccountImage"></div>
                                </div>
                            </div>

                            <div class="col-sm-12 form_alerts color-menu-text text-center"></div>

                            <div class="col-sm-12 text-center color-menu-text terms_accept">
                                <input type="checkbox" id="accept" class=""><label for="accept">Acepto </label> <span class="stretched-link text-primary" id="accept_terms">t�rminos y condiciones</span>
                            </div>

                            <div class="col-sm-12">
                                <div class="g-recaptcha" data-sitekey="<?php print $this->strKey?>"></div>
                            </div>
                            <?php
                            if(isset($_SESSION["register_error"])){
                                $strMessage = $_SESSION["register_error"];
                                print "<p class='color-menu-text'>$strMessage</p>";
                                unset($_SESSION["register_error"]);
                            }
                            ?>
                            <div class="col-sm-12 text-center add-margin">
                                <button type="button" onclick="ReCaptchaEvent()" class="btn buttonLogin button_login wallet-button no-margin" disabled><?php echo $this->lang["NEW_USER_LINK"]; ?></button>
                            </div>
                        </div>
                    </form>
                    <div class="add-to-app text-center">
                        <button class="add-to-screen btn wallet-button">Instalar WebApp</button>
                    </div>
                    <script src="/triggerServiceWorker.js"></script>
                </div>
            </div>
        </div>
        <?php
        $this->scripts();
    }

    public function scripts(){
        $termsOfService = <<<EOD
El Usuario por este acto solicita a Comunicaciones Celulares, Sociedad An�nima (en adelante "COMCEL") el enrolamiento en la plataforma tecnol�gica BONO FAMILIA.  La aceptaci�n de estos T�rminos y Condiciones vinculan al Usuario y a COMCEL de acuerdo con las condiciones de uso que se establecen adelante. <br><br>
Para efectos del enrolamiento en la plataforma BONO FAMILIA, las palabras a continuaci�n tendr�n los siguientes significados: <br><br>
Usuario: Persona individual o jur�dica que posee un establecimiento comercial incluido dentro de las categor�as comerciales que aplican para la recepci�n de pagos provenientes del Fondo Bono Familia, y que acepta estos T�rminos y Condiciones.<br><br>
Plataforma Tecnol�gica BONO FAMILIA: Es el medio de facilitaci�n de pagos electr�nicos mediante el aplicativo y registro de datos requeridos, desde cualquier tel�fono inteligente que tenga los sistemas operativos Android y IOS. La Plataforma Tecnol�gica BONO FAMILIA permite utilizar cualquier dispositivo con acceso a Internet como Punto de Venta ("POS" por sus siglas en ingl�s) Virtual para recibir pagos electr�nicos por los productos y/o servicios que el Usuario ofrece cuando dichos pagos provengan de los fondos acreditados por el Ministerio de Desarrollo Social a favor de los beneficiarios del programa Bono Familia al que se refiere el Decreto 13-2020 del Congreso de la Rep�blica.<br><br>
Beneficiario: Persona individual que ha sido beneficiada con aportes del BONO FAMILIA a que se refiere el art�culo 2 del Decreto 13-2020 del Congreso de la Rep�blica, y que tendr� acceso a dichos fondos utilizando su Documento Personal de Identificaci�n (DPI).<br><br>
COMCEL: Proveedor de la plataforma tecnol�gica denominada <b>"BONO FAMILIA"</b>. <br><br>
Transacci�n:  Es la operaci�n que realiza un Usuario desde la Plataforma Tecnol�gica BONO FAMILIA y que tiene como prop�sito recibir un pago electr�nico por los productos y/o servicios que el Usuario ofrece cuando dichos pagos provengan de los fondos acreditados por el Ministerio de Desarrollo Social a favor de los beneficiarios del programa Bono Familia al que se refiere el Decreto 13-2020 del Congreso de la Rep�blica. <br> <br>
1.	Para el enrolamiento del Usuario en la Plataforma Tecnol�gica BONO FAMILIA, el Usuario deber� acceder a al aplicativo y proporcionar los siguientes datos: nombre completo, Documento Personal de Identificaci�n (DPI), informaci�n de cuenta bancaria, direcci�n completa y cualquier otra informaci�n que sea necesaria para los fines del enrolamiento. <br>

2.	El enrolamiento en la Plataforma Tecnol�gica BONO FAMILIA ser� gratuito. <br><br>

3.	Las Transacciones se realizar�n en moneda de curso legal. <br><br>

4.	La funci�n de COMCEL se limita al enrolamiento del Usuario para proveer el acceso a la Plataforma Tecnol�gica BONO FAMILIA, por lo que, el procesamiento de pagos no se encuentra dentro del �mbito de responsabilidad de COMCEL.  Tampoco es funci�n o responsabilidad de COMCEL establecer un registro y control de Usuarios, lo cual es una funci�n del a cargo del Ministerio de Desarrollo Social, el cual podr� ejecutarla por s� mismo o mediante delegaci�n a tercera persona.<br><br>

5.	La comisi�n por recepci�n de pagos por medio de la Plataforma Tecnol�gica BONO FAMILIA es fijada y ser� informada por la entidad operadora del procesamiento de pagos, la cual realizar� el cobro por medio de retenci�n en la liquidaci�n correspondiente. La tarifa inicial de comisi�n es de uno punto cinco por ciento (1.5%) y podr� variar a discreci�n de la entidad operadora del procesamiento de pagos, la cual informar� oportunamente al Usuario sobre cualquier variaci�n.<br><br>

6.	Los Usuarios podr�n realizar Transacciones por medio de la Plataforma Tecnol�gica BONO FAMILIA en los horarios y d�as de atenci�n que los establecimientos comerciales de los Usuarios permanezcan abiertos al p�blico. <br><br>

7.	El Usuario es responsable de proporcionar los datos correctos para su enrolamiento. COMCEL no se hace responsable por errores en los datos que el Usuario ingres� al momento de su enrolamiento. Si existen inconsistencias, el Usuario no podr� completar su proceso de enrolamiento. De igual manera, el Usuario es responsable por el resguardo del usuario y contrase�a ya que conoce y acepta que estos datos son personales e intransferibles, por lo que cualquier uso de la Plataforma Tecnol�gica BONO FAMILIA que cualquier persona realice por descuido del Usuario o por compartir estos datos, no ser� responsabilidad de COMCEL. En este sentido, el Usuario ser� el �nico responsable de los da�os y perjuicios que pueda causarse a s� mismo o a terceros por la transgresi�n de lo aqu� estipulado.<br><br>

8.	El Usuario no podr� utilizar la Plataforma Tecnol�gica BONO FAMILIA para desarrollar actividades contempladas como il�citas por la legislaci�n guatemalteca ni pretender utilizar la plataforma para realizar transacciones no previstas o no autorizadas por la legislaci�n vigente.<br><br>

9.	COMCEL, a solicitud de la entidad operadora del procesamiento de pagos, podr� rechazar, cancelar, o suspender el enrolamiento del Usuario. <br><br>

10.	Uso de medios electr�nicos: el Usuario acepta expresamente y ratifica que al proporcionar los datos para su enrolamiento en la Plataforma Tecnol�gica BONO FAMILIA, est� prestando su consentimiento a estos t�rminos y condiciones, mismos que COMCEL podr� modificar unilateralmente de tiempo en tiempo. El Usuario confirma que la funci�n de la Plataforma Tecnol�gica BONO FAMILIA en cuanto a las comunicaciones electr�nicas que son procesadas por su medio, es �nicamente la de servir de intermediario bajo la Ley para el Reconocimiento de Comunicaciones y Firmas Electr�nicas, Decreto 47-2008 del Congreso de la Rep�blica de Guatemala. El Usuario reconoce la aplicaci�n de la Ley para el Reconocimiento de Comunicaciones y Firmas Electr�nicas, Decreto 47-2008 del Congreso de la Rep�blica de Guatemala en la funci�n de la Plataforma Tecnol�gica BONO FAMILIA y est� de acuerdo en que las partes se rijan por su contenido, incluyendo la aceptaci�n de estos t�rminos y condiciones en forma electr�nica. <br><br>

11.	El Usuario entiende y acepta que el cobro del servicio de telecomunicaciones es independiente del acceso a la Plataforma Tecnol�gica BONO FAMILIA.<br><br>

12.	El Usuario podr� cancelar en cualquier momento su enrolamiento en la Plataforma Tecnol�gica BONO FAMILIA, para lo cual �nicamente deber� seguir el procedimiento que la plataforma establece para dicho prop�sito. <br><br>


13.	COMCEL se compromete a dar un tratamiento responsable a la informaci�n que le proporcione el Usuario seg�n el Decreto 57-2008 del Congreso de la Rep�blica de Guatemala o cualquier otra norma que la complemente, adicione o modifique. Cuando dicha informaci�n provenga de un tercero, ese tercero ser� el �nico responsable del manejo de dicha informaci�n. <br><br>

AUTORIZACI�N EXPRESA DE CONSULTA DE INFORMACI�N DEL USUARIO. El Usuario autoriza a COMCEL, para que pueda tener acceso y consultar archivos de informaci�n del Usuario que no provengan de registros p�blicos y que est�n protegidos por el Decreto 57-2008 del Congreso de la Rep�blica de Guatemala, Ley de Acceso a la Informaci�n P�blica; as� como para corroborar dicha informaci�n por cualquier medio legal por s� o por la persona individual o jur�dica que COMCEL designe. Por lo que el Usuario autoriza expresamente a las empresas que distribuyen o comercializan con datos personales, para que distribuyan o comercialicen a COMCEL la informaci�n o estudios que contengan datos del Usuario. Asimismo, el Usuario  autoriza a COMCEL para suministrar, entregar, transferir, compartir y dar a conocer por cualquier medio o procedimiento, la informaci�n personal, comportamiento comercial, datos de contacto, clasificaci�n de tipo de cliente y actualizaci�n de datos personales a: a) agentes, b) filiales, c) subsidiarias, d) afiliadas, e) otras personas relacionadas a COMCEL  o un tercero aprobado por COMCEL, as� mismo la autorizo para crear una base de datos con toda la informaci�n que se genere dentro del giro normal de esta relaci�n contractual. COMCEL queda facultada para utilizar esa base de datos para analizar toda solicitud que el Usuario formule en futuras relaciones comerciales o para analizar la posibilidad de ofrecerme la contrataci�n de otros servicios que preste COMCEL o cualquier entidad afiliada o relacionada con �sta, y tambi�n para analizar dicha informaci�n, clasificarla, conservarla, distribuirla y/o comercializarla a terceros para generar historial de comportamiento comercial y validar reglas de decisi�n propias o de terceros. Por �ltimo, autorizo expresamente a COMCEL para que pueda suministrar, entregar, transferir, compartir, y dar a conocer, por cualquier medio o procedimiento, mi informaci�n personal a cualquier persona no relacionada o no vinculada a COMCEL, entendi�ndose �stas como centrales de riesgos o buros de cr�dito (en adelante, una "Parte No Relacionada"), con el fin que la Parte No Relacionada pueda ofrecerme servicios; y autorizo expresamente a las centrales de riesgo y buros de cr�ditos a recopilar, suministrar y comercializar informaci�n sobre mi persona.<br><br>

14.	DERECHOS INTELECTUALES: Por medio de estos t�rminos y condiciones, el Usuario no tendr� derecho de propiedad sobre el nombre, logotipo, marcas de servicio, marcas comerciales, nombres comerciales, lemas, frases acu�adas, ni ninguna otra designaci�n patentada o privilegiada perteneciente a COMCEL, por lo que no podr� utilizarlos en ning�n medio para promocionarse a s� mismo o a terceros, siendo responsable incluso criminalmente por la violaci�n de esta prohibici�n. Respecto a la propiedad intelectual e industrial relacionada con los productos y servicios de COMCEL, el Usuario reconoce y acepta la propiedad exclusiva de terceros, incluyendo a COMCEL, sobre la misma, y especialmente de las marcas, productos y servicios que COMCEL, opere o utilice bajo licencia de terceros,  se encuentre o no registrada y, en consecuencia, el Usuario, no podr� registrar a su nombre ninguna propiedad intelectual o industrial relacionada directa o indirectamente con los mismos, siendo responsable de los da�os y perjuicios que se ocasionen a la entidad que sea propietaria de la propiedad intelectual, por su incumplimiento. Como consecuencia del reconocimiento por parte del Usuario, de los derechos de COMCEL, sobre la propiedad intelectual e industrial a que se refiere esta cl�usula, as� como su uso en Guatemala, la promoci�n o defensa de cualquier acci�n civil o penal con respecto a tal propiedad intelectual e industrial corresponder� exclusivamente a COMCEL o la entidad titular de la marca, quien la ejercitar� de la manera que mejor estime conveniente. <br><br>

15.	LEY APLICABLE Y RESOLUCI�N DE CONTROVERSIAS Y ACCION JUDICIAL: La leyes y reglamentos de la Rep�blica de Guatemala ser�n aplicables a estos t�rminos y condiciones. En caso de cualquier controversia relacionada con la aplicaci�n, interpretaci�n o ejecuci�n de estos t�rminos y condiciones las partes la resolver�n acudiendo a la v�a judicial. Para este prop�sito, las partes renuncian a cualquier fuero que pudiera corresponderles, someti�ndose expresamente a los tribunales del Departamento de Guatemala. Las disposiciones de esta cl�usula sobrevivir�n la terminaci�n de este contrato por cualquier causa. Para efectos de este contrato, el Usuario se�ala como lugar para recibir notificaciones y emplazamiento, la direcci�n que ingres� para su registro en la Plataforma Tecnol�gica BONO FAMILIA; y COMCEL podr� ser notificada en el Kil�metro nueve punto cinco (9.5), Carretera a El Salvador, Plaza Tigo, Torre I, Cuarto Nivel, Santa Catarina Pinula, Guatemala. <br><br>

16.	MEDIANTE LA UTILIZACION DE LA PLATAFORMA BONO FAMILIA, EL USUARIO T�CITAMENTE MANIFIESTA ESTAR ENTERADO DE ESTOS T�RMINOS Y CONDICIONES Y PRESTA SU CONSENTIMIENTO PARA SOMETERSE A LOS MISMOS.<br><br>

EOD;
        ?>
        <script>
            let terms_of_service = `<?php print $termsOfService; ?>`;
            let url = `<?php print $this->strAction; ?>`;
            let lang = {
                'REGISTER_CONDITION': '<?php print $this->lang["REGISTER_CONDITION"]; ?>',
                'REGISTER_LAW': '<?php print $this->lang["REGISTER_LAW"]; ?>'
            };
        </script>
        <script src="/modules/users/objects/register/js/register_h.js?v=4" defer></script>
        <script defer>
            window.addEventListener('load', ()=>document.querySelector('.form_container').scrollIntoView({block: 'start', behavior: 'smooth'}));
        </script>
        <?php
    }
}