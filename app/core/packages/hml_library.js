/**
 * hml_library.js (2008-oct-28)
 * (c) by Alejandro Gudiel
 * All Rights Reserved




 * License does not permit use by third parties
**/

var localLibraryObject_BrowserInformation = false;
function getBrowserInformation() {
    if (localLibraryObject_BrowserInformation) {
        return localLibraryObject_BrowserInformation;
    }

    var strTMP = navigator.appVersion;

    var intTMP = strTMP.indexOf("(", 0);
    if (intTMP >= 1) strTMP = strTMP.substr(intTMP, strTMP.length);
    var strTMP2 = strTMP.substr(1, strTMP.length - 2);
    var arrTMP = strTMP2.split("; ");

    var objInfo = new Object();

    if (arrTMP[2]) {
        objInfo.OS = JavaScriptTextTrim(arrTMP[2]);

        if (arrTMP[1] == "U") {
            intTMP = strTMP.indexOf(")", intTMP);
            var strExtra = strTMP.substr(intTMP + 1, strTMP.length);
            objInfo.browser = JavaScriptTextTrim(strExtra);
        }
        else {
            objInfo.browser = JavaScriptTextTrim(arrTMP[1]);
        }
    }
    else {
        objInfo.OS = arrTMP[0];
        objInfo.browser = "Unknown";
    }

    objInfo.boolIsWindows = (objInfo.OS.substr(0, 3) == "Win");
    objInfo.boolIsMac = (objInfo.OS.indexOf("Mac", 0) >= 0);
    objInfo.boolIsMSIE = (objInfo.browser.substr(0, 4) == "MSIE");
    objInfo.IEVer = 0;
    if (objInfo.boolIsMSIE) {
        arrTMP = objInfo.browser.split(" ");
        objInfo.IEVer = arrTMP[1];
    }
    objInfo.boolIsChrome = (objInfo.browser.indexOf("Chrome", 0) >= 0);
    objInfo.boolIsSafari = (objInfo.browser.indexOf("Safari", 0) >= 0 && !objInfo.boolIsChrome);
    objInfo.boolIsMozilla = (objInfo.browser.indexOf("Gecko", 0) >= 0 && !objInfo.boolIsSafari && !objInfo.boolIsChrome);

    localLibraryObject_BrowserInformation = objInfo;

    return objInfo;
}

String.prototype.htmlEntities = function () {
   return this.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#039;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
};

String.prototype.htmlEntities_decode = function () {
   return this.replace(/&amp;/g,'&').replace(/&quot;/g,'"').replace(/&#039;/g,"'").replace(/&lt;/g,'<').replace(/&gt;/g,'>');
};

/*
function htmlspecialcharsUndo(strVariable) {
    strVariable = strVariable.replace(/&amp;/g,"&");
    strVariable = strVariable.replace(/&quot;/g,"\"");
    strVariable = strVariable.replace(/&#039;/g,"'");
    strVariable = strVariable.replace(/&lt;/g,"<");
    strVariable = strVariable.replace(/&gt;/g,">");

    return strVariable;
}
*/

var globalJavaIncludedFiles = new Array();
function include_once(filename, onload) {
    if (globalJavaIncludedFiles[filename]) return true;
    if (!onload) onload = "";

    var head = document.getElementsByTagName("head")[0];

    script = document.createElement("script");
    script.src = filename;
    script.type = "text/javascript";
    if (onload != "") script.onload = onload;

    head.appendChild(script);

    globalJavaIncludedFiles[filename] = true;

    return true;
}

function addLoadListener(objFunction, toObject) {
    if (!toObject) toObject = window;

    if (typeof toObject.addEventListener != "undefined") {
        toObject.addEventListener("load", objFunction, false);
    }
    else if (toObject == window && typeof document.addEventListener != "undefined") {
        document.addEventListener("load", objFunction, false);
    }
    else if (typeof toObject.attachEvent != "undefined") {
        toObject.attachEvent("onload", objFunction);
    }
    else {
        var hmlOnLoad = toObject.onload;
        if (typeof toObject.onload != "function") {
            toObject.onload = objFunction;
        }
        else {
            toObject.onload = function () {
                hmlOnLoad();
                objFunction();
            }
        }
    }
}

function addUnLoadListener(objFunction, toObject) {
    if (!toObject) toObject = window;

    if (typeof toObject.addEventListener != "undefined") {
        toObject.addEventListener("beforeunload", objFunction, false);
    }
    else if (toObject == window && typeof document.addEventListener != "undefined") {
        document.addEventListener("beforeunload", objFunction, false);
    }
    else if (typeof toObject.attachEvent != "undefined") {
        toObject.attachEvent("onunload", objFunction);
    }
    else {
        var hmlOnUnLoad = toObject.onunload;
        if (typeof toObject.onunload != "function") {
            toObject.onunload = objFunction;
        }
        else {
            toObject.onunload = function () {
                hmlOnUnLoad();
                objFunction();
            }
        }
    }
}

function addResizeListener(objFunction) {
    if (typeof window.addEventListener != "undefined") {
        window.addEventListener("resize", objFunction, false);
    }
    else if (typeof document.addEventListener != "undefined") {
        document.addEventListener("resize", objFunction, false);
    }
    else if (typeof window.attachEvent != "undefined") {
        window.attachEvent("onresize", objFunction);
    }
    else {
        var hmlOnResize = window.onresize;
        if (typeof window.onresize != "function") {
            window.onresize = objFunction;
        }
        else {
            window.onresize = function () {
                hmlOnResize();
                objFunction();
            }
        }
    }
}

function getDocumentLayer(strName, objDoc) {
    var p,i,x=false;

    if(!objDoc) objDoc=document;

    if(objDoc[strName]) {
        x=objDoc[strName];
        if (!x.tagName) x = false;
    }

    if (!x && objDoc.all) x=objDoc.all[strName];
    for (i=0;!x && i<objDoc.forms.length; i++) x=objDoc.forms[i][strName];
    if (!x && objDoc.getElementById) x=objDoc.getElementById(strName);
    for (i=0;!x && objDoc.layers && i<objDoc.layers.length; i++) x=getDocumentLayer(strName,objDoc.layers[i].document);

    return x;
}

function getImageMapAreaInfo(objElement) {
    var arrReturn = new Array();

    if (!objElement.hmlCoord) {
        objElement.hmlCoord = new Array();
        var arrCoords = objElement.coords.split(",");
        var boolIsX = true;
        var intX1 = 1000000;
        var intY1 = 1000000;
        var intX2 = 0;
        var intY2 = 0;
        for (intKey in arrCoords) {
            arrCoords[intKey] = 1*arrCoords[intKey];
            if (boolIsX) {
                if (arrCoords[intKey] < intX1) intX1 = arrCoords[intKey];
                if (arrCoords[intKey] > intX2) intX2 = arrCoords[intKey];
            }
            else {
                if (arrCoords[intKey] < intY1) intY1 = arrCoords[intKey];
                if (arrCoords[intKey] > intY2) intY2 = arrCoords[intKey]
            }
            boolIsX = (!boolIsX);
        }
        objElement.hmlCoord["left"] = intX1;
        objElement.hmlCoord["top"] = intY1;

        objElement.hmlCoord["width"] = intX2 - intX1;
        objElement.hmlCoord["height"] = intY2 - intY1;
    }

    arrReturn = objElement.hmlCoord;

    return arrReturn;
}

function getObjAbsoluteCoordinates(objElement) {
    var arrReturn = new Array();
    var intLeft = 0;
    var intTop = 0;
    var objTMP = objElement;
    var isInternetExplorer = (navigator.appName.indexOf("Microsoft") != -1);

    if(!objElement){
        arrReturn["left"] = 0;
        arrReturn["top"] = 0;
        return arrReturn;
    }
    
    if ((objElement.tagName == "area" || objElement.tagName == "AREA") && (!isInternetExplorer || true)) {
        var arrTMP = getImageMapAreaInfo(objElement);
        arrReturn["left"] = arrTMP["left"];
        arrReturn["top"] = arrTMP["top"];
    }
    else {
        if(objTMP.offsetParent) {
            while (1) {
                intLeft += objTMP.offsetLeft;
                intTop += objTMP.offsetTop;

                if (!objTMP.offsetParent) break;
                objTMP = objTMP.offsetParent;
            }
        }
        else if(objTMP.x && objTMP.y) {
            intLeft += objTMP.x;
            intTop += objTMP.y;
        }

        arrReturn["left"] = intLeft;
        arrReturn["top"] = intTop;
    }

    return arrReturn;
}

function getObjDimentions(objElement) {
    var arrReturn = new Array();
    var isInternetExplorer = (navigator.appName.indexOf("Microsoft") != -1);

    if(!objElement){
        arrReturn["width"] = 0;
        arrReturn["height"] = 0;
        return arrReturn;
    }
    
    if ((objElement.tagName == "area" || objElement.tagName == "AREA") && (!isInternetExplorer || true)) { //20100624 AG: Probando el true
        var arrTMP = getImageMapAreaInfo(objElement);
        arrReturn["width"] = arrTMP["width"];
        arrReturn["height"] = arrTMP["height"];
    }
    else {
        arrReturn["width"] = objElement.offsetWidth;
        arrReturn["height"] = objElement.offsetHeight;
    }

    return arrReturn;
}

// OJO, no devuelve lo mismo para IE que para FF
function getWindowSize(objDoc) {
    if(!objDoc) objDoc=document;

    var intWidth = 0;
    var intHeight = 0;
    if (parseInt(navigator.appVersion) > 3) {
         if (navigator.appName.indexOf("Microsoft")!=-1) {
             intWidth = objDoc.documentElement.clientWidth;
             intHeight = objDoc.documentElement.clientHeight;
             if (intWidth == 0 || intHeight == 0) {
                  intWidth = objDoc.body.offsetWidth;
                  intHeight = objDoc.body.offsetHeight;
             }
         }
         else {
             intWidth = window.innerWidth;
              intHeight = window.innerHeight;
         }
    }
    var arrReturn = new Array();
    arrReturn["width"] = intWidth;
    arrReturn["height"] = intHeight;

    return arrReturn;
}

function getScrollInformation(objDoc) {
    if(!objDoc) objDoc=document;

    var arrReturn = new Array();
    arrReturn["width"] = objDoc.body.scrollWidth;
    arrReturn["height"] = objDoc.body.scrollHeight;
    arrReturn["left"] = objDoc.body.scrollLeft;
    arrReturn["top"] = objDoc.body.scrollTop;

    return arrReturn;
}

function JavaScriptTextTrim(str) {
    var whitespace = new String(" \t\n\r");
    var s = new String(str);

    if (whitespace.indexOf(s.charAt(0)) != -1) {
        var j=0, i = s.length;
        while (j < i && whitespace.indexOf(s.charAt(j)) != -1) j++;
        s = s.substring(j, i);
    }
    if (whitespace.indexOf(s.charAt(s.length-1)) != -1) {
        var i = s.length - 1;
        while (i >= 0 && whitespace.indexOf(s.charAt(i)) != -1) i--;
        s = s.substring(0, i+1);
    }
    return s;
}

function deleteSelectOptions(objSelect) {
    objSelect.innerHTML = "";
}

function addOptionToSelect(objDocument, objSelect, strValue, strText, boolSelected, boolOptGroup) {
    if (!boolOptGroup) boolOptGroup = false;

    var objBrowserInformation = getBrowserInformation();

    if (boolOptGroup) {
        if (objBrowserInformation.boolIsMSIE && objBrowserInformation.IEVer < 7) {
            // Si es explorer menor a 7, que no agregue el group y regrese el mismo select para que el codigo de afuera funcione igual...
            optTMP = objSelect;
        }
        else {
            var optTMP = objDocument.createElement("OPTGROUP");
            optTMP.label = strText;

            objSelect.appendChild(optTMP);
        }
    }
    else {
        var optTMP = objDocument.createElement("option");
        optTMP.value = strValue;
        optTMP.text = strText;
        optTMP.label = strText;
        optTMP.selected = boolSelected;

        if (objSelect.tagName == "SELECT" || objSelect.tagName == "select") {
            objSelect.options.add(optTMP);
        }
        else {
            objSelect.appendChild(optTMP);
        }
    }

    return optTMP;
}

function preloadImage(strPath) {
    var imgPreloader = new Image();
    imgPreloader.src = strPath;

    return imgPreloader;
}

function isLeapYear(intYear) {
    if (intYear % 4 == 0) {
        if (intYear % 100 == 0) {
            if (intYear % 400 == 0) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return true;
        }
    }
    else {
        return false;
    }
}

function boolCheckDate(intYear, intMonth, intDay) {
    boolReturn = true;

    arrMeses = new Array();
    arrMeses[1] = 31;
    arrMeses[2] = (isLeapYear(intYear))?29:28;
    arrMeses[3] = 31;
    arrMeses[4] = 30;
    arrMeses[5] = 31;
    arrMeses[6] = 30;
    arrMeses[7] = 31;
    arrMeses[8] = 31;
    arrMeses[9] = 30;
    arrMeses[10] = 31;
    arrMeses[11] = 30;
    arrMeses[12] = 31;

    intYear = validarEntero(intYear);
    intMonth = validarEntero(intMonth);
    intDay = validarEntero(intDay);

    if(intYear.length == 0) boolReturn = false;
    else if(intMonth.length == 0) boolReturn = false;
    else if(intDay.length == 0) boolReturn = false;

    if(intMonth > 12 || intMonth < 1) {
        boolReturn = false;
    }
    else {
        if(intDay > arrMeses[intMonth * 1] || intDay < 1) {
            boolReturn = false;
        }
    }

    return boolReturn;
}

function validarEntero(intvalue){
    var RegExPattern = /^(?:\+|-)?\d+$/;
    if ((intvalue.match(RegExPattern)) && (intvalue !='')) {
        return intvalue;
    } else {
         return "";
    }

}

function debugJs($MyVar,$strName){
    if (!$MyVar) var $MyVar;
    if (!$strName)$strName="";

    var varType = typeof($MyVar);

    var strTitleDebug = "****HML-DEBUG****\n";
        strTitleDebug += "Var" + (($strName!=0) ? " *" + $strName + "* " : "" )+ "Type " + varType;

    console.log("\r\r");
    console.info(strTitleDebug);
    console.info($MyVar);
    console.info("*****************");
    return console.log("\r\r");;
}

function ucWords(string){
    if(string != null){
        var arrayWords;
        var returnString = "";
        var len;
        arrayWords = string.split(" ");
        len = arrayWords.length;
        for(i=0;i < len ;i++){
            if(i != (len-1)){
                returnString = returnString+ucFirst(arrayWords[i])+" ";
            }
            else{
                returnString = returnString+ucFirst(arrayWords[i]);
            }
        }
        return returnString;
    }
}
function ucFirst(string){
    return string.substr(0,1).toUpperCase()+string.substr(1,string.length).toLowerCase();
}

function array_flip(trans){
    var key, tmp_ar = {};
    if (trans && typeof trans=== 'object' && trans.change_key_case) { // Duck-type check for our own array()-created PHPJS_Array
        return trans.flip();
    }
    for (key in trans) {
        if (!trans.hasOwnProperty(key)) {continue;}
        tmp_ar[trans[key]] = key;
    }
    return tmp_ar;
}

/*Ejemplo de uso de la clase
    //Parametro opcional
*   objTEST = new drawWidgets({ objDialogAlert: "dialog" });
    var arrWidgets = new Array();
        arrWidgets['title']='Localidad aun no configurada:&nbsp;';
        arrWidgets['txt']='La localidad <i>jdjdjd</i>, aun no se a configurado.';


    //objTEST.alertDialog("test","test tittle",false);
    objTEST.drawMesaggeWidget(arrWidgets,{"width":"350px;"});
*/
var drawWidgets = function drawWidgets(customSettings) {
    var self = this;
    var elementLoading;
    var elementDialogMesagge;
    var elementDialogAlert;
    var defaults = {
        objLoading: "",
        objDialogMesagge: "",
        objDialogAlert: "",
        idDialog:"alertDialog",
        form: "frmAlertDialog"
    };

    customSettings || (customSettings = {});
    let settings = $.extend({}, defaults, customSettings);

    this.setOptions = function (customSettings) {
        customSettings || (customSettings = {});
        //settings = JSON.parse(JSON.stringify())
        settings = $.extend({}, defaults, customSettings);
    };

    this.getOptions = function () {
        return settings;
    };

     this.openLoading = function () {
        var objDialog = self.dialogoCargando(false);
        return objDialog;
    };

    this.openProgressBar = function (boolModal) {
        if (!boolModal) boolModal = false;
        var objDialog = self.dialogoProgressBar(false, boolModal);
        return objDialog;
    };

    this.dialogoCargando = function (boolCerrar, boolModal) {
        if (!boolModal) boolModal = true;
        if (!boolCerrar) boolCerrar = false;
        var objCargando = $(".g-af-loading").length > 0 ? $(".g-af-loading") : false;
        if (boolCerrar && !objCargando) return false;else if (!objCargando) {
            objCargando = $("<div></div>").attr({
                "class": "g-af-loading"
            });
        }
        if (objCargando.hasClass("g-af-loading-show") && !boolCerrar) {
            return true;
        }
        if (boolCerrar) {
            objCargando.removeClass("g-af-loading-show");
            objCargando.remove();
            return true;
        }
        var obj = $("<div></div>").attr({
            "class": "g-af-loading-obj"
        });
        for (var i = 1; i < 9; i++) {
            var objChildren = $("<div></div>").attr({
                "class": "fountainG fountainG_" + i
            });
            obj.append(objChildren);
        }
        objCargando.append(obj);
        var obj = $("<div>Cargando...</div>").attr({
            "class": "g-af-loading-lbl"
        });
        objCargando.append(obj);
        $("body").append(objCargando);
        objCargando.addClass("g-af-loading-show");
        return objCargando;
    };

    this.dialogoProgressBar = function (boolCerrar, boolModal) {
        var objCargando = self.dialogoCargando(boolCerrar, boolModal);
        return objCargando;
    };

    this.closeLoading = function () {
        var objDialog = self.dialogoCargando(true);
        return objDialog;
    };

    this.closeProgressBar = function () {
        var objDialog = self.dialogoProgressBar(true);
        return objDialog;
    };

    this.alertDialog = function (strText, strTitle, boolReload, fncBtnOk, waitFncBtnOk, arrButtons, cssSizeModal, boolDisMiss = true) {
        $(function () {
            let cssModal = "";
            if (!fncBtnOk) fncBtnOk = function fncBtnOk() {
                return true;
            };
            if (!waitFncBtnOk) waitFncBtnOk = false;
            if (!boolReload) boolReload = false;
            if (!strTitle) strTitle = "MENSAJE DEL SISTEMA";
            if (!arrButtons) {
                arrButtons = {};
                /*arrButtons.ok = {};
                arrButtons.ok.nombre = "Aceptar";
                arrButtons.ok.funcion = "close";*/
            }
            if (!cssSizeModal){
                cssModal = "modal-hml-header";
            }
            else{
                cssSizeModal = cssSizeModal;
            }

            if ($(`#${settings.idDialog}`).length > 0){
                $(`#${settings.idDialog}`).remove();
                $(`#${settings.form}`).remove();
            }

            if (!$(`#${settings.idDialog}`).length > 0) {
                let alertDialog = `   <div id="${settings.idDialog}" class="modal-hml">
                                          <div class="modal-hml-content-sm">
                                              <div class="modal-hml-header">
                                                  ${boolDisMiss ? '<button id="close-modal-hml" style="border: none; background: white;">&times;</button>' : ''}
                                                  <h3 class="strTitleModal-hml">
                                                      ${strTitle}
                                                  </h3>
                                              </div>
                                              <form id="${settings.form}">
                                                  <div class="modal-hml-body">
                                                        ${strText}
                                                  </div>
                                              </form>
                                              <div class="modal-hml-footer">
                                                  
                                              </div>
                                          </div>
                                      </div>`;
                $('body').append(alertDialog);
            }
            else {
                $(`#${settings.idDialog} .strTitleModal-hml`).html(strTitle);
                $(`#${settings.idDialog} .modal-hml-body`).html(strText);
                $(`#${settings.idDialog} .modal-hml-footer`).html("");
            }

            const modalElement = document.getElementById(`${settings.idDialog}`);

            for(let key in arrButtons){
                const val = arrButtons[key];
                var button = $("<button>" + val.nombre + "</button>").attr({
                    "type": "button",
                    "class": (val.cssClass !=null)?val.cssClass :"btn btn-default floatRightMargin"
                });
                if (val.funcion === "close") {
                    button.on('click', () => {
                        if (boolReload) {
                            location.reload();
                        }
                        modalElement.style.display = "none";
                    });
                }
                else {
                    if(typeof val.funcion ==="string"){
                        button.attr("onclick", val.funcion);
                    }
                    else{
                        button.on("click", (e)=>{
                            val.funcion(e);
                        });
                    }
                }

                $(`#${settings.idDialog} .modal-hml-footer`).append(button);
            }


            setTimeout( () => {
                modalElement.classList.add('swingY');
            }, 250);

            modalElement.style.display = "block";


            const spanClose = document.getElementById('close-modal-hml');
            if(spanClose){
                spanClose.addEventListener('click', () => {
                    modalElement.style.display = "none";

                    if (boolReload) {
                        location.reload();
                    }
                    else {
                        if (waitFncBtnOk) {
                            fncBtnOk();
                        } else {
                            fncBtnOk();
                        }
                        if ($(`#${settings.idDialog}`).length > 0){
                            $(`#${settings.idDialog}`).remove();
                            $('.modal-backdrop').remove();

                        }
                        if ($(`#${settings.form}`).length > 0){
                            $(`#${settings.form}`).remove();
                            $('.modal-backdrop').remove();
                        }
                    }
                });
            }
        });
    };

    this.closeDialog = function () {
        const modalElement = document.getElementById(`${settings.idDialog}`);
        if(modalElement){
            modalElement.style.display = "none";
        }
        //$('.modal-backdrop').remove();
    };

    this.alertBotstrap = function (strText, strTitle, strType, arrButtons) {
        if (!strText) return false;
        if (!strTitle) strTitle = "MENSAJE DEL SISTEMA";
        if (!strType) strType = "info";
        if (!arrButtons) arrButtons = {};

        var container = $(".alert-main");

        if (container.length > 0) {
            container.find(".alert").addClass("alert-" + strType);
            container.find("h4").html(strTitle);
            container.find(".msj").html(strText);

            container.find(".close").on("click", function () {
                container.addClass("hide");
            });

            if (Object.keys(arrButtons).length > 0) {
                container.find(".buttons").removeClass("hide");
                for (b in arrButtons) {
                    var button = arrButtons[b];
                    var objButton = "\n                        <button type=\"button\" class=\"btn btn-" + button.type + "\" onclick=\"" + button.allok + "()\">" + button.title + "</button>\n                    ";
                    container.find(".buttons").append(objButton);
                }
            } else {
                setTimeout(function () {
                    container.addClass("hide");
                }, 5000);
            }
            container.removeClass("hide");
        }
    };

    this.drawSelectFromObject = function(strIdObjHtml, objJson, addOpctionEmpty, tags, value, strLabelEmpty){

        if(!strIdObjHtml) strIdObjHtml = "select_javascript";
        if(!objJson)
            objJson = false;
        if(!addOpctionEmpty) addOpctionEmpty = false;
        if(!tags) tags = {};

        var arrValues = {};

        if(!value)
            arrValues[0] = true;
        else if(typeof(value) === 'object')
            arrValues = value;
        else if (typeof(value) !== 'boolean')
            arrValues[value] = true;

        if(typeof(strLabelEmpty) === "undefined"){
            strLabelEmpty = "Seleccionar uno";
        }

        var ObjHtmlSelect = $("<select class='field_selectbox'></select>").attr({"id":strIdObjHtml,"name":strIdObjHtml});
        ObjHtmlSelect.attr(tags)
            .addClass("field_selectbox");


        if(addOpctionEmpty){
            var ObjOption = $("<option></option>");
            ObjOption.val(0);
            ObjOption.html(strLabelEmpty);
            ObjHtmlSelect.append(ObjOption);
        }
        if(objJson){
            $.each(objJson,function (key,value){
                var ObjOption = $("<option></option>");
                ObjOption.val(key);
                ObjOption.html(value);
                if(arrValues[key])
                    ObjOption.attr({"selected":"selected"});
                ObjHtmlSelect.append(ObjOption);
            });
        }
        return ObjHtmlSelect;
    }
};
/*Para el uso correcto de la function incluir el plugin de noticeAdd de jquery
* o = Objeto de jquery eje: $("#tuobjeto")
* min = minimo de caracteres
* max = maximo de caracteres
* n = algun titulo para el objeto
* mostrar o no el error
*/
var checkLength = function( o, min, max, n, boolError ) {
    if(!n) n = false;
    if(!boolError) boolError = false;
    var boolMax = (max > 0)?true:false;
    var strTitle = "";
        if(n) var strTitle = n;
    if(boolMax){
        if (max == 0 || (o.val().length > max || o.val().length < min)) {
            if(boolError){
                jQuery.noticeAdd({text: "<b>El tamaño del campo " + strTitle + " tiene que ser entre "+min+" y "+max+". !</b><br><br>",type:"warning",stay:false});
            }
            return false;
        }
        else{
            return true;
        }
    }
    else{
        if((o.val().length < min)){
            if(boolError){
                jQuery.noticeAdd({text: "<b>El tamaño minimo del campo " + strTitle + " tiene que ser "+min+" !</b><br><br>",type:"warning",stay:false});
            }
            return false;
        }
        else {
            return true;
        }
    }
}
function MD5 (str){
    if(!str)str = "";
    /*
     * A JavaScript implementation of the RSA Data Security, Inc. MD5 Message
     * Digest Algorithm, as defined in RFC 1321.
     * Copyright (C) Paul Johnston 1999 - 2000.
     * Updated by Greg Holt 2000 - 2001.
     * See http://pajhome.org.uk/site/legal.html for details.
     */

    /*
     * Convert a 32-bit number to a hex string with ls-byte first
     */
    var hex_chr = "0123456789abcdef";
    function rhex(num)
    {
      str = "";
      for(j = 0; j <= 3; j++)
        str += hex_chr.charAt((num >> (j * 8 + 4)) & 0x0F) +
               hex_chr.charAt((num >> (j * 8)) & 0x0F);
      return str;
    }

    /*
     * Convert a string to a sequence of 16-word blocks, stored as an array.
     * Append padding bits and the length, as described in the MD5 standard.
     */
    function str2blks_MD5(str)
    {
      nblk = ((str.length + 8) >> 6) + 1;
      blks = new Array(nblk * 16);
      for(i = 0; i < nblk * 16; i++) blks[i] = 0;
      for(i = 0; i < str.length; i++)
        blks[i >> 2] |= str.charCodeAt(i) << ((i % 4) * 8);
      blks[i >> 2] |= 0x80 << ((i % 4) * 8);
      blks[nblk * 16 - 2] = str.length * 8;
      return blks;
    }

    /*
     * Add integers, wrapping at 2^32. This uses 16-bit operations internally
     * to work around bugs in some JS interpreters.
     */
    function add(x, y)
    {
      var lsw = (x & 0xFFFF) + (y & 0xFFFF);
      var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
      return (msw << 16) | (lsw & 0xFFFF);
    }

    /*
     * Bitwise rotate a 32-bit number to the left
     */
    function rol(num, cnt)
    {
      return (num << cnt) | (num >>> (32 - cnt));
    }

    /*
     * These functions implement the basic operation for each round of the
     * algorithm.
     */
    function cmn(q, a, b, x, s, t)
    {
      return add(rol(add(add(a, q), add(x, t)), s), b);
    }
    function ff(a, b, c, d, x, s, t)
    {
      return cmn((b & c) | ((~b) & d), a, b, x, s, t);
    }
    function gg(a, b, c, d, x, s, t)
    {
      return cmn((b & d) | (c & (~d)), a, b, x, s, t);
    }
    function hh(a, b, c, d, x, s, t)
    {
      return cmn(b ^ c ^ d, a, b, x, s, t);
    }
    function ii(a, b, c, d, x, s, t)
    {
      return cmn(c ^ (b | (~d)), a, b, x, s, t);
    }

    /*
     * Take a string and return the hex representation of its MD5.
     */
    function calcMD5(str)
    {
      x = str2blks_MD5(str);
      a =  1732584193;
      b = -271733879;
      c = -1732584194;
      d =  271733878;

      for(i = 0; i < x.length; i += 16)
      {
        olda = a;
        oldb = b;
        oldc = c;
        oldd = d;

        a = ff(a, b, c, d, x[i+ 0], 7 , -680876936);
        d = ff(d, a, b, c, x[i+ 1], 12, -389564586);
        c = ff(c, d, a, b, x[i+ 2], 17,  606105819);
        b = ff(b, c, d, a, x[i+ 3], 22, -1044525330);
        a = ff(a, b, c, d, x[i+ 4], 7 , -176418897);
        d = ff(d, a, b, c, x[i+ 5], 12,  1200080426);
        c = ff(c, d, a, b, x[i+ 6], 17, -1473231341);
        b = ff(b, c, d, a, x[i+ 7], 22, -45705983);
        a = ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
        d = ff(d, a, b, c, x[i+ 9], 12, -1958414417);
        c = ff(c, d, a, b, x[i+10], 17, -42063);
        b = ff(b, c, d, a, x[i+11], 22, -1990404162);
        a = ff(a, b, c, d, x[i+12], 7 ,  1804603682);
        d = ff(d, a, b, c, x[i+13], 12, -40341101);
        c = ff(c, d, a, b, x[i+14], 17, -1502002290);
        b = ff(b, c, d, a, x[i+15], 22,  1236535329);

        a = gg(a, b, c, d, x[i+ 1], 5 , -165796510);
        d = gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
        c = gg(c, d, a, b, x[i+11], 14,  643717713);
        b = gg(b, c, d, a, x[i+ 0], 20, -373897302);
        a = gg(a, b, c, d, x[i+ 5], 5 , -701558691);
        d = gg(d, a, b, c, x[i+10], 9 ,  38016083);
        c = gg(c, d, a, b, x[i+15], 14, -660478335);
        b = gg(b, c, d, a, x[i+ 4], 20, -405537848);
        a = gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
        d = gg(d, a, b, c, x[i+14], 9 , -1019803690);
        c = gg(c, d, a, b, x[i+ 3], 14, -187363961);
        b = gg(b, c, d, a, x[i+ 8], 20,  1163531501);
        a = gg(a, b, c, d, x[i+13], 5 , -1444681467);
        d = gg(d, a, b, c, x[i+ 2], 9 , -51403784);
        c = gg(c, d, a, b, x[i+ 7], 14,  1735328473);
        b = gg(b, c, d, a, x[i+12], 20, -1926607734);

        a = hh(a, b, c, d, x[i+ 5], 4 , -378558);
        d = hh(d, a, b, c, x[i+ 8], 11, -2022574463);
        c = hh(c, d, a, b, x[i+11], 16,  1839030562);
        b = hh(b, c, d, a, x[i+14], 23, -35309556);
        a = hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
        d = hh(d, a, b, c, x[i+ 4], 11,  1272893353);
        c = hh(c, d, a, b, x[i+ 7], 16, -155497632);
        b = hh(b, c, d, a, x[i+10], 23, -1094730640);
        a = hh(a, b, c, d, x[i+13], 4 ,  681279174);
        d = hh(d, a, b, c, x[i+ 0], 11, -358537222);
        c = hh(c, d, a, b, x[i+ 3], 16, -722521979);
        b = hh(b, c, d, a, x[i+ 6], 23,  76029189);
        a = hh(a, b, c, d, x[i+ 9], 4 , -640364487);
        d = hh(d, a, b, c, x[i+12], 11, -421815835);
        c = hh(c, d, a, b, x[i+15], 16,  530742520);
        b = hh(b, c, d, a, x[i+ 2], 23, -995338651);

        a = ii(a, b, c, d, x[i+ 0], 6 , -198630844);
        d = ii(d, a, b, c, x[i+ 7], 10,  1126891415);
        c = ii(c, d, a, b, x[i+14], 15, -1416354905);
        b = ii(b, c, d, a, x[i+ 5], 21, -57434055);
        a = ii(a, b, c, d, x[i+12], 6 ,  1700485571);
        d = ii(d, a, b, c, x[i+ 3], 10, -1894986606);
        c = ii(c, d, a, b, x[i+10], 15, -1051523);
        b = ii(b, c, d, a, x[i+ 1], 21, -2054922799);
        a = ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
        d = ii(d, a, b, c, x[i+15], 10, -30611744);
        c = ii(c, d, a, b, x[i+ 6], 15, -1560198380);
        b = ii(b, c, d, a, x[i+13], 21,  1309151649);
        a = ii(a, b, c, d, x[i+ 4], 6 , -145523070);
        d = ii(d, a, b, c, x[i+11], 10, -1120210379);
        c = ii(c, d, a, b, x[i+ 2], 15,  718787259);
        b = ii(b, c, d, a, x[i+ 9], 21, -343485551);

        a = add(a, olda);
        b = add(b, oldb);
        c = add(c, oldc);
        d = add(d, oldd);
      }
      return rhex(a) + rhex(b) + rhex(c) + rhex(d);
    }

    return calcMD5(str);
}

var clearCache = function clearCache(){
    this.expires = '<meta http-equiv="Expires" content="0">';
    this.lastModified = '<meta http-equiv="Last-Modified" content="0">';
    this.cacheControl = '<meta http-equiv="Cache-Control" content="no-cache, mustrevalidate">';
    this.noCache = '<meta http-equiv="Pragma" content="no-cache">';
}

/**
 * @autor Magdiel Canahuí
 * @description Envia un ajax y espera a que devuelva los datos, si ocurre un error o si el ajax devuelve la posición "status" diferente de "ok" entonces devuelve un false, de lo contrario devuelve la data del ajax.
 * @important Es necesario Jquery y la libreria JqueryUi para que funcione de lo contrario dara errores.
 * @returns object
 */
var fntSendData = function (ObjSettings,$fntBeforeSave,$fntSuccesSave){

    /*
      Ejemplo de ejecución 1:
      var getData = new fntSendData();
          getData.strParams = "post1=123&post2=321";
          getData.strUrl = "MyFile.php?get1=001";
          getData.strDataTypeAjax = "json";
          getData.boolMsjReturn = false;

      var rGetData = getData.fntRunSave();
      rGetData -> devuelve un objeto tipo json o un false

      Ejemplo de ejecución 2:
      var getData = new fntSendData({strParams : "post1=123&post2=321",
                                     strUrl = "MyFile.php?get1=001",
                                     strDataTypeAjax = "json",
                                     strStatus = "ok", // esta variable es importante, cuando se envia el ajax a la hora de retornar el json, este json debe tener la posicion "status" con el valor de esta variable.
                                     boolMsjReturn = false});

      getData -> devuelve un objeto, si devuelve la posición "status" en fail es porque no se ejecutó correctamente.

    */

    //Definición de variables privadas.

    if(!ObjSettings) ObjSettings = false;
    if(!$fntBeforeSave) $fntBeforeSave = false;
    if(!$fntSuccesSave) $fntSuccesSave = false;

    var self = this;
    this._RETURN = false;

    var jQueryAjax = false;
    var boolSuccesAjax = false;

    var boolSaved = false;
    var boolProccesSaved = false;

    var boolNewIntent = false;

    //Definición de variables publicas.

    this.strParams = (ObjSettings.strParams) ?ObjSettings.strParams : "";
    this.strUrl = (ObjSettings.strUrl) ?ObjSettings.strUrl: "";
    this.strStatus = (ObjSettings.strStatus) ?ObjSettings.strStatus: "ok";

    this.ObjWidgets = new drawWidgets();
    this.ObjClearCache = new clearCache();

    this.strTypeAjax = (ObjSettings.strTypeAjax) ?ObjSettings.strTypeAjax: "POST";
    this.strDataTypeAjax = (ObjSettings.strDataTypeAjax) ?ObjSettings.strDataTypeAjax: "";//xml, json, script, or html
    this.ObjDataAjax = false;

    //Seccion de mensajes
    this.boolMsjReturn = (ObjSettings.boolMsjReturn || ObjSettings.boolMsjReturn === false) ?ObjSettings.boolMsjReturn: true;
    this.strMsjReturn = (ObjSettings.strMsjReturn) ? ObjSettings.strMsjReturn : "<b>Error Inesperado!</b><br>Los datos no fueron enviados.";
    this.strTitleReturn = (ObjSettings.strTitleReturn) ?ObjSettings.strTitleReturn: "Mensaje del sistema";
    this.strMsjNewIntent = (ObjSettings.strMsjNewIntent) ?ObjSettings.strMsjNewIntent : "¿Desea intentarlo de nuevo?";
    this.boolFailData = (ObjSettings.boolFailData)?ObjSettings.boolFailData:false;
    this.boolDisplayLoad = (ObjSettings.boolDisplayLoad)?ObjSettings.boolDisplayLoad:true;
    this.boolDisplayLoadModal = (ObjSettings.boolDisplayLoadModal)?ObjSettings.boolDisplayLoadModal:false;

    //Seccion de intentos de envio de la funcion
    this.fntNewIntent = (ObjSettings.fntNewIntent) ?ObjSettings.fntNewIntent:  function (){return true;};
    this.fntWaitNewIntent = (ObjSettings.fntWaitNewIntent) ?ObjSettings.fntWaitNewIntent:false;
    this.intMaxIntents = (ObjSettings.intMaxIntents) ?ObjSettings.intMaxIntents:3;
    this.intCountIntents = (ObjSettings.intCountIntents) ?ObjSettings.intCountIntents:0;

    //Definición de funciones privadas

    //Función que envia el ajax y espera los datos antes de dar un return
    var fntSendAjax = function () {
        if(!boolSuccesAjax){
            if(!jQueryAjax){
                jQueryAjax = $.ajax({
                    type: self.strTypeAjax,
                    dataType :  self.strDataTypeAjax,
                    data :  self.strParams,
                    url :   self.strUrl,
                    cache: false,
                    async : false,
                    beforeSend: function(){
                        if(self.boolDisplayLoad){
                            self.ObjWidgets.openLoading(self.boolDisplayLoadModal);
                        }
                    },
                    success:function(data) {
                        if(typeof data != "undefined"){
                            if(self.strDataTypeAjax === 'html' || typeof(data) != 'object'){
                                self.ObjDataAjax = data;
                                boolNewIntent = false;
                                boolSaved = true;
                            }
                            else{
                                if(data["status"] == self.strStatus){
                                    self.ObjDataAjax = data;
                                    self.strMsjReturn = data['msj'];

                                    if(data['boolMsjReturn'])
                                        self.boolMsjReturn = (data['boolMsjReturn'] == "true");

                                    boolNewIntent = false;
                                    boolSaved = true;
                                }
                                else{
                                    if(self.boolFailData){
                                        self.ObjDataAjax = data;
                                        boolSaved = true;
                                    }

                                    if(data['msj'])
                                        self.strMsjReturn = data['msj'];

                                    if(data['boolMsjReturn'])
                                        self.boolMsjReturn = (data['boolMsjReturn'] == "true");

                                    if(data['boolNewIntent'])
                                        boolNewIntent=(data['boolNewIntent'] === "true") ? true : false;
                                }
                            }
                        }
                        else{
                            self.ObjDataAjax = "error";
                            boolNewIntent = false;
                            boolSaved = true;
                        }

                        if(self.boolDisplayLoad)
                            self.ObjWidgets.closeLoading();

                        boolSuccesAjax=true;
                    },
                    error:function (){
                        if(self.boolDisplayLoad)
                            self.ObjWidgets.closeLoading();

                        boolSuccesAjax=true;
                    }
                });
            }
            return fntSendAjax();
        }
        else
            return true;
    }


    //Función que manda a llamar a fntSendAjax y retorna si logro enviarlo

    var fntSave = function (){
        if(fntSendAjax()){
            if(!boolSaved){
                if(boolNewIntent){
                    jQueryAjax = false;
                    boolSuccesAjax = false;
                    self.fntNewIntent = function () {
                        boolProccesSaved = false;
                        self.ObjWidgets.alertDialog(self.strMsjNewIntent, self.strTitleReturn, false, fntSave, false);
                        self.intCountIntents++;
                    }
                    if (self.intCountIntents >= self.intMaxIntents)
                        self.fntNewIntent = function () {return true;}
                }
            }

            if(!boolProccesSaved && self.boolMsjReturn){
                self.ObjWidgets.alertDialog(self.strMsjReturn,self.strTitleReturn,false,self.fntNewIntent,self.fntWaitNewIntent);
            }
            boolProccesSaved = true;
            return boolSaved;
        }
    }
    //Función que se ejecuta antes de enviar el ajax
    var fntBeforeSave = function (fntBeforeSave){
        if(!fntBeforeSave)
            fntBeforeSave = function () { return true; }

        if(fntBeforeSave())
            return fntSave();
    }

    //Función que se ejecuta despues de enviar el ajax es necesaio que el ajax devuelve la variable boolSaved en true.
    var fntSuccesSave = function (fntSuccesSave){
        if(!fntSuccesSave)
            fntSuccesSave = function () { return self.ObjDataAjax;};
        return fntSuccesSave(self.ObjDataAjax);
    }

    //Definición de funciones publicas

    //Función publica, esta función viene siendo como el constructor de mi clase.
    this.fntRunSave = function ($fntBeforeSave,$fntSuccesSave){
        if(fntBeforeSave($fntBeforeSave)){
            return fntSuccesSave($fntSuccesSave);
        }
        else
            return false;
    }

    if(ObjSettings){
        var MyRetrun = self.fntRunSave($fntBeforeSave,$fntSuccesSave);
        self._RETURN = MyRetrun;
        if(!MyRetrun)
            return {status:"fail",msj:"<b>Error Inesperado!</b><br>Los datos no fueron enviados."};
        else
            return MyRetrun;
    }
}

function serializeObj (ObjTmp,strKey){
    var _RETURN = "";
    if(!strKey) strKey ="";

    if(!ObjTmp)
        ObjTmp = {};

    var NewObj = jQuery.extend({}, ObjTmp);
    $.each(NewObj,function (key,value){
        if(typeof(value) == "object"){
            var strTMP = (strKey) ? strKey + "["+key+"]" : key;
            var strObjVal = serializeObj(value,strTMP,_RETURN);
            _RETURN += strObjVal;
        }
        else{
            var strTMP = strKey + "["+key+"]";
            _RETURN += (strKey)?"&"+strTMP+"=" + value : "&" + key + "=" + value;
        }
    });
    return _RETURN;
}

function include_hmlautocomplete (){
    if(!jQuery.fn.hmlautocomplete){
        jQuery.fn.hmlautocomplete = function (_params){
                
                var _defaults = {
                    type:"GET",
                    dataType:"json",
                    data:{},
                    url:"",
                    cache:false,
                    success:function (data) { return data;},
                    error: function () { return true;},
                    beforeSend: function () { return true;},
                    uiautocomplete:false,
                    destroy:false,
                    dblClickClear:function (){ return true}
                };
                
                _params || ( _params = {} );
                var objSettings = $.extend({},_defaults,_params);
                
                if(objSettings.destroy){
                    $(this).removeData("hmlAutoComplete")
                           .unbind("autocomplete")
                           .unbind("keyup");
                    return $(this);
                }
                
                if(!$(this).data("hmlAutoComplete")){
                    $(this).data("hmlAutoComplete",objSettings);
                }
                else{
                    objSettings = $.extend({},$(this).data("hmlAutoComplete"),_params);
                    $(this).data("hmlAutoComplete",objSettings);
                }
                if((objSettings.url).search(/(autocomplete)/) == -1){
                    objSettings.url += ((objSettings.url).search(/(\?)/) == -1)? "?autocomplete=true" : "&autocomplete=true";
                }
                var fnBefore = objSettings.beforeSend;
                $(this).focus(fnBefore);
                if(objSettings.uiautocomplete){
                    var audata = {};
                    var fnSuccess = objSettings.success;
                    var strTerm = $(this).val();
                    $(this).keyup(function(){
                        if(this.value != strTerm)
                            strTerm = this.value;
                    });
                    
                    $(this).autocomplete({
                        source:objSettings.url,
                        minLength: 1,
                        select: function( event, ui ) {
                            audata = {id:0,value:"Error de recepción de datos",term:strTerm};
                            if(ui.item != undefined){
                                audata = ui.item;
                                audata.term = strTerm;
                            }
                        },
                        change: function(event, ui) {
                            audata = {id:0,value:"Error de recepción de datos",term:strTerm};
                            if(ui.item != undefined){
                                audata = ui.item;
                                audata.term = strTerm;
                            }
                        },
                        close : function(event, ui) {
                            if(audata.id != undefined){
                                if(audata.id <= 0)
                                    this.value = "";
                                else
                                    this.value = audata.label;
                            }
                            fnSuccess(audata);
                        }
                    });
                    
                    if(objSettings.dblClickClear){
                        $(this).dblclick(function () {
                            $(this).val("");
                            if(typeof(objSettings.dblClickClear) == "function"){
                                objSettings.dblClickClear();
                            }
                        })
                    }
                }
                else{
                    
                    if(!objSettings.cache){
                        var strNotCache = parseInt((Math.random()*9876547321098)*Math.random());
                        objSettings.data["_"] = strNotCache;
                    }
                    
                    if(typeof(objSettings.data) == "object"){
                        var strData = "";
                        var NewObj = jQuery.extend({}, objSettings.data);
                        $.each(NewObj,function (key,value){
                            if(typeof(value) != "object")
                                strData += "&" + key + "=" + value;
                        });
                        objSettings.data = strData;
                    }
                    
                    $(this).autocomplete({
                        source:objSettings.url+objSettings.data,
                        minLength: 1
                    });
                    
                    if(objSettings.dblClickClear){
                        $(this).dblclick(function () {
                            $(this).val("");
                            if(typeof(objSettings.dblClickClear) == "function"){
                                objSettings.dblClickClear();
                            }
                        });
                    }
                    
                    var fnSuccess = objSettings.success;
                    var fnError = objSettings.error;
                    var fnBefore = objSettings.success;
                    
                    objSettings.error = function (data) {
                        var data = {};
                            data[0] = {id:0,value:"Error de recepción de datos"}
                        fnError(data);
                    }
                    objSettings.success = function (data) {
                        if(typeof(data) != "object"){
                            data = {};
                            data[0] = {id:0,value:"Error de recepción de datos"}
                        }
                        fnSuccess(data);
                        return data;
                    }
                    
                    var objAcResponse = $(this).data('autocomplete').response;
                    $(this).data('autocomplete').response = function(data){
                        if(data){
                            objSettings.success(data);
                        }
                        else{
                            objSettings.error(data);
                        }
                        objAcResponse(data);
                    }
                    $(this).data('autocomplete')._renderItem = function(ul,item){
                        ul.css({visibility:"hidden"});
                    }
                }
                return $(this);
            }
    }
    return true;
}

function objectSize(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

function removeCharacter(stringChange) {
    var r = stringChange.toLowerCase();
    r = r.replace(new RegExp(/\s/g),"");
    r = r.replace(new RegExp(/[àáâãäå]/g),"a");
    r = r.replace(new RegExp(/[èéêë]/g),"e");
    r = r.replace(new RegExp(/[ìíîï]/g),"i");
    r = r.replace(new RegExp(/ñ/g),"n");
    r = r.replace(new RegExp(/[òóôõö]/g),"o");
    r = r.replace(new RegExp(/[ùúûü]/g),"u");
    return r;
}

/**
* Reemplaza caracteres latinos por su respectivo
* 
* @param string a reemplazar
* 
* @returns {String} sin caracteres latinos
*/
String.prototype.replaceLatinChar = function(){
    return output = this.replace(/á|é|í|ó|ú|ñ|ä|ë|ï|ö|ü/ig,function (str,offset,s) {
        var str =str=="á"?"a":str=="é"?"e":str=="í"?"i":str=="ó"?"o":str=="ú"?"u":str=="ñ"?"n":str;
        str =str=="Á"?"A":str=="É"?"E":str=="Í"?"I":str=="Ó"?"O":str=="Ú"?"U":str=="Ñ"?"N":str;
        str =str=="Á"?"A":str=="É"?"E":str=="Í"?"I":str=="Ó"?"O":str=="Ú"?"U":str=="Ñ"?"N":str;
        str =str=="ä"?"a":str=="ë"?"e":str=="ï"?"i":str=="ö"?"o":str=="ü"?"u":str;
        str =str=="Ä"?"A":str=="Ë"?"E":str=="Ï"?"I":str=="Ö"?"O":str=="Ü"?"U":str;
        return (str);
    });
}

// Changes XML to JSON
function xmlToJson(xml) {

    // Create the return object
    var obj = {};

    if (xml.nodeType == 1) { // element
        // do attributes
        if (xml.attributes.length > 0) {
            obj["attributes"] = {};
            for (var j = 0; j < xml.attributes.length; j++) {
                var attribute = xml.attributes.item(j);
                obj["attributes"][attribute.nodeName] = attribute.nodeValue;
            }
        }
    } else if (xml.nodeType == 3) { // text
        obj = xml.nodeValue;
    }

    // do children
    if (xml.hasChildNodes()) {
        for(var i = 0; i < xml.childNodes.length; i++) {
            var item = xml.childNodes.item(i);
            var nodeName = item.nodeName;
            if (typeof(obj[nodeName]) == "undefined") {
                obj[nodeName] = xmlToJson(item);
            } else {
                if (typeof(obj[nodeName].push) == "undefined") {
                    var old = obj[nodeName];
                    obj[nodeName] = [];
                    obj[nodeName].push(old);
                }
                obj[nodeName].push(xmlToJson(item));
            }
        }
    }
    return obj;
};

var loadImg = function(input,idImg){
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#'+idImg).removeClass("hide");
            $('#'+idImg).attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function reLoadImage(objIMG,intWidth){
    var sinRatio;

    sinRatio = objIMG.height/objIMG.width;

    objIMG.width = intWidth;
    objIMG.height = intWidth*sinRatio;
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

class modelFields{
    get placeholder() {
        return this._placeholder;
    }

    set placeholder(value) {
        this._placeholder = value;
    }
    get onchange() {
        return this._onchange;
    }

    set onchange(value) {
        this._onchange = value;
    }
    get hide() {
        return this._hide;
    }

    set hide(value) {
        this._hide = value;
    }
    get object() {
        return this._object;
    }

    set object(value) {
        this._object = value;
    }

    get key() {
        return this._key;
    }
    set key(value) {
        this._key = value;
    }
    get name() {
        return this._name;
    }
    set name(value) {
        this._name = value;
    }
    get type() {
        return this._type;
    }
    set type(value) {
        this._type = value;
    }
    get options() {
        return this._options;
    }
    set options(value) {
        this._options = value;
    }
    get required() {
        return this._required;
    }
    set required(value) {
        this._required = value;
    }
    get multiple() {
        return this._multiple;
    }
    set multiple(value) {
        this._multiple = value;
    }
    get onclick() {
        return this._onclick;
    }
    set onclick(value) {
        this._onclick = value;
    }
    get cssClass(){ return this._cssClass }
    set cssClass(value){ this._cssClass = value }
    get cssClass(){ return this._default }
    set cssClass(value){ this._default = value }
    constructor(key,type,name,required = false,options = false,multiple = false, cssClass=false, elementDefault = ""){
        this._object = false;
        this._onclick = false;
        this._onchange = false;
        this._placeholder = false;
        this._hide = false;
        this._key = key;
        this._type = type;
        this._name = name;
        this._required = required;
        this._options = options;
        this._multiple = multiple;
        this._cssClass = cssClass;
        this._default = elementDefault;
    }

    /* Método drawInput(), Puede recibir tres parámetros:
    *   frm: Este es el objeto (div, form, table, etc.) que contrendrá los elementos dibujados
    *   value: Es la data del objeto para asignar un valor a los elementos, si no se envía nada setearlo como 'null'
    *   contElement: Este es un contador que se tendrá que recibir dependiendo de dos cosas:
    *       1:  que se desee crear elementos múltiples,
    *       2:  que se creen input checkbox o radio (ES OBLIGATORIO que venga este parámetro)
    *
    *   IMPORTANTE:
    *   Si no se envía un contador y se añaden elementos input checkbox o radio, las inserciones se harán de una mala forma
    *   ya que en un submit NO SE ENVIARÁN LO VALORES, y como este método devuelve un name específico, se harán modificaciones o comportamientos no deseados
    *  */
    drawInput(frm, value = false, countElement = 0, boolEditForm = true){
        let strDisabled = 'disabled';
        if(boolEditForm){
            strDisabled = '';
        }
        if(!frm)return false;
        let formName = 'field';
        if(typeof frm.attr === 'function' && typeof frm.attr('name') !== 'undefined' && frm.attr('name') !== '')
            formName = frm.attr('name');
        let name = `${formName}_${this.key}`;
        const ID = `${formName}_${this.key}`;
        if(this.multiple)
            name += `[${countElement}]`;
        //DIBUJO EL INPUT
        if(this.type === 'radio' && Object.keys(this.options).length > 0){
            this.object = $(`
                <div class="form-group ${ (this._cssClass.length >=0)?this._cssClass: 'col-lg-12 col-md-6 col-sm-6 col-xs-12 ' } ">
                    <label ${strDisabled}>${this.name}</label><br>
                </div>
            `);frm.append(this.object);
            for(let key in this.options){
                let attrChecked = '';
                if(this.options[key] === this._default){
                    attrChecked = 'checked'
                }
                const input = $(`
                    <label class="radio-inline">
                      <input type="radio" name="${name}" id="${ID}_${key}" value="${key}" ${attrChecked} ${strDisabled}> ${this.options[key]}
                    </label>
                `);this.object.append(input);
            }

        }
        else if(this.type === 'checkbox' && Object.keys(this.options).length > 0){
            this.object = $(`
                <div class="form-group ${ (this._cssClass.length >=0)?this._cssClass: 'col-lg-12 col-md-6 col-sm-6 col-xs-12 ' } ">
                    <label ${strDisabled}>${this.name}</label><br>
                </div>
            `);frm.append(this.object);
            let boolMultiple = false;
            if(Object.keys(this.options).length > 1)
                boolMultiple = true;
            let intCount = 0;
            let extraName = '';
            for(let key in this.options){
                if(boolMultiple)extraName = `_${intCount}`;
                const input = $(`
                    <label class="checkbox-inline">
                      <input type="checkbox" name="${name}${extraName}" id="${ID}_${key}" value="${key}" ${strDisabled}> ${this.options[key]}
                    </label>
                `);this.object.append(input);
                intCount++;
            }
        }
        else if(this.type === 'select' && Object.keys(this.options).length > 0){
            this.object = $(`
                <div class="form-group ${ (this._cssClass.length >=0)?this._cssClass: 'col-lg-12 col-md-6 col-sm-6 col-xs-12 ' } ">
                    <label ${strDisabled}>${this.name}</label><br>
                    <select class="form-control" name="${name}" id="${ID}" ${strDisabled}></select>
                </div>
            `);frm.append(this.object);
            const input = $(`
                    <option value="0">Seleccione una opción</option>
                `);this.object.find('select').append(input);
            for(let key in this.options){
                const input = $(`
                    <option value="${key}">${this.options[key]}</option>
                `);this.object.find('select').append(input);
            }
        }
        else if(this.type === 'hidden'){
            if(!value){
                value = 0;
            }
            this.object = $(`<input type="${this.type}" id="${ID}" name="${name}" value="${value}">`);frm.append(this.object);
        }
        else if(this.type === 'button') {
            const validate = (this.required) ? 'required' : '';
            this.object = $(`
                <div class="form-group ${ (this._cssClass.length >=0)?this._cssClass: 'col-lg-12 col-md-6 col-sm-6 col-xs-12 ' } text-center">               
                    <label ${strDisabled}>&nbsp;</label><br>
                    <button type="${this.type}" class="btn btn-default" ${strDisabled}>${this.name}</button>
                </div>
            `);
            frm.append(this.object);

            if (typeof this.onclick === 'function')
                this.object.find('button').on('click',(e)=>this.onclick(e));
        }
        else if(this.type === "textarea"){
            const validate = (this.required)?'required':'';
            this.object = $(`
                <div class="form-group ${ (this._cssClass.length >=0)?this._cssClass: 'col-lg-12 col-md-6 col-sm-6 col-xs-12' }">
                    <label for="${name}" ${strDisabled}>${this.name}</label>
                    <textarea class="form-control" id="${ID}" name="${name}" rows="3" ${validate} ${strDisabled}></textarea>
                </div>
            `);frm.append(this.object);
            if(this.placeholder)
                this.object.find('input').attr('placeholder',this.placeholder);
        }
        else{
            const validate = (this.required)?'required':'';
            this.object = $(`
                <div class="form-group ${ (this._cssClass.length >=0)?this._cssClass: 'col-lg-12 col-md-6 col-sm-6 col-xs-12' }">
                    <label for="${name}" ${strDisabled}>${this.name}</label>
                    <input type="${this.type}" class="form-control" id="${ID}" name="${name}" ${validate} ${strDisabled}>
                </div>
            `);frm.append(this.object);
            if(this.placeholder)
                this.object.find('input').attr('placeholder',this.placeholder);
        }
        this.object.css({
            'min-height':'70px'
        });//Esto para que la vista se vea bien

        //AGREGO PROPIEDADES EXTRAS
        if(this.hide)
            this.object.addClass('hide');
        //SETEO VALORES
        if(value){
            if(this.type === 'radio'){
                this.object.find('input').each((index,obj)=>{
                    const radio = $(obj);
                    if(value === radio.attr('value'))
                        radio.prop('checked',true);
                    else
                        radio.prop('checked',false);
                });
            }
            else if(this.type === 'checkbox'){
                this.object.find('input').each((index,obj)=>{
                    const checkbox = $(obj);
                    if(checkbox.attr('value').indexOf(value))
                        checkbox.prop('checked',false);
                        //checkbox.prop('checked',true);
                    else
                        checkbox.prop('checked',true);
                        //checkbox.prop('checked',false);
                });
            }
            else if(this.type === 'select'){
                this.object.find('select').val(value);
            }
            else if(this.type === 'textarea'){
                this.object.find('textarea').html(value);
            }
            else{
                this.object.find('input').val(value);
            }
        }
    }
}

class drawTabs{
    get cntTitle(){
        return this._cntTitlesTabs;
    }
    set cntTitle(containerName){
        this._cntTitlesTabs = $("#"+containerName);
    }
    get cntData(){
        return this._cntDatatabs;
    }
    set cntData(containerName){
        this._cntDatatabs = $("#"+containerName);
    }
    get objTabs(){
        return this._objTabs;
    }
    set objTabs(tabs){
        this._objTabs = tabs;
    }

    constructor(cntTitlesTabs, cntDatatabs, objTabs){
        this._cntTitlesTabs = $("#"+cntTitlesTabs);
        this._cntDatatabs = $("#"+cntDatatabs);
        this._objTabs = objTabs;
    }

    drawStatic(){
        const self = this;
        let id = this._cntTitlesTabs.attr("id");
        for(let value in self._objTabs){
            let liCnt = $("<li></li>").addClass(
                `nav-item cntTitlesTabs ${id}`
            ).css("cursor","pointer"); this._cntTitlesTabs.append(liCnt);
            let linkTab = $("<a> " + self._objTabs[value].strDescription + " </a>").attr({
                "class": "nav-link",
                "id": self._objTabs[value].container + "-tab",
                "data-toggle": "tab",
                //"href": "#" + valTabs.attr,
                "role": "tab",
                "aria-controls": self._objTabs[value].container,
                "aria-selected": "false",
            }).on("click",()=>{
                $(`.${id}`).removeClass("active");
                liCnt.addClass("active");
                $(`.${self._objTabs[value].container}-tab`).addClass("hide").removeClass("show active in");
                $(`#${self._objTabs[value].container}`).removeClass("hide").addClass("show active in");
                self._objTabs[value].execute(self._objTabs[value].container);
                return false;
            });
            liCnt.append(linkTab);
            let tabPane = $("<div></div>").attr({
                "id": self._objTabs[value].container,
                "role": "tabpanel",
                "aria-labelledby": self._objTabs[value].container + "-tab"
            }).addClass(`tab-pane fade  ${self._objTabs[value].container}-tab`); this._cntDatatabs.append(tabPane);
            if(typeof self._objTabs[value].pivot != "undefined" ){
                if(self._objTabs[value].pivot == "Y"){
                    self._objTabs[value].execute(self._objTabs[value].container);
                    linkTab.click();
                }
            }
        }
    }

    draw(){
        const self = this;
        self._cntTitlesTabs.html("");
        self._cntDatatabs.html("");

        for(let value in self._objTabs){
            let liCnt = $("<li></li>").attr({
                "class": "nav-item cntTitlesTabs"
            }).css("cursor","pointer"); this._cntTitlesTabs.append(liCnt);
            let linkTab = $("<a> " + self._objTabs[value].strDescription + " </a>").attr({
                "class": "nav-link",
                "id": self._objTabs[value].container + "-tab",
                "data-toggle": "tab",
                //"href": "#" + valTabs.attr,
                "role": "tab",
                "aria-controls": self._objTabs[value].container,
                "aria-selected": "false",
            }).click(()=>{
                self.removeAttrMoreTabs(self._objTabs[value].container);
                self._objTabs[value].execute(self._objTabs[value].container)
            }); liCnt.append(linkTab);

            let tabPane = $("<div></div>").attr({
                "class": "tab-pane fade",
                "id": self._objTabs[value].container,
                "role": "tabpanel",
                "aria-labelledby": self._objTabs[value].container + "-tab"
            }); this._cntDatatabs.append(tabPane);
            if(typeof self._objTabs[value].pivot != "undefined" ){
                if(self._objTabs[value].pivot == "Y"){
                    liCnt.addClass("active");
                    linkTab.attr({
                        "aria-selected": "true"
                    });
                    tabPane.addClass("show active in");
                    linkTab.click();
                }
            }
        }
    }

    removeAttrMoreTabs(containerName){
        const self = this;
        for(let key in self._objTabs){
            $("#"+self._objTabs[key].container).removeClass("show active in").html("");
            if(containerName == self._objTabs[key].container){
                $("#"+self._objTabs[key].container).addClass("show active in")
            }
        }
    }
}

function drawCanvas(objElement){
    const self = this;
    let canvas = document.getElementById(objElement);
    let ctx = canvas.getContext("2d");
    ctx.strokeStyle = "#222222";
    ctx.lineWith = 2;

    let drawing = false;
    let mousePos = { x:0, y:0 };
    let lastPos = mousePos;
    canvas.addEventListener("mousedown", function (e) {
        drawing = true;
        lastPos = self.getMousePos(canvas, e);
    }, false);
    canvas.addEventListener("mouseup", function () {
        drawing = false;
    }, false);
    canvas.addEventListener("mousemove", function (e) {
        mousePos = self.getMousePos(canvas, e);
    }, false);

    // Get the position of the mouse relative to the canvas
    this.getMousePos = function (canvasDom, mouseEvent) {
        let rect = canvasDom.getBoundingClientRect();
        return {
            x: mouseEvent.clientX - rect.left,
            y: mouseEvent.clientY - rect.top
        };
    };

    window.requestAnimFrame = (function (callback) {
        return window.requestAnimationFrame ||
            window.webkitRequestAnimationFrame ||
            window.mozRequestAnimationFrame ||
            window.oRequestAnimationFrame ||
            window.msRequestAnimaitonFrame ||
            function (callback) {
                window.setTimeout(callback, 1000/60);
            };
    })();

    this.renderCanvas = () => {
        if (drawing) {
            ctx.moveTo(lastPos.x, lastPos.y);
            ctx.lineTo(mousePos.x, mousePos.y);
            ctx.stroke();
            lastPos = mousePos;
        }
    };

    // Allow for animation
    (function drawLoop () {
        requestAnimFrame(drawLoop);
        self.renderCanvas();
    })();

    //Events touch
    canvas.addEventListener("touchstart", function (e) {
        mousePos = self.getTouchPos(e);
        let touch = e.touches[0];
        let mouseEvent = new MouseEvent("mousedown", {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }, false);
    canvas.addEventListener("touchend", () => {
        let mouseEvent = new MouseEvent("mouseup", {});
        canvas.dispatchEvent(mouseEvent);
    }, false);
    canvas.addEventListener("touchmove", function (e) {
        let touch = e.touches[0];
        let mouseEvent = new MouseEvent("mousemove", {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        canvas.dispatchEvent(mouseEvent);
    }, false);

    // Get the position of a touch relative to the canvas
    this.getTouchPos = function (touchEvent) {
        let rect = canvas.getBoundingClientRect();
        return {
            x: touchEvent.touches[0].clientX - rect.left,
            y: touchEvent.touches[0].clientY - rect.top
        };
    }
    // Prevent scrolling when touching the canvas
    document.body.addEventListener("touchstart, touchend, touchmove", function (e) {
        if (e.target === canvas) {
            e.preventDefault();
        }
    }, false);
}


function goToId(idName) {
    if($("#"+idName).length) {
        let target_offset = $("#"+idName).offset();
        let target_top = target_offset.top;
        $('html,body').animate({scrollTop:target_top},{duration:"slow"});
    }
}

/* Uso de setSortableByID()

* strIDCntSortables: ID del elemento contenedor de todos elementos a modificar
* boolShowCount: Este depende:
*   si dentro del elemento <div class="column" draggable="true"></div>
*   existe este elemento: <div class="count" data-col-moves="0"></div>
*   se puede enviar como true, para ver cuantas veces se ha movido cada elemento,
*   de lo contrario su falor por defecto será false
*
*   <div id="cntSortablesOptions">
        <div class="column" draggable="true">
            <h3>A</h3>
            <div class="count" data-col-moves="0"></div>
        </div>
        <div class="column" draggable="true">
            <h3>B</h3>
            <div class="count" data-col-moves="0"></div>
        </div>
    </div>

*   $(document).ready( () => {
        setSortableByID('cntSortablesOptions', true);
    });

*strIDButtonAction: Este es el ID de un botón al que se le añade un efecto indicando que debe guardar cambios
*   se añade la posición : "changeSortable" al localStorage, para que se use en cualquier ventana y sepamos que no se ha guardado
* */
function setSortableByID(strIDCntSortables, boolShowCount = false, strIDButtonAction = null, strFunctionReturn = "")
{
    let cols_ = document.querySelectorAll(`#${strIDCntSortables} .column`);
    let dragSrcEl_ = null;
    const self = this;

    this.handleDragStart = function (e) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
        dragSrcEl_ = this;
        this.classList.add('moving');
    };

    this.handleDragOver = function (e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        return false;
    };

    /*Se comenta este código ya que mostraba un error en consola al asignar las clases a los elementos que se están arrastrando pero no afecta en nada la funcionalidad*/
    /*this.handleDragEnter = () => {
        this.classList.add('over');
    };
    this.handleDragLeave = () => {
        this.classList.remove('over');
    };*/

    this.handleDrop = function (e) {
        if (e.stopPropagation) e.stopPropagation();

        if (dragSrcEl_ !== this) {
            dragSrcEl_.innerHTML = this.innerHTML;
            this.innerHTML = e.dataTransfer.getData('text/html');

            if(boolShowCount){
                let count = this.querySelector('.count');
                let newCount = parseInt(count.getAttribute('data-col-moves')) + 1;
                count.setAttribute('data-col-moves', newCount);
                count.textContent = `moves: ${newCount}`;
            }
        }

        strFunctionReturn.call();
        return false;
    };

    this.handleDragEnd = () => {
        if(strIDButtonAction){
            const btn = document.getElementById(`${strIDButtonAction}`);
            btn.classList.add('shake');
            if(!localStorage.getItem('changeSortable')){
                localStorage.setItem('changeSortable', '1');
            }
            setTimeout( () => {
                btn.classList.remove('shake');
            }, 1200);
        }


        [].forEach.call(cols_, function (col) {
            col.classList.remove('over');
            col.classList.remove('moving');
        });
    };

    [].forEach.call(cols_, (col) => {
        col.setAttribute('draggable', 'true');
        col.addEventListener('dragstart', self.handleDragStart, false);
        //col.addEventListener('dragenter', self.handleDragEnter, false);
        col.addEventListener('dragover', self.handleDragOver, false);
        //col.addEventListener('dragleave', self.handleDragLeave, false);
        col.addEventListener('drop', self.handleDrop, false);
        col.addEventListener('dragend', self.handleDragEnd, false);
    });
}

function codificarEntidad(str) {
    var array = [];
    for (var i=str.length-1;i>=0;i--) {
        array.unshift(['&#', str[i].charCodeAt(), ';'].join(''));
    }
    return array.join('');
}

function descodificarEntidad(str) {
    return str.replace(/&#(\d+);/g, function(match, dec) {
        return String.fromCharCode(dec);
    });
}

function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds){
            break;
        }
    }
}

function previewImageBase64(objInputImg, callback, objParams = {})
{
    if (objInputImg.files && objInputImg.files[0]) {
        let reader = new FileReader();
        let image = new Image();
        reader.onload = function(e) {
            let image = new Image();
            image.src = e.target.result;

            if(Object.keys(objParams).length > 0){
                callback(image.src, objParams);
            }
            else{
                callback(image.src);
            }

        };
        reader.readAsDataURL(objInputImg.files[0]);
    }
}