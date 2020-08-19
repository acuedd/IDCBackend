<!--
* User: Alexander Flores
* Date: 5/01/2018
* Time: 4:41 PM
-->
<style type="text/css">
	.md-textbox-error:hover + div:after{
		content: attr(dt-error);
		position: absolute;
		margin-left: 8px;
		margin-top: -82px;
		width: auto;
		font-size: 18px;
		background-color: #D43F3A;
		color: white;
		border: 1px solid #D43F3A;
		border-radius: 3px;
		padding: 5px;
	}
	.md-textbox-error:hover + div:before{
		content: "";
		position: absolute;
		width: 0;
		height: 0;
		border-left: 10px solid transparent;
		border-top: 11px solid #D43F3A;
		border-right: 10px solid transparent;
		margin-left: 35px;
		margin-top: -45px;
	}
	.md-btn{
		margin: 15px;
		width: 150px;
		height: 50px;
		border-width: 1px;
		border-style: solid;
		border-radius: 3px;
		float: left;
		line-height: 50px;
		font-size: 16px;
		font-weight: bold;
		text-align: center;
		cursor: pointer;
		/* For Safari 3.1 to 6.0 */
		-webkit-transition-property: all;
		-webkit-transition-duration: 0.2s;
		-webkit-transition-timing-function: linear;
		/* Standard syntax */
		transition-property: all;
		transition-duration: 0.2s;
		transition-timing-function: linear;
	}
	.md-btn-default{
		background-color: white;
		border-color: #4355B1;
		color: #4355B1;
	}
	.md-btn-default:hover > .app-update:before{
		content: "";
		position: absolute;
		width: 30px;
		height: 30px;
		border-radius: 100%;
		margin-left: 58px;
		margin-top: -12px;
		background-color: #056874;
	}
	.md-btn-default:hover > .app-update:after{
		content: "\270E";
		position: absolute;
		margin-top: -24px;
		margin-left: 63px;
		color: #fff;
		font-size: 18px;
	}
	.md-btn-default:hover > .app-delete:before{
		content: "";
		position: absolute;
		width: 30px;
		height: 30px;
		border-radius: 100%;
		margin-left: 58px;
		margin-top: -22px;
		background-color: #DA4F49;
	}
	.md-btn-default:hover > .app-delete:after{
		content: "x";
		position: absolute;
		margin-top: -35px;
		margin-left: 68px;
		color: #fff;
		font-size: 22px;
		font-weight: bold;
	}
	.app-main{
		width: 100%;
		height: 100%;
	}
	.md-btn-circle{
		float: left;
		width: auto;
		height: auto;
		border: medium none;
		border-radius: 100%;
		cursor: pointer;
		color: #fff;
		background-color: #F0AD4E;
		border: 1px solid #F0AD4E;
	}
	.md-btn-circle-add{
		float: right;
	}
	.md-btn-circle-add:after{
		content: "+";
		position: absolute;
		width: 0px;
		height: 0px;
		font-size: 50px;
		font-weight: bold;
		margin-top: -19px;
		margin-left: 4px;
		color: #F6F6F6;
		/* For Safari 3.1 to 6.0 */
		-webkit-transition-property: all;
		-webkit-transition-duration: 0.2s;
		-webkit-transition-timing-function: linear;
		/* Standard syntax */
		transition-property: all;
		transition-duration: 0.2s;
		transition-timing-function: linear;
	}
	.md-btn-circle-add:hover:after{
		font-size: 55px;
		margin-top: -23px;
		margin-left: 2px;
	}
	.loading-app{
		display: none;
		width: 70%;
		margin-left: auto;
		margin-right: auto;
	}
	.title-tab{
		height: 40px;
		line-height: 40px;
	}
	.title-os{
		float: left;
	}
    .tab-pane > div.loading{
        width: 100%;
        padding: 20px;
        margin: 10px 0;
        background-color: #c5d7d8;
    }
	.opt-os{
		float: right;
	}
	.opt-os > .os-config{
		background-color: white;
		color: #4355B1;
		cursor: pointer;
	}
	.opt-os > .os-update{
		position: absolute;
		margin-left: 0px;
		margin-top: 0px;
		visibility: hidden;
		padding-left: 5px;
		padding-right: 5px;
		color: #056874;
		cursor: pointer;
		opacity: 0;
		/* For Safari 3.1 to 6.0 */
		-webkit-transition-property: all;
		-webkit-transition-duration: 0.5s;
		-webkit-transition-timing-function: linear;
		/* Standard syntax */
		transition-property: all;
		transition-duration: 0.5s;
		transition-timing-function: linear;
	}
	.opt-os > .os-delete{
		position: absolute;
		margin-left: 0px;
		margin-top: 0px;
		visibility: hidden;
		padding-left: 5px;
		padding-right: 5px;
		color: #DA4F49;
		cursor: pointer;
		opacity: 0;
		/* For Safari 3.1 to 6.0 */
		-webkit-transition-property: all;
		-webkit-transition-duration: 0.5s;
		-webkit-transition-timing-function: linear;
		/* Standard syntax */
		transition-property: all;
		transition-duration: 0.5s;
		transition-timing-function: linear;
	}
	.opt-os:hover{
		margin-left: 50px;
	}
	.opt-os:hover > .os-config{
		-webkit-animation-name: spin; /* Chrome, Safari, Opera */
		-webkit-animation-duration: 1s; /* Chrome, Safari, Opera */
		-webkit-animation-iteration-count: 5; /* Chrome, Safari, Opera */
		animation-iteration-count: 1;
		animation-name: spin;
		animation-duration: 1s;
	}
	.opt-os:hover > .os-update{
		margin-left: -60px;
		visibility: visible;
		opacity: 1;
	}
	.opt-os:hover > .os-delete{
		margin-left: -30px;
		visibility: visible;
		opacity: 1
	}
	@-moz-keyframes spin {
		from { -moz-transform: rotateY(0deg); }
		to { -moz-transform: rotateY(180deg); }
	}
	@-webkit-keyframes spin {
		from { -webkit-transform: rotateY(0deg); }
		to { -webkit-transform: rotateY(180deg); }
	}
	@keyframes spin {
	from {transform:rotateY(0deg);}
	to {transform:rotateY(180deg);}
	}

	.fix-add{
		float: left;
		color: #056874;
		padding: 0px 10px;
		cursor: pointer;
	}
	.fix-delete{
		float: left;
		color: #DA4F49;
		padding: 0px 10px;
		/* For Safari 3.1 to 6.0 */
		-webkit-transition-property: all;
		-webkit-transition-duration: 1s;
		-webkit-transition-timing-function: linear;
		/* Standard syntax */
		transition-property: all;
		transition-duration: 1s;
		transition-timing-function: linear;
		opacity: 1;
		visibility: visible;
		cursor: pointer;
	}
	.fix-delete-hide{
		-webkit-animation-name: spin; /* Chrome, Safari, Opera */
		-webkit-animation-duration: 1s; /* Chrome, Safari, Opera */
		-webkit-animation-iteration-count: 5; /* Chrome, Safari, Opera */
		animation-iteration-count: 1;
		animation-name: spin;
		animation-duration: 1s;
		opacity: 0;
		visibility: hidden;
	}
	.fix-delete-ok{
		opacity: 0;
		padding: 0px 10px;
		visibility: hidden;
		margin-left: 34px;
		position: absolute;
		/* For Safari 3.1 to 6.0 */
		-webkit-transition-property: all;
		-webkit-transition-duration: 1s;
		-webkit-transition-timing-function: linear;
		/* Standard syntax */
		transition-property: all;
		transition-duration: 1s;
		transition-timing-function: linear;
		cursor: pointer;
	}
	.fix-ok-show{
		visibility: visible;
		opacity: 1;
	}
	.obj-edit{
		color: #056874;
		cursor: pointer;
	}
	.obj-delete{
		color: #DA4F49;
		cursor: pointer;
	}
	.obj-fix{
		color: #5D727D;
		cursor: pointer;
	}
	.obj-bug{
		color: #731028;
		cursor: pointer;
	}
	#tabs-apps{
		display: none;
	}
	#notify-notify{
		display: none;
	}
	.alert-notify{
		display: block;
		width: 50%;
		margin-left: auto;
		margin-right: auto;
		font-size: 25px;
		border: 1px solid yellow;
		padding: 25px;
		background-color: rgb(240, 173, 78);
	}
    .btn-add-app{
        float: right;
        margin: 20px 0;
    }
    .form-group > .dropdown-menu{
        background: white;
    }
</style>

<h2>
	Aplicaciones
	<button class="btn btn-warning" onclick="AppControl.cleanModal(true)"><i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;Agregar</button>
</h2>
<div class="col-lg-12" id="div-apps"></div>
<div class="col-lg-12" id="div-os"></div>
<!-- Modal for add apps -->
<div class="modal fade" id="mdl-apps" tabindex="-1" role="dialog" aria-labelledby="mdl-apps-label">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="mdl-apps-label">Agregar Aplicación</h4>
			</div>
			<div class="modal-body">
				<form>
					<div class="form-group">
						<input type="hidden" name="txt_id_app" id="txt_id_app" value="0">
						<label for="txt_name_app">Nombre</label>
						<input type="text" class="form-control" id="txt_name_app" name="txt_name_app">
					</div>
					<div class="form-group">
						<label for="txt_unique_app">Identificador</label>
						<input type="text" class="form-control" id="txt_unique_app" name="txt_unique_app">
                    </div>
                    <div class="form-group">
                        <label for="">Api Key</label>
                        <input type="text" id="api_key" class="form-control">
                    </div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				<button type="button" class="btn btn-primary" onclick="app.saveApp()">Guardar</button>
			</div>
		</div>
	</div>
</div>
<!-- Modal for add versions -->
<div class="modal fade" id="mdl-versions" tabindex="-1" role="dialog" aria-labelledby="mdl-versions-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mdl-versions-label">Agregar Version</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <input type="hidden" id="txt_vesion_id" value="0">
                        <input type="hidden" id="txt_app_id" value="0">
                        <input type="hidden" id="txt_os_id" value="0">
                        <label for="txt_name_version">Nombre</label>
                        <input type="text" class="form-control" id="txt_name_version">
                    </div>
                    <div class="form-group">
                        <label for="txt_date_version">Fecha de publicación</label>
                        <input type="text" class="form-control" id="txt_date_version">
                    </div>
                    <div class="form-group">
                        <label>Permitida</label>
                        <div class="slideThree" dt-active="Si" dt-not-active="No">
                            <input type="checkbox" class="ios-chk" value="Y" id="chk_available" />
                            <label for="chk_available"></label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="app.saveVersion()">Guardar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal for extras -->
<div class="modal fade" id="mdl-extra" tabindex="-1" role="dialog" aria-labelledby="mdl-extra-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mdl-extra-label"></h4>
            </div>
            <div class="modal-body">
                <form id="frm-extra"></form>
                <div class="btn-add"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
	class AppControl {
	    constructor(){
            this.strAction = '[@strAction]';
            this.wd = new drawWidgets();
	        this.arrOS = [];
	        this.arrApps = [];
	        this.loadInfo();
		}

		loadInfo(){
            $.ajax({
                type: 'POST',
                url: `${this.strAction}&op=init`,
                beforeSend: ()=>{
                    this.wd.openLoading();
                },
                success: (data)=>{
                    this.wd.closeLoading();
					if(data.valido === 1){
						this.arrApps = data.apps;
						this.arrOS = data.os;
						this.drawApps();
					}
                },
                error: ()=>{
                    this.wd.closeLoading();
                }
            });
		}

		saveApp(){
            const txtID = document.getElementById('txt_id_app');
	        const txtName = document.getElementById('txt_name_app');
            const txtUnique = document.getElementById('txt_unique_app');
            let app_key = document.querySelector(`#api_key`);
            /* ¯\_(?)_/¯ */
            let boolOk = true;
			if(txtID.value.trim() === '')
                boolOk = false;
            if(txtName.value.trim() === '')
                boolOk = false;
            if(txtUnique.value.trim() === '')
                boolOk = false;
            if(app_key.value.trim() === ''){
                boolOk = false;
            }

            if(boolOk){
				$.ajax({
					type: 'POST',
					url: `${this.strAction}&op=save`,
					dataType: 'JSON',
					data : {txt_id_app:txtID.value,txt_name_app:txtName.value,txt_unique_app:txtUnique.value, api_key: app_key.value},
					beforeSend: ()=>{

					},
					success: (data)=>{
						if(data.valido === 1){
						    let boolExist = false;
                            for(let app in this.arrApps){
                                if(this.arrApps[app].id == data.id){
                                    this.arrApps[app].name = txtName.value;
                                    this.arrApps[app].name_unique = txtUnique.value;
                                    this.arrApps[app].api_key = app_key.value;
                                    boolExist = true;
								}
							}
							if(!boolExist){
                                let arrApp = {};
                                arrApp.id = data.id;
                                arrApp.name = txtName.value;
                                arrApp.name_unique = txtUnique.value;
                                arrApp.api_key = app_key.value;
                                this.arrApps.push(arrApp);
							}
                            this.drawApps();
                            AppControl.cleanModal();
						}
						else{
							AppControl.cleanModal();
                            this.wd.alertDialog('Hubo un problema al guardar los datos, intente de nuevo')
						}
					},
					error: ()=>{
                        AppControl.cleanModal();
                        this.wd.alertDialog('Hubo un problema al guardar los datos, intente de nuevo')
					}
				});
			}
			else{
                AppControl.cleanModal();
			    this.wd.alertDialog('Todos los campos son obligatorios')
			}
		}

		deleteApp(id){
            $.ajax({
                type: 'POST',
                url: `${this.strAction}&op=delete`,
				data: {id},
                success: (data)=>{
                    if(data.valido === 1){
                        for(let app in this.arrApps){
                            if(this.arrApps[app].id == id){
                                delete this.arrApps[app];
                            }
                        }
                    }
                    this.cleanArrApps();
                }
            });
		}

		cleanArrApps(){
	        let arrNew = [];
            for(let app in this.arrApps){
                if(typeof this.arrApps[app] !== 'undefined'){
                    arrNew.push(this.arrApps[app]);
				}
			}
			this.arrApps = arrNew;
            this.drawApps();
		}

        cleanArrOS(app){
            let arrNew = [];
            for(let os in this.arrOS){
                if(typeof this.arrOS[os] !== 'undefined'){
                    arrNew.push(this.arrOS[os]);
                }
            }
            this.arrOS = arrNew;
            this.drawTabs(app);
        }

		static cleanModal(boolOpen = false){
	        if(boolOpen)
                $('#mdl-apps').modal('show');
	        else
                $('#mdl-apps').modal('hide');
            document.getElementById('txt_id_app').value = 0;
            document.getElementById('txt_name_app').value = '';
            document.getElementById('txt_unique_app').value = '';
            document.querySelector(`#api_key`).value = '';
		}

		static cleanModalVersions(boolOpen = false, version = 0, app = 0, os = 0,name = '',date = '', available = false){
            if(boolOpen)
                $('#mdl-versions').modal('show');
            else
                $('#mdl-versions').modal('hide');

            $('#txt_vesion_id').val(version);
            $('#txt_app_id').val(app);
            $('#txt_os_id').val(os);
            $('#txt_name_version').val(name);
            $('#txt_date_version').val(date);
            $('#api_key').val('');
            $('#chk_available').prop('checked',available);
        }

		drawApps(){
	        if(Object.keys(this.arrApps).length > 0){
	            const cnt = $("#div-apps");
                cnt.html('');
				for(let app in this.arrApps){
					const templateApp = `
						<div class='md-btn md-btn-default'>
							<div class='app-update'></div>
							<div class='app-main'>${this.arrApps[app].name}</div>
							<div class='app-delete'></div>
						</div>
					`;

					const obj = $(templateApp);cnt.append(obj);
                    obj.find('.app-update').on('click',()=>{
                        console.log(this.arrApps[app]);
                        document.getElementById('txt_id_app').value = this.arrApps[app].id;
                        document.getElementById('txt_name_app').value = this.arrApps[app].name;
                        document.getElementById('txt_unique_app').value = this.arrApps[app].name_unique;
                        document.querySelector(`#api_key`).value = this.arrApps[app].api_key;
                        $('#mdl-apps').modal('show');
					});
                    obj.find('.app-delete').on('click',()=>{
                        obj.remove();
                        this.deleteApp(this.arrApps[app].id);
                    });
                    obj.find('.app-main').on('click',()=>{
                        this.drawTabs(this.arrApps[app].id);
                    });
				}
			}
		}

		drawTabs(idApp = 0){
            const cnt = $('#div-os');
            if(idApp){
                cnt.html('');

                const ul = $(`<ul class='nav nav-tabs' role='tablist'></ul>`);cnt.append(ul);
                const panel = $(`<div class='tab-content'></div>`);cnt.append(panel);
                for(let os in this.arrOS){
                    const li = $(`<li role='presentation'><a href='#tab-os-${os}' aria-controls='tab-os-${os}' role='tab' data-toggle='tab'>${this.arrOS[os].os}</a></li>`);ul.append(li);
                    const versions = $(`
                        <div role='tabpanel' class='tab-pane' id='tab-os-${os}'>
                            <h3 class="title-tab">
                                <div class="title-os">${this.arrOS[os].os}</div>
                                <div class="opt-os">
                                    <div class="os-update"><i class="fa fa-pencil"></i></div>
                                    <div class="os-delete"><i class="fa fa-trash-o"></i></div>
                                    <div class="os-config"><i class="fa fa-cog"></i></div>
                                </div>
                            </h3>
                            <div class="loading"></div>
                            <div class="loading"></div>
                            <div class="loading"></div>
                            <div class="loading"></div>
                            <div class="loading"></div>
                        </div>
                    `);panel.append(versions);
                    this.drawVersions(versions,os,idApp);
                }

                const li = $(`<li role='presentation'><a href='#add-os' aria-controls='add-os' role='tab' data-toggle='tab'>Agregar</a></li>`);ul.append(li);
                const versions = $(`
                    <div role='tabpanel' class='tab-pane' id='add-os'>
                        <form>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label for="txt_name_os">Nombre</label>
                                    <input type="hidden" id="txt_id_os" value="0">
                                    <input type="text" class="form-control" id="txt_name_os">
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" onclick="app.saveOS(${idApp})">Guardar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                `);panel.append(versions);
                li.on('click',()=>{
                    document.getElementById('txt_name_os').value = '';
                    document.getElementById('txt_id_os').value = 0;
                });
            }
        }

        drawVersions(cnt,os,idApp){
            $.ajax({
                type: 'POST',
                url: `${this.strAction}&op=versions`,
                data: {idApp,idOs:this.arrOS[os].id},
                success: (data)=>{
                    if(data.valido === 1){
                        cnt.html('');
                        const versionsNew = $(`
                                    <h3 class="title-tab">
                                        <div class="title-os">${this.arrOS[os].os}</div>
                                        <div class="opt-os">
                                            <div class="os-update"><i class="fa fa-pencil"></i></div>
                                            <div class="os-delete"><i class="fa fa-trash-o"></i></div>
                                            <div class="os-config"><i class="fa fa-cog"></i></div>
                                        </div>
                                    </h3>
                                    <div class="table-responsive">
                                        <table class="table-bordered table-hover display" cellspacing="0" cellpadding="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th data-sortable="true">Versión</th>
                                                    <th data-sortable="true">Registro</th>
                                                    <th data-sortable="true">Publicado</th>
                                                    <th data-sortable="true">Fixes</th>
                                                    <th data-sortable="true">Bugs</th>
                                                    <th data-sortable="true">Permitida</th>
                                                    <th data-sortable="true">Editar</th>
                                                    <th data-sortable="true">Eliminar</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                   <div class="btn btn-warning btn-add-app" onclick="AppControl.cleanModalVersions(true,0,${idApp},${this.arrOS[os].id})"><i class="fa fa-plus" aria-hidden="true"></i>&nbsp;&nbsp;Agregar versión</div>
                                `);cnt.append(versionsNew);

                        versionsNew.find('.os-update').on('click',()=>{
                            const contendedor = $('#div-os');
                            contendedor.find('.tab-pane').removeClass('active');
                            contendedor.find('.tab-pane:last-child').addClass('active');
                            document.getElementById('txt_name_os').value = this.arrOS[os].os;
                            document.getElementById('txt_id_os').value = this.arrOS[os].id;
                        });

                        versionsNew.find('.os-delete').on('click',()=>{
                            $.ajax({
                                type: 'POST',
                                url: `${this.strAction}&op=deleteOS`,
                                data: {os:this.arrOS[os].id},
                                success: (data)=>{
                                    if(data.valido === 1){
                                        delete this.arrOS[os];
                                    }
                                    this.cleanArrOS(idApp);
                                }
                            });
                        });

                        for(let key in data.versions){
                            const idVersion = data.versions[key].id;
                            const tr = $(`
                                <tr>
                                    <td align="center">${data.versions[key].version}</td>
                                    <td align="center">${data.versions[key].fecha_registro}</td>
                                    <td align="center">${data.versions[key].fecha_publicado}</td>
                                    <td align="center"><i class="fa fa-wrench fa-2x obj-fix" aria-hidden="true"></i></td>
                                    <td align="center"><i class="fa fa-bug fa-2x obj-bug" aria-hidden="true"></i></td>
                                    <td align="center">
                                        <div class="slideThree" dt-active="Si" dt-not-active="No">
                                            <input type="checkbox" class="ios-chk" value="Y" id="chk_permitted_${idVersion}"/>
                                            <label for="chk_permitted_${idVersion}"></label>
                                        </div>
                                    </td>
                                    <td align="center"><i class="fa fa-pencil fa-2x obj-edit" aria-hidden="true"></i></td>
                                    <td align="center"><i class="fa fa-trash-o fa-2x obj-delete" aria-hidden="true"></i></td>
                                </tr>
                            `);versionsNew.find('tbody').append(tr);

                            const checkbox = $(`#chk_permitted_${idVersion}`);

                            let boolPermited = false;
                            if(data.versions[key].permitido === 'Y'){
                                checkbox.prop('checked',true);
                                boolPermited = true;
                            }

                            checkbox.on('click',()=>{
                                let boolPer = false;
                                if(checkbox.prop('checked'))
                                    boolPer = false;
                                AppControl.cleanModalVersions(false,idVersion,idApp,this.arrOS[os].id,data.versions[key].version,data.versions[key].fecha_publicado,boolPer);
                                this.saveVersion();
                            });

                            tr.find('.fa-pencil').on('click',()=>{
                                AppControl.cleanModalVersions(true,idVersion,idApp,this.arrOS[os].id,data.versions[key].version,data.versions[key].fecha_publicado,boolPermited);
                            });
                            tr.find('.fa-trash-o').on('click',()=>{
                                this.deleteVersion(idVersion,cnt,os,idApp);
                            });

                            /*FIXES*/
                            tr.find('.fa-wrench').on('click',()=>{
                                const cntFixes = $('#mdl-extra');
                                cntFixes.find('.modal-title').html(`Fixes - ${data.versions[key].version}`);
                                cntFixes.find('form').html('');
                                cntFixes.find('.btn-add').html('');
                                cntFixes.modal('show');

                                for(let fix in data.versions[key].fixes){
                                    if(typeof data.versions[key].fixes[key] === 'undefined')continue;
                                    const templateFix = $(`
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="${data.versions[key].fixes[fix].description}">
                                                <div class="input-group-addon"><i class="fa fa-trash-o" aria-hidden="true"></i></div>
                                            </div>
                                        </div>
                                    `);cntFixes.find('form').append(templateFix);

                                    templateFix.find('input').on('change',()=>{
                                        const txtFix = templateFix.find('input').val();
                                        $.ajax({
                                            type: 'POST',
                                            url: `${this.strAction}&op=saveFix`,
                                            data: {txtID:data.versions[key].fixes[fix].id,txtFix,idVersion},
                                            success: (_response)=>{
                                                if(_response.valido !== 1){
                                                    templateFix.find('input').val(data.versions[key].fixes[fix].description);
                                                }
                                            }
                                        });
                                    });
                                    templateFix.find('.input-group-addon').on('click',()=>{
                                        templateFix.remove();
                                        this.deleteFixes(data.versions[key].fixes[fix].id)
                                        delete data.versions[key].fixes[fix];
                                    });
                                }

                                const btnAdd = $(`<button type="button" class="btn btn-warning">Agregar</button>`);cntFixes.find('.btn-add').append(btnAdd);
                                btnAdd.on('click',()=>{
                                    const templateFixAdd = $(`
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="">
                                                <div class="input-group-addon"><i class="fa fa-floppy-o" aria-hidden="true"></i></div>
                                            </div>
                                        </div>
                                    `);cntFixes.find('form').append(templateFixAdd);

                                    templateFixAdd.find('.input-group-addon').on('click',()=>{
                                        const txtFix = templateFixAdd.find('input').val();
                                        $.ajax({
                                            type: 'POST',
                                            url: `${this.strAction}&op=saveFix`,
                                            data: {txtID:0,txtFix,idVersion},
                                            success: (_response)=>{
                                                if(_response.valido === 1){
                                                    templateFixAdd.find('.input-group-addon').find('.fa-floppy-o').addClass('fa-trash-o');
                                                    templateFixAdd.find('.input-group-addon').find('.fa-floppy-o').removeClass('fa-floppy-o');

                                                    const arrTMP = {};
                                                    arrTMP.id = _response.id;
                                                    arrTMP.description = txtFix;
                                                    arrTMP.id_version = idVersion;
                                                    data.versions[key].fixes.push(arrTMP);

                                                    const countFix = data.versions[key].fixes.length - 1;

                                                    templateFixAdd.find('.input-group-addon').off();
                                                    templateFixAdd.find('.input-group-addon').on('click',()=>{
                                                        templateFixAdd.remove();
                                                        delete data.versions[key].fixes[countFix];
                                                        this.deleteFixes(_response.id)
                                                    });

                                                    templateFixAdd.find('input').on('change',()=>{
                                                        const txtFix = templateFixAdd.find('input').val();
                                                        $.ajax({
                                                            type: 'POST',
                                                            url: `${this.strAction}&op=saveFix`,
                                                            data: {txtID:_response.id,txtFix,idVersion},
                                                            success: (_res)=>{
                                                                if(_res.valido !== 1){
                                                                    templateFixAdd.find('input').val(data.versions[key].fixes[countFix].description);
                                                                }
                                                                else{
                                                                    data.versions[key].fixes[countFix].description = txtFix;
                                                                }
                                                            }
                                                        });
                                                    });
                                                }
                                            }
                                        });
                                    });
                                });
                            });

                            /*BUGS*/
                            tr.find('.fa-bug').on('click',()=>{
                                const cntFixes = $('#mdl-extra');
                                cntFixes.find('.modal-title').html(`Bugs - ${data.versions[key].version}`);
                                cntFixes.find('form').html('');
                                cntFixes.find('.btn-add').html('');
                                cntFixes.modal('show');
                                for(let bug in data.versions[key].bugs){
                                    if(typeof data.versions[key].bugs[bug] === 'undefined')continue;
                                    const templateBug = $(`
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="${data.versions[key].bugs[bug].description}">
                                                <div class="input-group-addon"><i class="fa fa-trash-o" aria-hidden="true"></i></div>
                                            </div>
                                        </div>
                                    `);cntFixes.find('form').append(templateBug);

                                    templateBug.find('input').on('change',()=>{
                                        const txtBug = templateBug.find('input').val();
                                        $.ajax({
                                            type: 'POST',
                                            url: `${this.strAction}&op=saveBug`,
                                            data: {txtID:data.versions[key].bugs[bug].id,txtBug,idVersion},
                                            success: (_response)=>{
                                                if(_response.valido !== 1){
                                                    templateBug.find('input').val(data.versions[key].bugs[bug].description);
                                                }
                                                else{
                                                    data.versions[key].bugs[bug].description = txtBug;
                                                }
                                            }
                                        });
                                    });
                                    templateBug.find('.input-group-addon').on('click',()=>{
                                        templateBug.remove();
                                        this.deleteBugs(data.versions[key].bugs[bug].id)
                                        delete data.versions[key].bugs[bug];
                                    });
                                }

                                const btnAdd = $(`<button type="button" class="btn btn-warning">Agregar</button>`);cntFixes.find('.btn-add').append(btnAdd);
                                btnAdd.on('click',()=>{
                                    const templateBugAdd = $(`
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="">
                                                <div class="input-group-addon"><i class="fa fa-floppy-o" aria-hidden="true"></i></div>
                                            </div>
                                        </div>
                                    `);cntFixes.find('form').append(templateBugAdd);

                                    templateBugAdd.find('.input-group-addon').on('click',()=>{
                                        const txtBug = templateBugAdd.find('input').val();
                                        $.ajax({
                                            type: 'POST',
                                            url: `${this.strAction}&op=saveBug`,
                                            data: {txtID:0,txtBug,idVersion},
                                            success: (_response)=>{
                                                if(_response.valido === 1){
                                                    templateBugAdd.find('.input-group-addon').find('.fa-floppy-o').addClass('fa-trash-o');
                                                    templateBugAdd.find('.input-group-addon').find('.fa-floppy-o').removeClass('fa-floppy-o');

                                                    const arrTMP = {};
                                                    arrTMP.id = _response.id;
                                                    arrTMP.description = txtBug;
                                                    arrTMP.id_version = idVersion;
                                                    data.versions[key].bugs.push(arrTMP);

                                                    const countBugs = data.versions[key].bugs.length - 1;

                                                    templateBugAdd.find('.input-group-addon').off();
                                                    templateBugAdd.find('.input-group-addon').on('click',()=>{
                                                        templateBugAdd.remove();
                                                        delete data.versions[key].bugs[countBugs];
                                                        this.deleteBugs(_response.id)
                                                    });

                                                    templateBugAdd.find('input').on('change',()=>{
                                                        const txtBug = templateBugAdd.find('input').val();
                                                        $.ajax({
                                                            type: 'POST',
                                                            url: `${this.strAction}&op=saveBug`,
                                                            data: {txtID:_response.id,txtBug,idVersion},
                                                            success: (_res)=>{
                                                                if(_res.valido !== 1){
                                                                    templateBugAdd.find('input').val(data.versions[key].bugs[countBugs].description);
                                                                }
                                                                else{
                                                                    data.versions[key].bugs[countBugs].description = txtBug;
                                                                }
                                                            }
                                                        });
                                                    });
                                                }
                                            }
                                        });
                                    });
                                });
                            });
                        }


                        $.extend( $.fn.dataTable.defaults, {
                            "paging":   false,
                            "info":     false
                        });
                        versionsNew.find('table').dataTable({
                            "language": {
                                "zeroRecords": "No se encontraron resultados",
                                "search":"Buscar:   "
                            }
                        });
                    }
                }
            });
        }

        saveOS(idApp){
            const txtOS = document.getElementById('txt_name_os').value;
            const idOs = document.getElementById('txt_id_os').value;
            if(txtOS.trim() !== ''){
                $.ajax({
                    type: 'POST',
                    url: `${this.strAction}&op=saveOS`,
                    data: {txtOS,idOs},
                    success: (data)=>{
                        if(data.valido === 1){
                            if(idOs > 0){
                                for(let os in this.arrOS){
                                    if(this.arrOS[os].id == data.id){
                                        this.arrOS[os].os = txtOS;
                                    }
                                }
                            }
                            else{
                                let arrOS = {};
                                arrOS.id = data.id;
                                arrOS.os = txtOS;
                                this.arrOS.push(arrOS);
                            }
                            this.drawTabs(idApp)
                        }
                        else{
                            this.wd.alertDialog('Hubo un problema al guardar los datos, intente de nuevo')
                        }
                    }
                });
            }
        }

        saveVersion(){
	        const txtVersion = $('#txt_vesion_id').val();
	        const txtApp = $('#txt_app_id').val();
	        const txtOs = $('#txt_os_id').val();
	        const txtName = $('#txt_name_version').val();
	        const txtDate = $('#txt_date_version').val();
	        let txtPermitted = 'N';
	        if($('#chk_available').prop('checked')){
                txtPermitted = 'Y';
            }
            else if($(`#chk_permitted_${txtVersion}`).prop('checked')){
                txtPermitted = 'Y';
            }
	        if(txtName.trim() !== ''){
	            let intOs = null;
	            for(let os in this.arrOS){
                    if(this.arrOS[os].id == txtOs){
                        intOs = os;
                    }
                }
                $.ajax({
                    type: 'POST',
                    url: `${this.strAction}&op=saveVersion`,
                    data: {txtVersion,txtApp,txtOs,txtName,txtDate,txtPermitted},
                    success: (data)=>{
                        AppControl.cleanModalVersions();
                        if(data.valido === 1){
                            if(intOs !== null){
                                const cnt = $(`#tab-os-${intOs}`);
                                this.drawVersions(cnt,intOs,txtApp);
                            }
                        }
                        else{
                            this.wd.alertDialog(data.msj);
                        }
                    }
                });
            }
        }

        deleteVersion(versionID,cnt,os,idApp){
	        if(versionID){
                $.ajax({
                    type: 'POST',
                    url: `${this.strAction}&op=deleteVersion`,
                    data: {versionID},
                    success: (data)=>{
                        if(data.valido === 1){
                            this.drawVersions(cnt,os,idApp);
                        }
                        else{
                            this.wd.alertDialog(data.msj);
                        }
                    }
                });
            }
        }

        deleteFixes(idFix){
            $.ajax({
                type: 'POST',
                url: `${this.strAction}&op=deleteFix`,
                data: {idFix},
                success: (data)=>{}
            });
        }

        deleteBugs(idBug){
            $.ajax({
                type: 'POST',
                url: `${this.strAction}&op=deleteBug`,
                data: {idBug},
                success: (data)=>{}
            });
        }

        searchKey(obj,id){
	        let key = null;
            for(let search in obj){
                if(obj[search] == id){
                    key = search;
                }
            }
            return key;
        }

	}

    const app = new AppControl();
	$(document).ready(()=>{
        $('#txt_date_version').datetimepicker({
            format: 'DD-MM-YYYY'
        });
    });
</script>
<script defer>

    function fetchNotificationTypes(){
        let action = `[@strAction]`;
        return fetch(`${action}&op=getNotificationTypes`)
    }

    fillTypes();

</script>