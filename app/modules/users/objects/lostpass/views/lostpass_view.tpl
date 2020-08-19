<style type="text/css">
	body{
		color: black;
	}
	h3{
		text-align: center;
	}

	.cnt-lost{
		margin: 0 auto;
        min-height: 600px;
	}
	.cnt-form{
		width: 100%;
		padding: 30px;
		background-color: #f7f7f7;
		box-shadow: 5px 5px 20px #888888;

	}
	.cnt-form form > button{
		width: 100%;
		margin: 5px 0;
	}
	.cnt-form > input{
		margin: 5px 0;
	}
	.all_content{
		display: flex;
		flex-direction: column;
		padding: 0 1em;
		justify-content: center;
	}
	form p{
		color: #000;
	}
	.div-user{
		margin: 0 auto;
		width: auto;
		text-align: center;
	}
	.ctn-main{
		padding: 0;
	}
</style>

<div class="main-sidebar"></div>
<div class="color-menu-text_background"></div>
<div class="color-menu-text_color"></div>
<div class="color-deg_background"></div>

<div class="cnt-lost all_content">
	<div class="div-user">
		<h3>Asistencia de la cuenta</h3>

		<p>Responde a lo siguiente para verificar que esta cuenta es tuya.</p>
	</div>

	<div class="cnt-form div-user">
		<form id="frmUser">
			<p>[@USER_LOSTPASS_MSG1]</p>
			<input type="text" class="form-control" name="iptUser" id="iptUser" placeholder="[@LOGIN_NAME]" required autocomplete="off">
			<button type="submit" class="btn btn-info">Siguiente</button>
		</form>
	</div>
	<div class="cnt-form hide div-token div-user">
		<form id="frmToken">
			<p id="pMessageUser">Se te acaba de enviar un correo con un token.</p>
			<input type="text" class="form-control" name="iptToken" id="iptToken" placeholder="Escribe el token aqui" maxlength="4" required autocomplete="off">
			<button type="submit" class="btn btn-info">Siguiente</button>
		</form>
	</div>
	<div class="cnt-form hide div-reset div-user">
		<form id="frmReset">
            <input type="hidden" name="token" id="token" value="">
			<p>Por ultimo crea la nueva contraseña</p>
			<div class="form-group">
				<input type="password" class="form-control" id="pass_1" name="pass_1" aria-describedby="pass_1status" placeholder="Ingresa la contraseña" required>
			</div>
			<div class="form-group">
				<input type="password" class="form-control" id="pass_2" name="pass_2" aria-describedby="pass_2status" placeholder="Repite la contraseña" required>
			</div>
			<button type="submit" class="btn btn-info">Guardar</button>
		</form>
	</div>
</div>
<script type="text/javascript">
    const strAction = "[@strAction]";
    const objWd = new drawWidgets();
    $(document).ready(()=>{

        $("#frmReset").on("submit",(e)=>{
            e.preventDefault();
            resetpass();
        });
        $("#frmUser").on("submit",(e)=>{
            e.preventDefault();
            validateUser();
        });
        $("#frmToken").on("submit",(e)=>{
            e.preventDefault();
            validateToken();
        });

        $("#pass_2").on("change",()=>{
            $(this).removeClass("has-error");
        });
        $(".content-wrapper").css({"margin-left":"0"})
    });

    function validateToken(){
        const iptToken = $("#iptToken").val();
        $.ajax({
            type:"POST",
            url:`${strAction}&op=validateToken`,
            data: {
                token : iptToken
            },
            dataType:"JSON",
            beforeSend: ()=>{
                objWd.openLoading();
            },
            success: (data)=>{
                objWd.closeLoading();
                if(data.valido === 1){
                    $(".div-token").addClass("hide");
                    $(".div-reset").removeClass("hide");
                    $("#token").val(iptToken);
                }
                else{
                    objWd.alertDialog(data.msj);
                }
            },
            error: ()=>{
                objWd.closeLoading();
            }
        });
    }

    function validateUser(){
        const iptUser = $("#iptUser").val();
        $.ajax({
            type:"POST",
            url:`${strAction}&op=send`,
            data: {
                username : iptUser
            },
            dataType:"JSON",
            beforeSend: ()=>{
                objWd.openLoading();
            },
            success: (data)=>{
                objWd.closeLoading();
                if(data.valido === 1){
                    $(".div-user").addClass("hide");
                    $(".div-token").removeClass("hide");
					$("#pMessageUser").html(data.razon);
                }
                else{
                    objWd.alertDialog(data.msj);
                }
            },
            error: ()=>{
                objWd.closeLoading();
            }
        });
    }

    function resetpass(){
        const iptPass = $("#pass_1");
        const iptPass2 = $("#pass_2");

        if(iptPass.val() !== iptPass2.val()){
            iptPass2.parent().addClass("has-error");
            objWd.alertDialog("No coiciden las contraseñas");
            return false;
        }
        else{
            $.ajax({
                type:"POST",
                url:`${strAction}&op=savePass`,
	            data : $("#frmReset").serialize(),
                dataType:"JSON",
                beforeSend: ()=>{
                    objWd.openLoading();
                },
                success: (data)=>{
                    objWd.closeLoading();
                    if(data.valido === 1){
                        objWd.alertDialog(data.msj,false,false,getInit);
                    }
                    else{
                        objWd.alertDialog(data.msj);
                    }
                },
                error: ()=>{
                    objWd.closeLoading();
                }
            });
        }
    }

    function getInit(){
	    document.location.href = "index.php";
    }
</script>