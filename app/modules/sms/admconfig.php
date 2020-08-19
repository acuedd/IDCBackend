<?php
/**
 * Created by PhpStorm.
 * User: nelsonrodriguez
 * Date: 13/04/2020
 * Time: 12:31
 */
$config_var["modules"]["sms"]["type"] = "checkbox";
$config_var["modules"]["sms"]["desc"] = "Mensajeria SMS";
$config_var["modules"]["sms"]["default"] = false;
$config_var["modules"]["sms"]["obs"] = "Módulo para mensajeria sms";
$config_var["sms"]["Remitente"]["type"] = "textbox";
$config_var["sms"]["Remitente"]["desc"] = "Remitente";
$config_var["sms"]["Remitente"]["default"] = "HML:";
$config_var["sms"]["Remitente"]["obs"] = "Remitente a mostrar en los mensajes SMS";