<?php
/**
 * Created by PhpStorm.
 * User: nelsonrodriguez
 * Date: 14/04/2020
 * Time: 15:02 PM
 */
$strProfile = $cfg["core"]["site_profile"];
include_once "profiles/$strProfile/functions.php";

$config["extra_access"]["users/commerce_edit"] = array("module" => "Site Admin", "descripcion" => "Acceso para editar comercios");
$config["extra_access"]["users/commerce_delete"] = array("module" => "Site Admin", "descripcion" => "Acceso para eliminar comercios");
/*$config["extra_access"]["credit_card/make_payment"] = array("module" => "Site Admin", "descripcion" => "Accesso para hacer pago con comercio");
$config["extra_access"]["credit_card/history"] = array("module" => "Site Admin", "descripcion" => "Accesso para ver transacciones");*/
$config["extra_access"]["users/commerce_create"] = array("module" => "Site Admin", "descripcion" => "Accesso para crear comercio");
$config["extra_access"]["users/commerce_notify"] = array("module" => "Site Admin", "descripcion" => "Accesso para añadir a lista envio a correos cada vez que se registra un usuario");
$config["extra_access"]["users/commerce_notify_resend"] = array("module" => "Site Admin", "descripcion" => "Accesso de reenviar comercio a mails de la lista");
$config["extra_access"]["users/afilation_delete"] = array("module" => "Site Admin", "descripcion" => "Acceso para eliminar");
/* como solo se mostrara para este proyecto */
$config["extra_access"]["credit_card/graphics"] = array("module" => "Site Admin", "descripcion" => "Acceso para ver gráficas");
$config["extra_access"]["credit_card/remove_transaction"] = array("module" => "Site Admin", "descripcion" => "Accesso para anular transacciones");

$config["extra_access"]["create_gerentes"] = array("module" => "Site Admin", "descripcion" => "Acceso que permite crear gerentes");
$config["extra_access"]["create_others"] = array("module" => "Site Admin", "descripcion" => "Acceso que permite crear supervisores y vendedores");
$config["extra_access"]["unassign_family"] = array("module" => "Site Admin", "descripcion" => "Acceso para eliminar miembros de su familia.");

$lang["TARJETA_CREDITO_TITLE"] = "Bono Familiar";
$lang["LOGIN_NAME"] = "DPI";
$lang["USER_LOSTPASS_MSG1"] = "Introduce el DPI que utilizas para iniciar sesión.";