const profileJs = function ProfileJs(settings){
    this.init = ()=>{
        let allTogether = ``;
        allTogether += tempTabHeader();
        allTogether += tempTabBody();
        let body = document.getElementById(settings.body);
        let dw = new drawWidgets();
        body.innerHTML = allTogether;

        apiProfile.Types.getByType('image')
            .then((r)=>r.json())
            .then((response)=>{
                let tempBox = document.querySelector(`#images_container`);
                tempBox.innerHTML = tempCardsImages(response.data);
                manageButtons(settings.action);
                setImageButtons();
            })
            .catch((e)=>console.log(e));

        apiProfile.Types.getByType('color')
            .then((r)=>r.json())
            .then((response)=>{
                let tempBox = document.querySelector(`#colors_container`);
                tempBox.innerHTML = returnPickers(response.data);
                setTimeout(()=>{
                    colorButtonEvents(settings.action);
                    setColorsByInput();
                    setColors();
                }, 100);
            })
            .catch((e)=>console.log(e));

        apiProfile.Caption.getAllCaptions()
            .then((r)=>r.json())
            .then((response)=>{
                setAdminCaption(response.data);
                setTimeout(()=>{
                    triggerCaptionEvents(settings.action);
                }, 90);
            })
            .catch((e)=>console.log(e));

        apiProfile.Menu.getMenuItems()
            .then((r)=>r.json())
            .then((response)=>{
                let dom_menu_container = document.getElementById('menu_container_data');
                if(response.data.length){
                    dom_menu_container.innerHTML = tempMenuItems(response.data, true);
                }else{
                    dom_menu_container.innerHTML = `<h2 class="text-center">No hay links</h2>`;
                }
                setTimeout(()=>{
                    setMenuLinks();
                    triggerEvents(settings.action);
                    newForm(settings);
                }, 100);
            })
            .catch((e)=>console.log(e));

        apiProfile.Email.GetImagesCorreo()
            .then((r)=>r.json())
            .then((response)=>{
                let tempBox = document.querySelector("#imgae_correo");
                let temp;
                temp = DrawAdminImageCorreo(response.data);
                tempBox.innerHTML = temp;
            })
            .catch(e=>console.log(e));
    }
};

function setAdminCaption(data){
    let admin_caption_container = document.querySelector('#template_captions-body');
    let admin_caption_header = document.querySelector('#template_captions-header');
    let temp = ``;
    data.forEach((capti)=>{
        temp += `<div class="card intern_">
                    <div class="card-header custom_card-header">
                        <div class="one_line auto_margin">
                            <h4>${capti.title}</h4>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <form class="form-caption" id="form-caption_${capti.id}">
                            <input type="hidden" name="id" value="${capti.id}">
                            <textarea name="content" maxlength="75" class="form-control no-resize" cols="30" rows="3">${capti.content}</textarea>
                            <button class="btn btn-success-outline">Actualizar</button>
                        </form>
                    </div>
                 </div>`;
    });
    admin_caption_header.innerHTML = `<h2>Puede definir un breakpoint con "|" No se recomiendan mas de dos y un máximo de 75 caracteres</h2>`;
    admin_caption_container.innerHTML = temp;
}
function getAdminCaptions(url){
    fetch(`${url}&op=getAllCaptions`)
        .then(r=>r.json())
        .then((response)=>{
            setAdminCaption(response.data);
        })
        .catch((e)=>{
            console.log(e);
            FAlert('Error', 'error');
        })
}

function setMenuLinks(){
    let new_container = document.createElement('div');
    new_container.innerHTML = tempButtonNewMenuItem();
    document.getElementById('menu_container_options').append(new_container);
}
function returnPickers(data){
    let temp = ``;
    temp += `<button type="button" class="button_set_default-colors btn"><i class="fa fa-undo"></i> Regresar a los colores del tema</button>`;
    data.forEach((picker)=>{
        let {id, title, description, color, specified} = picker;
        temp += `<div class="card intern_">
                    <div class="card-header">
                        <h4>${title}</h4>
                    </div>
                    <div class="card-body">
                        <div class="all_card">
                            <div class="card_desc">
                                <p>${description}</p>
                            </div>
                            <div class="">
                                <input class="picker_button specified-color_${specified}" type="color" ${color.length > 4 ? 'value="'+color+'"' : ''}  id="${specified}_${id}">
                                <input class="picker_text" id="text_${specified}__${id}" type="text" value="${color}">
                            </div>
                            <div class="">
                                <!---<button class="btn_save btn btn-success-outline" id="save-${specified}_${id}">Guardar</button>
                                ---><button class="btn_delete btn btn-danger-outline" id="delete-${specified}_${id}">Eliminar</button>
                            </div>
                        </div>
                    </div>
                 </div>`;
    });
    temp += `<button style="width: 100%; font-size: 1.2em;" class="save_all_colors btn btn-success-outline">Aplicar Cambios</button>`;
    return temp;
}
function tempTabHeader(){
    const tempHeader = `<ul class="nav nav-tabs">
                            <li class="active"><a data-toggle="tab" href="#images_container">Imagenes</a></li>
                            <li><a data-toggle="tab" href="#colors_container">Colores</a></li>
                            <li><a data-toggle="tab" href="#imgae_correo">Diseño de correo</a></li>
                            <li><a data-toggle="tab" href="#menu_container">Menú</a></li>
                            <li><a data-toggle="tab" href="#template_captions">Texto</a></li>
                        </ul>`;
    return tempHeader;
}

function tempTabBody(){
    const tabBody = `<div class="tab-content">
                        <div id="images_container" class="tab-pane fade in active">
                          
                        </div>
                        <div id="colors_container" class="tab-pane fade">
                          
                        </div>
                        <div id="imgae_correo" class="tab-pane fade">
                        
                        </div>
                        <div id="menu_container" class="tab-pane fade">
                          <div id="menu_container_options"></div>
                          <div id="menu_container_data"></div>
                        </div>
                        <div id="template_captions" class="tab-pane fade">
                            <div id="template_captions-header">
                                
                            </div>
                            <div id="template_captions-body" class="boxes">
                                
                            </div>
                        </div>
                      </div>`;
    return tabBody;
}

function GetImagesCorreo(url) {
    let real = new FormData();
    fetch(`${url}&op=getImageCorreo`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            let tempBox = document.querySelector("#imgae_correo");
            let temp;
            temp = DrawAdminImageCorreo(response.data);
            tempBox.innerHTML = temp;
        })
        .catch(e=>console.log(e));
}

function DrawAdminImageCorreo(data) {
    let tempAdmiEmail = ``;
    if(data){
        tempAdmiEmail += ` <div style="text-align: right">
                <button id="add-admin-image" class="admin-image btn btn-primary-outline" type="button">
                                            <i class="fa fa-plus"></i> Agregar Enlace </button>
            </div>`;
        data.forEach((profile)=>{
            let {id, title, description,path,link,allow} = profile;
            let bttonDelet = ``;
            let AddImage = ``;
            let AddImageNew = ``;
            let btnDelet = ``;
            if (id <= 2 ){
                AddImage += `<button id="save-image_${id}" class="save-image-correo btn btn-success-outline" type="button">
                       <i class="fa fa-upload"></i> Cargar Image
                       </button>
                       <button ${!path ? "disabled" : ""} id="delete-image_${id}" class="delete-image-correo btn btn-danger-outline" type="button">
                       <i class="fa fa-trash"></i> Eliminar Imagen
                       </button>`;
                AddImageNew = `   <input type="file" class="data_input_Correo inputFileUpload" name="image" id="imageCorreo_${id}" accept="image/jpeg, image/png, image/jpg">
                                             <label for="imageCorreo_${id}">
                                                      <strong>
                                                            <i class="fa fa-cloud-upload" aria-hidden="true"></i>
                                                            &nbsp;&nbsp;Seleccionar archivo...
                                                      </strong>
                                                      <span class="text-image_Correo_${id}">Ningún archivo seleccionado</span>
                                             </label>
                                   `;
            }
            if (id > 2){
                bttonDelet += `<button style="margin-top: -3%" id="edit-link_${id}" class="edit_Link btn btn-primary-outline" type="button">
                                              <i class="fa fa-refresh"></i> Editar
                                  </button>`;
                btnDelet = `
                                        <button style="float: right; margin-top: -3%" ${!link ? "disabled" : ""} id="delete-link_${id}" class="delete-Link-correo btn btn-danger-outline" type="button">
                                              <i class="fa fa-trash"></i>
                                        </button>`;
            }
            tempAdmiEmail += `<div class="card">
                        <div class="card-header">
                                <div class="card_image"><img src="${path}" alt=""></div>
                                <div class="card_header-body">
                                         <div class=""> 
                                                <h4 style="color: black">${title}</h4> <br>
                                                  ${btnDelet}
                                                <p>${description}</p>
                                                <p>${path || ''}</p>
                                                <p>${link}</p>
                                         </div>
                                     <div class="card-body">
                                        <form action="">
                                            <div class="all_card">
                                                <div>
                                                ${AddImageNew}
                                                 ${bttonDelet}
                                                </div>
                                                <div>
                                                     <p>Activo</p>
                                                     <label class="switch">
                                                           <input id="check_${id}" class="allowCheck" name="allow" type="checkbox" ${allow > 0 || allow === null ? 'checked value="'+allow+'"' : ""}>
                                                           <span class="slider round"></span>
                                                     </label>
                                                </div>
                                              
                                             </div>
                                             <div>
                                                    ${AddImage}
                                             </div>
                                        </form>
                                    </div>
                                </div>
                        </div>
                        <div class="card-body">
                        </div>
                    </div>`;
        });
    }else{
        tempAdmiEmail += `<h3>No hay registros</h3>`;
    }
    return tempAdmiEmail;
}

function getMenuItems(url){
    fetch(`${url}&op=getMenuItems`)
        .then(r=>r.json())
        .then((response)=>{
            let dom_menu_container = document.getElementById('menu_container_data');
           if(response.data.length){
               dom_menu_container.innerHTML = tempMenuItems(response.data, true);
           }else{
               dom_menu_container.innerHTML = `<h2 class="text-center">No hay links</h2>`;
           }
        })
        .catch(e=>console.log(e));
}

const tempButtonNewMenuItem = ()=>{
    return `<div> 
                <h3>${lang.RECOMENDATION_LINK ? lang.RECOMENDATION_LINK : 'Si no es un link de genius, no olvides colocar https'}</h3>
                <button class="addNewMenu btn btn-primary-outline">Agregar nuevo</button>
            </div>`;
};

const orderMenuItemSelect = (data, id, ord)=>{
    let temp = `<select class="select_order-menu form-control" id="select_${id}-${ord}" value="2">`;
    let count = 1;
    data.forEach((order)=>{
        temp += `<option value="${order.order_menu}" ${order.order_menu === ord ? 'selected' : ''}>${count++}</option>`
    });
    temp += `</select>`;
    return temp;
};

const tempForm = (i, edit = false) => {
    const {id, title, icon, available, blank, url} = i ? i : {id: '', title: '', icon: '', available: '', blank: '', url: ' '};
    return `
        <form class="form_menu" id="${edit ? `edit-` : ''}form-menu_${id}">
                                    <div class="one_line space-lr ${!i ? 'block' : ''}">
                                        ${i ? `
                                            <input type="hidden" name="id" value="${id}">
                                            <input type="hidden" name="order_menu" value="${i.order_menu}">
                                        ` : ''}
                                        <div class="form-group">
                                            <label for="">url</label>
                                            <input name="url" class="form-control" type="text" value="${url}">
                                        </div>
                                        <div class="form-group">
                                            <label for="">titulo</label>
                                            <input name="title" class="form-control" type="text" value="${title}">
                                        </div>    
                                        <div class="form-group">
                                            <label for="">icono</label>
                                            <p>para añadir un icono debe agregar todo: "fa fa-icon" y no "fa-icon"</p>
                                            <input name="icon" class="form-control" type="text" value="${icon}">
                                        </div>
                                            <div class="form-group two_or flex_center">
                                                <label for="check col-sm-12">Abrir en nueva página</label>
                                                <label class="switch">
                                                  <input id="check-blank_${id}" class="allowCheck" name="blank" type="checkbox" ${blank > 0 || blank === null ? 'checked value="'+blank+'"' : ""}>
                                                  <span class="slider round"></span>
                                                </label>
                                            </div>
                                            <div class="form-group two_or flex_center">
                                                <label for="check col-sm-12">visible</label>
                                                <label class="switch">
                                                  <input id="check-available_${id}" class="allowCheck" name="available" type="checkbox" ${available > 0 || available === null ? 'checked value="'+available+'"' : ""}>
                                                  <span class="slider round"></span>
                                                </label>
                                            </div>
                            </div>
                                    <button id="save-menu_${id}" class="save_menu_link w100 mr-all btn btn-success-outline">${i ? 'Guardar cambios' : 'Guardar'}</button>
                                </form>
                                ${edit ? 
                                `<form class="delete_menu_form" id="form-menu-delete_${id}">
                                    <input type="hidden" name="id" value="${id}">
                                    <button id="confirm-delete_${id}" class="w100 mr-all btn btn-danger-outline no_visible">Confirmar Eliminación</button>
                                    <button id="delete-confirmation_${id}" type="button" class="delete-confirmation w100 mr-all btn btn-danger-outline">Eliminar</button>
                                </form>` : '' }
    `;
}

const newForm = (obj)=>{
    const triggerNew = document.querySelectorAll('.addNewMenu');
    triggerNew.forEach((element)=>{
        element.addEventListener('click', ()=>{
            obj.widget.setOptions({idDialog:"form-menu_new",form:"form-menu_new-element"});
            obj.widget.alertDialog(`${tempForm(null, false)}`, 'Nuevo link');
            setTimeout(()=>{    
                let form = document.querySelector('#form-menu_new-element');
                form.addEventListener('submit', (e)=>{
                    e.preventDefault();
                    updateMenuLink(obj.action, form);
                    obj.widget.closeDialog();
                })
            }, 3000);
        })
    })
}

const tempMenuItems = (data, edit = false)=>{
    let temp = ``;
    data.forEach((menuItem)=>{
        const {id, title, icon, available, blank, url} = menuItem;
        temp += `<div class="card intern_">
                    <div class="card-header custom_card-header">
                        <div class="one_line">
                            <i class="${icon}"></i><h4>${title}</h4> <span class="text-gray">${url}</span>
                        </div>
                        <div class="one_line no_wrap"> 
                            <div class="">
                                <button id="trigger-menu_${id}" class="edit_menu_item btn btn-success-outline">Editar</button>
                            </div>
                            ${data.length > 1 ? 
                                orderMenuItemSelect(data, id, menuItem.order_menu)
                             : ''}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="all_card no_visible" id="menu-item-edit_${id}">
                            <div class="w100">
                                ${tempForm(menuItem , edit)}
                            </div>
                        </div>
                    </div>
                 </div>`;
    });
    return temp;
};

function triggerEvents(url){
    const open_edit_form = document.querySelectorAll('.edit_menu_item');
    const delete_menu_link = document.querySelectorAll('.delete-confirmation');
    const not_a_real_api = url;
    const update_menu_link = document.querySelectorAll('.form_menu');
    const menu_item_order = document.querySelectorAll('.select_order-menu');
    open_edit_form.forEach((element)=>{
        element.addEventListener('click', ()=>{toggleVisible(element)});
    });
    delete_menu_link.forEach((element)=>{
        element.addEventListener('click', ()=>{
            const element_id = element.id.split('_')[1];
            let form_delete = document.querySelector(`#form-menu-delete_${element_id}`);
            let trigger_delete = document.querySelector(`#confirm-delete_${element_id}`);
            trigger_delete.classList.remove('no_visible');
            element.classList.add('no_visible');

            form_delete.addEventListener('submit', (e)=>{
                e.preventDefault();
                removeMenuLink(url, form_delete);
            });

            let x = setTimeout(()=>{
                trigger_delete.classList.add('no_visible');
                element.classList.remove('no_visible');
                form_delete.removeEventListener('submit', (e)=>{
                    e.preventDefault();
                    removeMenuLink(url, form_delete);
                })
            }, 2000);
        })
    });
    menu_item_order.forEach((element)=>{
        element.addEventListener('change', ()=>{
           let id = element.id.split('_')[1];
           setOrder(url, id.toString().split('-')[0], id.toString().split('-')[1], element.value);
        });
    });
    update_menu_link.forEach((element)=>{
        element.addEventListener('submit', (e)=>{
            e.preventDefault();
            updateMenuLink(not_a_real_api, element)
        })
    });
}

function updateCaptionAdmin(url, form){
    let real = new FormData(form);
    fetch(`${url}&op=updateCaptionAdmin`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert('actualizado', 'success');
        })
        .catch(e=>{
            FAlert('error', 'error');
        });
}

function triggerCaptionEvents(url){
    const form_caption = document.querySelectorAll('.form-caption');
    form_caption.forEach((element)=>{
        element.addEventListener('submit', (e)=>{
            e.preventDefault();
            updateCaptionAdmin(url, element);
        });
    })
}

function setOrder(url, id, original, order){
    if(id && order){
        let real = new FormData();
        real.append('id', id);
        real.append('order_menu', order);
        real.append('original_pos', original);
        fetch(`${url}&op=moveMenuItemOrder`, {
            method: 'POST',
            body: real
        })
            .then(r=>r.json())
            .then((response)=>{
                /*alert success*/
                FAlert('Acción realizada con éxito', 'success');
                setUpMenuLink(url);
            })
            .catch(e=>FAlert('Error', 'error'));
    }
}

const toggleVisible = (element)=>{
    let element_id = element.id.split('_')[1];
    let new_element = document.querySelector(`#menu-item-edit_${element_id}`);
    new_element.classList.toggle('no_visible');
}

function removeListeners(url){
    const open_edit_form = document.querySelectorAll('.edit_menu_item');
    const update_menu_link = document.querySelectorAll('.form_menu');
    open_edit_form.forEach((element)=>{
        element.removeEventListener('click', ()=>toggleVisible(element));
    });
    if(url){
        update_menu_link.forEach((element)=>{
            element.removeEventListener('submit', (e)=>{
                e.preventDefault();
                updateMenuLink(url, element);
            })
        });
    }
}

function removeMenuLink(url, form){
    let real = new FormData(form);
    fetch(`${url}&op=deleteMenuItem`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert(`<p>Se ha eliminado con éxito</p>`, 'success');
            setUpMenuLink(url);
        })
        .catch(e=>FAlert('Ocurrio un error', 'error'));
}

function setUpMenuLink(url){
    removeListeners(url);
    getMenuItems(url);
    setTimeout(()=>{
        triggerEvents(url);
    }, 300);
}

function updateMenuLink(url, form){
    let real = new FormData(form);
    fetch(`${url}&op=updateMenuItem`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert(`<p>Se ha actualizado con éxito</p>`, 'success');
            setUpMenuLink(url);
        })
        .catch(e=>FAlert('Ocurrio un error', 'error', 'alert'));
}

function getByType(url, str, container){
    let real = new FormData();
    real.append('type', str);
    fetch(`${url}&op=getByType`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            let tempBox = document.querySelector(`${container}`);
            let temp;
            if(str === 'image'){
                temp = tempCardsImages(response.data);
            }
            else{
                temp = returnPickers(response.data);
            }
            tempBox.innerHTML = temp;

        })
        .catch(e=>console.log({error: e}));
}

function tempCardsImages(data){
    let temp = ``;
    temp += `<button class="button_set_default-image btn"><i class="fa fa-undo"></i>Regresar a las imagenes del tema</button>`;
    if(data){
        data.forEach((profile)=>{
            let {id, path, title, description} = profile;
            temp += `<div class="card">
                        <div class="card-header">
                            <div class="card_image"><img src="${path}" alt=""></div>
                            <div class="card_header-body">
                                <div class=""> 
                                    <h4>${title}</h4>
                                    <p>${description}</p>
                                    <p>${path || ''}</p>
                                </div>
                                <div class="card-body">
                            <form action="">
                                <div class="all_card">
                                    <div class="">
                                        <input type="file" class="data_input inputFileUpload" name="image" id="image_${id}" accept="image/jpeg, image/png, image/jpg">
                                        <label for="image_${id}">
                                            <strong>
                                                <i class="fa fa-cloud-upload" aria-hidden="true"></i>
                                                &nbsp;&nbsp;Seleccionar archivo...
                                            </strong>
                                            <span class="text-image_${id}">Ningún archivo seleccionado</span>
                                        </label>
                                    </div>
                                    <div class="">
                                        <button id="save-image_${id}" class="save-image-button btn btn-success-outline" type="button">
                                            <i class="fa fa-upload"></i> Cargar
                                        </button>
                                        <button ${!path ? "disabled" : ""} id="delete-image_${id}" class="delete-image-button btn btn-danger-outline" type="button">
                                            <i class="fa fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                            </div>
                        </div>
                        <div class="card-body">
                            
                        </div>
                    </div>`;
        });
    }
    else{
        temp += `<h3>No hay registros</h3>`;
    }
    return temp;
}

function colorButtonEvents(url){
    let button_save_color = document.querySelectorAll('.btn_save');
    let button_delete_color = document.querySelectorAll('.btn_delete');
    let button_save_all = document.querySelector('.save_all_colors');
    let set_color = document.querySelector('.button_set_default-colors');

    let handletime;

    button_delete_color.forEach((element)=>{
        element.addEventListener('click', ()=>{
            let color_picker = document.querySelector(`#${element.id.split('delete-')[1]}`);
            if(color_picker.value != '#000000'){
                let button = `<div>
                                <button type="button"
                                 class='btn btn-warning-outline element_modal-right' 
                                 onclick="deleteColor(`+color_picker.id.split('_')[1]+`, '`+url+`')">
                                 Confirmar
                                  </button></div>`;
                confirmationModal(button, '¿Seguro que desea eliminar?');
                /*deleteColor(color_picker.id.split('_')[1], url);*/
            }
            else{
                FAlert('No hay datos que eliminar', 'warning', 'fa fa-warning');
            }
        });
    });
    button_save_color.forEach((element)=>{
        element.addEventListener('click', ()=>{
            let color_picker = document.querySelector(`#${element.id.split('save-')[1]}`);
            if(color_picker.value !== '#000000'){
                sendColor({id: element.id.split('_')[1], color: color_picker.value, url: url});
            }
            else{
                FAlert('Selecciona un color', 'warning', 'fa fa-warning');
            }
        })
    });
    set_color.addEventListener('click', ()=>{
        let temp = `<div><span>Esta accion no puede ser revertida.
                                Todos los colores se remplazaran por
                                los colores del tema.</span> <br></div> <button style="float: right" type="button" class="delete_all_colors btn btn-success-outline">Confirmar</button></div>`;
        dw.alertDialog(temp, `Confirmación`, false);
        setTimeout(()=>{
            let button_delete_all = document.querySelector('.delete_all_colors');
            button_delete_all.addEventListener('click', ()=>{
                setDefault({type: 'color', url: url});
            });
        }, 300);
    });
    button_save_all.addEventListener('click', ()=>{
        let temp = `<button type="button" class="save_all_color btn btn-success-outline">Aplicar cambios</button>`;
        confirmationModal(`${temp}`, `Seguro que desea aplicar los cambios?`);
        let timefunc = () => handletime = setTimeout(()=>saveAllColors(url), 500);
        timefunc();
        /*clearTimeout(handletime);*/
    });
}

function manageButtons(url){
    let buttons = document.querySelectorAll('.save-image-button');
    let buttons_delete = document.querySelectorAll('.delete-image-button');
    let set_image = document.querySelector('.button_set_default-image');
    set_image.addEventListener('click', ()=>{
        let temp = `<div><span>Esta accion no puede ser revertida.
                                Todas las imagenes se remplazaran por
                                las imagenes del tema.</span> <br></div> <button style="float: right" type="button" class="delete_all_colors btn btn-success-outline">Confirmar</button></div>`;
        /*confirmationModal(`${temp}`, 'Confirmación');*/
        dw.alertDialog(temp, `Confirmación`, false);
        setTimeout(()=>{
            let button_delete_all = document.querySelector('.delete_all_colors');
            button_delete_all.addEventListener('click', ()=>{
                setDefault({type: 'image', url: url});
            });
        }, 300);
    });
    buttons.forEach((element)=>{
        element.addEventListener('click', ()=>{
            let id_button = element.id.split('_')[1];
            let input_img = document.getElementById(`image_${id_button}`);
            if(input_img.value){
                /*let base = toBase64(input_img);*/
                previewImageBase64(input_img, sendImage, {id: id_button, url: url});
                /*sendImage({id: id_button, src: base, url: url});*/
            }
            else{
                FAlert('Selecciona una imagen', 'warning', 'fa fa-warning');
            }
        })
    });
    buttons_delete.forEach((element)=>{
        element.addEventListener('click', ()=>{
            let button = `<div class="element_modal-right">
                            <button type="button" 
                            class='btn btn-warning-outline' 
                            onclick="deleteImage(`+element.id.split('_')[1]+`, '`+url+`')">
                            Confirmar</button></div>`;
            confirmationModal(button, '¿Seguro que desea eliminar?');
            /*deleteImage(element.id.split('_')[1], url);*/
        });
    });
}

function sendImage(image, data){
    let {id, url} = data;
    let real = new FormData();
    real.append('id', id);
    real.append('image', image);
    fetch(`${url}&op=saveImage`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert('Guardado con éxito', 'success', 'fa fa-check', true);
        })
        .catch(e=>FAlert('Error al guardar', 'error', 'fa fa-warning'));
}

function deleteImage(id, url){
    let real = new FormData();
    real.append('id', id);
    fetch(`${url}&op=deleteImage`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert('Imagen eliminada con éxito', 'success', 'fa fa-check', true);
            dw.closeDialog();
        })
        .catch(e=>FAlert('Ocurrio un error', 'error', 'fa fa-warning'));
}

function deleteColor(id, url){
    let real = new FormData();
    real.append('id', id);
    fetch(`${url}&op=deleteColor`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            dw.closeDialog();
            FAlert('Color eliminado', 'success', 'fa fa-check');
            FAlert('Puedes ver los cambios actualizando la página', '', '', false, 2500);
        })
        .catch(e=>FAlert('Ocurrio un error', 'error', 'fa fa-warning', true));
}

function confirmationModal(template, message){
    dw.alertDialog(template, `${message}`, false);
}

function setImageButtons(){
    let inputs = document.querySelectorAll('.data_input');
    inputs.forEach((element)=>{
        let id = element.id.split('_')[1];
        element.addEventListener('change', ()=>{
            let textContainer = document.querySelector(`.text-image_${id}`);
            textContainer.innerHTML = element.value;
        });
    })
}

function saveAllColors(url){
    let save_button = document.querySelector('.save_all_color');
    let arr_color = [];
    let arr_just_colors = {};
    save_button.addEventListener('click', ()=>{
        const picker = document.querySelectorAll('.picker_button');
        picker.forEach((element)=>{
            if(element.value != '#000000'){
                let id = element.id.split('_');
                arr_just_colors[`${id[0]}`] = {
                  id: id[1],
                  color: element.value
                };
            }
        });
        arr_color["colors"] = arr_just_colors;
        arr_color["url_direction"] = {url: url};
        sendColor(arr_color);
        location.reload();
    });
}

function sendColor(data){
    let {id, colors, url_direction: {url}, reload = false} = data;
    let real = new FormData();
    real.append('id', id);
    real.append('color', JSON.stringify(colors));
    fetch(`${url}&op=saveColor`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            /*FAlert('Guardado con éxito!', 'success', 'fa fa-check', false);
            FAlert('Puedes ver los cambios actualizando la página', '', '', false, 2400);*/
        })
        .catch((e)=>FAlert('Ocurrio un error', 'error', ''));
}

function is_hexadecimal(str){
    let regexp = /^[0-9a-fA-F]+$/;

    if (regexp.test(str))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function find_unique_characters(str, char){
    return [...new Set(str.split(char))].join(char);
}

function setColorsByInput(){
    let text_color = document.querySelectorAll('.picker_text');
    let time;
    text_color.forEach((element)=>{
        let real_picker = document.querySelector(`#${element.id.split('_')[1]}_${element.id.split('__')[1]}`);
        element.addEventListener('keyup', ()=>{
            element.value = element.value.replace(/\s/g, '');
            let picker_assigned_value = element.value.split(' ')[0];
            if(!picker_assigned_value.includes('#')){
                element.value = `#${element.value}`;
            }
            else{
                element.value = find_unique_characters(element.value, '#');
            }
            clearTimeout(time);
            time = setTimeout(()=>{
                if(element.value.length > 6 && element.value.length <= 7){
                    /*real_picker.value = element.value;*/
                    is_hexadecimal(element.value.split('#')[1]) ?
                    setStyle({element_specified: real_picker.id.split('_')[0],
                        value: element.value, id: real_picker.id})
                    : FAlert('El color debe ser un hexadecimal válido', 'error');
                }
                else{
                    FAlert('El color debe tener 6 caracteres', 'warning');
                }
            }, 600);
        });
    });
}

function setStyle(element){
    let {element_specified, value, id} = element;
    let input_to_change = document.querySelector(`#${id}`);
    input_to_change.value = value;
    if(element_specified === "color-menu"){
        let meroHeader = document.querySelector('body');
        let styleTag = document.createElement('style');
        let color_deg = document.querySelector('.specified-color_color-deg');
        let background = color_deg ? `.main-sidebar {background: linear-gradient(${value}, ${color_deg.value}) !important}` : '';
        let style = `.logo, .main-sidebar, .fa-bars,  .navbar-static-top{background: ${value} !important;} ${background}`;
        styleTag.innerHTML = style;
        meroHeader.append(styleTag);
    } else if(element_specified === "color-menu-text"){
        let meroHeader = document.querySelector('body');
        let styleTag = document.createElement('style');
        styleTag.innerHTML = `.sidebar-menu>.treeview>a, .user>a, .sidebar-menu > .header {color: ${value} !important;}`;
        meroHeader.append(styleTag);
    }
    else if(element_specified === "color-menu-active"){
        let meroHeader = document.querySelector('body');
        let styleTag = document.createElement('style');
        styleTag.innerHTML = `
                                .menu-open > a, .sidebar-menu > .header{
                                    background: ${value} !important
                                    }
                                .menu-open > a{
                                    background: ${value} !important;
                                }
                                .treeview>a:focus{
                                    background: ${value} !important;
                                }
                                .treeview>a:hover{
                                    background: ${value} !important;
                                }
                                .treeview-menu > li > a:hover{
                                    background: ${value} !important;
                                    }`;
        meroHeader.append(styleTag);
        /*item_menu.style.background = element.value;
        item_acti.forEach((domElement)=>{
            domElement.styles.background = element.value;
        });*/
    }
    else if(element_specified === "color-title"){
        let meroHeader = document.querySelector('body');
        let styleTag = document.createElement('style');
        styleTag.innerHTML = `.title-page {background: ${value} !important;}`;
        meroHeader.append(styleTag);
    }
    else if(element_specified === "color-title-text") {
        let meroHeader = document.querySelector('body');
        let styleTag = document.createElement('style');
        styleTag.innerHTML = `.title-page {color: ${value} !important;}`;
        meroHeader.append(styleTag);
    }
    else if(element_specified === 'color-menu-elements'){
        let meroHeader = document.querySelector('body');
        let styleTag = document.createElement('style');
        styleTag.innerHTML = `.treeview > ul {background: ${value} !important;}`;
        meroHeader.append(styleTag);
    }
    else if(element_specified === 'color-deg'){
        let meroHeader = document.querySelector('body');
        let styleTag = document.createElement('style');
        let element_color = document.querySelector('.specified-color_color-menu');
        let color = element_color.value;
        styleTag.innerHTML = `.main-sidebar{background: linear-gradient(${color}, ${value}) !important;}`;
        meroHeader.append(styleTag);
    }
}

function setDefault(data){
    let {type, url} = data;
    let real = new FormData();
    real.append('is', type);
    fetch(`${url}&op=setDefault`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            /*FAlert('Guardado con éxito!', 'success', 'fa fa-check', false);
            FAlert('Puedes ver los cambios actualizando la página', '', '', false, 2400);*/
            location.reload();
        })
        .catch((e)=>FAlert('Ocurrio un error', 'error', ''));
}

function setColors(){
    let picker_btn = document.querySelectorAll('.picker_button');
    /*let item_menu = document.querySelector('.sidebar-menu>li.header');
    let item_acti = document.querySelectorAll('.menu-open>a');*/
    const hex2rgba = (hex, alpha = 1) => {
        const [r, g, b] = hex.match(/\w\w/g).map(x => parseInt(x, 16));
        return `rgba(${r},${g},${b},${alpha})`;
    };
    picker_btn.forEach((element)=>{
        let element_id = element.id;
        let element_specified = element_id.split('_')[0];

        element.addEventListener('change', ()=>{
            let id_element = `text_${element_specified}__${element_id.split('_')[1]}`;
            setStyle({element_specified: element_specified, value: element.value, id: id_element});
        });
    });
}

function ButtonsImageCorreo(url) {
    let button_save_ImageCorreo = document.querySelectorAll('.save-image-correo');
    let button_delet_ImageCorreo = document.querySelectorAll('.delete-image-correo');
    let button_add_Admin_Image = document.querySelectorAll('.admin-image');
    let buttonActive = document.querySelectorAll('.allowCheck');
    let deletLink = document.querySelectorAll('.delete-Link-correo');
    let EditLink = document.querySelectorAll('.edit_Link');

    buttonActive.forEach((button)=>{
        button.addEventListener('change', ()=>{
            button.checked ? button.value = 1 : button.value = 0;
            let id = button.id.split('_')[1];
            let formData = new FormData();
            formData.append('allow', button.value);
            formData.append('idPage', id);
            fetch(`${urlGlobalSettings}&op=saveAllow`, {
                method: 'POST',
                body: formData
            })
                .then(r=>r.json())
                .then(r=>'')
                .catch(e=>dw.alertDialog('', 'Error', true));
        });
    });

    button_add_Admin_Image.forEach((element)=>{
        element.addEventListener('click', ()=>{
            let AddAdmin = `<div>       
                                       
                                        <div>
                                           <h3>${lang.RECOMENDATION_MESSAJE}</h3>
                                                <ul>
                                                    <h4>1.${lang.DIMENSION_WITDT}</h4>
                                                    <h4>2.${lang.DIMENSION_HEIGTH}</h4>
                                                </ul> 
                                        </div>
                                        <div class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;">
                                            <p>title</p>
                                            <input type="text" class="form-control" id="descriptionTitle" />
                                        </div>                                    
                                        <div class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;">
                                            <p>Description</p>
                                            <input type="text" class="form-control" id="Description" />
                                        </div>
                                        <div class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;">
                                            <p>Link Pagina</p>
                                            <input type="text" class="form-control" id="Link" />
                                        </div> 
                                        <div  class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;">
                                            <p>Seleccione una Imagen</p>
                                            <input class="form-control" type="file" name="field_carga" id="imageCorreo" accept="image/jpeg, image/png, image/jpg" />
                                            </div>
                                        <div class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;text-align: right;">
                                            <button id="saveNewReegistr" class="saveNewReegistr btn btn-success-outline" type="button" 
                                            onclick="saveRegistro()">
                                            <i class="fa fa-upload"></i> Agregar
                                        </button>
                                        </div>   
                                
                            </div>`;
            dw.alertDialog(AddAdmin);
        })
    });

    button_save_ImageCorreo.forEach((element)=>{
        element.addEventListener('click', ()=> {
            let id_button = element.id.split('_')[1];
            let input_img = document.getElementById(`imageCorreo_${id_button}`);
            if(input_img.value){
                /*let base = toBase64(input_img);*/
                previewImageBase64(input_img, sendImageCorreo, {id: id_button, url: url});
                /*sendImage({id: id_button, src: base, url: url});*/
            }else{
                FAlert('Selecciona una imagen', 'warning', 'fa fa-warning');
            }
        })
    });

    EditLink.forEach((element)=>{
        element.addEventListener('click',()=>{
            let id = element.id.split('_')[1];
            let data;
            let real = new FormData();
            real.append('id', id);
            fetch(`${url}&op=getTitle`, {
                method: 'POST',
                body: real
            })
                .then(r=>r.json())
                .then((response)=>{
                    data =(response.data);
                    let AddAdmin = `<div>       
                                            <h3>${lang.RECOMENDATION_MESSAJE}</h3>
                                                <ul>
                                                    <h4>1.${lang.DIMENSION_WITDT}</h4>
                                                    <h4>2.${lang.DIMENSION_HEIGTH}</h4>
                                                </ul> 
                                            <div class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;">
                                                <p>title</p>
                                                <input type="text" class="form-control" id="descriptionTitlee" value="${data.title}" /> 
                                            </div>                                    
                                            <div class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;">
                                                <p>Description</p>
                                                <input type="text" class="form-control" id="Descriptionn" value="${data.description}" />
                                            </div>
                                            <div class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;">
                                                <p>Link Pagina</p>
                                                <input type="text" class="form-control" id="Linkk" value="${data.link}" />
                                            </div>
                                            <div class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;">
                                                <p>Seleccione una Imagen</p>
                                                <input class="form-control" type="file" name="field_carga" id="imageLink" value="${data.path}" accept="image/jpeg, image/png, image/jpg">
                                            </div>
                                            <div class="col-xs-12" style="margin-bottom: 10px; padding: 20px 0 20px 0;text-align: right;">
                                                <button id="saveNewReegistr" class="saveNewReegistr btn btn-success-outline" type="button" 
                                                onclick="Actualizar_Datos(`+element.id.split('_')[1]+`)">
                                                <i class="fa fa-refresh"></i> Actualizar
                                                </button>
                                             </div>   
                            </div>`;
                    dw.alertDialog(AddAdmin, "Actualizar Informacion");
                })
                .catch(e=>console.log(e));

        })
    })

    deletLink.forEach((element) =>{
        element.addEventListener('click',()=>{
            let button = `<div class="element_modal-right">
                            <button type="button" 
                            class='btn btn-warning-outline' 
                            onclick="deletLink(`+element.id.split('_')[1]+`, '`+url+`')">
                            Confirmar</button></div>`;
            confirmationModal(button, '¿Seguro que desea eliminar?');
        })
    });

    button_delet_ImageCorreo.forEach((element) =>{
        element.addEventListener('click', ()=>{
            let button = `<div class="element_modal-right">
                            <button type="button" 
                            class='btn btn-warning-outline' 
                            onclick="deleteImageCorreo(`+element.id.split('_')[1]+`, '`+url+`')">
                            Confirmar</button></div>`;
            confirmationModal(button, '¿Seguro que desea eliminar?');
        });
    });
}

function Actualizar_Datos(id) {
    let inputTitlee = document.getElementById(`descriptionTitlee`);
    let inputDescc = document.getElementById(`Descriptionn`);
    let inputLinkk = document.getElementById(`Linkk`);
    let inpuImage = document.getElementById(`imageLink`);
    if (inpuImage.value =="" && inputTitlee.value && inputDescc.value && inputLinkk.value){
        sendInfoNewdata({title: inputTitlee.value, desc: inputDescc.value, link: inputLinkk.value, id: id})
    }
    else if (inputTitlee.value && inputDescc.value && inputLinkk.value && inpuImage.value !=""){
        previewImageBase64(inpuImage, sendInfoNew,{title: inputTitlee.value, desc: inputDescc.value, link: inputLinkk.value, id: id});
    }else{
        FAlert('Todos los campos deben estar llenos', 'warning', 'fa fa-warning');
    }
}

function saveRegistro() {
    let input_img = document.getElementById(`imageCorreo`);
    let inputTitle = document.getElementById(`descriptionTitle`);
    let inputDesc = document.getElementById(`Description`);
    let inputLink = document.getElementById('Link');
    if(input_img.value && inputTitle.value && inputDesc.value && inputLink.value){
        /*let base = toBase64(input_img);*/
        previewImageBase64(input_img, sendImagepat, {title: inputTitle.value, desc: inputDesc.value, link: inputLink.value});
        /*sendImage({id: id_button, src: base, url: url});*/
    }else{
        FAlert('Todos los campos deben estar llenos', 'warning', 'fa fa-warning');
    }

}

function sendInfoNewdata(data) {
    let {title, desc, link,id} = data;
    let real = new FormData();
    real.append('title', title);
    real.append('description', desc);
    real.append('link', link);
    real.append('id', id);
    fetch(`${urlGlobalSettings}&op=editData`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert('Datos guardados correctamente', 'success', 'fa fa-check', true);
        })
        .catch(e=>FAlert('No se pudo guardar los datos', 'error', 'fa fa-warning'));
}

function sendInfoNew(image,data) {
    let {title, desc, link,id} = data;
    let real = new FormData();
    real.append('title', title);
    real.append('description', desc);
    real.append('link', link);
    real.append('id', id);
    real.append('image', image);
    fetch(`${urlGlobalSettings}&op=EditRegister`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert('Datos guardados correctamente', 'success', 'fa fa-check', true);
        })
        .catch(e=>FAlert('No se pudo guardar los datos', 'error', 'fa fa-warning'));

}

function sendImagepat(image, data ) {
    let {title, desc, link} = data;
    let real = new FormData();
    real.append('title', title);
    real.append('description', desc);
    real.append('link', link);
    real.append('image', image);
    fetch(`${urlGlobalSettings}&op=saveNewRegister`, {
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert('Datos guardados correctamente', 'success', 'fa fa-check', true);
        })
        .catch(e=>FAlert('No se pudo guardar los datos', 'error', 'fa fa-warning'));

}

function deletLink(id,url) {
    let real = new FormData();
    real.append('id', id);
    fetch(`${urlGlobalSettings}&op=deleteLinkCorreo`,{
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert('Link eliminado con éxito', 'success', 'fa fa-check', true);
            dw.closeDialog();
        })
        .catch(e=>FAlert('Ocurrio un error', 'error', 'fa fa-warning'));
}

function deleteImageCorreo(id, url) {
    let real = new FormData();
    real.append('id', id);
    fetch(`${url}&op=deleteImageCorreo`,{
        method: 'POST',
        body: real
    })
        .then(r=>r.json())
        .then((response)=>{
            FAlert('Imagen eliminada con éxito', 'success', 'fa fa-check', true);
            dw.closeDialog();
        })
        .catch(e=>FAlert('Ocurrio un error', 'error', 'fa fa-warning'));

}

function setNameImageButton() {
    let input = document.querySelectorAll('.data_input_Correo');
    input.forEach((element) =>{
        let id = element.id.split('_')[1];
        element.addEventListener('change', ()=>{
            let textContainerImage = document.querySelector(`.text-image_Correo_${id}`)
            textContainerImage.innerHTML = element.value;
        });
    })
}
