const Notifications = function (customSettings) {
    const self = this;
    this.dw = "";
    let defaults = {
        strAction : '',
        htmlDOM: document.implementation.createHTMLDocument(),
        objSwUserTypes: {},
        objWindowsExist: {},
    };

    customSettings || (customSettings = {});
    let settings = $.extend({}, defaults, customSettings);

    this.setDrawWidget = (dw) => {
        this.dw = dw;
    };

    this.setSwUserTypes = (objSwUserTypes) => {
        settings.objSwUserTypes = objSwUserTypes;
    };

    this.setWindowsExist = (setWindowsExist) => {
        settings.objWindowsExist = setWindowsExist;
    };

    this.showFormNotification = (objDataNotification = {}, titleNotification = '') => {
        const date = new Date();
        const dd = String(date.getDate()).padStart(2, '0');
        const mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
        const yyyy = date.getFullYear();
        const today = `${yyyy}-${mm}-${dd}`;

        let strTitle = '';
        let strDescription = '';
        let strWindow = 0;
        let strProfileAccess = '';
        let boolFilterDate = '';
        let strKeyChar = '';
        let strDateTo = today;
        let strDateFrom = today;
        let intIDNotificationExist = 0;
        if( Object.keys(objDataNotification).length ){
            strTitle = `${objDataNotification.title}`;
            strDescription = `${objDataNotification.message}`;
            strWindow = `${objDataNotification.url_window}`;
            strProfileAccess = `${objDataNotification.sw_user_type}`;
            boolFilterDate = (objDataNotification.bool_filter_date === 'Y')?"checked":"";
            strDateTo = `${objDataNotification.date_to}`;
            strDateFrom = `${objDataNotification.date_from}`;
            intIDNotificationExist = `${objDataNotification.id}`;
            strKeyChar = `${objDataNotification.key_char_to_draw}`;
        }

        const html = `  <div class="col-xs-12">
                            <form id="formNotification">
                                <div class="col-xs-12 col-md-6 rowDataDrawDynamic">
                                    <p>
                                        Título de Notificación
                                    </p>
                                    <input type="text" class="form-control" name="titleNotification" id="titleNotification" value="${strTitle}" />
                                    <input type="hidden" class="form-control" name="idNotification" id="idNotification" value="${intIDNotificationExist}" />
                                </div>
                                <div class="col-xs-12 col-md-6 rowDataDrawDynamic">
                                    <p>
                                        Descripción de la Notificación
                                    </p>
                                    <input type="text" class="form-control" name="descriptionNotification" id="descriptionNotification" value="${strDescription}" />
                                </div>
                                <div class="col-xs-12 col-md-6 rowDataDrawDynamic">
                                    <p>
                                        Ventana
                                    </p>
                                    <select class="form-control" id="windowNotification" name="windowNotification">
                                        <option value="0">Seleccione una opción</option>
                                    </select>
                                </div>
                                <div class="col-xs-12 col-md-6 rowDataDrawDynamic">
                                    <p>
                                        Texto clave para que el sistema lo dibuje
                                    </p>
                                    <input type="text" class="form-control" name="keyCharNotification" id="keyCharNotification" value="${strKeyChar}" />
                                </div>
                                <div class="col-xs-12 col-md-6 rowDataDrawDynamic">
                                    <p>
                                        Perfil de Acceso
                                    </p>
                                    <select class="form-control" multiple class="chosen-select" id="swUserTypeNotification" name="swUserTypeNotification">
                                        <option value="0">Seleccione una opción</option>
                                    </select>
                                </div>
                                <div class="col-xs-12 col-md-6 rowDataDrawDynamic">
                                    <p>
                                        Filtrar por Fecha
                                    </p>
                                    <div class="slideThree" dt-active="Si" dt-not-active="No">
                                        <input type="checkbox" class="ios-chk" id="printFilterDateNotification" name="printFilterDateNotification" ${boolFilterDate}>
                                        <label for="printFilterDateNotification"></label>
                                    </div>
                                </div>
                                <div class="col-xs-12 rowDataDrawDynamic" id="cntInputsFilterDateNotification"></div>
                            </form>
                        </div>`;

        let strNotification = 'Crear Notificación';
        if(titleNotification){
            strNotification = `${titleNotification}`;
        }

        const arrButtons = {};
        arrButtons.save = {};
        arrButtons.save.nombre = 'Guardar';
        arrButtons.save.cssClass = 'btn btn-primary-outline floatRightMargin';
        arrButtons.save.funcion = `saveNotification(${intIDNotificationExist}, 'formNotification')`;
        this.dw.alertDialog(html, `${strNotification}`, false, false, '', arrButtons);


        setTimeout( () => {
            self.setActionsToFormNotificationByID(strWindow, strProfileAccess, strDateTo, strDateFrom);
        }, 250);
    };

    this.setActionsToFormNotificationByID = (strWindow, strProfileAccess, strDateTo, strDateFrom) => {
        const sltWindow = document.getElementById('windowNotification');
        const sltUserType = document.getElementById('swUserTypeNotification');
        const inputFilterDate = document.getElementById('printFilterDateNotification');
        const cntInputs = document.getElementById('cntInputsFilterDateNotification');

        self.setSelectWindows(sltWindow);

        self.setSelectSWUserTypes(sltUserType, strProfileAccess);

        $(sltWindow).val(`${strWindow}`);

        $(sltUserType).on('change', (e) => {
            e = e.target;
            objNotifications = $(e).val();
        });

        if($(inputFilterDate).prop('checked') === true){
            self.drawElementsDateNotification(cntInputs, true, strDateTo, strDateFrom);
        }
        inputFilterDate.addEventListener('change', (e) => {
            e = e.target;
            if($(e).prop('checked') === true){
                self.drawElementsDateNotification(cntInputs, true, strDateTo, strDateFrom);
            }
            else{
                self.drawElementsDateNotification(cntInputs);
            }
        });
    };

    this.setSelectWindows = (sltWindow) => {
        for(let key in settings.objWindowsExist){
            const data = settings.objWindowsExist[key];

            const option = `<option value="${data.class}">
                                ${data.name}
                            </option>`;
            $(sltWindow).append(`${option}`);
        }
    };

    this.setSelectSWUserTypes = (sltUserType, strProfileAccess) => {
        for(let key in settings.objSwUserTypes){
            const data = settings.objSwUserTypes[key];

            const option = `<option value="${data.name}">
                                ${data.descr}
                            </option>`;
            $(sltUserType).append(`${option}`);
        }


        const arrProfiles = (strProfileAccess !== '') ? strProfileAccess.split(',') : '';
        for(let key in arrProfiles){
            if(arrProfiles[key] === ""){
                delete arrProfiles[key];
            }
        }
        objNotifications = arrProfiles;
        $(sltUserType).val(arrProfiles);
        $(sltUserType).chosen({
            placeholder_text_multiple: "Seleccione su coordinación",
            no_results_text: "No se encuentran resultados"
        });
    };

    this.getNotificationsActive = (cntAllNotificationsPreview) => {
        fetch(`${settings.strAction}&op=getNotificationsActive`)
            .then( (response) => {
                return response.json();
            })
            .then( (data) => {
                if(data.status === 'ok'){
                    if( Object.keys(data.notifications).length ){
                        $(cntAllNotificationsPreview).html('');
                        for(let key in data.notifications){
                            const dataNotification = data.notifications[key];
                            self.drawNotificationExist(cntAllNotificationsPreview, dataNotification);
                        }
                    }
                    else{
                        self.drawNoExistNotifications(cntAllNotificationsPreview);
                    }
                }
                else {
                    self.drawNoExistNotifications(cntAllNotificationsPreview);
                }
            })
            .catch( () => {
            })
    };

    this.drawNotificationExist = (cntNotifications, objDataNotification) => {
        const strTitleNotification = (objDataNotification.title !== "") ? objDataNotification.title : "~ Título ~";
        const strDescriptionNotification = (objDataNotification.message !== "") ? objDataNotification.message : "~ Descripción ~";
        const strWindowNotification = (objDataNotification.url_window !== "") ? objDataNotification.url_window : "~ Ventana ~";

        const element = `   <div class="col-xs-12 col-sm-4 col-lg-3 notificationExistShow" id="cntNotification_${objDataNotification.id}">
                                <div class="col-xs-12 ${objDataNotification.class_style_notification}">
                                    <div class="col-xs-12">
                                        <h4 style="min-height: 30px; max-height: 30px; overflow-y: hidden;">
                                            ${strTitleNotification}
                                        </h4>
                                    </div>
                                    <div class="col-xs-12">
                                        <p style="min-height: 45px; max-height: 45px; overflow-y: hidden;">
                                            <strong>
                                                ${strDescriptionNotification}
                                            </strong>
                                        </p>
                                    </div>
                                    <div class="col-xs-12">
                                        <p style="min-height: 30px; max-height: 30px; overflow-y: hidden;">
                                            ${strWindowNotification}
                                        </p>
                                    </div>
                                    <div>
                                        <button class="btn btn-danger-outline floatRightMargin" id="btnDeleteNotification_${objDataNotification.id}" style="margin-bottom: 15px;" >
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                        <button class="btn btn-primary-outline floatRightMargin" id="btnEditNotification_${objDataNotification.id}" style="margin-bottom: 15px;" >
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>`;
        $(cntNotifications).append(element);

        const btnEditNotification = document.getElementById(`btnEditNotification_${objDataNotification.id}`);
        const btnDeleteNotification = document.getElementById(`btnDeleteNotification_${objDataNotification.id}`);

        btnEditNotification.addEventListener('click', () => {
            self.showFormNotification(objDataNotification, objDataNotification.title);
        });

        btnDeleteNotification.addEventListener('click', () => {
            self.deleteNotificationByID(objDataNotification.id, cntNotifications);
        });
    };

    this.drawElementsDateNotification = (cntElements, boolDraw = false, strDateTo = '', strDateFrom = '') => {
        if(!boolDraw){
            $(cntElements).html('');
            return false;
        }
        const element = `   <div class="col-xs-12 col-md-6">
                                <p>
                                    Desde
                                </p>
                                <input type="date" class="form-control" name="dateToNotification" id="dateToNotification" value="${strDateTo}" />
                            </div>
                            <div class="col-xs-12 col-md-6">
                                <p>
                                    Hasta
                                </p>
                                <input type="date" class="form-control" name="dateFromNotification" id="dateFromNotification" value="${strDateFrom}" />
                            </div>`;
        $(cntElements).append(element);
    };

    this.drawNoExistNotifications = (cntNotifications) => {
        const element = `   <div class="col-xs-12 shadow" style="cursor: pointer;">
                                <span>
                                    <h2 style="display: inline-block;">No hay notificaciones a mostrar, si desea crear una pulse en el botón "Añadir"</h2>
                                    <i class="fa fa-arrow-up fa-2x floatRightMargin" style="margin-top: 10px;"></i>
                                </span>
                            </div>`;
        $(cntNotifications).append(element);
    };

    this.deleteNotificationByID = (intIDNotification, cntNotifications) => {
        fetch(`${settings.strAction}&op=deleteNotification&notification=${intIDNotification}`)
            .then( (response) => {
                return response.json();
            })
            .then( (data) => {
                if(data.status === 'ok'){
                    if( Object.keys(data.notifications).length ){
                        $(cntNotifications).html('');
                        for(let key in data.notifications){
                            const dataNotification = data.notifications[key];
                            self.drawNotificationExist(cntNotifications, dataNotification);
                        }
                    }
                }
            })
            .catch( () => {
            })
    };

};