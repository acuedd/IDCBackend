<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 10/24/18
 * Time: 10:52 AM
 */
include_once("core/main.php");
draw_header();
?>
	<style>
		*{
			text-align: justify;
		}
		.contPolPrivacy{
			margin: 50px 120px;
		}
		.contDataPP{
			padding-top: 20px;
		}
		.firstTextPP{
			border-left: 5px solid #00adef;
			padding-left: 25px;
		}
		.subtitleTextPP{
			border-left: 5px solid #A2EA15;
			padding-left: 25px;
		}
		.contPPsubtitle{
			margin: 25px 65px;
		}
		.subtitlePP{
			font-size: 21px;
		}
		.textPP{
			font-size: 18px;
		}

	</style>
	<br/>
	<div class="contPolPrivacy">
		<h3>�ltima modificaci�n: 24 de octubre de 2018</h3>

		<h2>*  �Qu� datos obtenemos?</h2>
		<h2>*  Como utilizamos esos datos.</h2>
		<h2>*  Las opciones que brindamos para poder consultar los datos obtenidos.</h2>

		<div class="contDataPP">
			<p class="subtitlePP"><strong>Datos obtenidos por Samsung Advertising app.</strong></p>
			<p class="textPP firstTextPP">
				Genius solo almacena los datos necesarios para funcionar los cuales son obtenidos por medio de la aplicaci�n y se utiliza
				para poder brindar una mayor funcionalidad a los usuarios de genius web.
			</p>
			<p class="subtitlePP"><strong>Como se utilizan los datos.</strong></p>
			<div class="contPPsubtitle subtitleTextPP">
				<p class="textPP">
					Toda la informaci�n y datos almacenados por parte de GeniusApp son utilizados para la ayuda de creaci�n de nuevas
					herramientas as� como para poder brindar una mejor soluci�n para los negocios y clientes.
					Dicha informaci�n NO es vendida ni distribuida a terceros para fines comerciales.
				</p>
			</div>

			<p class="subtitlePP"><strong>Consultar informaci�n</strong></p>
			<div class="contPPsubtitle subtitleTextPP">
				<p class="textPP"><strong>Planes</strong> <br>
					Dentro de la aplicaci�n se puede consultar informaci�n sobre el estado de los tel�fonos y permite ver
					todas las opciones para los planes y sus precios.<br>
				</p>
			</div>
		</div>
	</div>
<?php
draw_footer();