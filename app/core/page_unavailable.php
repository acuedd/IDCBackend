<html>
<HEAD>
<meta http-equiv=Content-Type content="text/html;  charset=ISO-8859-1">
<TITLE>Homeland Online Communities</TITLE>
<style type="text/css">
    @font-face {
        font-family: Cronos-Pro_12459;
        src: url(./themes/geniusAdminLTE/fonts/Cronos-Pro_12459.ttf)format('truetype');
    }
    *{font-family: Cronos-Pro_12459;}
    a{
        font-size:11px;
        color:#999999;
        text-decoration : none;
    }
    .cntUnavailable {
        margin-left: 25%;
        width: 50%;
    }
    .btnAction {
        border-radius: 7px;
        padding: 10px;
        background: #9d9d9d;
        border: none;
    }
    .btnAction > a {
        color: white !important;
        font-size: 16px;
    }
    .imgFooter{
        bottom: 0;
        position: absolute;
        width: 10%;
        left: 45%;
    }
    .imgNoAvailable{
        max-width: 100% !important;
    }
    .strNoAvailable{
        font-size: 23px;
        color: #717272;
    }
    .strDetailNoAvailable{
        color: #9d9d9d;
    }
</style>
</HEAD>

<BODY bgcolor="white">
    <div class="cntUnavailable">
        <div align="center">
            <img class="imgNoAvailable" src="themes/geniusAdminLTE/images/site_no_available.png">
            <p class="strNoAvailable">
                Temporalmente <br> <strong>No disponible</strong>
            </p>
        </div>

        <div align="center">
            <p class="strDetailNoAvailable">
                Disculpe los inconvenientes, el sitio se encuentra en mantenimiento. <br>
                Nuestro equipo trabaja constantemente para mantener este sitio funcionando a niveles óptimos <br>
                por lo que hacemos todo lo posible para prevenir caídas inesperadas del sitio.
            </p>
            <button class="btnAction">
                <a href="<?php print $_SERVER["PHP_SELF"];?>">
                    Reintentar
                </a>
            </button>
        </div>
    </div>
</body>
<footer>
    <div align="center">
        <img class="imgFooter" src="themes/geniusAdminLTE/images/homeland-orange.png"/>
    </div>
</footer>
</html>
