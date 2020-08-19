<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 7/02/2017
 * Time: 15:27
 */
include_once("core/global_config.php");
class emulate_model extends global_config implements window_model {
    private static $_instance;
    public function __construct($arrParams){
        parent::__construct($arrParams);
    }

    public static function getInstance($arrParams){
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($arrParams);
        }
        return self::$_instance;
    }

    public function report_users($boolAux, $uidUserAux){
        if (!check_user_class("emularusuario") && !isset($_SESSION["wt"]["originalUserToTest"]))
            die($this->lang["ACCESS_DENIED"]);

        include_once("libs/hml_report/hml_report.php");
        header("Content-Type: text/html; charset=iso-8859-1");
        if($boolAux){
            $strQuery = "SELECT  U.uid, U.name as Usuario,
                                    U.nombres AS Nombres, 
                                    U.apellidos AS Apellidos, 
                                    SWU.descr AS Tipo
                            FROM wt_users AS U 
                                LEFT JOIN wt_swusertypes AS SWU 
                                    ON U.swusertype = SWU.name
                        WHERE U.father = '{$uidUserAux}' AND
                              U.active = 'Y' ¿f? ¿o?";
        }
        else{
            $strQuery = "SELECT  U.uid, U.name as Usuario, U.nombres AS Nombres, U.apellidos AS Apellidos, S.descr AS Tipo
                 FROM    wt_users AS U, wt_swusertypes AS S
                 WHERE   S.name = U.swusertype AND
                         U.active = 'Y' AND
                         U.retirado = 'N' AND
                         U.class <> 'admin' AND
                         U.class <> 'helpdesk' AND
                         U.swusertype <> 'ext_homeland' ¿f? ¿o?";
        }
        //debug::drawdebug($strQuery);
        $arrEncabezado = array();
        $arrParametros = array();

        $arrEncabezado["filter"]["Usuario"] = "U.name";
        $arrEncabezado["filter"]["Nombres"] = "U.nombres";
        $arrEncabezado["filter"]["Apellidos"] = "U.apellidos";
        $arrEncabezado["filter"]["Tipo"] = "S.descr";
        $arrEncabezado["sort"]["Nombres"] = "U.nombres";
        $arrEncabezado["sort"]["Apellidos"] = "U.apellidos";
        $arrEncabezado["sort"]["Tipo"] = "S.descr";
        $arrEncabezado["hidden"]["uid"] = "uid";

        $arrEncabezado["onclick"]["all_row"]["function"] = "setUser";
        $arrEncabezado["onclick"]["all_row"]["params"][] = "uid";
        $arrEncabezado["onclick"]["all_row"]["params"][] = "Nombres";
        $arrEncabezado["onclick"]["all_row"]["params"][] = "Apellidos";

        $arrEncabezado["align"]["Nombres"] = "right";
        $arrEncabezado["align"]["Apellidos"] = "right";
        $arrEncabezado["align"]["Tipo"] = "right";

        $arrParametros["tipo"] = "paginador";
        $arrParametros["btnExportar"] = false;
        $arrParametros["porPagina"] = "5";

        $objPrintRPTest = new hml_report($strQuery, $arrEncabezado, $arrParametros,false,false,true);
        print $objPrintRPTest->dibujarHML_RPT();
    }

    public function setParamsEmulateUser($userTest, $newUser)
    {
        $this->arrParams["hidUserToTest"] = $userTest;
        $this->arrParams["hidUsuarioNuevo"] = $newUser;
        $this->emulate_user();
    }

    public function emulate_user(){
        $intNewUser = (isset($this->arrParams["chkUnsetUserToTest"]))?0:intval($this->arrParams["hidUsuarioNuevo"]);
        if ($intNewUser) {
            if ($_SESSION["wt"]["uid"] != $intNewUser) {
                $intOriginalUser = (isset($_SESSION["wt"]["originalUserToTest"]))?$_SESSION["wt"]["originalUserToTest"]:$_SESSION["wt"]["uid"];
                $strUserType = $_SESSION["wt"]["swusertype"];

                clear_login();
                fill_login($intNewUser);

                // Correr las funcion "onlogin"
                reset($this->cfg['modules']);
                foreach ($this->cfg['modules'] AS $key => $value){
                    $arrModule["key"] = $key;

                    if (check_module($arrModule["key"], false)) {
                        $strFunction = "{$arrModule["key"]}_OnLogIn_Function";

                        if (function_exists($strFunction)) {
                            $strFunction();
                        }
                    }
                }
                reset($this->cfg['modules']);

                $_SESSION["wt"]["originalUserToTest"] = $intOriginalUser;

                $intAddLogMaster = LogInsertWithDetail($intOriginalUser, $strUserType, "Usuario {$intNewUser} emulado por {$intOriginalUser}",
                    "core", "changeUserToTest", "emulate", "emularusuario");

                $strOriginal = sqlGetValueFromKey("SELECT realname FROM wt_users WHERE uid = {$intOriginalUser}");
                $strNuevo = sqlGetValueFromKey("SELECT realname FROM wt_users WHERE uid = {$intNewUser}");
                LogDetailInsert($intAddLogMaster, "UID Original", "wt_users", "uid", $intOriginalUser);
                LogDetailInsert($intAddLogMaster, "Nombre Original", "wt_users", "realname", $strOriginal);
                LogDetailInsert($intAddLogMaster, "UID Emulado", "wt_users", "uid", $intNewUser);
                LogDetailInsert($intAddLogMaster, "Nombre Emulado", "wt_users", "realname", $strNuevo);
            }
        }
        else {
            $this->arrParams["revertUser"] = true;
        }
    }

    public function revert_user(){
        $intNewUser = $_SESSION["wt"]["originalUserToTest"];
        $strUserType = $_SESSION["wt"]["swusertype"];

        clear_login();
        fill_login($intNewUser);

        // Correr las funcion "onlogin"
        reset($this->cfg['modules']);
        foreach($this->cfg['modules'] AS $key => $value){
            $arrModule["key"] = $key;
            $arrModule["value"] = $value;
            if (check_module($arrModule["key"], false)) {
                $strFunction = "{$arrModule["key"]}_OnLogIn_Function";

                if (function_exists($strFunction)) {
                    $strFunction();
                }
            }
        }
        reset($this->cfg['modules']);

        $intAddLogMaster = LogInsertWithDetail($intNewUser, $strUserType, "Usuario {$intNewUser} dejó de emular a usuario {$_SESSION["wt"]["uid"]}",
            "core", "changeUserToTest", "emulate", "emularusuario");

        $strOriginal = sqlGetValueFromKey("SELECT realname FROM wt_users WHERE uid = {$intNewUser}");
        $strEmulado = sqlGetValueFromKey("SELECT realname FROM wt_users WHERE uid = {$_SESSION["wt"]["uid"]}");

        LogDetailInsert($intAddLogMaster, "UID Original", "wt_users", "uid", $intNewUser);
        LogDetailInsert($intAddLogMaster, "Nombre Original", "wt_users", "realname", $strOriginal);
        LogDetailInsert($intAddLogMaster, "UID Emulado", "wt_users", "uid", $_SESSION["wt"]["uid"]);
        LogDetailInsert($intAddLogMaster, "Nombre Emulado", "wt_users", "realname", $strEmulado);

        unset($_SESSION["wt"]["originalUserToTest"]);
    }

    public function getUserAux($userID){
        $strQuery = "SELECT URA.id_user 
                        FROM wt_users AS U 
                            LEFT JOIN wt_users_role_aux AS URA 
                                ON U.uid = URA.id_user_role_aux 
                        WHERE URA.id IS NOT NULL AND URA.id_user_role_aux = '{$userID}'";
        $qTMP = db_query($strQuery);
        $arrResponse = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $nameUsers = $this->getRoleByID($rTMP["id_user"]);
                $rTMP["nombres"] = $nameUsers["nombres"];
                $rTMP["apellidos"] = $nameUsers["apellidos"];
                $arrResponse[$rTMP["id_user"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrResponse;
    }

    public function getRoleByID($userID){
        $strQuery = "SELECT nombres, apellidos
                          FROM wt_users 
                       WHERE uid = '{$userID}'";
        return sqlGetValueFromKey($strQuery);
    }

    public function getFamilyUserAux($IDUserFather){
        $strQuery = "SELECT  U.uid, 
                                    U.nombres, 
                                    U.apellidos, 
                                    SWU.descr
                            FROM wt_users AS U 
                                LEFT JOIN wt_swusertypes AS SWU 
                                    ON U.swusertype = SWU.name
                        WHERE U.father = '{$IDUserFather}'";
        $qTMP = db_query($strQuery);
        $arrResponse = array();
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
                $arrResponse[$rTMP["uid"]] = $rTMP;
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $arrResponse;
    }
}