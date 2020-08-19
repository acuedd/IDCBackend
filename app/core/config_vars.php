<?php
// Info publica del sitio
$config_var["core"]["title"]["type"] = "textbox";
$config_var["core"]["title"]["desc"] = "Site title";
$config_var["core"]["title"]["default"] = "homeland";
$config_var["core"]["title"]["obs"] = "The title that appears as the browser title";

$config_var["core"]["slogan"]["type"] = "textbox";
$config_var["core"]["slogan"]["desc"] = "Site slogan";
$config_var["core"]["slogan"]["default"] = "";
$config_var["core"]["slogan"]["obs"] = "Slogan for the site owner.";

$config_var["core"]["keywords"]["type"] = "textarea";
$config_var["core"]["keywords"]["desc"] = "Keywords";
$config_var["core"]["keywords"]["default"] = "php,mysql,homeland";
$config_var["core"]["keywords"]["obs"] = "Some keywords to add to the meta tags at your site, and make some spiders happy.";

$config_var["core"]["description"]["type"] = "textarea";
$config_var["core"]["description"]["desc"] = "Description";
$config_var["core"]["description"]["default"] = "Homeland";
$config_var["core"]["description"]["obs"] = "A short description to appear at sites-search.";

$config_var["core"]["url"]["type"] = "textbox";
$config_var["core"]["url"]["desc"] = "Site url";
$config_var["core"]["url"]["default"] = "http://localhost/";
$config_var["core"]["url"]["obs"] = "Direccion del sitio NORMAL.";

$config_var["core"]["HTTPS"]["type"] = "checkbox";
$config_var["core"]["HTTPS"]["desc"] = "Use HTTPS";
$config_var["core"]["HTTPS"]["default"] = false;
$config_var["core"]["HTTPS"]["obs"] = "Use HTTPS";

$config_var["core"]["HTTPS_logged"]["type"] = "checkbox";
$config_var["core"]["HTTPS_logged"]["desc"] = "Use HTTPS when logged in";
$config_var["core"]["HTTPS_logged"]["default"] = true;
$config_var["core"]["HTTPS_logged"]["obs"] = "Use HTTPS to log in and everywhere once logged in.";

$config_var["core"]["url_secure"]["type"] = "textbox";
$config_var["core"]["url_secure"]["desc"] = "Site url SECURE";
$config_var["core"]["url_secure"]["default"] = "https://localhost/";
$config_var["core"]["url_secure"]["obs"] = "La direccion del sitio SEGURO (valido solo si Use HTTPS esta activo).";

$config_var["core"]["SSO_identityP"]["type"] = "checkbox";
$config_var["core"]["SSO_identityP"]["desc"] = "Habilitar SSP para IdP";
$config_var["core"]["SSO_identityP"]["default"] = false;
$config_var["core"]["SSO_identityP"]["obs"] = "Utilizar este sitio como servidor de identidad para SSO.<br>
											   Requiere subir certificados a directorio por definir.<br>
											   Ver <a href='https://developers.google.com/google-apps/sso/saml_reference_implementation' target='_BLANK'>Referencia de Google</a>";

$config_var["core"]["SSO_certPath_PK"]["type"] = "textbox";
$config_var["core"]["SSO_certPath_PK"]["desc"] = "Path para el archivo PrivateKey para SSO";
$config_var["core"]["SSO_certPath_PK"]["default"] = "";
$config_var["core"]["SSO_certPath_PK"]["obs"] = "Path de LINUX para llegar al archivo del Private Key de este sitio en particular.<br>
											     <b>DEBE</b> estar afuera del directorio www por seguridad.  Debiera ser algo como /home/[linux_user]/SSO_certs/archivo.pem";

$config_var["core"]["SSO_certPath_CERT"]["type"] = "textbox";
$config_var["core"]["SSO_certPath_CERT"]["desc"] = "Path para el archivo del Certificado para SSO";
$config_var["core"]["SSO_certPath_CERT"]["default"] = "";
$config_var["core"]["SSO_certPath_CERT"]["obs"] = "Path de LINUX para llegar al archivo del Certificado de este sitio en particular.<br>
											       <b>DEBE</b> estar afuera del directorio www por seguridad.  Debiera ser algo como /home/[linux_user]/SSO_certs/archivo.crt";

// Comportamiento del sitio
$config_var["core"]["drawExternalFrame"]["type"] = "checkbox";
$config_var["core"]["drawExternalFrame"]["desc"] = "Frame";
$config_var["core"]["drawExternalFrame"]["default"] = true;
$config_var["core"]["drawExternalFrame"]["obs"] = "Dibujar <b>TODO</b> dentro de un frame para que la direccion quede oculta.";

$config_var["core"]["GZIP"]["type"] = "checkbox";
$config_var["core"]["GZIP"]["desc"] = "GZIP html contents";
$config_var["core"]["GZIP"]["default"] = true;
$config_var["core"]["GZIP"]["obs"] = "Use GZIP to reduce download time";

$config_var["core"]["CACHE_CSS_AND_JAVA"]["type"] = "checkbox";
$config_var["core"]["CACHE_CSS_AND_JAVA"]["desc"] = "Cache and compress CSS & Java";
$config_var["core"]["CACHE_CSS_AND_JAVA"]["default"] = true;
$config_var["core"]["CACHE_CSS_AND_JAVA"]["obs"] = "Esta variable hace que los archivos dynamiccss.php y dynamicjava.php jalen los includes para que estos vayan comprimidos y con cache";

$config_var["core"]["show_login_only"]["type"] = "checkbox";
$config_var["core"]["show_login_only"]["desc"] = "Mostrar solo Log In";
$config_var["core"]["show_login_only"]["default"] = false;
$config_var["core"]["show_login_only"]["obs"] = "Mostrar solo Log In en el área pública (e-mail sites)";

$config_var["core"]["update_server"]["type"] = "checkbox";
$config_var["core"]["update_server"]["desc"] = "Update Server";
$config_var["core"]["update_server"]["default"] = false;
$config_var["core"]["update_server"]["obs"] = "Configura este servidor como un update_server";

$config_var["core"]["LogWithMail"]["type"] = "checkbox";
$config_var["core"]["LogWithMail"]["desc"] = "Log-in with E-Mail";
$config_var["core"]["LogWithMail"]["default"] = false;
$config_var["core"]["LogWithMail"]["obs"] = "Permite hacer login usando la dirección de correo";

$config_var["core"]["Show_email"]["type"] = "checkbox";
$config_var["core"]["Show_email"]["desc"] = "Mostrar Email";
$config_var["core"]["Show_email"]["default"] = false;
$config_var["core"]["Show_email"]["obs"] = "Muestra el Email bajo el nombre en el Directorio Interno";

$config_var["core"]["allow_multi_session"]["type"] = "checkbox";
$config_var["core"]["allow_multi_session"]["desc"] = "Permitir sesiones múltiples";
$config_var["core"]["allow_multi_session"]["default"] = false;
$config_var["core"]["allow_multi_session"]["obs"] = "Permitir sesiones múltiples por usuario";

$config_var["core"]["IPBlockOperation_public"]["type"] = "listbox";
$config_var["core"]["IPBlockOperation_public"]["desc"] = "Visitas publicas";
$config_var["core"]["IPBlockOperation_public"]["default"] = "any";
$config_var["core"]["IPBlockOperation_public"]["values"] = "any;Cualquier IP,reg;IP registradas";
$config_var["core"]["IPBlockOperation_public"]["obs"] = "Permitir visitas publicas desde...<br><i>(se configura en la ventana de Administración de IP y accesos)</i>";

$config_var["core"]["IPBlockOperation_private"]["type"] = "listbox";
$config_var["core"]["IPBlockOperation_private"]["desc"] = "Visitas privadas";
$config_var["core"]["IPBlockOperation_private"]["default"] = "any";
$config_var["core"]["IPBlockOperation_private"]["values"] = "any;Cualquier IP,reg;IP registradas";
$config_var["core"]["IPBlockOperation_private"]["obs"] = "Permitir visitas privadas desde...<br><i>(se configura en la ventana de Administración de IP y accesos)</i>";

$config_var["core"]["use_nickname_in_welcome"]["type"] = "checkbox";
$config_var["core"]["use_nickname_in_welcome"]["desc"] = "Usar nickname en bienvenida";
$config_var["core"]["use_nickname_in_welcome"]["default"] = false;
$config_var["core"]["use_nickname_in_welcome"]["obs"] = "Utilizar el nickname del usuario en vez de los nombres en el mensaje de bienvenida.";

$config_var["core"]["page_processed"]["type"] = "checkbox";
$config_var["core"]["page_processed"]["desc"] = "Page processed";
$config_var["core"]["page_processed"]["default"] = false;
$config_var["core"]["page_processed"]["obs"] = "Do you want to show the time spent for every page?";

$config_var["core"]["page_processed_LOG"]["type"] = "checkbox";
$config_var["core"]["page_processed_LOG"]["desc"] = "Page processed Log";
$config_var["core"]["page_processed_LOG"]["default"] = true;
$config_var["core"]["page_processed_LOG"]["obs"] = "Bitacora del tiempo de proceso por usuario";

$config_var["core"]["query_performance_log"]["type"] = "checkbox";
$config_var["core"]["query_performance_log"]["desc"] = "Query performance log";
$config_var["core"]["query_performance_log"]["default"] = false;
$config_var["core"]["query_performance_log"]["obs"] = "Bitacora del tiempo de proceso de los queries.  <b>OJO:</b> Esto afecta <i>severamente</i> el comportamiento de db_affected_rows pues siempre va a haber un query mas en cada llamada a db_query, haciendo que esto regrese un 1.";


$config_var["core"]["hide_homeland_users"]["type"] = "checkbox";
$config_var["core"]["hide_homeland_users"]["desc"] = "Ocultar usuarios Homeland.";
$config_var["core"]["hide_homeland_users"]["default"] = true;
$config_var["core"]["hide_homeland_users"]["obs"] = "Ocultar la presencia de los usuarios de Homeland.";

$config_var["core"]["auto_user_name"]["type"] = "checkbox";
$config_var["core"]["auto_user_name"]["desc"] = "Auto Username.";
$config_var["core"]["auto_user_name"]["default"] = false;
$config_var["core"]["auto_user_name"]["obs"] = "Generar nombre de usuario y contraseña automáticamente (uid=name=pwd).";

$config_var["core"]["directorio_publico"]["type"] = "checkbox";
$config_var["core"]["directorio_publico"]["desc"] = "Directorio público.";
$config_var["core"]["directorio_publico"]["default"] = false;
$config_var["core"]["directorio_publico"]["obs"] = "Mostrar un directorio público (solo con los nombres de los usuarios inscritos.";

$config_var["core"]["ocultar_directorio_interno"]["type"] = "checkbox";
$config_var["core"]["ocultar_directorio_interno"]["desc"] = "Ocultar directorio interno";
$config_var["core"]["ocultar_directorio_interno"]["default"] = false;
$config_var["core"]["ocultar_directorio_interno"]["obs"] = "Ocultar el directorio interno a usuarios que no tengan acceso.";

$config_var["core"]["show_personal_info"]["type"] = "checkbox";
$config_var["core"]["show_personal_info"]["desc"] = "Mostrar información personal.";
$config_var["core"]["show_personal_info"]["default"] = false;
$config_var["core"]["show_personal_info"]["obs"] = "Mostrar informacion personal de los usuarios.";

$config_var["core"]["birthdayAlert"]["type"] = "checkbox";
$config_var["core"]["birthdayAlert"]["desc"] = "Alerta de cumpleaños";
$config_var["core"]["birthdayAlert"]["default"] = false;
$config_var["core"]["birthdayAlert"]["obs"] = "Trabajar las ventanas de alerta de cumpleaños.";

$config_var["core"]["SendEmailBirthday"]["type"] = "listbox";
$config_var["core"]["SendEmailBirthday"]["desc"] = "Enviar email a cumpleañeros por mes";
$config_var["core"]["SendEmailBirthday"]["default"] = "no_enviar";
$config_var["core"]["SendEmailBirthday"]["obs"] = "Envia un email a los cumpleañeros del mes.<br><b>no_enviar</b>: No realiza nada<br><b>manual</b>:se configura a quien se enviará el email<br><b>auto</b>:realiza un cronjob nocturno cada mes";
$config_var["core"]["SendEmailBirthday"]["values"] = "no_enviar,auto,manual";

$config_var["core"]["SendEmailBirthdayForPostal"]["type"] = "checkbox";
$config_var["core"]["SendEmailBirthdayForPostal"]["desc"] = "Enviar email a cumpleañeros según postal asignada";
$config_var["core"]["SendEmailBirthdayForPostal"]["default"] = false;
$config_var["core"]["SendEmailBirthdayForPostal"]["obs"] = "Envia correo segun la variable <b>Enviar email a cumpleañeros por mes</b>, con la opción de poder asignar la postal por areas.";

$config_var["core"]["UserMayChangeName"]["type"] = "checkbox";
$config_var["core"]["UserMayChangeName"]["desc"] = "Cambiar nombre.";
$config_var["core"]["UserMayChangeName"]["default"] = false;
$config_var["core"]["UserMayChangeName"]["obs"] = "Los usuarios pueden cambiar su nombre.";

$config_var["core"]["lostPWD"]["type"] = "checkbox";
$config_var["core"]["lostPWD"]["desc"] = "Bloquear 'Perdi mi contraseña'...";
$config_var["core"]["lostPWD"]["default"] = false;
$config_var["core"]["lostPWD"]["obs"] = "Bloquear el link de 'Perdi mi contraseña'";

$config_var["core"]["showVisitors"]["type"] = "checkbox";
$config_var["core"]["showVisitors"]["desc"] = "Mostrar visitantes";
$config_var["core"]["showVisitors"]["default"] = true;
$config_var["core"]["showVisitors"]["obs"] = "Mostrar cantidad de visitantes conectados al sitio (personas que no han hecho log in)";

$config_var["core"]["changePWD"]["type"] = "checkbox";
$config_var["core"]["changePWD"]["desc"] = "Cambiar contraseña";
$config_var["core"]["changePWD"]["default"] = true;
$config_var["core"]["changePWD"]["obs"] = "Permitir que los usuarios cambien su contraseña";

$config_var["core"]["UserMayChangeEMail"]["type"] = "checkbox";
$config_var["core"]["UserMayChangeEMail"]["desc"] = "Cambiar E-Mail alternativo.";
$config_var["core"]["UserMayChangeEMail"]["default"] = true;
$config_var["core"]["UserMayChangeEMail"]["obs"] = "Los usuarios pueden cambiar su correo alternativo.";

$config_var["core"]["logreport"]["type"] = "checkbox";
$config_var["core"]["logreport"]["desc"] = "Reporte de Actividad";
$config_var["core"]["logreport"]["default"] = false;
$config_var["core"]["logreport"]["obs"] = "Imprime reportes de Actividad.";

$config_var["core"]["visits_log"]["type"] = "checkbox";
$config_var["core"]["visits_log"]["desc"] = "Log de visitas";
$config_var["core"]["visits_log"]["default"] = false;
$config_var["core"]["visits_log"]["obs"] = "Contador de visitas y logs (no de clicks)";

$config_var["core"]["allow_webservice_devices"]["type"] = "checkbox";
$config_var["core"]["allow_webservice_devices"]["desc"] = "Webservices para dispositivos";
$config_var["core"]["allow_webservice_devices"]["default"] = false;
$config_var["core"]["allow_webservice_devices"]["obs"] = "Permitir el uso de dispositivos para consultar websevices.  Esto habilita que el usuario pueda habilitar y deshabilitar dispositivos.  Si se quita esto, ningun webservice de dispositivos funcionará.";

/**/
$config_var["core"]["limit_webservice_devices"]["type"] = "checkbox";
$config_var["core"]["limit_webservice_devices"]["desc"] = "Habilitar uso de licencias para webservices";
$config_var["core"]["limit_webservice_devices"]["default"] = false;
$config_var["core"]["limit_webservice_devices"]["obs"] = "Permite reducir la cantidad de dispositivos según las licencias, para un sitio pero tambien hace que el comportamiento sea que una relacion de uno a uno con los users";

$config_var["core"]["webservices_limitDevicesPerUser"]["type"] = "checkbox";
$config_var["core"]["webservices_limitDevicesPerUser"]["desc"] = "Limitar la cantidad de dispositivos por usuario";
$config_var["core"]["webservices_limitDevicesPerUser"]["default"] = false;
$config_var["core"]["webservices_limitDevicesPerUser"]["obs"] = "Permite limitar la cantidad de dispositivos que puede tener activos un usuario, esto habilita a que solamente 1 dispositivo se mantenga activo";

$config_var["core"]["webservice_notificationRegisterDevice"]["type"] = "checkbox";
$config_var["core"]["webservice_notificationRegisterDevice"]["desc"] = "Permite enviar una notificación cuando se registra un dispositivo";
$config_var["core"]["webservice_notificationRegisterDevice"]["default"] = false;
$config_var["core"]["webservice_notificationRegisterDevice"]["obs"] = "Envía una notificación por correo cada que un dispositivo se registra y se asocia a un usuario";

/*
$config_var["core"]["show_map"]["type"] = "checkbox";
$config_var["core"]["show_map"]["desc"] = "Show map?";
$config_var["core"]["show_map"]["default"] = false;
$config_var["core"]["show_map"]["obs"] = "Should the core show the user where he is (ex: 'Home &gt; Forum &gt; Forum Name &gt; Topic')";

$config_var["core"]["allow_user_bkp"]["type"] = "checkbox";
$config_var["core"]["allow_user_bkp"]["desc"] = "Backups locales";
$config_var["core"]["allow_user_bkp"]["default"] = false;
$config_var["core"]["allow_user_bkp"]["obs"] = "Permitir que los usuarios hagan backups locales";
*/

// Administración
$config_var["core"]["name_admin"]["type"] = "textbox";
$config_var["core"]["name_admin"]["desc"] = "Admin name";
$config_var["core"]["name_admin"]["default"] = "Webmaster";
$config_var["core"]["name_admin"]["obs"] = "The name that appears at the e-mail activation, sendemail admin function and some other places.";

$config_var["core"]["mail_admin"]["type"] = "textbox";
$config_var["core"]["mail_admin"]["desc"] = "Admin e-mail";
$config_var["core"]["mail_admin"]["default"] = "webmaster@homeland.com.gt";
$config_var["core"]["mail_admin"]["obs"] = "Admin e-mail, that will be used for all e-mails sent from this site.";

$config_var["core"]["sess_timeout"]["type"] = "textbox";
$config_var["core"]["sess_timeout"]["desc"] = "Session Timeout";
$config_var["core"]["sess_timeout"]["default"] = "20";
$config_var["core"]["sess_timeout"]["obs"] = "Tiempo, en minutos, para la expiracion de la sesion.  OJO, las sesiones de PHP duran 24 minutos de forma predeterminada de modo que no sirve de nada poner un numero mayor a 24.";

$config_var["core"]["sess_timeout_notification"]["type"] = "checkbox";
$config_var["core"]["sess_timeout_notification"]["desc"] = "Session Timeout Notification";
$config_var["core"]["sess_timeout_notification"]["default"] = false;
$config_var["core"]["sess_timeout_notification"]["obs"] = "Habilitar ventanita que notifica al usuario que su sesion expiro para que la pueda revivir sin perder su info.";

$config_var["core"]["lang"]["type"] = "listbox";
$config_var["core"]["lang"]["desc"] = "Site language";
$config_var["core"]["lang"]["default"] = "enus";
$config_var["core"]["lang"]["obs"] = "Choose the language for this site.";

// read drectory
$langs = "";
$dir = opendir("lang/") or die( $lang["ERROR_05"] );
while( ($file = readdir($dir))!==false )
{
	if($file!="." && $file!="..") {
		if( preg_match("/^msg_([a-z]+)\.php$/", $file, $regs ) ) {
			if( !empty($langs) ) $langs .= ",";
			$langs .= $regs[1];
		}
	}
}
closedir($dir);
$config_var["core"]["lang"]["values"] = $langs;

$config_var["core"]["theme"]["type"] = "listbox";
$config_var["core"]["theme"]["desc"] = "Site theme";
$config_var["core"]["theme"]["default"] = "simple";
$config_var["core"]["theme"]["obs"] = "Choose the theme for this site.";
// read drectory
$themes = "";
$dir = opendir("themes/") or die( $lang["ERROR_05"] );
$arrThemes = array();
while( ($file = readdir($dir))!==false )
{
	if($file!="." && $file!=".." && $file != "CVS") {
		if( is_dir( "themes/".$file ) ) {
			if( file_exists( "themes/".$file."/main.php" ) ) {
				if( !empty($themes) ) $themes .= ",";
				//$themes .= $file;
				$arrThemes[] = $file;
			}
		}
	}
}
closedir($dir);
$arrThemes = array_flip($arrThemes);
ksort($arrThemes);

$arrThemes = array_flip($arrThemes);
$themes = implode(",", $arrThemes);
$config_var["core"]["theme"]["values"] = $themes;

if( isset($cfg["core"]["theme"]) && !empty($cfg["core"]["theme"]) ) {
	/*CC: Esto debera o podemos cambiarlo para que funcione con los profiles general, y que ya no sean profiles con themes*/
	if( is_dir("themes/{$cfg["core"]["theme"]}/profiles") ) {

		$dirProfiles = opendir("themes/{$cfg["core"]["theme"]}/profiles");
		$profiles = "";
		$arrProfiles = array();
		$arrProfiles[] = "";

		while( ($fileProfiles = readdir($dirProfiles))!==false ) {

			if($fileProfiles!="." && $fileProfiles!=".." && $fileProfiles != "CVS") {
				if( is_dir( "themes/{$cfg["core"]["theme"]}/profiles/".$fileProfiles ) ) {
					if( file_exists( "themes/{$cfg["core"]["theme"]}/profiles/".$fileProfiles."/main.php" ) ) {
						if( !empty($profiles) ) $profiles .= ",";
						//$themes .= $file;
						$arrProfiles[] = $fileProfiles;
					}
				}
			}

		}

		closedir($dirProfiles);
		$arrProfiles = array_flip($arrProfiles);
		ksort($arrProfiles);

		$arrProfiles = array_flip($arrProfiles);
		$profiles = implode(",",$arrProfiles);

		$config_var["core"]["theme_profile"]["type"] = "listbox";
		$config_var["core"]["theme_profile"]["desc"] = "Theme Profile";
		$config_var["core"]["theme_profile"]["default"] = "simple";
		$config_var["core"]["theme_profile"]["obs"] = "Choose the profile for this theme.";
		$config_var["core"]["theme_profile"]["values"] = $profiles;

	}

	/*CContreras 20110214: Esta parte de aqui seran para los profiles generales, es decir profiles que ya no seran definidos por theme,
	*/
	if( is_dir("profiles") ) {
		$dirProfiles = opendir("profiles");
		$profiles = "";
		$arrProfiles = array();
		$arrProfiles[] = "";

		while( ($fileProfiles = readdir($dirProfiles))!==false ) {

			if($fileProfiles!="." && $fileProfiles!=".." && $fileProfiles != "CVS") {
				if( is_dir( "profiles/".$fileProfiles ) ) {
						if( !empty($profiles) ) $profiles .= ",";
						//$themes .= $file;
						$arrProfiles[] = $fileProfiles;

				}
			}

		}


		closedir($dirProfiles);
		$arrProfiles = array_flip($arrProfiles);
		ksort($arrProfiles);

		$arrProfiles = array_flip($arrProfiles);
		$profiles = implode(",",$arrProfiles);

		$config_var["core"]["site_profile"]["type"] = "listbox";
		$config_var["core"]["site_profile"]["desc"] = "Site Profile";
		$config_var["core"]["site_profile"]["default"] = "simple";
		$config_var["core"]["site_profile"]["obs"] = "Choose the profile for this Site.";
		$config_var["core"]["site_profile"]["values"] = $profiles;

	}
}

//ESTO ES PARA LA ADMINISTRACIÓN DE UN THEME INTERNO

$config_var["core"]["theme_interno"]["type"] = "listbox";
$config_var["core"]["theme_interno"]["desc"] = "Theme Interno";
$config_var["core"]["theme_interno"]["default"] = "simple";
$config_var["core"]["theme_interno"]["obs"] = "Theme para la parte interna del sitio";
// read drectory
$themes = "";
$dir = opendir("themes/") or die( $lang["ERROR_05"] );
$arrThemes = array();
$arrThemes[] = "";

while( ($file = readdir($dir))!==false )
{
	if($file!="." && $file!=".." && $file != "CVS") {
		if( is_dir( "themes/".$file ) ) {
			if( file_exists( "themes/".$file."/main.php" ) ) {
				if( !empty($themes) ) $themes .= ",";
				//$themes .= $file;
				$arrThemes[] = $file;
			}
		}
	}
}
closedir($dir);
$arrThemes = array_flip($arrThemes);
ksort($arrThemes);

$arrThemes = array_flip($arrThemes);
$themes = implode(",", $arrThemes);
$config_var["core"]["theme_interno"]["values"] = $themes;

//FIN DE LA ADMINISTRACIÓN DEL THEME INTERNO

//SI EL THEME INTERNO TIENE PROFILES, ENTONCES LOS PUEDO USAR TAMBIEN

if( isset($cfg["core"]["theme_interno"]) && !empty($cfg["core"]["theme_interno"]) ) {

	if( is_dir("themes/{$cfg["core"]["theme_interno"]}/profiles") ) {

		$dirProfiles = opendir("themes/{$cfg["core"]["theme_interno"]}/profiles");
		$profiles = "";
		$arrProfiles = array();
		$arrProfiles[] = "";

		while( ($fileProfiles = readdir($dirProfiles))!==false ) {

			if($fileProfiles!="." && $fileProfiles!=".." && $fileProfiles != "CVS") {
				if( is_dir( "themes/{$cfg["core"]["theme_interno"]}/profiles/".$fileProfiles ) ) {
					if( file_exists( "themes/{$cfg["core"]["theme_interno"]}/profiles/".$fileProfiles."/main.php" ) ) {
						if( !empty($profiles) ) $profiles .= ",";
						//$themes .= $file;
						$arrProfiles[] = $fileProfiles;
					}
				}
			}

		}

		closedir($dirProfiles);
		$arrProfiles = array_flip($arrProfiles);
		ksort($arrProfiles);

		$arrProfiles = array_flip($arrProfiles);
		$profiles = implode(",",$arrProfiles);

		$config_var["core"]["theme_interno_profile"]["type"] = "listbox";
		$config_var["core"]["theme_interno_profile"]["desc"] = "Theme Interno Profile";
		$config_var["core"]["theme_interno_profile"]["default"] = "simple";
		$config_var["core"]["theme_interno_profile"]["obs"] = "Profile para el THEME INTERNO";
		$config_var["core"]["theme_interno_profile"]["values"] = $profiles;

	}

}

//FIN DEL PROFILE PARA EL THEME INTERNO

$config_var["core"]["images_path"]["type"] = "textbox";
$config_var["core"]["images_path"]["desc"] = "Path de imágenes";
$config_var["core"]["images_path"]["default"] = "images";
$config_var["core"]["images_path"]["obs"] = "Path al directorio de imagenes para controles e iconos.";

$config_var["core"]["date_format"]["type"] = "listbox";
$config_var["core"]["date_format"]["desc"] = "Date format";
$config_var["core"]["date_format"]["default"] = "fmtEUR";
$config_var["core"]["date_format"]["obs"] = "How should dates be formatted. (fmtSQL: yyyy-mm-dd, fmtEUR: dd/mm/yyyy, fmtUSA: mm/dd/yyyy).";
$config_var["core"]["date_format"]["values"] = "fmtSQL,fmtEUR,fmtUSA";

$config_var["core"]["municipio"]["type"] = "textbox";
$config_var["core"]["municipio"]["desc"] = "Municipio";
$config_var["core"]["municipio"]["default"] = "Guatemala";
$config_var["core"]["municipio"]["obs"] = "Municipio para escribir en los textos de las fechas.";

$config_var["core"]["AccountRequest"]["type"] = "checkbox";
$config_var["core"]["AccountRequest"]["desc"] = "Solicitud de usuario";
$config_var["core"]["AccountRequest"]["default"] = false;
$config_var["core"]["AccountRequest"]["obs"] = "Habilitar la solicitud publica de usuarios para el sitio";

$config_var["core"]["AccountRequest_type"]["type"] = "textbox";
$config_var["core"]["AccountRequest_type"]["desc"] = "Tipo de usuario solicitado";
$config_var["core"]["AccountRequest_type"]["default"] = "ext_RequestedAccount";
$config_var["core"]["AccountRequest_type"]["obs"] = "Tipo de usuario para las solicitudes publicas de usuario";

$config_var["core"]["AccountRequest_type_internal"]["type"] = "checkbox";
$config_var["core"]["AccountRequest_type_internal"]["desc"] = "Tipo de usuario solicitado son usuarios internos";
$config_var["core"]["AccountRequest_type_internal"]["default"] = false;
$config_var["core"]["AccountRequest_type_internal"]["obs"] = "Para saber que son usuarios internos con acceso al directorio interno";

$config_var["core"]["AccountRequest_manual"]["type"] = "checkbox";
$config_var["core"]["AccountRequest_manual"]["desc"] = "Habilitación manual de cuenta de usuario";
$config_var["core"]["AccountRequest_manual"]["default"] = false;
$config_var["core"]["AccountRequest_manual"]["obs"] = "Habilita la ventana de seleccion de manual de estado de cuentas con correo ya confirmado.";

$config_var["core"]["AccountRequest_email"]["type"] = "textbox";
$config_var["core"]["AccountRequest_email"]["desc"] = "E-Mail con notificacion";
$config_var["core"]["AccountRequest_email"]["default"] = "webmaster@homeland.com.gt";
$config_var["core"]["AccountRequest_email"]["obs"] = "Direccion(es) de correo para notificar que un usuario solicitado públicamente se ha activado.";

$config_var["core"]["force_changed_password"]["type"] = "checkbox";
$config_var["core"]["force_changed_password"]["desc"] = "Cambio de contraseña obligado";
$config_var["core"]["force_changed_password"]["default"] = false;
$config_var["core"]["force_changed_password"]["obs"] = "Esto obliga a que si el usuario no ha cambiando su contraseña, al momento de loguearse lo envia a la pagina de acutalizacion de datos.";

// Array para registrar los modulos disponibles para los grupos inactivos
$arrDGM = array();
/*
$arrDGM["_Normal"]["type"] = "checkbox";
$arrDGM["_Normal"]["desc"] = "Modo normal";
$arrDGM["_Normal"]["default"] = true;
$arrDGM["_Normal"]["obs"] = "El modo normal de operacion es deshabilitar el ingreso a los usuarios del grupo";
*/
// load modules configurations
$dir = opendir("modules/") or die( "No modules dir" );
while( ($file = readdir($dir))!==false )
{
	if($file!="." && $file!="..") {
		if(is_dir("modules/{$file}")) {
			if(file_exists("modules/{$file}/admconfig.php")) {
				include("modules/{$file}/admconfig.php");

				if (isset($cfg['modules'][$file]) && $cfg['modules'][$file]) {
					$arrDGM[$file]["type"] = "checkbox";
					$arrDGM[$file]["desc"] = $file;
					$arrDGM[$file]["default"] = false;
					$arrDGM[$file]["obs"] = $file;
				}
			}
		}
	}
}
closedir($dir);
ksort($arrDGM);
$config_var["_dis_grp_modules"] = $arrDGM;


$config_var["core"]["error_log"]["type"] = "textbox";
$config_var["core"]["error_log"]["desc"] = "Ver error log";
$config_var["core"]["error_log"]["default"] = "";
$config_var["core"]["error_log"]["obs"] =  "Habilita ventana para ver el error log si el textbox se encuentra lleno con el path donde se encuentra";

$config_var["core"]["SEND_MAIL_SMTP"]["type"] = "checkbox";
$config_var["core"]["SEND_MAIL_SMTP"]["desc"] = "Usar SMTP";
$config_var["core"]["SEND_MAIL_SMTP"]["default"] = false;
$config_var["core"]["SEND_MAIL_SMTP"]["obs"] = "Usar SMTP para el envío de correos.";

$config_var["core"]["mailToSendEmail"]["type"] = "textbox";
$config_var["core"]["mailToSendEmail"]["desc"] = "Correo para enviar emails";
$config_var["core"]["mailToSendEmail"]["default"] = "";
$config_var["core"]["mailToSendEmail"]["obs"] = "Correo por medio del cual se debería enviar cualquier email en el sistema";

$config_var["core"]["passToMailToSendEmail"]["type"] = "textbox";
$config_var["core"]["passToMailToSendEmail"]["desc"] = "Acceso al correo de emails";
$config_var["core"]["passToMailToSendEmail"]["default"] = "";
$config_var["core"]["passToMailToSendEmail"]["obs"] = "Contraseña para el correo de emails por medio de SMTP";

$config_var["core"]["currency_label"]["type"] = "textbox";
$config_var["core"]["currency_label"]["desc"] = "Currency label";
$config_var["core"]["currency_label"]["default"] = "Q. ";
$config_var["core"]["currency_label"]["obs"] = "Etiqueta de moneda a mostrar en los valores que se refieran a montos monetarios.";