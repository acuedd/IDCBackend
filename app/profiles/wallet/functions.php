<?php
/**
 * Created by PhpStorm.
 * User: nelsonrodriguez
 * Date: 14/04/2020
 * Time: 15:03 PM
 */
include_once("modules/users/objects/user_merchanting/user_merchanting_model.php");

function getUserMerchanting(){
    $intUserId = $_SESSION["wt"]["uid"];
    $objMerchanting = new user_merchanting_model();
    $arrCommerce = $objMerchanting->getUserCommerceBy('', $intUserId);
    $strCommerceAddress = $arrCommerce["address"];
    $strCommerceZone = $arrCommerce["address_zone"];
    $strCommerceSuburb = $arrCommerce["suburb"];
    $strCommerceTown = $arrCommerce["town"];
    $strCommerceState = $arrCommerce["department"];
    $strCommerceCountry = $arrCommerce["country"];
    $strCommerceFinancial = $arrCommerce["bank_name"];
    $strCommerceName = $arrCommerce["commerce_name"];
    $strCommerceAccount = $arrCommerce["account"];
    $temp = <<<EOD
        <div class="row"> 
            <div class="form-group custom-form-group col-lg-6"> 
                <label for="">Comercio</label>
                <input type="text" class="form-control" value="$strCommerceName" disabled>
            </div>
            <div class="form-group custom-form-group col-lg-6"> 
                <label for="">Direccion</label>
                <input type="text" class="form-control" value="$strCommerceAddress" disabled>
            </div>
            <div class="form-group custom-form-group col-lg-6"> 
                <label for="">Zona</label>
                <input type="text" class="form-control" value="$strCommerceZone" disabled>
            </div>
            <div class="form-group custom-form-group col-lg-6"> 
                <label for="">Colonia o aldea</label>
                <input type="text" class="form-control" value="$strCommerceSuburb" disabled>
            </div>
            <div class="form-group custom-form-group col-lg-6"> 
                <label for="">Municipio</label>
                <input type="text" class="form-control" value="$strCommerceTown" disabled>
            </div>
            <div class="form-group custom-form-group col-lg-6"> 
                <label for="">Departamento</label>
                <input type="text" class="form-control" value="$strCommerceState" disabled>
            </div>
            <div class="form-group custom-form-group col-lg-6"> 
                <label for="">Banco</label>
                <input type="text" class="form-control" value="$strCommerceFinancial" disabled>
            </div>
            <div class="form-group custom-form-group col-lg-6"> 
                <label for="">N°. Cuenta</label>
                <input type="text" class="form-control" value="$strCommerceAccount" disabled>
            </div>
        </div>
        <hr>
EOD;
    return $temp;

}

function getExtraDataDevice(&$arrResponse, $intUserID, $strCodigoSeguridad_E, $strApiVersion)
{
    if(!empty($intUserID)) {
        $objModel = user_merchanting_model::getInstance(array());
        $arrExtraData = $objModel->getInfoDataUserMerchanting($intUserID);
        if(!empty($arrExtraData)){
            foreach ($arrExtraData AS $key => $value){
                if(!is_array($arrResponse)){
                    $arrResponse = array();
                }
                $arrResponse["type_user"] = (!empty($value["type_user"])) ? $value["type_user"] : "";
                $arrResponse["commerce_name"] = (!empty($value["commerce_name"])) ? $value["commerce_name"] : "";
                $arrResponse["bank_name"] = (!empty($value["bank_name"])) ? $value["bank_name"] : "";
                $arrResponse["person_name"] = (!empty($value["person_name"])) ? $value["person_name"] : "";
                $arrResponse["account_number"] = (!empty($value["account_number"])) ? $value["account_number"] : "";
                $arrResponse["address"] = (!empty($value["address"])) ? $value["address"] : "";
                $arrResponse["active"] = (!empty($value["active"])) ? $value["active"] : "";
            }
        }

        //TODO Se verifica el device_auth del usuario y si hay se le asigna al dispositivo
        if(check_module("credit_card")){
            require_once("core/objects/devices/devices_model.php");
            $objDeviceModel = new devices_model();
            $arrCedential = $objDeviceModel->getCredential($intUserID);
            if($arrCedential){
                $objDeviceModel->assignDeviceAuthToDevice($arrCedential["id_deviceauth"], 0, $strCodigoSeguridad_E);
            }
        }
    }
}