<?php
/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 07/06/17
 * Time: 15:07
 */
$config_var["modules"]["users"]["type"] = "checkbox";
$config_var["modules"]["users"]["desc"] = "Usuarios";
$config_var["modules"]["users"]["default"] = false;
$config_var["modules"]["users"]["obs"] = "Módulo donde se gestiona todo lo referente a los usuarios";

$config_var["users"]["Save_Unencrypted_pwd"]["type"] = "checkbox";
$config_var["users"]["Save_Unencrypted_pwd"]["desc"] = "Unencrypted Password";
$config_var["users"]["Save_Unencrypted_pwd"]["default"] = false;
$config_var["users"]["Save_Unencrypted_pwd"]["obs"] = "Permite guardar una copia del password desencryptado";
