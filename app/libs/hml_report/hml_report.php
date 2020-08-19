<?php

class hml_report {

    protected $arrEncabezado = array();
    protected $arrParametros = array();
    protected $arrCustom = array();
    protected $strQuery = "";
    protected $strModulo = "";
    protected $strVentana = "";
    protected $boolFilter = false;
    protected $strReportKey = "";
    protected $boolDieEvents = true;

    function __construct($strQuery, $arrEncabezado = array(), $arrParametros = array(), $strModulo = false, $strVentana = false, $boolDoNotIncludeMain = false, $strReportKey = false, $boolDieEvents = true) {
        
        if (!$strReportKey) {
            $strReportKey = uniqid();
        }

        $this->strReportKey = $strReportKey;
        $this->arrEncabezado = $arrEncabezado;
        $this->arrParametros = $arrParametros;
        $this->strModulo = $strModulo;
        $this->strVentana = $strVentana;
        $this->boolDieEvents = $boolDieEvents;

        if (is_array($strQuery)) {
            $this->arrCustom = $strQuery;
            $_SESSION["rpt_hml"][$this->strReportKey]["query"] = $this->arrCustom;
        }
        else {
            $this->strQuery = $strQuery;
            $_SESSION["rpt_hml"][$this->strReportKey]["query"] = $this->strQuery;
        }

        $_SESSION["rpt_hml"][$this->strReportKey]["encabezado"] = $this->arrEncabezado;
        $_SESSION["rpt_hml"][$this->strReportKey]["parametros"] = $this->arrParametros;
        $_SESSION["rpt_hml"][$this->strReportKey]["ventana"] = $this->strVentana;
        $_SESSION["rpt_hml"][$this->strReportKey]["modulo"] = $this->strModulo;

        if (!empty($this->arrEncabezado)) {
            $this->boolFilter = true;
        }

        if (!$boolDoNotIncludeMain) {
            include_once("core/main.php");
        }
    }

    /* --------------------------------------Jquery-------------------------------------- */

    public function dibujarHML_RPT() {
        $strReturn = $this->stylePaginador();
        $strReturn .= <<<EOD
        <form id="form_rpt_hml-{$this->strReportKey}" name="form_rpt_hml-{$this->strReportKey}" action="adm_hmlreport_ajax.php?keyReport={$this->strReportKey}" method="post" target="_blank">
            <div id="container_hml_report-{$this->strReportKey}" class="container_hml_report">
            </div>
        </form>
EOD;

        if (isset($this->arrParametros["tipo"]) && $this->arrParametros["tipo"] == "scroll") {
            $strReturn .= $this->autoCargaScroll();
        }
        elseif (isset($this->arrParametros["tipo"]) && $this->arrParametros["tipo"] == "paginador") {
            $strReturn .= $this->paginacionReporte();
        }

        return $strReturn;
    }

    private function autoCargaScroll() {
        $strFiltro = "";
        if ($this->boolFilter) {
            $strFiltro = $this->busquedaFiltros("scroll");
        }

        $strOrdena = $this->ordenarColumna("scroll");
        $strRefresca = $this->refrescarReporte("scroll");
        $strAccionesHerramientas = $this->accionesHerramientas();
        $strDropDownMenu = $this->dropdownMenu();
        $strCabeceraFlotante = $this->cabeceraFlotante();

        $strReturn = <<<EOD
        <script type="text/javascript">
            
            var procesando = false;
            var intContPage = 1;
            var strTMPfilters = "";
            var boolPararCarga = false;
            
            $(document).ready(function(){
                
                var rGetData = descargarDataS(1, true);
                
                if(rGetData && !rGetData.NMR){
                    if(intContPage == 1){
                        $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);
                    }
                    else if (intContPage > 1){
                        $("#tbl_hml_rpt-{$this->strReportKey}").append(rGetData.tabla);
                    }
                    procesando = false;
                }
                else {
                    if(intContPage <= 1){
                        $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);    
                    }
                    boolPararCarga = true;
                }
                
                {$strFiltro}
                {$strOrdena}
                {$strRefresca}
                {$strAccionesHerramientas}
                {$strDropDownMenu}
                
                $(document).scroll(function(e){
                
                    if (procesando){
                        return false;
                    }
                    else if ($(window).scrollTop() >= ($(document).height() - $(window).height())*0.7){
                        intContPage++;
                        
                        var rGetData = descargarDataS(intContPage, false);
                        
                        if(rGetData && !rGetData.NMR){
                            if(intContPage == 1){
                                $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);
                            }
                            else if (intContPage > 1){
                                $("#tbl_hml_rpt-{$this->strReportKey}").append(rGetData.tabla);
                            }
                            
                            procesando = false;
                        }
                        else {
                            if(intContPage <= 1){
                                $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);    
                            }
                            
                            boolPararCarga = true;
                            procesando = false;
                        }
                    }
                });
                
                ActivateFloatingHeaders("#tbl_hml_rpt-{$this->strReportKey}");
                
            });
            
            function descargarDataS(page, boolPrimerScroll){
                
                procesando = true;
                
                if(!boolPararCarga){
                    var getData = new fntSendData();
                    getData.strParams = "page="+page+"&boolPrimerScroll="+boolPrimerScroll+strTMPfilters;
                    getData.strUrl = "adm_hmlreport_ajax.php?keyReport={$this->strReportKey}";
                    getData.strDataTypeAjax = "json";
                    getData.strTypeAjax = "POST";
                    getData.boolMsjReturn = false;
                    getData.boolFailData = true;
                    
                    return getData.fntRunSave();
                    
                    if(intContSearchs > 0){
                        $("#refrescar_rpt-{$this->strReportKey}").show();
                    }
                }
            }
                    
            {$strCabeceraFlotante}
        </script>
EOD;
        return $strReturn;
    }

    private function paginacionReporte() {

        $strFiltro = "";
        if ($this->boolFilter) {
            $strFiltro = $this->busquedaFiltros("paginador");
        }

        $strDieEvents = ($this->boolDieEvents) ? $this->matarEventosAnteriores() : "";

        $strOrdena = $this->ordenarColumna("paginador");
        $strRefresca = $this->refrescarReporte("paginador");
        $strAccionesHerramientas = $this->accionesHerramientas();
        $strDropDownMenu = $this->dropdownMenu();
        $strCabeceraFlotante = $this->cabeceraFlotante();

        $strReturn = <<<EOD
        <script type="text/javascript">
            
            {$strDieEvents}
            
            var strTMPfilters = "";
            var intContSearchs = 0;
            
            $(document).ready(function(){
                
                descargarData(1, false);  // Para cargar los resultados de la primera pagina
                
                {$strFiltro}
                
                {$strOrdena}
                
                {$strRefresca}
                
                {$strAccionesHerramientas}
                
                {$strDropDownMenu}
                
                ActivateFloatingHeaders("#tbl_hml_rpt-{$this->strReportKey}");

            });
            
            function descargarData(page, boolFilter){
            
                var getData = new fntSendData();
                getData.strParams = "page="+page+"&paginador=true"+strTMPfilters+"&boolFilter="+boolFilter;
                getData.strUrl = "adm_hmlreport_ajax.php?keyReport={$this->strReportKey}";
                getData.strDataTypeAjax = "json";
                getData.strTypeAjax = "POST";
                getData.boolMsjReturn = false;
                getData.boolFailData = true;

                var rGetData = getData.fntRunSave();
                
                if(rGetData){
                    
                    if(boolFilter){
                        $("#tbl_hml_rpt-{$this->strReportKey} > tbody").replaceWith(rGetData.tabla);
                        $("#pagination_container_all-{$this->strReportKey}").html(rGetData.paginacion);
                    }
                    else {
                        $("#container_hml_report-{$this->strReportKey}").html(rGetData.tabla);
                    }
                    
                    procesando = false;
                    
                    if(intContSearchs > 0){
                        $("#refrescar_rpt-{$this->strReportKey}").show();
                    }
                    refreh_items_hml_report();
                }
            }
            
            $('#container_hml_report-{$this->strReportKey} .pagination li.active').on('click',function(){
                var page = $(this).attr('p');
                descargarData(page, true);
                _UpdateTableHeadersResize();
                _UpdateTableHeadersScroll();
            });
            
            $('#go_btn-{$this->strReportKey}').on('click',function(){
                
                var page = parseInt($('.input_ir_a').val());
                var no_of_pages = parseInt($('.total_num_pagin').attr('a'));
                
                if(page != 0 && page <= no_of_pages){
                    descargarData(page, true);
                }
                else {
                
                    alert('Ingresa una pagina entre 1 y '+no_of_pages);
                    $('.input_ir_a').val("").focus();
                    return false;
                    
                }
                _UpdateTableHeadersResize();
                _UpdateTableHeadersScroll();
            });
            
            $('.input_ir_a').on('change',function(){
                
                var page = parseInt($('.input_ir_a').val());
                var no_of_pages = parseInt($('.total_num_pagin').attr('a'));
                
                if(page != 0 && page <= no_of_pages){
                    descargarData(page, true);
                }
                else {
                    alert('Ingresa una pagina entre 1 y '+no_of_pages);
                    $('.input_ir_a').val("").focus();
                    return false;
                }
                
                _UpdateTableHeadersResize();
                _UpdateTableHeadersScroll();
            });
            
            {$strCabeceraFlotante}
            
            function refreh_items_hml_report(){
                $('#container_hml_report-{$this->strReportKey} .pagination li.active').on('click',function(){
                    var page = $(this).attr('p');
                    descargarData(page, true);
                });

                $('#go_btn-{$this->strReportKey}').on('click',function(){

                    var page = parseInt($('.input_ir_a').val());
                    var no_of_pages = parseInt($('.total_num_pagin').attr('a'));

                    if(page != 0 && page <= no_of_pages){
                        descargarData(page, true);
                    }
                    else {

                        alert('Ingresa una pagina entre 1 y '+no_of_pages);
                        $('.input_ir_a').val("").focus();
                        return false;

                    }
                });

                $('.input_ir_a').on('change',function(){

                    var page = parseInt($('.input_ir_a').val());
                    var no_of_pages = parseInt($('.total_num_pagin').attr('a'));

                    if(page != 0 && page <= no_of_pages){
                        descargarData(page, true);
                    }
                    else {
                        alert('Ingresa una pagina entre 1 y '+no_of_pages);
                        $('.input_ir_a').val("").focus();
                        return false;
                    }
                });
            }
        </script>
EOD;
        return $strReturn;
    }

    private function busquedaFiltros($strTipo) {
        $strReturn = "";
        switch ($strTipo) {
            case "scroll":
                $strReturn = <<<EOD
                $('input[name*="filter"]').on("keypress change", function(e){
                    if((e.type == "keypress" && e.keyCode == 13) || (e.type == "change")){
                        boolPararCarga = false;
                        strTMPfilters = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                        strTMPfilters = "&"+strTMPfilters;
                        $("#container_hml_report-{$this->strReportKey}").html("");
                        intContPage = 1;
                        intContSearchs++;
                        
                        var rGetData = descargarDataS(1, true);
                        
                        if(rGetData && !rGetData.NMR){
                            if(intContPage == 1){
                                $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);
                            }
                            else if (intContPage > 1){
                                $("#tbl_hml_rpt-{$this->strReportKey}").append(rGetData.tabla);
                            }
                            procesando = false;
                        }
                        else {
                            if(intContPage <= 1){
                                $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);
                            }
                            boolPararCarga = true;
                        }
                    }
                });
                
                $('input[name*="having"]').on("keypress change", function(e){
                    if((e.type == "keypress" && e.keyCode == 13) || (e.type == "change")){
                        boolPararCarga = false;
                        strTMPfilters = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                        strTMPfilters = "&"+strTMPfilters;
                        $("#container_hml_report-{$this->strReportKey}").html("");
                        intContPage = 1;
                        intContSearchs++;
                        
                        var rGetData = descargarDataS(1, true);
                        
                        if(rGetData && !rGetData.NMR){
                            if(intContPage == 1){
                                $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);
                            }
                            else if (intContPage > 1){
                                $("#tbl_hml_rpt-{$this->strReportKey}").append(rGetData.tabla);
                            }
                            procesando = false;
                        }
                        else {
                            if(intContPage <= 1){
                                $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);
                            }
                            boolPararCarga = true;
                        }
                    }
                });
EOD;
                break;
            case "paginador":
                if (isset($this->arrParametros["search_mode"]) && $this->arrParametros["search_mode"] == "keyup") {
                    $strReturn = <<<EOD
                    var timer;
                    $(".filters").on("keyup", function(e){
                        
                        clearInterval(timer);
                        
                        timer = setTimeout(function() {
                            var strNameInput = $(this).attr("name");
                            var strValue = $(this).val();
                            $("input[name='"+strNameInput+"']").each(function(){
                                $(this).val(strValue);
                            });
                        
                            intContSearchs++;
                            strTMPfilters = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                            strTMPfilters = "&"+strTMPfilters;
                            descargarData(1, true);
                            
                            _UpdateTableHeadersResize();
                            _UpdateTableHeadersScroll();
                            
                        }, 500);
                    });
                    
                    $(".havings").on("keyup", function(e){
                        
                        clearInterval(timer);
                        
                        timer = setTimeout(function() {
                            
                            var strNameInput = $(this).attr("name");
                            var strValue = $(this).val();
                            $("input[name='"+strNameInput+"']").each(function(){
                                $(this).val(strValue);
                            });
                            
                            intContSearchs++;
                            strTMPfilters = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                            strTMPfilters = "&"+strTMPfilters;
                            descargarData(1, true);
                            _UpdateTableHeadersResize();
                            _UpdateTableHeadersScroll();
                            
                        }, 500);
                    });
EOD;
                }
                else {
                    $strReturn = <<<EOD
                    var timer;
                    $(".filters").on("change", function(e){

                        if((e.type == "keypress" && e.keyCode == 13) || (e.type == "change")){
                            var strNameInput = $(this).attr("name");
                            var strValue = $(this).val();
                            $("input[name='"+strNameInput+"']").each(function(){
                                $(this).val(strValue);
                            });
                            
                            intContSearchs++;
                            strTMPfilters = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                            strTMPfilters = "&"+strTMPfilters;
                            descargarData(1, true);

                         _UpdateTableHeadersResize();
                         _UpdateTableHeadersScroll();
                        }
                    });

                    $(".havings").on("change", function(e){

                        if((e.type == "keypress" && e.keyCode == 13) || (e.type == "change")){
                            
                            var strNameInput = $(this).attr("name");
                            var strValue = $(this).val();
                            $("input[name='"+strNameInput+"']").each(function(){
                                $(this).val(strValue);
                            });
                            
                            intContSearchs++;
                            strTMPfilters = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                            strTMPfilters = "&"+strTMPfilters;
                            descargarData(1, true);

                             _UpdateTableHeadersResize();
                             _UpdateTableHeadersScroll();

                        }
                    });
EOD;
                }

                break;
        }

        return $strReturn;
    }

    private function ordenarColumna($strTipo) {
        $strReturn = "";
        switch ($strTipo) {
            case "scroll":
                $strReturn = <<<EOD
                $(".asc, .desc").on("click",function(){
                    
                    boolPararCarga = false;
                    var idSorter;
                    idSorter = $(this).attr('id').split("-");
                    $("form#form_rpt_hml-{$this->strReportKey} input[class=sorters]").val("");
                    $("form#form_rpt_hml-{$this->strReportKey} input[id="+idSorter[0]+"-isorter]").val($(this).attr('class'));
                    strTMPfilters = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                    strTMPfilters = "&"+strTMPfilters;
                    $("#container_hml_report-{$this->strReportKey}").html("");
                    intContPage = 1;
                    intContSearchs++;
                    
                    var rGetData = descargarDataS(1, true);
                    if(rGetData && !rGetData.NMR){
                        if(intContPage == 1){
                            $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);
                        }
                        else if (intContPage > 1){
                            $("#tbl_hml_rpt-{$this->strReportKey}").append(rGetData.tabla);
                        }
                        procesando = false;
                    }
                    else {
                        if(intContPage <= 1){
                            $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);    
                        }
                        boolPararCarga = true;
                    }
                    
                    $('.up-arrow').show();
                    $('.down-arrow').show();
                    
                    if($(this).attr('class') == 'asc'){
                        $(this).attr('class', 'desc');
                        $(this).find('.up-arrow').show();
                        $(this).find('.down-arrow').hide();                        
                    }
                    else {
                        $(this).attr('class', 'asc')
                        $(this).find('.up-arrow').hide();
                        $(this).find('.down-arrow').show();
                    }
                });
EOD;
                break;
            case "paginador":
                $strReturn = <<<EOD
                $(".asc, .desc").on("click",function(){
                    var idSorter;
                    idSorter = $(this).attr('id').split("-");
                    $("form#form_rpt_hml-{$this->strReportKey} input[class=sorters]").val("");
                    $("form#form_rpt_hml-{$this->strReportKey} input[id="+idSorter[0]+"-isorter]").val($(this).attr('class'));
                    strTMPfilters = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                    strTMPfilters = "&"+strTMPfilters;
                    intContSearchs++;
                    
                    descargarData(1, true);
                    
                    $('.up-arrow').show();
                    $('.down-arrow').show();
                    
                    if($(this).attr('class') == 'asc'){
                        $(this).attr('class', 'desc');
                        $(this).find('.up-arrow').show();
                        $(this).find('.down-arrow').hide();                        
                    }
                    else {
                        $(this).attr('class', 'asc')
                        $(this).find('.up-arrow').hide();
                        $(this).find('.down-arrow').show();
                    }
                    
                    _UpdateTableHeadersResize();
                    _UpdateTableHeadersScroll();
                    
                });
EOD;
                break;
        }

        return $strReturn;
    }

    private function refrescarReporte($strTipo) {
        $strReturn = "";
        switch ($strTipo) {
            case "scroll":
                $strReturn = <<<EOD
                $("#refrescar_rpt-{$this->strReportKey}").on("click",function(){
                    boolPararCarga = false;
                    $('#form_rpt_hml-{$this->strReportKey}').each(function(){
                        this.reset();
                    });
                    strTMPfilters = "";
                    $("#container_hml_report-{$this->strReportKey}").html("");
                    intContPage = 1;
                    
                    var rGetData = descargarDataS(1, true);
                    if(rGetData && !rGetData.NMR){
                        if(intContPage == 1){
                            $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);
                        }
                        else if (intContPage > 1){
                            $("#tbl_hml_rpt-{$this->strReportKey}").append(rGetData.tabla);
                        }
                        procesando = false;
                    }
                    else {
                        if(intContPage <= 1){
                            $("#container_hml_report-{$this->strReportKey}").append(rGetData.tabla);    
                        }
                        boolPararCarga = true;
                    }
                    
                    $(this).hide();
                    intContSearchs = 0;
                    
                    $('.up-arrow').show();
                    $('.down-arrow').show();
                    
                });
EOD;
                break;
            case "paginador":
                $strReturn = <<<EOD
                $("#refrescar_rpt-{$this->strReportKey}").on("click",function(){
                    $('#form_rpt_hml-{$this->strReportKey}').each(function(){
                        this.reset();
                    });
                    strTMPfilters = "";
                    descargarData(1, true);
                    $(this).hide();
                    
                    $('.up-arrow').show();
                    $('.down-arrow').show();
                });
EOD;
                break;
        }

        return $strReturn;
    }

    private function accionesHerramientas() {

        $strAddImg = strGetCoreImageWithPath("mas.png");
        $strRemoveImg = strGetCoreImageWithPath("menos.png");

        $strReturn = <<<EOD
            $("#prt_frm-{$this->strReportKey} label").on("click",function(){
                $("#form_rpt_hml-{$this->strReportKey}").append("<input id=\"boolPrint\" name=\"boolPrint\" value=\"true\">");
                $("#form_rpt_hml-{$this->strReportKey}").append("<input id=\"strPrint\" name=\"strPrint\" value=\""+$(this).attr("class")+"\">");
                $("#form_rpt_hml-{$this->strReportKey}").submit();
                $("#form_rpt_hml-{$this->strReportKey} input[name=boolPrint]").remove();
                $("#form_rpt_hml-{$this->strReportKey} input[name=strPrint]").remove();
                
            });
            
            jQuery.fn.slideFadeOut = function(speed, easing, callback) {
                return this.animate({opacity: 'hide', height: 'hide'}, speed, easing, callback);
            };
            
            jQuery.fn.slideFadeIn = function(speed, easing, callback) {
                return this.animate({opacity: 'show', height: 'show'}, speed, easing, callback);
            };
            
            function scrollPrtTop(){
                return true;
                //$('html, body').animate({ scrollTop: $('#prt_frm-{$this->strReportKey}').offset().top}, 2000);
            };
            
            var intCountRowEmail = 0;
            
            $("#exportar_html-{$this->strReportKey}").on('click', function(event) {
                $("div.pop").slideFadeOut();
                $("#prt_frm-{$this->strReportKey}").slideFadeIn(100,"linear",scrollPrtTop);
                return false;
            });

            $("#close_prt_frm-{$this->strReportKey}").on('click', function() {
                $("#prt_frm-{$this->strReportKey}").slideFadeOut();
                return false;
            });

            $("#imprimir_html-{$this->strReportKey}").on('click', function(event) {
                $("#form_rpt_hml-{$this->strReportKey}").append("<input id=\"boolPrint\" name=\"boolPrint\" value=\"true\">");
                $("#form_rpt_hml-{$this->strReportKey}").append("<input id=\"strPrint\" name=\"strPrint\" value=\"html_print\">");
                $("#form_rpt_hml-{$this->strReportKey}").submit();
                $("#form_rpt_hml-{$this->strReportKey} input[name=boolPrint]").remove();
                $("#form_rpt_hml-{$this->strReportKey} input[name=strPrint]").remove();
                return false;
            });

            $("#enviar_email-{$this->strReportKey}").on('click', function(event) {
                $("div.pop").slideFadeOut();
                $("#email_frm-{$this->strReportKey}").slideFadeIn(100,"linear",scrollPrtTop);
                return false;
            });

            $("#close_email_frm-{$this->strReportKey}").on('click', function() {
                $("#email_frm-{$this->strReportKey}").slideFadeOut();
                return false;
            });

            $("#send_data_email-{$this->strReportKey}").on("click",function(e){
                var boolOk = validateEmailData();

                if(boolOk){

                    var objHidDivEmailFrm = $("#email_frm-{$this->strReportKey}").clone();
                    objHidDivEmailFrm.attr("id","hid_email_frm-{$this->strReportKey}").css({"display":"none"});

                    $("#form_rpt_hml-{$this->strReportKey}").append(objHidDivEmailFrm);
                    $("#form_rpt_hml-{$this->strReportKey}").append("<input id=\"boolPrint\" name=\"boolPrint\" value=\"true\" hidden='hidden'>");
                    $("#form_rpt_hml-{$this->strReportKey}").append("<input id=\"strPrint\" name=\"strPrint\" value=\"email\" hidden='hidden'>");

                    var strValues = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                    var getData = new fntSendData();
                    getData.strParams = strValues;
                    getData.strUrl = "adm_hmlreport_ajax.php?keyReport={$this->strReportKey}";
                    getData.strDataTypeAjax = "json";
                    getData.strTypeAjax = "POST";
                    getData.boolMsjReturn = true;
                    getData.boolFailData = true;

                    getData.fntRunSave();

                    $("#form_rpt_hml-{$this->strReportKey} div[id=hid_email_frm-{$this->strReportKey}]").remove();
                    $("#form_rpt_hml-{$this->strReportKey} input[name=boolPrint]").remove();
                    $("#form_rpt_hml-{$this->strReportKey} input[name=strPrint]").remove();

                }
            });

            addEmailRow();
            
            function validateEmailData(){
                var boolOk = true;

                $("#email_frm-{$this->strReportKey} input:text").each(function(intKey, objInput){
                    if($(objInput).attr("name") == "send_email[asunto]" && $(objInput).val().length <= 0){
                        showBadInputDataEmail($(objInput),true);
                        boolOk = false;
                    }
                    else if(/^send_email\[para\]/.test($(objInput).attr("name"))){
                        if($(objInput).val().length <= 0){
                            boolOk = false;
                            showBadInputDataEmail($(objInput),true);
                        }
                        else if(!validateEmail($(objInput).val())){
                            boolOk = false;
                            showBadInputDataEmail($(objInput),true);
                            showErrorInvalidEmail($(objInput),true);
                        }
                        else{
                            showBadInputDataEmail($(objInput),false);
                            showErrorInvalidEmail($(objInput),false);
                        }
                    }
                    else{
                        showBadInputDataEmail($(objInput),false);
                    }
                });
                
                return boolOk;
            }

            function showBadInputDataEmail(objInput, boolShow){
                if(boolShow){
                    var strColors = "0px 0px 5px 1px #C10000";
                    objInput.css({"box-shadow":strColors,"-webkit-box-shadow":strColors,"-webkit-box-shadow":strColors});
                }
                else{
                    var strColors = "0px 0px 0px 0px #FFFFFF";
                    objInput.css({"box-shadow":strColors,"-webkit-box-shadow":strColors,"-webkit-box-shadow":strColors})
                }
            }
            
            function showErrorInvalidEmail(objInput, boolShow){
                var objTdParent = objInput.parent();
                objTdParent.find("span[class='msj_invalid_email']").remove();
                
                if(boolShow){
                    var strColors = "0px 0px 5px 1px #C10000";
                    var objSpan = $("<span class='msj_invalid_email'></span>").css({
                        "width":"100%",
                        "text-align":"center",
                        "background-color":"#F5A9A9",
                        "color":"#FE2E2E",
                        "display":"inline-block",
                        "box-shadow":strColors,
                        "-webkit-box-shadow":strColors,
                        "-webkit-box-shadow":strColors
                    }).html("El e-mail no es valido");
                    objTdParent.append(objSpan);
                }
            }
            
            function addEmailRow(){
                var objTBodyEmail = $("#tb_data_email-{$this->strReportKey}");
                
                var objTr = $("<tr></tr>");
                
                var objInputEmail = $("<input/>").attr({
                    type:"text",
                    name:"send_email[para][" + intCountRowEmail + "]",
                    class:"field_textbox"
                }).css({
                    width:"100%"
                });
                var objImageAddEmailRow = $("<img src='{$strAddImg}'/>").css({
                    "width":"20px",
                    "height":"20px"
                });
                var objAAddEmailRow = $("<a href='#'></a>").css({
                    "display":"inline-block"
                }).html(objImageAddEmailRow).bind("click",function(e){
                    var objImageRemoveEmailRow = $("<img src='{$strRemoveImg}'/>").css({
                        "width":"20px",
                        "height":"20px"
                    }).bind("click",function(e){
                        removeEmailRow(objTr);
                        e.stopPropagation();
                        e.preventDefault();
                    });
                    $(this).children().replaceWith(objImageRemoveEmailRow);
                    addEmailRow();
                    e.stopPropagation();
                    e.preventDefault();
                });
                
                var objTdEmail = $("<td></td>").html(objInputEmail);
                var objTdAdd = $("<td></td>").css({
                    "text-align":"center",
                    "vertical-align":"middle"
                }).html(objAAddEmailRow);
                
                objTr.append(objTdEmail);
                objTr.append(objTdAdd);
                objTBodyEmail.append(objTr);
                
                intCountRowEmail++;
            }
            
            function removeEmailRow(objRow){
                objRow.remove();
            }

            function validateEmail(email){
                var emailReg = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
                var valid = emailReg.test(email);

                if(!valid) {
                    return false;
                } else {
                    return true;
                }
            }
EOD;

        return $strReturn;
    }

    private function dropdownMenu() {
        $strReturn = <<<EOD
            $(".menudropdown label").on("click",function(){
                var arrSplitId = $(this).parent().parent().attr("id").split("_");
                $("input[name='filter["+arrSplitId[1]+"]']").val($(this).html());
                strTMPfilters = $("#form_rpt_hml-{$this->strReportKey}").serialize();
                strTMPfilters = "&"+strTMPfilters;
                descargarData(1, true);
            });
            
            jQuery.fn.slideFadeOut = function(speed, easing, callback) {
                return this.animate({opacity: 'hide', height: 'hide'}, speed, easing, callback);
            };
             
            jQuery.fn.slideFadeIn = function(speed, easing, callback) {
                return this.animate({opacity: 'show', height: 'show'}, speed, easing, callback);
            };
            
            function scrollPrtTop() {
                return true;
                //$('html, body').animate({ scrollTop: $('#prt_frm-{$this->strReportKey}').offset().top}, 2000);
            };
            
            $("div[id^='dropdown_']").on('click', function(event) {
                var arrSplitId = $(this).attr("id").split("_");
                var intAnchoActualTh = $(this).parent().parent().width();
                $("div[id='dropdownmenu_"+arrSplitId[1]+"']").css("width",intAnchoActualTh-11);
                $("div.pop").slideFadeOut();
                $("div[id='dropdownmenu_"+arrSplitId[1]+"']").slideFadeIn(100,"linear");
                return false;
            });
            
            $(".menudropdown").on('mouseleave',function(){
                $(this).slideFadeOut();
            });
            
            $(document).mouseup(function (e){
                var container = $(".menudropdown");

                if (!container.is(e.target) && container.has(e.target).length === 0){
                    container.slideFadeOut();
                }
            });
EOD;
        return $strReturn;
    }

    private function stylePaginador() {
        global $cfg;
        $strReturn = "";
        if (isset($cfg["core"]["theme_profile"]) && file_exists("profiles/{$cfg["core"]["theme_profile"]}/style_rpt.css")) {
            $strReturn = "<link rel=\"stylesheet\" href=\"profiles/{$cfg["core"]["theme_profile"]}/style_rpt.css\">";
        }
        else if (isset($cfg["core"]["site_profile"]) && file_exists("profiles/{$cfg["core"]["site_profile"]}/style_rpt.css")) {
            $strReturn = "<link rel=\"stylesheet\" href=\"profiles/{$cfg["core"]["site_profile"]}/style_rpt.css\">";
        }
        else if (isset($cfg["core"]["theme"]) && file_exists("themes/{$cfg["core"]["theme"]}/style_rpt.css")) {
            $strReturn = "<link rel=\"stylesheet\" href=\"themes/{$cfg["core"]["theme"]}/style_rpt.css\">";
        }
        else {
            $strReturn = "<link rel=\"stylesheet\" href=\"libs/hml_report/style_rpt.css\">";
        }
        //debug::drawdebug($strReturn);
        return $strReturn;
    }

    private function matarEventosAnteriores() {
        $strReturn = <<<EOD
            $('#container_hml_report-{$this->strReportKey} .pagination li.active').off();
            $('#go_btn-{$this->strReportKey}').off();
            $('.input_ir_a').off();
            $('input[name*="filter"]').off();
            $('input[name*="having"]').off();
            $(".filters").off();
            $(".havings").off();
            $(".asc, .desc").off();
            $("#refrescar_rpt-{$this->strReportKey}").off();
            $("#prt_frm-{$this->strReportKey} label").off();
            $("#exportar_html-{$this->strReportKey}").off();
            $("#close_prt_frm-{$this->strReportKey}").off();
            $("#imprimir_html-{$this->strReportKey}").off();
            $("#enviar_email-{$this->strReportKey}").off();
            $("#close_email_frm-{$this->strReportKey}").off();
            $("#send_data_email-{$this->strReportKey}").off();
            $("#close_prt_frm-{$this->strReportKey}").off();
            $(".menudropdown label").off();
            $("div[id^='dropdown_']").off();
            $(".menudropdown").off();
EOD;
        return $strReturn;
    }

    private function cabeceraFlotante() {

        $strReturn = <<<EOD
            function _UpdateTableHeadersScroll() {
                $("div.divTableWithFloatingHeader").each(function() {
                    var originalHeaderRow = $(".tableFloatingHeaderOriginal", this);
                    var floatingHeaderRow = $(".tableFloatingHeader", this);
                    var offset = $(this).offset();
                    var scrollTop = $(window).scrollTop();
                    // check if floating header should be displayed
                    if ((scrollTop > offset.top) && (scrollTop < offset.top + $(this).height() - originalHeaderRow.height())) {
                        floatingHeaderRow.css("display", "block");
                        floatingHeaderRow.css("left", -$(window).scrollLeft());
                    }
                    else {
                        floatingHeaderRow.css("display", "none");
                    }
            
                    // Copy cell widths from original header
                    $("#hml_rpt_tr_filters-{$this->strReportKey} > th", floatingHeaderRow).each(function(index) {
                        var cellWidth = $("th", originalHeaderRow).eq(index).css('width');
                        $(this).css('width', cellWidth);
                    });
            
                    // Copy row width from whole table
                    floatingHeaderRow.css("width", Math.max(originalHeaderRow.width(), $(this).width()) + "px");
                    floatingHeaderRow.css("margin-left", originalHeaderRow.offset().left);
                });
            }

            function _UpdateTableHeadersResize() {
                $("div.divTableWithFloatingHeader").each(function() {
                    var originalHeaderRow = $(".tableFloatingHeaderOriginal", this);
                    var floatingHeaderRow = $(".tableFloatingHeader", this);
                    // Copy cell widths from original header
                    $("#hml_rpt_tr_filters-{$this->strReportKey} > th", floatingHeaderRow).each(function(index) {
                        var cellWidth = $("th", originalHeaderRow).eq(index).css('width');
                        $(this).css('width', cellWidth);
                    });

                    // Copy row width from whole table
                    floatingHeaderRow.css("width", Math.max(originalHeaderRow.width(), $(this).width()) + "px");
                    floatingHeaderRow.css("margin-left", originalHeaderRow.offset().left);

                });
            }

            function ActivateFloatingHeaders(selector_str){
                $(selector_str).each(function() {
                    $(this).wrap("<div class=\"divTableWithFloatingHeader table-responsive\" style=\"position:relative\"></div>");

                    // use first row as floating header by default
                    var floatingHeaderSelector = "thead:first";
                    var explicitFloatingHeaderSelector = "thead.floating-header"
                    if ($(explicitFloatingHeaderSelector, this).length){
                        floatingHeaderSelector = explicitFloatingHeaderSelector;
                    }

                    var originalHeaderRow = $(floatingHeaderSelector, this).first();
                    var clonedHeaderRow = originalHeaderRow.clone()
                    originalHeaderRow.before(clonedHeaderRow);

                    clonedHeaderRow.addClass("tableFloatingHeader");
                    clonedHeaderRow.css("position", "fixed");
                    // not sure why but 0px is used there is still some space in the top
                    clonedHeaderRow.css("top", "-2px");
                    clonedHeaderRow.css("margin-left", $(this).offset().left);
                    clonedHeaderRow.css("display", "none");
                    clonedHeaderRow.css("z-index", "102");

                    originalHeaderRow.addClass("tableFloatingHeaderOriginal");
                });
                _UpdateTableHeadersResize();
                _UpdateTableHeadersScroll();
                $(window).scroll(_UpdateTableHeadersScroll);
                $(window).resize(_UpdateTableHeadersResize);
            }
EOD;

        return $strReturn;
    }

}
