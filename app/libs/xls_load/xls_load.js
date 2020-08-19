/**
 * Author: Alexander Flores
 * Date: 14/09/2017
 * Version: 0.1
 */

class xls_load{

    constructor(arrWidget,draWidget){
        this.op = 'd019c0ac-9cb9-11e7-93c0-286ed488ca86';
        this.aw = arrWidget;
        this.dw = draWidget;
        this.boolProcess = false;
        this.mr = 500;
        this.arrOk = this.validateArray();
        this.pluginOk();

        this.objXHR = null;
    }

    validateArray(){
        if(this.aw.length > 0){
            let o = true;
            for(let i = 0; i < this.aw.length; i++){
                if(typeof this.aw[i].processWhileUpload === "undefined" || typeof this.aw[i].processWhileUpload === false) {

                    if (this.aw[i].elementID === "")
                        o = false;
                    else if ($(`#${this.aw[i].elementID}`).length !== 1)
                        o = false;
                    if (typeof this.aw[i].validate === 'undefined')
                        o = false;
                    else if (this.aw[i].validate === '')
                        o = false;
                    if (typeof this.aw[i].process === 'undefined')
                        o = false;
                    else if (this.aw[i].process === "")
                        o = false;
                }
            }
            return o;
        }
        return false;
    }

    pluginOk(){
        if(!this.arrOk){
            this.badConfig();
            return this.arrOk;
        }
        return this.arrOk;
    }

    badConfig(){
        this.dw.alertDialog("Mala configuración de plugin");
    }

    drawInputFile(k){
        let content = $(`#${this.aw[k].elementID}`);
        content.html('');
        let form = $('<form></form>').attr({
            'class':'',
            'enctype':'multipart/form-data'
        });content.append(form);

        let divFile = $('<div></div>').addClass("form-group");form.append(divFile);
        let input = $('<input />').attr({
            'type':'file',
            'name':'iXload',
            'id':`file-xls-${k}`,
            'class':'inputfile-xls',
            'accept':'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });divFile.append(input);

        let labelInput = $('<label></label>').attr({
            'for':`file-xls-${k}`
        });divFile.append(labelInput);
        let strongInput = $('<strong></strong>');labelInput.append(strongInput);
        let iInput = $('<i></i>').attr({
            'class':'fa fa-cloud-upload',
            'aria-hidden':'true'
        });strongInput.append(iInput);
        strongInput.append('&nbsp;&nbsp;Seleccionar archivo&hellip;');
        let spanInput = $('<span>Ningún archivo seleccionado</span>');labelInput.append(spanInput);

        input.on('change',(e)=>{
            if(this.boolProcess){
                this.dw.alertDialog('No se pueden procesar 2 archivos simultaneamente');
                return false;
            }
            const fileName = e.target.value.split( '\\' ).pop();
            if( fileName ){
                spanInput.html(fileName);
                form.submit();
            }
            else
                spanInput.html('Ningún archivo seleccionado');
        });
        input.on('click',(e)=>{
            if(this.boolProcess){
                this.dw.alertDialog('No se pueden procesar 2 archivos simultaneamente');
                return false;
            }
            const fileName = e.target.value.split( '\\' ).pop();
            if( fileName ){
                e.preventDefault();
                this.drawInputFile(k);
            }
        });

        let cntMessages = $('<div></div>').addClass('xls-msg');content.append(cntMessages);

        let divProgress = $('<div></div>').addClass('progress hide');cntMessages.append(divProgress);
        let divInsideProgress = $('<div></div>').attr({
            'class':'progress-bar',
            'role':'progressbar',
            'aria-valuenov':'0',
            'aria-valuemin':'0',
            'aria-valuemax':'100'
        }).css('width','0%');divProgress.append(divInsideProgress);

        let divStep = $('<div></div>').addClass('xls-step');cntMessages.append(divStep)

        form.on('submit',(e)=>{
            e.preventDefault();

            $.ajax({
                type :  'POST',
                url  :  this.constructor.getToken(this.op) + '&opt=save',
                data :  new FormData(form[0]),
                dataType : 'JSON',
                contentType: false,
                cache: false,
                processData:false,
                beforeSend: ()=>{
                    this.dw.openLoading();
                },
                success: (d)=>{
                    this.dw.closeLoading();
                    if(d.valido === 1){
                        this.aw[k].r = d;
                        this.aw[k].r.aw = k;
                        this.aw[k].r.file = spanInput.html();
                        this.processResponse(k,this.aw[k].r);
                    }
                    else{
                        this.dw.alertDialog(d.msj)
                    }
                },
                error: ()=>{
                    this.dw.closeLoading();
                }
            });
        });
    }

    drawWidget(){
        if(!this.pluginOk())return false;
        for(let i = 0; i < this.aw.length; i++){
            this.drawInputFile(i);
        }
    }

    static getToken(o, f, m){
        if (!o) {
            alert("Se necesita codigo de operacion");
            return false;
        }
        if (!f) f = "json";
        if (!m) m = "w";

        return "webservice.php?o="+o+"&f="+f+"&m="+m;
    }

    processResponse(k){
        if(this.aw[k].column.length > 0)
            this.validateColumn(k);
        if(typeof this.aw[k].run !== 'undefined'){
            this.aw[k].run(this.aw[k].r);
        }
        else{
            this.processXls(k);
        }
    }

    validateColumn(k){
        $.each(this.aw[k].r.sheets,(key,val)=>{
            this.aw[k].r.sheets[key].columnOk = false;
            if(typeof this.aw[k].column[key] !== 'undefined'){
                let o = true;
                if(val.headers.length > 0){
                    $.each(val.headers,(c,d)=>{
                        if(typeof this.aw[k].column[key][c] === 'undefined' || this.aw[k].column[key][c] !== d){
                            o = false;
                            return false;
                        }
                    });
                }
                else{
                    o = false;
                }
                this.aw[k].r.sheets[key].columnOk = o;
            }
        });
    }

    processXls(k,p){

        if(Object.keys(p).length > 0){
            this.saveParams(k,p);
        }

        const arrSteps = [];
        let counter = 0;
        if( typeof this.aw[k] !== 'undefined' ){
            const content = $(`#${this.aw[k].elementID}`);
            if(content.length > 0){
                const progress = content.find('.xls-msg').find('.progress');
                progress.removeClass('hide');

                const dStep = content.find('.xls-step');

                if(typeof this.aw[k].processWhileUpload === "undefined" || typeof this.aw[k].processWhileUpload === false) {
                    let btnValidate = $('<button>Validar datos</button>').attr({
                        'class':'btn btn-default pro-validate'
                    });dStep.append(btnValidate);

                    let btnSave = $('<button>Guardar datos</button>').attr({
                        'class':'btn btn-default pro-save'
                    });dStep.append(btnSave);
                }

                $.each(this.aw[k].r.sheets,(s,d)=>{
                    if(d.columnOk){
                        let numLimit = (typeof this.aw[k].limit != "undefined")?this.aw[k].limit:this.mr;
                        const steps = this.calculateSteps(d.rows, k)
                        let init = 2;
                        let sheet = 0;
                        let process = 0;

                        for(let ss in steps){
                            counter++;
                            if(s !== sheet){
                                init = 2;
                                process = steps[ss] + 1;
                                sheet = s;
                            }
                            else{
                                init = process;
                                if(steps[ss] < numLimit){
                                    process = process + steps[ss];
                                }
                                else{
                                    process += (numLimit);
                                }
                            }

                            let divStep = $(`<div><span>${counter}) </span> Hoja ${s} - procesando de ${init} a ${process} filas</div>`).addClass('col-lg-12');dStep.append(divStep);

                            let btnData = $('<button>Obteniendo datos</button>').attr({
                                'class':'btn btn-default pro-data'
                            });divStep.append(btnData);

                            arrSteps.push({
                                sheet: s,
                                rows: steps[ss],
                                obj: divStep
                            });
                        }
                    }
                });
                if(arrSteps.length > 0){
                    this.nextLoad(k,arrSteps,0);
                }
            }
        }
    }

    nextLoad(k,arrSteps,step){
        let message = "Archivo procesado";
        if(typeof arrSteps[step] !== 'undefined'){
            this.boolProcess = true;

            let i = $('<i><i>').attr({
                'class':'fa fa-spinner',
                'aria-hidden':'true'
            });
            arrSteps[step].obj.find('.pro-data').append(i);

            if(this.objXHR !== null)this.objXHR = null;

            this.objXHR = $.ajax({
                type: 'GET',
                url: `${this.constructor.getToken(this.op)}&opt=getData`,
                data : {
                    load : this.aw[k].r.load_id,
                    sheet : arrSteps[step].sheet,
                    rows : arrSteps[step].rows,
                    processWhileUpload : (this.aw[k].processWhileUpload)?this.aw[k].process:""
                },
                cache: false,
                dataType: 'json',
                beforeSend: ()=>{

                },
                success: (d)=>{
                    arrSteps[step].obj.find('.pro-data').find('i').remove();
                    if(d.valido === 1){
                        let i = $('<i><i>').attr({
                            'class':'fa fa-check-circle',
                            'aria-hidden':'true'
                        });
                        arrSteps[step].obj.find('.pro-data').append(i);
                    }
                    else{
                        let i = $('<i><i>').attr({
                            'class':'fa fa-times-circle',
                            'aria-hidden':'true'
                        });
                        arrSteps[step].obj.find('.pro-data').append(i);
                    }
                    message = d.msj;
                    this.updateProgressBar(k,arrSteps.length,(step + 1));
                    this.nextLoad(k,arrSteps,step+1);
                },
                error: ()=>{
                    this.boolProcess = false;
                    let i = $('<i><i>').attr({
                        'class':'fa fa-times-circle',
                        'aria-hidden':'true'
                    });
                    arrSteps[step].obj.find('.pro-data').append(i);
                    this.dw.alertDialog("Hubo un problema al obtener datos");
                }
            });
        }
        else{
            if(typeof this.aw[k].processWhileUpload === "undefined" || typeof this.aw[k].processWhileUpload === false){
                this.boolProcess = false;
                this.validateData(k,arrSteps.length,step+1);
            }
            else{
                this.boolProcess = true;
                this.dw.alertDialog(message);
            }
        }
    }

    validateData(k,coutnSteps,step){
        this.boolProcess = true;
        const content = $(`#${this.aw[k].elementID}`);
        const dStep = content.find('.xls-step');

        let i = $('<i><i>').attr({
            'class':'fa fa-spinner',
            'aria-hidden':'true'
        });
        dStep.find('.pro-validate').append(i);

        if(this.objXHR !== null)this.objXHR = null;
        this.objXHR = $.ajax({
            type: 'GET',
            url: `${this.constructor.getToken(this.op)}&opt=validateData`,
            data : {
                load : this.aw[k].r.load_id,
                validate: this.aw[k].validate
            },
            cache: false,
            dataType: 'json',
            beforeSend: ()=>{

            },
            success: (d)=>{
                dStep.find('.pro-validate').find('i').remove();
                if(d.valido === 1){
                    let i = $('<i><i>').attr({
                        'class':'fa fa-check-circle',
                        'aria-hidden':'true'
                    });
                    dStep.find('.pro-validate').append(i);
                }
                else{
                    let i = $('<i><i>').attr({
                        'class':'fa fa-times-circle',
                        'aria-hidden':'true'
                    });
                    dStep.find('.pro-validate').append(i);
                }
                this.updateProgressBar(k,coutnSteps,step);
                this.bulk_data(k,coutnSteps,step+1)

            },
            error: ()=>{
                this.boolProcess = false;
                let i = $('<i><i>').attr({
                    'class':'fa fa-times-circle',
                    'aria-hidden':'true'
                });
                dStep.find('.pro-validate').append(i);
                this.dw.alertDialog("Hubo un problema al validar datos");
            }
        });
    }

    bulk_data(k,coutnSteps,step){
        this.boolProcess = true;
        const content = $(`#${this.aw[k].elementID}`);
        const dStep = content.find('.xls-step');

        let i = $('<i><i>').attr({
            'class':'fa fa-spinner',
            'aria-hidden':'true'
        });
        dStep.find('.pro-save').append(i);

        if(this.objXHR !== null)this.objXHR = null;
        this.objXHR = $.ajax({
            type: 'GET',
            url: `${this.constructor.getToken(this.op)}&opt=process`,
            data : {
                load : this.aw[k].r.load_id,
                process: this.aw[k].process
            },
            cache: false,
            dataType: 'json',
            beforeSend: ()=>{

            },
            success: (d)=>{
                dStep.find('.pro-save').find('i').remove();
                if(d.valido === 1){
                    let i = $('<i><i>').attr({
                        'class':'fa fa-check-circle',
                        'aria-hidden':'true'
                    });
                    dStep.find('.pro-save').append(i);
                }
                else{
                    let i = $('<i><i>').attr({
                        'class':'fa fa-times-circle',
                        'aria-hidden':'true'
                    });
                    dStep.find('.pro-save').append(i);
                }
                this.updateProgressBar(k,coutnSteps,step);
                this.dw.alertDialog(d.msj);
                // valida si el mensjae de respuesta es diferente a Datos procesados correctamente, para pasar a la funcion
                //donde se dibujara la alerta por nuevas categorias agregadas por planes,
                //Si no solo nos muestra el mensaje de Datos procesados correctamente.
                if (d.msj!="Datos procesados correctamente") {
                    this.drawalert(d.categorias);

                }

            },
            error: ()=>{
                this.boolProcess = false;
                let i = $('<i><i>').attr({
                    'class':'fa fa-times-circle',
                    'aria-hidden':'true'
                });
                dStep.find('.pro-save').append(i);
                this.dw.alertDialog("Hubo un problema al procesar datos");
            }
        });
    }

    //Se dibuja el Mensaje de Alerta cuando detecta nuevas categorias
    drawalert(d){
        let NewCategoria = '';
        for (const [key, value] of Object.entries(d)) {
            for (const [key, val] of Object.entries(value)) {
                const value = val.category_name;
                NewCategoria += ` ${value}, `;
            }

        }

        const element = `
                          <div style="border: 5px solid #d9d9d9; border-radius: 10px;">
                                <h1 class="titulo"> Genius: Categorías</h1>
                                     <img src="images/pending.png" style="margin-top: 6%; width: 12%; margin-left: 42%" />
                                  <p class="texto">Se han detectado nuevas categorías: ${NewCategoria} Ingresar a Perfiles de
                                                    acceso para dar los respectivos permisos a los perfiles de usuarios.
                                  </p>
                          </div>
                    `;
        this.dw.alertDialog("",element);
    }

    updateProgressBar(k,c,step){
        const p = 100 / (c + 2);
        let s = Math.round(p);
        if(p > s )s++;
        s = s * (step * 1);
        if(s > 100)
            s = 100;
        const content = $(`#${this.aw[k].elementID}`);
        const progress = content.find('.xls-msg').find('.progress');
        const bar = progress.find('.progress-bar');
        bar.attr({
            'aria-valuenov':s
        }).css({
            'width':`${s}%`
        });
    }

    calculateSteps(r, k){
        const s = [];
        let numLimit = (typeof k != "undefined" && typeof this.aw[k].limit != "undefined")?this.aw[k].limit:this.mr;
        while(r > 0){
            r -= numLimit;
            if(r <= 0)
                s.push(r + numLimit);
            else
                s.push(numLimit);
        }
        return s;
    }

    deletexls(k){
        if(typeof this.aw[k] !== 'undefined'){
            if(this.aw[k].r.load_id !== 'undefined'){
                $.ajax({
                    type: 'POST',
                    url: `${this.constructor.getToken(this.op)}&opt=delete&load=${this.aw[k].r.load_id}`,
                    beforeSend: ()=>{
                        this.dw.openLoading();
                    },
                    success: (d)=>{
                        this.dw.closeLoading();
                        if(d.valido === 1) this.drawInputFile(k)
                        this.dw.alertDialog(d.msj)
                    },
                    error: ()=>{
                        this.dw.closeLoading();
                    }
                });
            }
        }
    }

    saveParams(k,p){
        if(this.objXHR !== null)this.objXHR = null;
        this.objXHR = $.ajax({
            type: 'GET',
            url: `${this.constructor.getToken(this.op)}&opt=extra&load=${this.aw[k].r.load_id}`,
            data : p,
            dataType: 'json',
            beforeSend: ()=>{

            },
            success: (d)=>{
                if(d.valido === 0)
                    this.dw.alertDialog(d.msj);
            },
            error: ()=>{
                this.dw.alertDialog("Hubo un problema al guardar parametros extras");
            }
        });
    }
}