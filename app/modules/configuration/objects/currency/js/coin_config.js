
/**
 * Author: Edward Acu
 * 2019/05/27
 * Licence: Homeland S.A.
 * */


const configCurrencies = function configCurrencies (customSettings){
    let defaults = {
        body: "", selectClass: "selectCloin",
        coins: {}, widget: false, action: "", masterCoin: ""
    };
    const self = this;
    let coinSelected = ""
    customSettings || (customSettings = {});
    let settings = Object.assign({}, defaults,customSettings);
    let idBodyTable = "curr-body-table";

    this.init = ()=>{
        let body = `
        <div class="separator">
            <div class="row">
                <div class="col-lg-3 col-xs-8">
                    <select name="" id="selectCloin"></select>
                </div>
                <div class="col-lg-2 col-xs-1">
                    <button id="btnAddCurrency" class="btn btn-success" style="min-height: 38px;">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        ${lang.CONFIGURATION_ADD}
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <h3 >${lang.CONFIGURATION_TITLE_TABLE}</h3>
            </div>
        </div>`;
        let htmlTable = this.layoutTable();
        let objContainer = document.querySelector(`#${settings.body}`);
        objContainer.innerHTML = body + htmlTable;
        document.querySelector(`#btnAddCurrency`).onclick = ()=>{
            let configSelect = {
                default: {
                    placeholder: "Seleccione una",
                    selectedValue: coinSelected
                }
            };
            self.addCoin(coinSelected, self.visualRate, false,configSelect);
            return false;
        };
        //self.processedCoins(settings.selectClass, "");
        self.getCurrencies();
    };

    this.processedCoins = (strElementClass, value, callback, idCoin,configCoin)=>{
        if(!callback) callback = false;
        if(!configCoin) configCoin = false;
        if(!idCoin) idCoin= false;

        self.removeAutocomplete(strElementClass);
        let coinsParsed = [];
        for ( let coin in settings.coins){
            if(!idCoin){
                if(settings.coinsDB.hasOwnProperty(coin)){
                    continue;
                }
            }
            coinsParsed.push({
                text: coin + " " + coins[coin].name,
                value: coin,
            });
        }


        let configs = {
            default: {
                data: coinsParsed,
                //searchable: false,
                //defaultSelected: false,
                placeholder: "Seleccione una",
                //selectedValue: "EUR"
            },
        };
        let conf = {}
        if(typeof configCoin.default != "undefined"){
            conf = configCoin.default;
        }
        let setSelector = Object.assign({}, configs.default,conf);
        setTimeout(()=>{
            let selectorDefault = new Selectr(`#${strElementClass}`, setSelector);
            const selectElement = document.querySelector(`#${strElementClass}`);
            selectElement.addEventListener('change', (event) => {
                coinSelected = event.target.value;
                if(callback){
                    callback();
                }
            });
        },200);
    };

    this.setCoinsDB = (data)=>{
        //console.log(data)
        let myCoinsDB = [];
        for(let coin in data){
            myCoinsDB[data[coin].area_code] = data[coin].area_code;
        }
        settings.coinsDB = myCoinsDB;
        //console.log(settings.coinsDB,"coins db")
        //console.log(myCoinsDB);
    };

    this.removeAutocomplete = (strElementClass)=>{
        let element = document.querySelector(`#${strElementClass}`);
        let parent = element.parentElement;
        if(typeof parent != "undefined"){
            parent.innerHTML = "";
            parent.innerHTML = `<select name="" id="${strElementClass}"></select>`;
        }
        sleep(300)
    };

    this.addCoin = (coin, callback, idCoin,configs)=>{
        if(!idCoin) idCoin = false;
        if(!callback) callback = false;
        let strhtml = self.layoutEditing();
        let buttons = [];
        buttons.push({nombre:"Guardar", cssClass:"btn btn-success", funcion: self.saveCoin});
        settings.widget.alertDialog(strhtml,lang.CONFIGURATION_TITLE_MODAL,false,false,false,buttons);
        setTimeout(()=>{
            self.resetVisualRate();
            self.processedCoins("selectCloinEdit", coin,callback,idCoin, configs);
            self.eventsEditing();
        },300);

    };

    this.visualRate = ()=>{
        let objLblRate = document.querySelector("#lbl-rate");
        let objLblReverseRate = document.querySelector("#lbl-revers-rate");

        if(typeof objLblRate != "undefined" && typeof objLblReverseRate != "undefined"){
            objLblRate.innerHTML = `${settings.masterCoin} `;
            objLblReverseRate.innerHTML = `${coinSelected} ` ;
        }
    };

    this.resetVisualRate = ()=>{
        let objLblRate = document.querySelector("#lbl-rate");
        let objLblReverseRate = document.querySelector("#lbl-revers-rate");

        if(typeof objLblRate != "undefined" && typeof objLblReverseRate != "undefined"){
            objLblRate.innerHTML = lang.CONFIGURATION_CURRENCY_RATE;
            objLblReverseRate.innerHTML = lang.CONFIGURATION_CURRENCY_REVERSE_RATE;
        }
    };

    this.layoutEditing = ()=>{
        let html = `
            <div class="row">
                <div class="col-lg-12 col-xs-12">
                    <div class="form-group">
                        <label for="autoCompleteEditing">${lang.CONFIGURATION_CURRENCY_NAME}</label>
                        <select name="" id="selectCloinEdit"></select>
                    </div>                    
                </div>
            </div>
            <div class="row">
                <div class="col-lg-5 col-xs-5">
                    <div class="form-group">
                        <label id="lbl-rate" for="rate">${lang.CONFIGURATION_CURRENCY_RATE}</label>
                        <input type="tel" class="form-control" id="frm-currency-rate" placeholder="0.00">
                    </div>
                </div>
                <div class="col-lg-2 col-xs-2">
                    <br/>
                    <span> = </span>
                </div>
                <div class="col-lg-5 col-xs-5">
                    <div class="form-group">
                        <label id="lbl-revers-rate" for="reverseRate">${lang.CONFIGURATION_CURRENCY_REVERSE_RATE}</label>
                        <input type="tel" class="form-control" id="frm-currency-reverseRate" placeholder="0.00">
                    </div>
                </div>
            </div>`;
        return html;
    };

    this.layoutTable = ()=>{
        let html = `<table class="table table-striped .table-hover">
                        <thead>
                            <th><h4>${lang.CONFIGURATION_CURRENCY_CODE}</h4></th>
                            <th><h4>${lang.CONFIGURATION_CURRENCY_NAME}</h4></th>
                            <th><h4>${lang.CONFIGURATION_CURRENCY_RATE}</h4></th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </thead>
                        <tbody id="${idBodyTable}">
                        
                        </tbody>
                    </table>`;
        return html;
    };

    this.eventsEditing = ()=>{
        let objRate = document.querySelector("#frm-currency-rate");
        let objReverseRate = document.querySelector("#frm-currency-reverseRate");

        objRate.onkeyup = ()=>{
            let sinRate = objRate.value;
            objReverseRate.value = (1/sinRate);
        };
        objReverseRate.onkeyup = ()=>{
            let sinReverseRate = objReverseRate.value;
            objRate.value = (1/sinReverseRate);
        };
        self.visualRate();
    };

    this.getCurrencies = ()=>{
        fetch(`${settings.action}&op=getCurrencies`)
            .then( (response) => {
                settings.widget.openLoading();
                return response.json();
            })
            .then( (data) => {
                settings.widget.closeLoading();
                if(data.status == 'ok'){
                    self.setCoinsDB(data.currencies);
                    self.drawTable(data.currencies);
                    self.processedCoins(settings.selectClass, "");
                }
                return true
            })
            .catch( (error) => {
                settings.widget.closeLoading();
                settings.widget.alertDialog("Ocurrió un problema, porfavor intentelo de nuevo");
            });
    };

    this.drawTable = (data)=>{
        let bodyTable = document.querySelector(`#${idBodyTable}`);
        bodyTable.innerHTML = "";
        //console.log(bodyTable.innerHTML)
        for(let item in data){
            let rowsTable = '';
            let description = `1 ${settings.masterCoin} = ${data[item].rounding} ${data[item].area_code}  &nbsp;&nbsp;--&nbsp;&nbsp;  1${data[item].area_code} = ${data[item].decimal_digits} ${settings.masterCoin}`;
            if(data[item].pivot =='Y') description = `Esta es su moneda base`;
            let rowclickeable = `<td style="cursor: pointer;" id="edit-${item}"><p class="text-info" >Editar</p></td>
                                <td style="cursor: pointer;" id="delete-${item}"><p class="text-danger" >Eliminar</p></td>`;
            if(data[item].area_code == settings.masterCoin){
                rowclickeable = `<td >&nbsp;</td>
                                <td >&nbsp;</td>`;
            }

            rowsTable = `<tr>
                            <td>${data[item].area_code}</td>
                            <td>${data[item].name}</td>
                            <td>${description}</td>
                            ${rowclickeable}                         
                        </tr>`;
            bodyTable.insertAdjacentHTML('beforeend', rowsTable);

            if(data[item].area_code != settings.masterCoin){
                let elementEdit = document.querySelector(`#edit-${item}`);
                let elementDelete = document.querySelector(`#delete-${item}`)
                elementEdit.onclick = ()=> {
                    self.edit(data[item]);
                };
                elementDelete.onclick = ()=>{
                    self.delete(data[item]);
                };
            }
        }
    };

    this.edit = (data)=>{
        coinSelected = data.area_code;
        let configSelect = {
            default: {
                    selectedValue: coinSelected,
                    disabled: true,
                }
        };
        self.addCoin(data.area_code,false,data.id,configSelect);
        setTimeout(()=>{
            let objRate = document.querySelector("#frm-currency-rate");
            let objReverseRate = document.querySelector("#frm-currency-reverseRate");
            objRate.value = data.rounding;
            objReverseRate.value = data.decimal_digits;
        },300);
    };

    this.delete = (data)=>{
        fetch(`${settings.action}&op=delete&id=${data.id}`)
            .then( (response) => {
                settings.widget.openLoading();
                return response.json();
            })
            .then( (data) => {
                settings.widget.closeLoading();
                //console.log(data);
                if(data.status == 'ok'){
                    self.getCurrencies();
                }
                return true
            })
            .catch( (error) => {
                settings.widget.closeLoading();
                settings.widget.alertDialog("Ocurrió un problema, porfavor intentelo de nuevo");
            });
    };

    this.saveCoin = ()=>{
        let objRate = document.querySelector("#frm-currency-rate");
        let objReverseRate = document.querySelector("#frm-currency-reverseRate");
        let objSelector = document.querySelectorAll(".selectr-selected");
        let objSelectorLbl = document.querySelectorAll(".selectr-label");
        let parentRate = objRate.parentElement;
        let parentReverse = objReverseRate.parentElement;

        objSelector.forEach((item)=>{
            item.classList.remove("selectr-selected-error");
        });
        objSelectorLbl.forEach((item)=>{
            item.classList.remove("text-danger");
        });
        parentRate.classList.remove("has-error");
        parentReverse.classList.remove("has-error");

        if(coinSelected.length > 0 && Number(objRate.value) && Number(objReverseRate.value)){
            let myDataForm = {
                "rate": objRate.value,
                "reverseRate": objReverseRate.value,
                "area_code": coinSelected,
                "symbol": settings.coins[coinSelected].symbol,
                "symbol_native": settings.coins[coinSelected].symbol_native,
                "name": settings.coins[coinSelected].name,
                "name_plural": settings.coins[coinSelected].name_plural,
            };

            fetch(`${settings.action}&op=save`,{
                    method: 'POST',
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(myDataForm)
                })
                .then((response)=>{
                    return response.json();
                })
                .then((data)=>{
                    settings.widget.closeDialog();
                    self.getCurrencies();
                })
                .catch((error)=>{
                    console.log(error)
                });
        }
        else{
            objSelector.forEach((item)=>{
                item.classList.add("selectr-selected-error");
            });
            objSelectorLbl.forEach((item)=>{
                item.classList.add("text-danger");
            });
            parentRate.classList.add("has-error");
            parentReverse.classList.add("has-error");
        }
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
};