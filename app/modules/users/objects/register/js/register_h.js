let dom_submit = document.querySelector(`#login_form`);
let dom_password = document.querySelector(`#commerce_password`);
let dom_send = document.querySelector(`.button_login`);
let dom_alerts = document.querySelector(`.form_alerts`);
let dom_confirm = document.querySelector(`#commerce_password_confirm`);
let dom_department = document.querySelector(`#commerce_address_department`);
let dom_municipality = document.querySelector(`#commerce_address_town`);
let dom_bank_number = document.querySelector(`#commerce_bank_number`);
let dom_number = document.querySelector(`#commerce_phone`);
let dom_country = document.querySelector(`#country`);
let dom_suburb = document.querySelector(`#suburb`);
let dom_mail = document.querySelector(`#commerce_mail`);
let dom_dpi = document.querySelector(`#commerce_user_dpi`);
let dom_really_accept = document.querySelector(`#accept`);
let arrInput = {};
let dom_bank_select = document.querySelector(`#commerce_bank`);
let dom_dpi_front = document.querySelector(`#dpiFront`);
let dom_dpi_back = document.querySelector(`#dpiBack`);
let dom_account_image = document.querySelector(`#stateAccountImage`);
let dom_samples = document.querySelectorAll(`.item-sample`);
let dom_accept = document.querySelector(`#accept_terms`);

let dw = new drawWidgets();

triggerEvents();

setTimeout(()=>{
    dom_accept.addEventListener('click', ()=>{
        let temp = tempTermsAndConditions();
        dw.alertDialog(temp, `Términos y condiciones`);
        setTimeout(()=>{
            let term_modal = document.querySelector(`.modal-hml-content-sm`);
            term_modal.style.width = '66%';
            term_modal.style.maxWidth = '600px';
        }, 100);
    });
}, 100);

function tempTermsAndConditions(){
    let temp = `<div> 
                    <div>${terms_of_service}</div>
                    <div class="col-sm-12 text-center"> 
                        <button type="button" class="btn wallet-button wallet-button" onclick="dw.closeDialog();">Cerrar</button>
                        <button type="button" class="btn wallet-button wallet-button-positive" onclick="acceptTerms()">Acepto</button>
                    </div>
                </div>`;
    return temp;
}

function acceptTerms(){
    if(!dom_really_accept.checked){
        dom_really_accept.checked = true;
        if(dom_really_accept.checked && triggerValidate()){
            if(dom_password.value.length && dom_confirm.value.length){
                dom_send.removeAttribute('disabled');
            }
        }
        else{
            dom_send.setAttribute('disabled', 'true');
        }
    }
    dw.closeDialog();
}

let apiData = {
    baseRequest(type, options = {}){
        return fetch(`${url}&op=${type}`, options);
    },
    Register: {
        login(form){
            return apiData.baseRequest(`login`, {
                method: 'POST',
                body: form
            })
        }
    },
    Bank: {
        getAll(){
            return apiData.baseRequest(`getAllBanks`);
        }
    },
    country : {
        returnStates(intIdDepartment){
            let real = new FormData();
            real.append('id', intIdDepartment);
            return apiData.baseRequest(`getTown`, {
                method: 'POST',
                body: real
            })
        },
        returnDepartments(){
            return apiData.baseRequest(`getAllDepartments`);
        }
    }
};

apiData.country.returnDepartments()
    .then((r)=>r.json())
    .then((response)=>{
        let temp = ``;
        Object.values(response.data).map((data)=>{
            temp += `<option value="${data.id}">${data.nombre}</option>`;
        });
        dom_department.innerHTML = temp;
        let trigger = new Event('change');
        dom_country.value = response.country[0].id ? response.country[0].id : 0;
        dom_department.dispatchEvent(trigger);
    })
    .catch((e)=>console.log(e));

apiData.Bank.getAll()
    .then((r)=>r.json())
    .then((response)=>{
        if(response.data){
            let temp = `<option value="0" selected disabled>Selecciona un banco</option>`;
            Object.values(response.data).forEach((b)=>{
                temp += `<option value="${b.id}">${b.bank_name}</option>`
            });
            dom_bank_select.innerHTML = temp;
        }
    })
    .catch((e)=>console.log(e));

function ReCaptchaEvent(){
    let temp = `<div class="text-center"> 
                    <p>${lang.REGISTER_CONDITION}</p>
                    <p>${lang.REGISTER_LAW}</p>
                    <div class="text-center"> 
                        <button type="button" class="btn wallet-button wallet-button-positive ok_take_my_soul">Acepto</button>
                        <button type="button" class="btn wallet-button wallet-button-negative nope_i_like_my_soul">No</button>
                    </div>
                </div>`;
    dw.alertDialog(`${temp}`, `Confirmación`, '', '', '', '', '', false);
    setTimeout(()=>{
        triggerReCaptchaEvents();
    }, 100);
}

function triggerEvents(){
    let time = 0;
    [dom_password, dom_confirm].forEach((element)=>{
        element.addEventListener('keyup', ()=>triggerValidate());
    });

    dom_really_accept.addEventListener('change', ()=>{
       if(dom_really_accept.checked){
           dom_really_accept.checked = false;
           let temp = tempTermsAndConditions();
           dw.alertDialog(`${temp}`, `Términos y condiciones`);
           setTimeout(()=>{
               let term_modal = document.querySelector(`.modal-hml-content-sm`);
               term_modal.style.width = '66%';
               term_modal.style.maxWidth = '600px';
           }, 100);
       }
       if(dom_really_accept.checked && triggerValidate()){
           if(dom_password.value.length && dom_confirm.value.length){
               dom_send.removeAttribute('disabled');
           }
       }
       else{
           dom_send.setAttribute('disabled', 'true');
       }
    });

    dom_bank_number.addEventListener('keyup', ()=>{
        clearTimeout(time);
        if(dom_bank_number.value.length <= 23){
            time = setTimeout(()=>{
                dom_bank_number.value = setBankNumber(dom_bank_number.value, dom_bank_number) ? setBankNumber(dom_bank_number.value, dom_bank_number) : '';
            }, 370);
        }
        else{
            dom_bank_number.value = dom_bank_number.value.length ? dom_bank_number.value.substr(0, 23) : '';
        }
    });

    dom_number.addEventListener('keyup', (e)=>{
        if(dom_number.value){
            if(dom_number.value.length >8){
                dom_number.value = dom_number.value.substr(0, 8);
            }
        }
    });

    dom_dpi.addEventListener('keyup', ()=>{
       clearTimeout(time);
       let nospaces = removeSpaces(dom_dpi.value);
       if(nospaces.length <= 13){
           setTimeout(()=>{
               dom_dpi.value = realDPIFormat(dom_dpi.value);
           }, 100);
       }
       else{
           dom_dpi.value = dom_dpi.value.substr(0, 15);
       }
    });

    dom_department.addEventListener('change', ()=>{
        apiData.country.returnStates(dom_department.value)
            .then((r)=>r.json())
            .then((response)=>{
                let temp = ``;
                Object.values(response.data).forEach((data)=>{
                    temp += `<option value="${data.id}">${data.nombre}</option>`;
                });
                dom_municipality.innerHTML = temp;
            })
            .catch((e)=>console.log(e));
    });
}

function triggerValidate(){
    if(dom_password.value === dom_confirm.value){
        dom_password.classList.remove('fill');
        dom_confirm.classList.remove('fill');
        dom_alerts.innerHTML = '';
        if(dom_password.value.length && dom_confirm.value.length && dom_really_accept.checked){
            dom_send.removeAttribute('disabled');
        }
        return true;
    }
    else{
        /*dom_send.setAttribute('disabled', 'true');*/
        if(dom_password.value && dom_confirm.value){
            dom_password.classList.add('fill');
            dom_confirm.classList.add('fill');
            dom_alerts.innerHTML = `<h3>Las contraseñas no coinciden</h3>`;
            return false;
        }
        return false;
    }
}

function validateMail(mail){
    let regex = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/g;
    return !!mail.match(regex);

}

function validateForm(){
    let all_inputs = document.querySelectorAll(`.form-control`);
    arrInput = {};

    all_inputs.forEach((element)=>{
        if(element.id != 'suburb'){
            if(element.value.length < 1 || element.value === "0"){
                element.classList.add('fill');
                arrInput[element.id] = true;
                element.addEventListener('change', ()=>{
                    if(element.value){
                        element.classList.remove('fill');
                    }
                })
            }
            else{
                element.removeEventListener('change', ()=>{
                    if(element.value){
                        element.classList.remove('fill');
                    }
                })
            }
        }
    });
    if(Object.keys(arrInput).length){
        let dom_new_element = document.createElement('h3');
        dom_new_element.innerHTML = `Completa los campos requeridos`;
        dom_alerts.innerHTML = ``;
        dom_alerts.appendChild(dom_new_element);
    }
    if(dom_password.value !== dom_confirm.value){
        arrInput[dom_password] = true;
    }
    if(validateMail(dom_mail.value)){
        dom_mail.classList.remove('fill');
    }
    else{
        arrInput[dom_mail.id] = true;
        let dom_new_element = document.createElement('h3');
        dom_new_element.innerHTML = `Ingresa un correo válido`;
        dom_alerts.appendChild(dom_new_element);
    }
    if(!dom_really_accept.checked){
        let dom_new_element = document.createElement('h3');
        dom_new_element.innerHTML = `Acepta términos y condiciones`;
        dom_alerts.appendChild(dom_new_element);
        arrInput[dom_really_accept.id] = true;
    }
    [dom_dpi_front, dom_dpi_back, dom_account_image].forEach((element)=>{
        let element_container = document.querySelector(`.item_${element.id}`);
        if(!element.value){
            if(element_container){
                element_container.classList.add('fill');
                arrInput[element.id] = true;
            }
        }
        else{
            if(element_container){
                element_container.classList.remove('fill');
            }
        }
    });
    return !!Object.keys(arrInput).length;
}

function setBankNumber(value, element){
    if(value.length > 0){
        value = value.replace(/[a-zA-Z]/g, '');

        let regexNumber = /^(?=.*?[1-9])[0-9()-]+$/g;

        let trimmed = removeSpaces(value);
        trimmed = trimmed.replace(/-/g, '');

        if(trimmed.length > 10 && trimmed.length < 16){
            if(element){
                element.classList.add('fill');
            }
        }
        else{
            if(element){
                element.classList.remove('fill');
            }
        }

        let number_value = value.match(regexNumber);
        if(!number_value){
            if(element){
                element.classList.add('fill');
            }
            return value ? value : '';
        }
        let spaced_value = String(value).replace(/\W/gi, '').replace(/(.{4})/g, '$1-');
        let result = String(spaced_value).trim().replace(/\-$/g, '');
        return result ? result : '';
    }
}

function removeSpaces(value){
    return value.replace(/\s/g, '');
}

function createAlert(alert, type = 'div'){
    let new_element = document.createElement(type);
    new_element.innerHTML = alert;
    return new_element;
}

function realDPIFormat(value){
    let no_spaces = removeSpaces(value);
    no_spaces = no_spaces.replace(/[a-zA-Z]/g, '');
    if(no_spaces.length <= 9){
        let dpiRegex = /\b(\d{4})(\d{0,5})\b/g;
        return no_spaces.replace(dpiRegex, '$1 $2');
    }
    else{
        if(no_spaces.length > 13) no_spaces.substr(0, 13);
        let dpiRegex = /\b(\d{4})(\d{5})(\d{0,4})\b/g;
        return no_spaces.replace(dpiRegex, '$1 $2 $3');
    }
}

function setPreviewImage(input, callback = ''){
    let dom_container = document.querySelector(`.image_${input.id}`);
    if(input.files && input.files[0]){
        let size = input.files[0].size;
        let fileName = input.files[0].name;
        let limit = 4000000;
        let recommended_limit = 500000;
        console.log({
            size,
            limit,
            recommended_limit
        });
        if(size > limit){
            input.value = '';
            dom_container.innerHTML = `El límite es: ${limit / 1000} kb`;
            return;
        }
        let reader = new FileReader();
        let base = "";
        reader.onload = (e)=>{

            /*compress*/
                let maxWidth = 800;
                let maxHeight = 800;
                const img = new Image();
                img.src = e.target.result;
                img.onload = ()=>{
                    const elem = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    if(!(size >= recommended_limit)){
                        maxWidth = width;
                        maxHeight = height;
                    }

                    if(width > maxWidth){
                        let ratio = maxWidth / width;
                        height = height * ratio;
                        width = width * ratio;
                    }
                    if(height > maxHeight){
                        let ratio = maxHeight / height;
                        height = height * ratio;
                        width = width * ratio;
                    }

                    elem.height = height;
                    elem.width = width;
                    const ctx = elem.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    ctx.canvas.toBlob((blob)=>{
                        const file = new File([blob], fileName, {
                            type: 'image/png',
                            lastModified: Date.now()
                        });
                        const sum_file = (e)=>{
                            let newFile = new FileReader();
                            newFile.onload = ()=>{
                                let temp = `<img src="${newFile.result}"><input type="hidden" id="base64_${input.id}" value="${newFile.result}">`;
                                dom_container.innerHTML = temp;
                            };
                            newFile.readAsDataURL(e);
                            if(callback.length){
                                callback(base);
                            }
                        };
                        sum_file(file);
                    });
                }
            /**/

            /*base = e.target.result;
            let temp = `<img src="${base}"><input type="hidden" id="base64_${input.id}" value="${base}">`;
            dom_container.innerHTML = temp;
            if(callback.length){
                callback(base);
            }*/
        };
        reader.readAsDataURL(input.files[0]);
    }
    else{
        let original_text = ``;
        if(input.id === 'dpiFront') original_text = 'Foto de DPI (frontal)';
        if(input.id === 'dpiBack') original_text = 'Foto de DPI (reverso)';
        if(input.id === 'stateAccountImage') original_text = 'Foto de soporte de cuenta, ejemplo: encabezado estado de cuenta, cheque, libreta de ahorro, etc.';
        dom_container.innerHTML = original_text ? original_text : `Cargar Archivo`;
    }
}

[dom_dpi_front, dom_dpi_back, dom_account_image].forEach((element)=>{
    element.addEventListener('change', ()=>{
        setPreviewImage(element);
    });
    element.addEventListener('click', ()=>{
        setPreviewImage(element);
    });
});

dom_samples.forEach((element)=>{
    element.addEventListener('click', ()=>{
        let temp = `<div class="image_in_modal">${element.innerHTML}</div>`;
        dw.alertDialog(temp, 'Muestra');
        setTimeout(()=>{
            let modal = document.querySelector(`.modal-hml-content-sm`);
            modal.style.width = '60%';
            modal.style.maxWidth = '600px';
        }, 1);
    })
})

function triggerReCaptchaEvents(){
    let accept_terms = document.querySelector(`.ok_take_my_soul`);
    let not_accepted = document.querySelector(`.nope_i_like_my_soul`);
    accept_terms.addEventListener('click', ()=>{
        dw.closeDialog();
        if(!validateForm()){
            let real = new FormData(dom_submit);
            real.append('dpi', removeSpaces(dom_dpi.value));
            real.append('accountNumber', dom_bank_number.value.replace(/-/g, ''));
            real.append('dpi_front', document.querySelector(`#base64_${dom_dpi_front.id}`).value);
            real.append('dpi_back', document.querySelector(`#base64_${dom_dpi_back.id}`).value);
            real.append('account_data', document.querySelector(`#base64_${dom_account_image.id}`).value);
            real.append('fromFetch', '');
            dw.alertDialog('Cargando', 'Cargando', '', '', '', '', '', false);
            apiData.Register.login(real)
                .then((r)=>r.json())
                .then((response)=>{
                    dw.closeDialog();
                    let temp = `<div>
                                    <div class=""> 
                                        <h3>${response.razon}</h3>
                                    </div>
                                    <div class="text-center"> 
                                        ${response.valido ? '<a href="/" class="btn wallet-button wallet-button-positive">Ok</a>' : '<span onclick="window.location.reload()" class="btn wallet-button wallet-button-negative">Cerrar</span>'}
                                    </div>
                                </div>`;
                    let title = response.valido ? 'Registrado' : 'Ocurrio un error';
                    dw.alertDialog(temp, title, '', '', '', '', '', false);
                })
                .catch((e)=>{
                    dw.closeDialog();
                    let temp = `<div>
                                    <div class=""> 
                                        <h3>Ocurrio un error</h3>
                                    </div>
                                </div>`;
                    let title = `Error`;
                    dw.alertDialog(temp, title);
                    console.log(e);
                });
        }
    });
    not_accepted.addEventListener('click', ()=>{
        dw.closeDialog();
        window.location = "/";
    });
}