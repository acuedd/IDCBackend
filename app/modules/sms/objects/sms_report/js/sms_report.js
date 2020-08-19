let app_container = document.querySelector(`#sms_report_app`);

app_container.innerHTML = tempAppBody();

setTable();

/**
 primero los fetch, luego temps y por ultimo eventos
 */

/**inicio fetch*/

function fetchGetAllMessages(){
    return fetch(`${url}&op=getAllMessages`);
}

function setTable(){
    fetchGetAllMessages()
        .then((r)=>r.json())
        .then((response)=>{
            let table_container = document.querySelector(`#report_table`);
            let temp = tempTable(response.data);
            table_container.innerHTML = temp;
            setTimeout(()=>{
                $(`#report_table`).DataTable({
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
                /*¯\_(?)_/¯*/
            }, 100);
        })
        .catch((e)=>{
            FAlert('Ocurrio un error', 'error');
        });
}

/** inicio templates **/

function tempAppBody() {
    return `<div> 
                <div class=""> 
                    
                </div>
                <div class=""> 
                    <table id="report_table"></table>
                </div>
            </div>`;
}

function tempTableHeader(){
    return `<thead><tr>
                <th>Fecha</th> 
                <th>Destino</th> 
                <th>Mensaje</th> 
                <th>Proveedor</th> 
                <th>Estado</th>
                <th>Referencia</th>
            </tr></thead>`;
}

function tempTableBody(data){
    if(!data) return false;
    let {id, fecha, hora, destino, mensaje, usuario, proveedor, status, ref} = data;
    return `<tr> 
                <td>${fecha} ${hora}</td> 
                <td>${destino}</td> 
                <td>${mensaje}</td> 
                <td>${proveedor}</td> 
                <td>${status}</td>
                <td>${ref}</td>
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
