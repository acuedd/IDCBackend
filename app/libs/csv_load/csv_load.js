/**
 * Author: David Rosales
 * Date: 12/11/2018
 * Version: 0.0.1
 */

class csv_load{

    constructor(arrWidget,draWidget){
        this.op = 'd019c0ac-9cb9-11e7-93c0-286ed488ca87';
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
            'name':'iXloadCsv',
            'id':`file-csv-${k}`,
            'class':'inputfile-csv input-file-default',
            'accept':'.csv',
        });divFile.append(input);

        let labelInput = $('<label></label>').attr({
            'for':`file-csv-${k}`
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

        let cntMessages = $('<div></div>').addClass('csv-msg');content.append(cntMessages);

        let divProgress = $('<div></div>').addClass('progress hide');cntMessages.append(divProgress);
        let divInsideProgress = $('<div></div>').attr({
            'class':'progress-bar',
            'role':'progressbar',
            'aria-valuenov':'0',
            'aria-valuemin':'0',
            'aria-valuemax':'100'
        }).css('width','0%');divProgress.append(divInsideProgress);

        let divStep = $('<div></div>').addClass('csv-step');cntMessages.append(divStep)
        form.on('submit',(e)=>{
            e.preventDefault();
            let limit = this.aw[k].limit;
            $.ajax({
                type :  'POST',
                url  :  this.constructor.getToken(this.op) + '&opt=save&limit='+this.aw[k].limit,
                data : new FormData(form[0]),
                dataType : 'JSON',
                contentType: false,
                cache: false,
                processData:false,
                limit:limit,
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
            this.processCsv(k);
        }
    }

    validateColumn(k){
        for(let key in this.aw[k].r.sheets){
            const val = this.aw[k].r.sheets[key];
            this.aw[k].r.sheets[key].columnOk = false;
            key++;
            if(typeof this.aw[k].column[key] !== 'undefined'){
                let o = true;
                if(val.headers.length > 0){
                    for(let c in val.headers){
                        const d = val.headers[c];
                        if(typeof this.aw[k].column[key][c] === 'undefined' || this.aw[k].column[key][c] !== d){
                            o = false;
                            return false;
                        }
                    }
                }
                else{
                    o = false;
                }
                key--;
                this.aw[k].r.sheets[key].columnOk = o;
            }
        }
    }

    processCsv(k,p){
        if(Object.keys(p).length > 0){
            this.saveParams(k,p);
        }

        const arrSteps = [];
        let counter = 0;
        if( typeof this.aw[k] !== 'undefined' ){
            const content = $(`#${this.aw[k].elementID}`);
            if(content.length > 0){
                const progress = content.find('.csv-msg').find('.progress');
                progress.removeClass('hide');

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
                            const dStep = content.find('.csv-step');
                            let divStep = $(`<div><span>${counter}) </span> Hoja ${s} - procesando parte  ${counter} </div>`).addClass('col-lg-12');dStep.append(divStep);

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
                    let valida = 0;
                    let a = [];
                    this.nextLoad(k,arrSteps,0,valida,a);
                }
            }
        }
    }

    nextLoad(k,arrSteps,step,valida,a){
        let message = "Archivo procesado";
        let status = valida;
        if(typeof arrSteps[step] !== 'undefined'){
            this.boolProcess = true;

            let i = $('<i><i>').attr({
                'class':'fa fa-spinner',
                'aria-hidden':'true'
            });
            arrSteps[step].obj.find('.pro-data').append(i);

            if(this.objXHR !== null)this.objXHR = null;
            let classe = this.aw[k].classe;
            let method = this.aw[k].method;

            this.objXHR = $.ajax({
                type: 'GET',
                url: `${this.constructor.getToken(this.op)}&opt=getData`,
                data : {
                    load : this.aw[k].r.load_id,
                    sheet : arrSteps[step].sheet,
                    rows : arrSteps[step].rows,
                    intPar:step,
                    classe:classe,
                    method:method,
                    processWhileUpload : (this.aw[k].processWhileUpload)?this.aw[k].process:""
                },
                cache: false,
                dataType: 'json',
                beforeSend: ()=>{

                },
                success: (d)=>{

                    arrSteps[step].obj.find('.pro-data').find('i').remove();

                    if(d.valido === 1){
                        status = status +0;
                        let i = $('<i><i>').attr({
                            'class':'fa fa-check-circle',
                            'aria-hidden':'true'
                        });
                        arrSteps[step].obj.find('.pro-data').append(i);
                    }
                    else{
                        status = status +1;
                        let i = $('<i><i>').attr({
                            'class':'fa fa-times-circle repro',
                            'aria-hidden':'true',
                            'id':'repro'
                        });
                        arrSteps[step].obj.find('.pro-data').append(i);
                        $(".pro-data").attr("id","repross");

                    }
                    message = d.msj;
                    this.updateProgressBar(k,arrSteps.length,(step + 1));
                    this.nextLoad(k,arrSteps,step+1,status,a);
                },
                error: ()=>{
                    a.push(step);
                    arrSteps[step].obj.find('.pro-data').find('i').remove();
                    let j = $('<i>Reprocesar datos<i>').attr({
                        'class':'reproceso',
                        'aria-hidden':'true',
                        'id':'reproceso'
                    });
                    arrSteps[step].obj.find('.pro-data').append(j);
                    $("#reproceso").on("click",()=>{
                        status = 0;
                        this.nextLoad(k,arrSteps,a[0],status,a);
                        this.dw.alertDialog("Reprocesar Datos");
                    });
                    status = status +1;

                    let i = $('<i><i>').attr({
                        'class':'fa fa-times-circle repro',
                        'aria-hidden':'true',
                        'id':'repro'
                    });
                    arrSteps[step].obj.find('.pro-data').append(i);
                    $(".pro-data").attr("id","repross");
                    this.nextLoad(k,arrSteps,step+1,status,a);
                    this.dw.alertDialog("Erro al procesar los datos","");
                }

            });
        }
        else{
            if(typeof this.aw[k].processWhileUpload === "undefined" || typeof this.aw[k].processWhileUpload === false){
                this.boolProcess = false;
                if ( valida == 0 ){
                    this.dw.alertDialog("Los Datos se Procesaron Correctamente","MENSAJE DEL SISTEMA",true);
                }
                else {
                    this.dw.alertDialog("Los Datos se Procesaron pero Existe un Erro");
                }

            }
            else{
                this.boolProcess = true;
                this.dw.alertDialog(message);
            }
        }
    }

    updateProgressBar(k,c,step){
        const p = 100 / c ;
        let s = Math.round(p);
        if(p > s )s++;
        s = s * (step * 1);
        if(s > 100)
            s = 100;
        const content = $(`#${this.aw[k].elementID}`);
        const progress = content.find('.csv-msg').find('.progress');
        const bar = progress.find('.progress-bar');
        bar.attr({
            'aria-valuenov':s
        }).css({
            'width':`${s}%`
        });
    }

    calculateSteps(r, k){
        const s = [];
        let numLimit = (typeof k != "undefined" && this.aw[k].limit != "undefined")?this.aw[k].limit:this.mr;
        while(r > 0){
            r -= numLimit;
            if(r <= 0)
                s.push(r + numLimit);
            else
                s.push(numLimit);
        }
        return s;
    }

    saveParams(k,p){
        if(this.objXHR !== null)this.objXHR = null;
        if(typeof this.aw[k].processWhileUpload === "undefined" || typeof this.aw[k].processWhileUpload === false){

        }

        this.objXHR = $.ajax({
            type: 'GET',
            url: `${this.constructor.getToken(this.op)}&opt=extra&load=${this.aw[k].r.load_id}`,
            data : p,
            dataType: 'json',
            async: false,
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

    clearDat(k){
        let nombre = this.aw[k].r.sheets[0].nombre;
        let load_id =this.aw[k].r.load_id;
        $.ajax({
            type: 'POST',
            url: `${this.constructor.getToken(this.op)}&opt=delete`,
            data:{
                name: nombre,
                load_id: load_id,
            },
            name:nombre,
            beforeSend: ()=>{
                this.dw.openLoading();
                },
            success: (d)=>{
                this.dw.closeLoading();
                if(d.valido === 1) this.drawInputFile(k)
                this.dw.alertDialog(d.msj,"MENSAJE DE SISTEMA",true)
            },
            error: ()=>{
                this.dw.closeLoading();
            }
        });


    }
}