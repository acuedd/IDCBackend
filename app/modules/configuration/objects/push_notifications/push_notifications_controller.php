<?php

include_once "core/global_config.php";
include_once "modules/configuration/objects/push_notifications/push_notifications_model.php";
include_once "modules/configuration/objects/push_notifications/push_notifications_view.php";

class push_notifications_controller extends global_config implements window_controller
{
    private $strAction = "";
    private $boolPrintJSON = false;
    private $boolUTF8 = true;

    public function setStrAction($strAction)
    {
        // TODO: Implement setStrAction() method.
        $this->strAction = $strAction;
    }

    public function setBoolPrintJson($boolPrintJson)
    {
        // TODO: Implement setBoolPrintJson() method.
        $this->boolPrintJSON = $boolPrintJson;
    }

    public function setBoolUTF8($boolUTF8)
    {
        // TODO: Implement setBoolUTF8() method.
        $this->boolUTF8 = $boolUTF8;
    }

    public function main()
    {
        // TODO: Implement main() method.
        if (!check_user_class($this->config["admmenu"][$this->lang["CONFIGURATION_PUSH_NOTIFICATION"]]["class"])) die($this->lang["ACCESS_DENIED"]);
        if ($this->checkParam("op") != "") {
            $this->setBoolPrintJson(true);
            $this->setBoolUTF8(true);
            $op = $this->checkParam('op');

            if ($op == "sendNotification") {
                return $this->sendNotification();
            }

            if($op == "getUser"){
                return $this->getUser();
            }

            if($op == "getApps"){
                return $this->getApps();
            }

            if($op == "postNotification"){
                return $this->postNotification();
            }

            if($op == "getNotificationTypes"){
                return $this->getNotificationTypes();
            }

            if ($op == "getInfoUser") {
                return $this->getInfoUser();
            }

            return;
        }
        $objView = push_notifications_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();
    }

    public function sendNotification()
    {
        if(check_module("users")){
            include_once "modules/users/objects/users/users_controller.php";
            $arrUserSend = $this->arrParams["users"];
            $strType = $this->getParam("notification_type", $this->arrParams, "e");
            $strTitle = $this->getParam("notification_body", $this->arrParams, "");
            $strDescription = $this->getParam("notification_title", $this->arrParams, "");
            $objModel = users_model::getInstance(array());

            $strFiler = "";
            if(!empty($arrUserSend)){
                $intLength = count($arrUserSend);
                $intEnd = 0;
                foreach ($arrUserSend AS $keyUser => $valueUser){
                    $intEnd++;
                    $strComma = ",";
                    if($intEnd == $intLength){
                        $strComma = "";
                    }
                    $strFiler .= " {$valueUser} {$strComma} ";
                }
            }

            $arrAllUsers = $objModel->getUsersSendNotification($strFiler);
            if(!empty($arrAllUsers)){
                if($strType == "e") {//email
                    foreach ($arrAllUsers AS $key => $value) {
                        if(!empty($value["name"])){
                            if(!$this->sendNotificationEmail($value, $strTitle, $strDescription)){
                                $this->addError("Al usuario {$value['dpi']} no se le pudo enviar el correo");
                            }
                        }
                        else {
                            $this->addError("No tiene DPI válido, error del sistema");
                        }
                    }
                }
                elseif ($strType == "s") {//sms
                    if(check_module("sms")){
                        include_once "modules/sms/objects/send_sms/send_sms_controller.php";
                        $objSMS = new sms_send_controller(array(
                            "mensaje" => $strDescription,
                        ));
                        foreach ($arrAllUsers AS $key => $value) {
                            if(!empty($value["name"])){
                                if(!$this->sendNotificationSMS($value, $strTitle, $strDescription, $objSMS)){
                                    $this->addError("Al usuario {$value['dpi']} no se le pudo enviar el mensaje");
                                }
                            }
                            else {
                                $this->addError("No tiene DPI válido, error del sistema");
                            }
                        }
                    }
                    else {
                        return response::standard(0, "No puede enviar información por mensaje.", array(), $this->boolUTF8, $this->boolPrintJSON);
                    }
                }
                elseif ($strType == "n"){//notification
                    foreach ($arrAllUsers AS $key => $value) {
                        if(!empty($value["name"])){
                            if(!$this->sendNotificationPush($value, $strTitle, $strDescription)){
                                $this->addError("Al usuario {$value['dpi']} no le llegó la notificación");
                            }
                        }
                        else {
                            $this->addError("No tiene DPI válido, error del sistema");
                        }
                    }
                }

                if($this->hasError()){
                    return response::standard(1, $this->getErrors("string"), array(), $this->boolUTF8, $this->boolPrintJSON);
                }
                else {
                    return response::standard(1, "Proceso realizado con éxito", array(), $this->boolUTF8, $this->boolPrintJSON);
                }
            }
            else {
                return response::standard(0, "No se encontraron usuarios para enviar su notificación.", array(), $this->boolUTF8, $this->boolPrintJSON);
            }
        }

        return response::standard(0, "No tiene acceso a ver la información de los usuarios", array(), $this->boolUTF8, $this->boolPrintJSON);
    }

    public function sendNotificationEmail($objUser, $strTitle, $strDescription, $arrPaths = [])
    {
        $mail = new AttachMailer(isset($objUser["email"]) ? $objUser["email"] : $objUser["mail"], $strTitle, $strDescription);
        if(!empty($arrPaths)){
            foreach($arrPaths as $key => $path){
                $mail->setAttachFile("{$path}");
                $mail->attachFile("{$path}", "$key.png");
            }
        }
        $boolSend = $mail->send();
        if($boolSend){
            return true;
        }
        else {
            return false;
        }
    }

    public function sendNotificationSMS($objUser, $strTitle, $strDescription, $objSMS)
    {
        if(!empty($objUser["tel_cel"])){
            if($objSMS->send_sms(true, $objUser["tel_cel"])){
                return true;
            }
            else {
                return false;
            }
        }
    }

    public function sendNotificationPush($objUser, $strTitle, $strDescription)
    {

    }

    public function getUser($id = 0, $arrResponse = false){
        $intDpi = $this->checkParam('dpi', false, $id);
        if(check_module('users')){
            include_once("modules/users/objects/users/users_model.php");
            $objModel = new users_model($this->arrParams);
            $users = $objModel->getUsers('','','', $intDpi);
            if($arrResponse){
                return $users;
            }
            return response::standard(1, "Resultado Obtenido", ["data" => $users], $this->boolUTF8, $this->boolPrintJSON);
        }
    }

    public function getApps(){
        include_once("core/objects/app_control/app_control_model.php");
        $objAppModel = new app_control_model();
        $apps = $objAppModel->getApps();
        return response::standard(1, 'Datos obtenidos', ["data" => $apps], $this->boolUTF8, $this->boolPrintJSON);
    }

    public function getNotificationTypes(){
        $arrTypes = array();
        if(check_module('sms')){
            $arrTypes["sms"]["name"] = "SMS";
            $arrTypes["sms"]["value"] = "s";
        }
        if(!empty($this->cfg["core"]["allow_webservice_devices"])) {
            if (check_user_class($this->config["admmenu"][$this->lang["CONFIGURATION_PUSH_NOTIFICATION"]]["class"])) {
                $arrTypes["notification"]["name"] = "Push Notification";
                $arrTypes["notification"]["value"] = "n";
            }
        }
        $arrTypes["mail"]["name"] = "Mail";
        $arrTypes["mail"]["value"] = "m";

        return response::standard(1, 'Tipos de notificacion obtenidos', ["data" => $arrTypes], true, true);
    }

    public function postNotification($strTitle = '', $strBody= '', $strType = 'e', $arrUsers = [], $strAppKey = ''){
        if(!check_module('users')) return response::standard(0, 'No tiene acceso al modulo', [], $this->boolUTF8, $this->boolPrintJSON);
        $strNotificationTitle = $this->checkParam('notification_title', false, $strTitle, true);
        $strNotificationBody = $this->checkParam('notification_body', false, $strBody, true);
        $strNotificationType = $this->checkParam('notification_type', false, $strType);
        $strAppKey = $this->checkParam('application_key', false, $strAppKey);
        $arrUsers = !empty($this->arrParams["users"]) ? $this->arrParams["users"] : $arrUsers;
        $strUsers = '';
        $arrDbUsers = '';
        $arrNotificationResponse = [];

        include_once("modules/users/objects/users/users_model.php");
        $objModel = new users_model($this->arrParams);

        if(!empty($arrUsers)){
            $strUsers = "'".implode("', '", $arrUsers)."'";
            $arrDbUsers = $objModel->getAllUsers($strUsers);
        }
        else{
            $arrDbUsers = $objModel->getAllUsers();
        }

        if($strNotificationType === 'm'){
            foreach ($arrDbUsers as $user){
                if($user["email"]){
                    $boolSentMail = $this->sendNotificationEmail($user, $strNotificationTitle, $strNotificationBody);
                    if($boolSentMail){
                        $this->addError($user['email']);
                    }
                }
            }
        }
        elseif($strNotificationType === 'n'){
            if(!$strAppKey) return response::standard(0, 'Debe enviar el app key', false, $this->boolUTF8, $this->boolPrintJSON);
            $arrIds = [];
            $strIds = "";
            $countUsers = count($arrDbUsers);
            foreach($arrDbUsers as $key => $user){
                if($user){
                    $arrIds[] = $user["uid"];
                    $strIds .= $user["uid"];
                    if($key < ($countUsers -1)){
                        $strIds .= ',';
                    }
                }
            }
            $arrNotificationResponse = $this->sendPushNotification($strAppKey, $strIds, $strNotificationTitle, $strNotificationBody);
        }
        else{
            foreach ($arrDbUsers as $user){
                include_once("modules/sms/objects/send_sms/send_sms_controller.php");
                $objControllerSms = new sms_send_controller();
                if($user["tel_cel"]){
                    $objControllerSms->send_sms(true, $user["tel_cel"], "{$strNotificationTitle} - {$strNotificationBody}");
                }
            }
        }

        if($this->hasError()){
            return response::standard(0, "Ocurrio un error ", ["errors" => $this->getErrors('string')], $this->boolUTF8, $this->boolPrintJSON);
        }

        return response::standard(1, 'Notificación enviada correctamente', ["data" => $arrNotificationResponse], $this->boolUTF8, $this->boolPrintJSON);
    }

    public function sendPushNotification($strApiKey, $arrIds, $strTitle, $strBody)
    {
        $obj = new GCM();
        return $obj->sendNotification($strApiKey, $arrIds, $strTitle, $strBody);
    }

    public function getInfoUser()
    {
        if(check_module("users")){
            include_once "modules/users/objects/users/users_model.php";
            $objModel = users_model::getInstance(array());
            $intUser = $this->getParam("user", $this->arrParams, "");
            $arrUser = $objModel->getInfoUserByName($intUser);
            if(!empty($arrUser)){
                return response::standard(1, "Usuario", array("detail" => $arrUser), $this->boolUTF8, $this->boolPrintJSON);
            }
            return response::standard(0, "No existe usuario", array(), $this->boolUTF8, $this->boolPrintJSON);
        }
        return response::standard(0, "No existe accesos necesarios", array(), $this->boolUTF8, $this->boolPrintJSON);
    }
}