<?php
/**
 * Created by PhpStorm.
 * User: NelsonRodriguez
 * Date: 23/04/2019
 * Time: 11:14 AM
 */

include_once("modules/configuration/objects/administration/administration_model.php");
include_once("modules/configuration/objects/administration/administration_view.php");
class administration_controller extends global_config implements window_controller
{
    private $strAction = "";
    private $boolPrintJson = false;
    private $boolUTF8 = true;

    public function __construct($arrParams = array())
    {
        parent::__construct($arrParams);
        $this->strDomain = core_getBaseDomain();
    }

    public function setStrAction($strAction)
    {
        // TODO: Implement setStrAction() method.
        $this->strAction = $strAction;
    }

    public function setBoolPrintJson($boolPrintJson)
    {
        // TODO: Implement setBoolPrintJson() method.
        $this->boolPrintJson = $boolPrintJson;
    }

    public function setBoolUTF8($boolUTF8)
    {
        // TODO: Implement setBoolUTF8() method.
        $this->boolUTF8 = $boolUTF8;
    }

    public function main()
    {
        // TODO: Implement main() method.
        if (!check_user_class($this->config["admmenu"][$this->lang["NOTIFICATION_ADMIN"]]["class"])) die($this->lang["ACCESS_DENIED"]);
        $this->setBoolUTF8(true);
        $this->setBoolPrintJson(true);
        $option = $this->checkParam("op");
        if (!empty($option)) {
            if($option === "getNotificationsActive"){
                return $this->getNotificationsActive();
            }
            if($option === "deleteNotification"){
                return $this->deleteNotification();
            }
            if($option === "getSwUserTypes"){
                return $this->getSwUserTypes();
            }
            if($option === "saveNotification"){
                return $this->saveNotification();
            }
        }

        $objView = administration_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();
    }

    public function getNotificationsActive()
    {
        $objModel = administration_model::getInstance($this->arrParams);
        $arrNotificationsExist = $objModel->getNotificationsExist();

        if(!empty($arrNotificationsExist)){
            return response::standard(1, "Notificaciones obtenidas correctamente.", ["notifications" => $arrNotificationsExist], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "No hay notificaciones a mostrar", [], $this->boolUTF8, $this->boolPrintJson);
    }

    public function deleteNotification()
    {
        $intIDNotification = $this->checkParam('notification', false, 0);

        if(!empty($intIDNotification)){
            $objModel = administration_model::getInstance($this->arrParams);
            if($objModel->deleteNotificationByID($intIDNotification)){
                $arrNotificationsExist = $objModel->getNotificationsExist();
                return response::standard(1, "Notificación eliminaca correctamente", ["notifications" => $arrNotificationsExist], $this->boolUTF8, $this->boolPrintJson);
            }
        }

        return response::standard(0, "No se pudo eliminar la notificación", [], $this->boolUTF8, $this->boolPrintJson);
    }

    public function getSwUserTypes()
    {
        if(check_module('users')){
            $arrProfiles = [];
            $arrUsersProfiles = [];
            if( file_exists("modules/users/objects/users/users_controller.php") ){
                include_once "modules/users/objects/users/users_controller.php";
                $objUsers = new users_controller($this->arrParams);
                $arrProfiles = $objUsers->getSWUserTypes();
                $arrUsersProfiles = $this->getWindows();
            }
            return response::standard(1, "Perfiles obtenidos correctamente", ["profiles" => $arrProfiles, "windows" => $arrUsersProfiles], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "No tiene acceso al módulo de usuarios, contacte primero con soporte para verificar el error");
    }

    public function getWindows()
    {
        $arrReturn = [];
        foreach($this->config["admmenu"] AS $key => $value){
            if(!empty($value["name"])){
                $arrReturn[] = $value;
            }
        }

        return $arrReturn;
    }

    public function saveNotification()
    {
        $intNotification = $this->checkParam('notification', false, 0);
        $arrKey = [];
        $arrKey["id"] = $intNotification;
        $arrValue = [];

        $arrValue["title"] = $this->checkParam("titleNotification", false, "", true);
        $arrValue["message"] = $this->checkParam("descriptionNotification", false, "", true);
        $arrValue["url_window"] = $this->checkParam("windowNotification", false, "", true);
        $arrValue["sw_user_type"] = $this->checkParam("strProfileAccess", false, "", true);
        $arrValue["key_char_to_draw"] = $this->checkParam("keyCharNotification", false, "", true);

        $strFilterNotification = $this->checkParam("printFilterDateNotification", false, "off");
        $boolNotification = ($strFilterNotification == "on")?"Y":"N";
        $arrValue["bool_filter_date"] = $boolNotification;

        $arrValue["date_to"] = $this->checkParam("dateToNotification", false, "");
        $arrValue["date_from"] = $this->checkParam("dateFromNotification", false, "");
        $arrValue["class_style_notification"] = "notificationDefault";

        if($this->sql_tableupdate("wt_notification_admin", $arrKey, $arrValue)){
            return response::standard(1, "Datos guardados correctamente.", [], $this->boolUTF8, $this->boolPrintJson);
        }

        return response::standard(0, "No se pudo guardar la notificaión, por favor contacte a soporte", [], $this->boolUTF8, $this->boolPrintJson);
    }

}