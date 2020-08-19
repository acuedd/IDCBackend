<?php
/**
 * Created by PhpStorm.
 * User: nelsonrodriguez
 * Date: 13/04/2020
 * Time: 12:27
 */
require_once("core/global_config.php");

//define("API_CLIENT_ID_TIGOPOS", "anSnDWxX1Xof9YBALSuuJb2zqKKylkr5");   // TEST "kMmK28XAx5FZjlpQz6b67nJyAoeysw7c");
define("API_USER_GET_TOKEN", "anSnDWxX1Xof9YBALSuuJb2zqKKylkr5");   // TEST "kMmK28XAx5FZjlpQz6b67nJyAoeysw7c");
//define("API_CLIENT_SECRET_TIGOPOS", "gGEAxW8eircG8Rd5");  // TEST "iKZhOxBz6t6qaMr3");
define("API_PASS_GET_TOKEN", "jTXAaZb0dDbboUTK");  // TEST "iKZhOxBz6t6qaMr3");
define("API_URL_SEND_MESSAGE", "https://prod.api.tigo.com/oauth/client_credential/accesstoken?grant_type=client_credentials");


class startSMS extends global_config
{
    protected $strProveedorServicio;
    protected $intProveedorServicioKey;
    protected $strError;
    protected $intRetry;
    protected $isServidor;
    protected $strRemitente = "";
    protected $intCodeArea = "502";
    protected $intIdSMS = 0;
    protected $intMaxLength = 145;
    protected $intento = 0;
    protected $boolReturnLength = "";
    protected $organizationID;
    protected $strKeyValidate;
    protected $strKeySecret;
    protected $shortCodeID;
    protected $token;
    protected $username;
    protected $password;
    protected $strURL;
    public $strResponse = "";
    protected $boolStatusMsg = false;

    function __construct($strProveedorServicio = "sms_tigo_pos")
    {

        $this->strProveedorServicio = $strProveedorServicio;

        //Necesito ir a comprobar si el proveedor para el servicio existe y traer la llave para validacion
        $arrProveedorKey = sqlGetValueFromKey("SELECT id, key_validate, key_secret, organization_id, cod_area, max_length, url_send, short_code_id, token, username, password FROM wt_sms_config WHERE descripcion = '{$this->strProveedorServicio}' AND active = 'Y'");
        $this->intMaxLength = $arrProveedorKey["max_length"];
        $this->intProveedorServicioKey = $arrProveedorKey["id"];
        $this->organizationID = $arrProveedorKey["organization_id"];
        $this->strKeyValidate = $arrProveedorKey["key_validate"];
        $this->strKeySecret = $arrProveedorKey["key_secret"];
        $this->shortCodeID = $arrProveedorKey["short_code_id"];
        $this->token = $arrProveedorKey["token"];
        $this->username = $arrProveedorKey["username"];
        $this->password = $arrProveedorKey["password"];
        $this->intCodeArea = $arrProveedorKey["cod_area"];
        $this->strURL = $arrProveedorKey["url_send"];


        //registro en mi log el intento de conexion
        if ($arrProveedorKey == false) {
            $this->strError = true;
            //inserto a error log
            $strErrorDescripcion = "Error al iniciar SMS, El proveedor no existe o esta desactivado";
            $this->insert_error_log($strErrorDescripcion);
        }
        else if ($arrProveedorKey["key_validate"] == "") {
            $this->strError = true;
            //inserto a error log
            $strErrorDescripcion = "Error al iniciar SMS, La clave de autenticacion no se encuentra";
            $this->insert_error_log($strErrorDescripcion);
        }
        else {
            $this->strError = false;
        }
    }

    public function setRemitente($strRemitente)
    {
        $this->strRemitente = $strRemitente;
    }

    protected function do_get_request($strURL, $arrParams)
    {
        $results = do_getOnly_request($strURL, $arrParams, 5, true);
        return $results;
    }

    protected function validate_sms($intNumeroTelefonico = "", $strMensaje = "")
    {
        if (!$this->strError) {
            $arrResponse = array();
            //proceso mi mensaje
            $arrResponse["numero"] = $this->intCodeArea . $intNumeroTelefonico;
            $arrResponse["mensaje"] = strip_tags($strMensaje);
            $arrResponse["lengthMessage"] = $this->validate_message_size($arrResponse["mensaje"]);
            //vuelvo entero mi numero de telefono
            $boolOkNumberFormat = preg_match("/^[0-9]{11}$/", $arrResponse["numero"]);

            //mi variable de errores
            $strErrorDescripcion = "";

            //valido mi numero telefonico
            if ($arrResponse["numero"] == "" && $arrResponse["mensaje"] == "") {
                $strErrorDescripcion = "Error al validar mensaje, Numero telefonico y mensaje vacio";
            }
            else if ($arrResponse["numero"] == "" && $arrResponse["mensaje"] != "") {
                $strErrorDescripcion = "Error al validar mensaje, Numero telefonico vacio";
            }
            else if ($arrResponse["mensaje"] == "" && $arrResponse["numero"] != "") {
                $strErrorDescripcion = "Error al validar mensaje, Mensaje vacio";
            }
            /*else if(!$boolOkNumberFormat){
                $strErrorDescripcion = "Error al validar mensaje, Numero telefonico con formato invalido";
            }*/
            //si no hay ningun error con el mensaje o el numero
            if ($strErrorDescripcion == "") {
                $this->insert_mensaje_log($arrResponse["numero"], $arrResponse["mensaje"]);
                return $arrResponse;
            }
            else {
                //inserto al error log
                $this->addError($strErrorDescripcion);
                $this->insert_error_log($strErrorDescripcion);
                return false;
            }
        }
        else {
            //Quiere decir que ocurrio un al intentar iniciar SMS, no inserto al error log porque ya inserte el error en __construct
            $this->addError("Error al iniciar SMS");
            return false;
        }
    }

    protected function insert_mensaje_log($intNumeroTelefonico = "", $strMensaje = "", $status = "process")
    {
        $intUser = $this->getParam("userid",$this->arrParams["user"], $_SESSION["wt"]["uid"]);
        $strMensaje = db_escape($strMensaje);
        db_query("INSERT INTO wt_sms_mensajes (fecha, hora, destino, mensaje, usuario, proveedor) VALUES (NOW(), NOW(), '{$intNumeroTelefonico}','{$strMensaje}', '{$intUser}', '{$this->intProveedorServicioKey}')");
        $this->intIdSMS = db_insert_id();
    }

    protected function update_mensaje_log($strStatus = "fail", $intReference = 0)
    {
        db_query("UPDATE wt_sms_mensajes SET status = '{$strStatus}', ref = '{$intReference}' WHERE id = {$this -> intIdSMS}");
    }

    protected function insert_error_log($strErrorDescripcion = "")
    {
        $strErrorDescripcion = db_escape($strErrorDescripcion);
        db_query("INSERT INTO wt_sms_error_log (fecha, descripcion, usuario, proveedor, mensaje_id) VALUES (NOW(), '{$strErrorDescripcion}', '{$_SESSION["wt"]["uid"]}', '{$this->intProveedorServicioKey}', '{$this -> intIdSMS}')");
    }

    protected function validate_message_size(&$strMessage)
    {
        $intLengthRemitente = strlen($this->strRemitente);
        $intLengthMessage = strlen($strMessage);
        $intLength = intval($intLengthRemitente) + intval($intLengthMessage) + 1;
        if ($intLength > $this->intMaxLength) {
            if ($this->intento == 0) {
                $this->boolReturnLength = "fail";
                $this->intento++;
                if (!empty($this->strRemitente)) {
                    $this->strRemitente = generateAlias($this->strRemitente, 4, 20);
                    $this->validate_message_size($strMessage);
                }
            }
        }
        else {
            $strMessage = $this->strRemitente . " " . $strMessage;
            $this->boolReturnLength = "ok";
        }
        return $this->boolReturnLength;
    }

    public function search_message_send($intNumeroTelefonico, $strMensaje, $status = "ok", $interval = 5)
    {
        $intNumeroTelefonico = $this->intCodeArea . $intNumeroTelefonico;
        $strMensaje = strip_tags($strMensaje);

        return sqlGetValueFromKey("SELECT   COUNT(*) 
                                   FROM     wt_sms_mensajes 
                                   WHERE    fecha = CURDATE() 
                                            AND hora BETWEEN TIME(DATE_SUB(NOW(), interval {$interval} minute)) AND TIME(NOW())
                                            AND status = '{$status}'
                                            AND destino = '{$intNumeroTelefonico}'
                                            AND mensaje LIKE '%{$strMensaje}%'");
    }

    public function sendSMS($intNumeroTelefonico = "", $strMensaje = "")
    {
        //valido mi sms
        $responseValidate = $this->validate_sms($intNumeroTelefonico, $strMensaje);
        //si todo va ok
        if ($responseValidate) {
            if ($responseValidate["lengthMessage"] == "ok") {
                //compruebo si mi metodo para ese proveedor de servicio existe
                if (method_exists("startSMS", $this->strProveedorServicio)) {
                    //si existe, lo llamo
                    $strNameMethod = $this->strProveedorServicio;
                    return $this->$strNameMethod($responseValidate["numero"], $responseValidate["mensaje"]);
                }
                else {
                    $this->insert_error_log("Error al procesar mensaje, no existen metodos para ese proveedor");
                    $this->addError("Error al procesar mensaje, no existen métodos para el proveedor");
                    return $this->boolStatusMsg;
                }
            }
            else {
                $this->addError("El tamaño del mensaje no puede pasar de {$this -> intMaxLength} caracteres.");
                return $this->boolStatusMsg;
            }
        }
        else {
            return $this->boolStatusMsg;
        }
    }

    public function sms_tigo_pos($intPhoneNumber = 50200000000, $strMessage = "")
    {
        $strMessage = str_replace(["á","é","í","ó","ú","ñ","ä","ë","ï","ö","ü"],
                                    ["a","e","i","o","u","n","a","e","i","o","u"],
                                    $strMessage);

        $strURLApiToken = API_URL_SEND_MESSAGE;
        $arrParams = array(
            "client_id" => API_USER_GET_TOKEN,
            "client_secret" => API_PASS_GET_TOKEN
        );
        $strResponse = do_postOnly_request($strURLApiToken, $arrParams);
        $arrResponse = json_decode($strResponse, true);
        /* //Esto viene en la url que está configurada desde base de datos
         * $arrData["OrganizationiId"] = $this->strKeyValidate;*/
        if (!empty($arrResponse["access_token"])) {
            $strURL = $this->strURL;
            $strAuthorization = "Bearer {$arrResponse["access_token"]}";

            $arrHeader = array(
                "Authorization: {$strAuthorization}",
                "Content-Type: application/json",
                "APIKey: {$this->strKeyValidate}",
                "APISecret: UeAg1FLvuHgCRVAIjp0vAikhB8ee6osXE2JAjnIyCft4Y0Y8wW6rKUY1",
            );
            $arrFields = array(
                "protocol" => "sms",
                "shortcodeId" => "{$this->shortCodeID}",
                "shortcodeType" => "pretty_code",
                "msisdn" => "{$intPhoneNumber}",
                "priority" => 0,
                "body" => "{$strMessage}"
            );

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $strURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 600,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_SSLVERSION => 6,
                CURLOPT_HTTPHEADER => $arrHeader,
                CURLOPT_POSTFIELDS => json_encode($arrFields)
            ));
            $strResponseGetStatus = curl_exec($curl);
            curl_close($curl);
            $arrResponse = json_decode($strResponseGetStatus, true);
            if (!empty($arrResponse["id"])) {
                $this->boolStatusMsg = true;
                $this->strResponse = "Mensaje enviado correctamente, autorización {$arrResponse["id"]}";
                $this->update_mensaje_log("ok");
                return $this->boolStatusMsg;
            }
            else{
                $strMsg = (isset($arrResponse["error"]["description"]))
                    ? $arrResponse["error"]["description"]
                    :"No responde el api para envío de mensajes, contacte con soporte";
                $this->update_mensaje_log("fail");
                $this->addError($strMsg);
                return $this->boolStatusMsg;
            }
        }
        else{
            $this->update_mensaje_log("fail");
            $this->addError("No se pudo obtener el token, contacte con soporte");
            return $this->boolStatusMsg;
        }
    }
}