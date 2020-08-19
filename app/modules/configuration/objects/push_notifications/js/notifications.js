let app_container = document.querySelector(`#notification_app`);
let modal = $(`#notification_modal`);
/* ¯\_(?)_/¯ */
let modal_title = document.querySelector(`#modal_title`);
let modal_body = document.querySelector(`#modal_body`);
let arr_users = [];

app_container.innerHTML = tempForm();
setTimeout(()=>{
    triggerSendActions();
    setNotificationTypes();
}, 100);

let fetchUser = (DPI)=>{
    let form = new FormData();
    form.append('dpi', DPI);
    return fetch(`${url}&op=getUser`, {
        method: 'POST',
        body: form
    });
};

function setApps(){
    fetchApps()
        .then((r)=>r.json())
        .then((response)=>{
            let select_app = document.querySelector(`#application_key`);

            let temp = ``;
            Object.values(response.data).forEach((app)=>{
                if(app.api_key){
                    temp += `<option value="${app.api_key ? app.api_key : ''}">${app.name}</option>`;
                }
            });
            select_app.innerHTML = temp;
        })
        .catch((e)=>console.log(e));
}

function setNotificationTypes(){
    getNotificationTypes()
        .then((r)=>r.json())
        .then((response)=>{
            let arrTypes = response.data;
            let select_notification_type = document.querySelector(`#notification_type`);
            let temp = ``;

            if(arrTypes){
                Object.values(arrTypes).forEach((type)=>{
                    temp += `<option value="${type.value}">${type.name}</option>`;
                })
            }
            else{
                temp += `<option selected disabled>No tienes tipos disponibles</option>`;
            }
            select_notification_type.innerHTML = temp;
            setTimeout(()=>{
                let select_app_container = document.querySelector(`#select_app`);
                select_notification_type.addEventListener('change', ()=>{
                    if(select_notification_type.value === 'n'){
                        select_app_container.classList.remove('hide-element');
                        setApps();
                    }
                    else{
                        let select_app = document.querySelector(`#application_key`);
                        select_app.innerHTML = '';
                        select_app_container.classList.add('hide-element')
                    }
                })
            }, 3000);
        })
        .catch((e)=>console.log(e));
}

function getNotificationTypes(){
    return fetch(`${url}&op=getNotificationTypes`);
}

function fetchApps(){
    return fetch(`${url}&op=getApps`);
}

let postNotification = (arrDPI, title, notification, app, type) => {
    let form = new FormData();
    if(arrDPI.length){
        arrDPI.forEach((user)=>{
            form.append('users[]', user);
        });
    }
    form.append('notification_title', title);
    form.append('notification_body', notification);
    form.append('application_key', app);
    form.append('notification_type', type);
    return fetch(`${url}&op=postNotification`, {
        method: 'POST',
        body: form
    });
};

let tempNotificationModal = (data, boolconfirm) => {
    return `<div> 
                <div class="confirm-content"> 
                    <div class="col-xs-12"> 
                        <h4>${data.title.value}</h4> 
                        <p>${data.body.value}</p>
                    </div>
                    <div class="text-center"> 
                        <button class="btn btn-primary-outline" id="send_to_all">Confirmar envio</button>
                    </div>
                </div>
            </div>`;
};

let tempFewNotification = (data) => {
    return `<div> 
                <div class="search-people-container"> 
                    <div class="form-group">
                        <label for="">Buscar</label>
                        <input type="text" class="form-control" id="input_search_user">
                    </div>
                    <div class=""> 
                        <div class=""> 
                            <span class="btn btn-primary" data-toggle="collapse" data-target="#users_to_send">
                                Usuarios 
                                <span class="badge" id="count"></span>
                            </span>
                        </div>
                        <div class="collapse"id="users_to_send"></div>
                        <div id="users_results"></div>
                    </div>
                </div>
            </div>`;
};

let triggerModal = (temp, title)=>{
    modal.modal();
    /* ¯\_(?)_/¯ */
    modal_title.innerHTML = title;
    modal_body.innerHTML = temp;

    setTimeout(()=>{
        triggerModalEvents();
    }, 100);
};

function triggerModalEvents(){
    let send_to_all = document.querySelector(`#send_to_all`);
    let get_all_user = document.querySelector('#input_search_user');

    if(send_to_all){
        send_to_all.addEventListener('click', ()=>{
            let notification_title = document.querySelector(`#notification_title`);
            let notification_body = document.querySelector(`#notification_body`);
            let application_key = document.querySelector(`#application_key`);
            let notification_type = document.querySelector(`#notification_type`);
            let error_container = document.querySelector(`.all_errors`);

            send_to_all.setAttribute('disabled', 'true');
           postNotification([... new Set(arr_users)], notification_title.value, notification_body.value, application_key.value, notification_type.value)
               .then((r)=>r.json())
               .then((response)=>{
                   send_to_all.setAttribute('disabled', 'false');
                   modal.modal('hide');
                   notification_title.value = '';
                   notification_body.value = '';
                   arr_users = [];
                   FAlert(`${response.msj}`, `${response.valido ? 'success' : 'error'}`);
                   if(!response.valido && response.errors){
                       error_container.innerHTML = `<div class="alert alert-danger-outline">${response.error ? response.error : 'Ocurrio un error'}</div>`;
                   }
                   if(response.data){
                       Object.values(response.data).forEach((notification)=>{
                          try{
                              let parsed = JSON.parse(notification);
                              let temp = ``;
                              let dom_div = document.createElement('div');
                              Object.entries(parsed).map(([i, data])=>{
                                  let div_temp = ``;
                                  if(i === 'success'){
                                      div_temp += `success: ${data}, `;
                                      dom_div.append(div_temp);
                                  }
                                  if(i === 'failure'){
                                      div_temp += `error: ${data} `;
                                      dom_div.append(div_temp)
                                  }
                                  error_container.appendChild(dom_div);
                              });
                          }catch (e) {
                              console.log(e);
                              console.log('catch');
                              error_container.innerHTML = notification;
                          }
                       });
                   }
               })
               .catch((e)=>{
                   arr_users = [];
                   modal.modal('hide');
                   FAlert('Ocurrio un error', 'error');
               });
        });
    }

    if(get_all_user){
        let temp_container = document.querySelector(`#users_results`);
        get_all_user.addEventListener('keyup', ()=>{
            if(get_all_user.value.length > 3){

                /*este reg es solo para number*/
                let reg = /^[0-9]*$/g;
                /*get_all_user.value.match(reg)*/
                if(get_all_user.value){
                    fetchUser(get_all_user.value)
                        .then((r)=>r.json())
                        .then((response)=>{
                            let temp = `<div class="text-center"> Resultados:`;

                            if(response.data.length !== 0){
                                temp += tempUser(response.data);
                            }
                            else{
                                temp += `<div>No encontrado</div>`;
                            }
                            temp_container.innerHTML = temp;
                            setTimeout(()=>{
                                addUserEvent();
                            }, 100);
                        })
                        .catch((e)=>{
                            console.log(e);
                            temp_container.innerHTML = `Ocurrio un error`;
                        });
                }
            }
        });
    }
}

function tempUser(data) {
    let temp = ``;
    if(data){
        Object.values(data).forEach((user)=>{
            if(user){
                temp += `<div class="alert alert-primary-outline user_to_add" id="${user.name}">${user.name} - ${user.nombres ? user.nombres : user.realname}</div>`;
            }
        });
    }
    return temp;
}

function removeUserEvent(element){
    let arrFilter = arr_users.filter((e)=>{
        return e !== element.id;
    });
    arr_users = arrFilter;
    setSelectedUsers([... new Set(arr_users)]);
}

function setSelectedUsers(arr_data){
    let selected_users = document.querySelector(`#users_to_send`);
    let counter = document.querySelector(`#count`);
    counter.innerHTML = arr_data.length;
    let temp = ``;
    if(arr_data.length){
        arr_data.forEach((d)=>{
            temp += `<div id="${d}" class="alert alert-default-outline custom_alert user_to_remove" onclick="removeUserEvent(this)">${d}</div>`;
        });
        selected_users.innerHTML = temp;
    }
    else{
        selected_users.innerHTML = temp;
    }
}

function addUserEvent(){
    let users = document.querySelectorAll(`.user_to_add`);
    users.forEach((element)=>{
        element.addEventListener('click', ()=>{
            if(element.id){
                arr_users.push(element.id);
                let arr_unique_users = [... new Set(arr_users)];
                setSelectedUsers(arr_unique_users);
            }
        });
    })
}

function triggerSendActions(){
    let trigger_action_all = document.querySelector(`#send_all`);
    let trigger_action_few = document.querySelector(`#send_few`);

    let notification_title = document.querySelector(`#notification_title`);
    let notification_body = document.querySelector(`#notification_body`);

    let objNoti = {
        title: notification_title,
        body: notification_body
    };

    const validate = ()=>{
        if(objNoti.title.value && objNoti.body.value){
            return true;
        }
        return false;
    };

    trigger_action_all.addEventListener('click', ()=>{
        if(validate()){
            let temp = tempNotificationModal(objNoti);
            triggerModal(temp, 'Enviar a todos');
            arr_users = [];
        }
        else{
            FAlert('Llena los campos');
        }
    });

    trigger_action_few.addEventListener('click', ()=>{
        if(validate()){
            let temp = `<div class="row" style="padding: 0 1em;">`;
            temp += `<div class="col-sm-6">${tempNotificationModal(objNoti)}</div>`;
            temp += `<div class="col-sm-6">${tempFewNotification(objNoti)}</div>`;
            temp += `</div>`;
            triggerModal(temp, 'Enviar a usuarios');
        }
        else{
            FAlert('Llena los campos');
        }
    });
}

function tempForm(){
    return `<div>
                <div class=""> 
                    <div class=""> 
                        <div class="form-group col-sm-6"> 
                            <label for="">Tipo de notificación</label> 
                            <select id="notification_type" class="form-control"> 
                                
                            </select> 
                        </div>
                        <div id="select_app" class="form-group hide-element col-sm-6"> 
                            <label for="">Aplicación</label> 
                            <select name="" id="application_key" class="form-control"></select> 
                        </div>
                    </div>
                    <div class="form-group col-xs-12"> 
                        <label for="">Título</label> 
                        <input type="text" id="notification_title" class="form-control">
                    </div> 
                    <div class="form-group col-sm-12"> 
                        <label for="">Notificación</label> 
                        <textarea name="" id="notification_body" cols="30" rows="10" class="form-control"></textarea>
                    </div>
                    <div class="form-group text-center col-xs-12"> 
                        <button class="btn btn-success-outline" id="send_all">Enviar a todos</button> 
                        <button class="btn btn-primary-outline" id="send_few">Seleccionar usuarios</button>
                    </div>
                </div>
                <div class="all_errors col-xs-12"></div>
            </div>`;
}