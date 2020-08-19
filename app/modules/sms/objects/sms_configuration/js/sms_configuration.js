let app_container = document.querySelector(`#sms_config_app`);

app_container.innerHTML = tempAppBody();

setTable();

setTimeout(()=>{
    triggerOpenModalEvent();
}, 100);

/**
    primero los fetch, luego temps y por ultimo eventos
*/

/**inicio fetch*/

function fetchGetAllConfigurations(){
    return fetch(`${url}&op=getAllConfigurations`);
}

function fetchPostConfiguration(form){
    let real = new FormData(form);
    return fetch(`${url}&op=postSmsConfiguration`, {
        method: 'POST',
        body: real
    });
}

function fetchConfiguration(id){
    let real = new FormData();
    real.append('id', id);
    return fetch(`${url}&op=getConfiguration`, {
        method: 'POST',
        body: real
    });
}

function setTable(boolSet = false){
    fetchGetAllConfigurations()
        .then((r)=>r.json())
        .then((response)=>{
            let table_container = document.querySelector(`#configuration_table`);
            let temp = tempTable(response.data);
            table_container.innerHTML = temp;
            setTimeout(()=>{
                let configuration_table = $(`#configuration_table`);
                if(boolSet) configuration_table.DataTable().destroy();
                configuration_table.DataTable({
                    "language": {
                        "lengthMenu": "Mostrando _MENU_ registros por página",
                        "zeroRecords": "No encontrado",
                        "info": "Mostrando página _PAGE_ de _PAGES_",
                        "infoEmpty": "No hay registros disponibles",
                        "infoFiltered": "(Filtrado de _MAX_ registros)",
                        "search": " ",
                        "searchPlaceholder": "Buscar",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        }
                    }
                });
                triggerTableEvents();
                /*¯\_(?)_/¯*/
            }, 100);
        })
        .catch((e)=>{
            FAlert('Ocurrio un error', 'error');
        });
}

function setForm(){
    let form = document.querySelector(`#form-configuration_element`);
    form.addEventListener('submit', (e)=>{
        e.preventDefault();
        fetchPostConfiguration(form)
            .then((r)=>r.json())
            .then((response)=>{
                modalHomeland.closeDialog();
                setTable(true);
                FAlert(`${response.razon}`, 'success');
            })
            .catch((e)=>{
                FAlert('Ocurrio un error', 'error');
            });
    });
}

function fetchDeleteConfiguration(id){
    let real = new FormData();
    real.append('id', id);
    return fetch(`${url}&op=deleteConfiguration`, {
        method: 'POST',
        body: real
    });
}

/** inicio templates **/

function tempAppBody(){
    return `<div> 
                <div class=""> 
                    <button id="add_open_modal" class="btn btn-primary-outline">Agregar</button>
                </div>
                <div class=""> 
                    <table id="configuration_table"></table>
                </div>
            </div>`;
}

function triggerOpenModalEvent(){
    let dom_trigger_element = document.querySelector(`#add_open_modal`);
    let temp = tempForm();
    dom_trigger_element.addEventListener('click', ()=> {
        triggerModal(temp, `Nueva Configuración`);
        setTimeout(()=>{
            setForm();
        }, 1000);
    });
}

function triggerModal(temp, title){
    modalHomeland.setOptions({
        idDialog: "form-configuration-container",
        form: "form-configuration_element"
    });
    modalHomeland.alertDialog(temp, title);
}

function tempTableHeader(){
    return `<thead><tr>
                <th>Active</th> 
                <th>Area</th> 
                <th>Username</th> 
                <th>Url</th> 
                <th>Options</th>
            </tr></thead>`;
}

function tempTableBody(data){
    if(!data) return false;
    let {id, descripcion, key_validate, key_secret, fecha_creacion, active, cod_area, max_length, url_send, short_code_id, token, username, password, organization_id} = data;
    return `<tr> 
                <td>${active}</td> 
                <td>${cod_area}</td> 
                <td>${username}</td> 
                <td>${url_send}</td> 
                <td><button class="btn btn-primary-outline edit_configuration" id="edit_${id}"><i class="fa fa-edit"></i></button><button class="btn btn-danger-outline delete_configuration" id="delete_${id}"><i class="fa fa-trash"></i></button></td>
            </tr>`;
}

function tempTable(data){
    let temp = ``;
    if(data){
        temp += `${tempTableHeader()}
                 <tbody>`;
        Object.values(data).forEach((configuration)=>{
            temp += tempTableBody(configuration);
        });
        temp += `</tbody>`;
    }
    return temp;
}

function triggerTableEvents(){
    let dom_edit = document.querySelectorAll(`.edit_configuration`);
    let dom_delete = document.querySelectorAll(`.delete_configuration`);

    dom_edit.forEach((element)=>{
        element.addEventListener('click', ()=>{
            fetchConfiguration(element.id.split('_')[1])
                .then((r)=>r.json())
                .then((response)=>{
                    let temp = tempForm(response.data);
                    triggerModal(temp, 'Editar');
                    setTimeout(()=>{
                        setForm();
                    }, 100);
                })
                .catch((e)=>{
                    console.log(e);
                    FAlert('Ocurrio un error', 'error');
                });
        });
    })

    dom_delete.forEach((element)=>{
        element.addEventListener('click', ()=>{
            let temp = `<button type="button" class="btn btn-danger-outline" id="delete">Confirmar</button>`;
            triggerModal(temp, 'Confirmar');
            setTimeout(()=>{
                let confirm = document.querySelector(`#delete`)
                confirm.addEventListener('click', ()=>{
                    fetchDeleteConfiguration(element.id.split('_')[1])
                        .then((r)=>r.json())
                        .then((response)=>{
                            modalHomeland.closeDialog();
                            setTable(true);
                            FAlert(`${response.razon}`, 'success');
                            /**/
                        })
                        .catch((e)=>{
                            FAlert(`Ocurrio un error`, 'error');
                        });
                });
            }, 1000);
        });
    })
}

function tempForm(data){
    let {id, descripcion, key_validate, key_secret, fecha_creacion, active, cod_area, max_length, mensaje, url_send, short_code_id, token, username, password, organization_id} = data ? data : {};
    return `<div class=""> 
                ${data && id ? '<input type="hidden" name="id" value="'+id+'">' : ''}
                <div class="col-sm-12"> 
                    <div class="form-group col-sm-6"> 
                        <label for="">Key Validate</label>
                        <input name="key_validate" type="text" class="form-control" value="${data && key_validate ? key_validate : ''}">
                    </div> 
                    <div class="form-group col-sm-6"> 
                        <label for="">Key Secret</label> 
                        <input name="key_secret" type="text" class="form-control" value="${data && key_secret ? key_secret : ''}"> 
                    </div>
                </div>
                <div class="col-sm-12"> 
                    <div class="form-group col-sm-6">
                        <label for="">Active</label>
                        <select name="active" id="" class="form-control"> 
                            <option value="Y" ${data && active === 'Y' ? 'selected' : ''}>si</option>
                            <option value="N" ${data && active === 'N' ? 'selected' : ''}>no</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="">Área</label>
                        <input name="area" value="${data && cod_area ? cod_area : ''}" type="text" class="form-control">
                    </div>
                </div>
                <div class="col-sm-12"> 
                    <div class="form-group col-sm-6">
                        <label for="">Descripción</label>
                        <textarea name="description" id="description" class="form-control" cols="10" rows="5">${data && descripcion ? descripcion : ''}</textarea>
                    </div>
                    <div class="form-group col-sm-6">
                        <div class="form-group">
                            <label for="">Máx length</label>
                            <input name="max_length" value="${data && max_length ? max_length : ''}" type="number" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="col-sm-12"> 
                    <div class="form-group col-sm-6">
                        <label for="">Url</label>
                        <input name="url_send" value="${data && url_send ? url_send : ''}" type="text" class="form-control">
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="">Short Code ID</label>
                        <input name="short_code_id" value="${data && short_code_id ? short_code_id : ''}" type="text" class="form-control">
                    </div>
                </div>
                <div class="col-sm-12"> 
                    <div class="form-group col-sm-6">
                        <label for="">User</label>
                        <input name="username" value="${data && username ? username : ''}" type="text" class="form-control">
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="">Password</label>
                        <input name="password" value="${data && password ? password : ''}" type="text" class="form-control">
                    </div>
                </div>
                <div class="col-sm-12"> 
                    <div class="form-group col-sm-6">
                        <label for="">Token</label>
                        <input name="token" value="${data && token ? token : ''}" type="text" class="form-control">
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="">Organización</label>
                        <input type="text" class="form-control" name="organization_id" value="${data && organization_id ? organization_id : ''}">
                    </div>
                </div>
                <div class="text-center">
                    <button class="btn btn-primary-outline" id="form_configuration_trigger">Guardar</button>
                </div>
            </div>`;
}

