<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once("core/main.php");

$index_page = false;
$page_name = "ERROR LOG";

if(!check_user_class($config["admmenu"][$lang["ERROR_LOG"]]["class"])) die($lang["ACCESS_DENIED"]);
if(empty($cfg["core"]["error_log"])) die($lang["ACCESS_DENIED"]);
if(!check_user_class("admin")) die($lang["ACCESS_DENIED"]);

draw_header();
theme_draw_centerbox_open($page_name);
?>
<style>
    .error-log{
        margin-top: 50px;
    }
    .field-textbox{
        width: 100%;
        font-size: 18px;
        height: 550px;
        line-height: 10px;
        overflow: auto;
    }
    .col-lg-12{
        font-size: 16px;
    }
</style>
<div class="error-log">
    <h3>
        Esta función muestra los ultimos 50 errores del sitio.<br>
        Este error log solo se activa mediante un crontab en linux.
        <b onclick="$('#codCrontab').toggle()" style="cursor: pointer;">Ver código</b>
    </h3>
    <div id="codCrontab" style="display: none;font-size: 16px;border: 1px solid #DADADA;padding: 10px;background: #EDEFF0;">
        <b>paso 1</b>: crear un archivo bash (sh) <br>
        <b>paso 2</b>: pegar el siguiente codigo en el archivo bash<br><br>

          #! /bin/bash<br>
          # Script de eacu@homeland.com.gt<br><br><br>


          echo "" > /home/webmaster/pruebas/htdocs/_preview_error_log<br>
          tail -50 /home/webmaster/pruebas/htdocs/error_log > /home/webmaster/pruebas/htdocs/_preview_error_log<br>
        <b>paso 3</b>: editar el crontab (crontab -e)<br>
          SHELL=/bin/bash<br>
          PATH=/sbin:/bin:/usr/sbin:/usr/bin<br>
          MAILTO=eacu@homeland.com.gt<br>
          HOME=/<br>
          */3 * * * * /home/webmaster/pruebas/homeland.sh
    </div>
    <h1>Ultimos 50 mensajes en orden descendente.</h1>
    <div class="container-fluid">
        <div class="row" style="border: 1px solid #DADADA;padding: 5px;background: #EDEFF0;overflow-x: auto;">
            <?php
            $strPath3 = trim($cfg["core"]["error_log"]);
            $lineas = array_reverse(file($strPath3));
            foreach ($lineas as $linea){
                ?>
                <div class="col-lg-12 box-asterisco">
                    <b style="font-size: 18px">></b>
                    <?php
                    /*$arrLinea = explode("]", $linea);
                    $arrLinea[0] = str_replace("[", "", $arrLinea[0]);
                    print "<b>{$arrLinea[0]}</b>";
                    print $arrLinea[1];*/
                    print $linea;
                    ?>
                </div>
                <?php
                unset($linea);
            }
            ?>
        </div>
    </div>
</div>
<?php
theme_draw_centerbox_close();
draw_footer();