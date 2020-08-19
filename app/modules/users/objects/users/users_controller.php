<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 16/02/2017
 * Time: 12:18
 */
include_once("modules/users/objects/user_profile/user_profile_controller.php");
include_once("modules/users/objects/users/users_model.php");
include_once("modules/users/objects/users/users_view.php");
class users_controller extends user_profile_controller implements window_controller{

	private $boolPrintJson = false;
	private $boolUTF8 = true;
	private $strAction = "";

    public function __construct($arrParams){
        parent::__construct($arrParams);
    }

    public function setStrAction($strAction){
        $this->strAction = $strAction;
    }

	/**
	 * @param bool $boolPrintJson
	 */
	public function setBoolPrintJson( $boolPrintJson)
	{
		$this->boolPrintJson = $boolPrintJson;
	}

	/**
	 * @param bool $boolUTF8
	 */
	public function setBoolUTF8( $boolUTF8)
	{
		$this->boolUTF8 = $boolUTF8;
	}

    public function main(){
        if(!check_user_class($this->config["admmenu"][$this->lang["ADM_USERS"]]["class"]))die($this->lang["ACCESS_DENIED"]);
        if(isset($this->arrParams["op"])) {
            $this->boolPrintJson = true;
            $this->boolUTF8 = true;
            $option = db_escape($this->arrParams["op"]);
            if($option == "saveUser"){
                return $this->saveUser();
            }
            if($option == "roles"){
                $objModel = users_model::getInstance($this->arrParams);
                $arrTMP["roles"] = $objModel->getRoles();
                return response::standard(1,"Roles",$arrTMP,true,true);
            }
            if($option == "tags"){
                $objModel = users_model::getInstance($this->arrParams);
                $term = $this->checkParam("term");
                $arrTMP["tags"] = $objModel->getTags(0,$term);
                return response::standard(1,"Tags",$arrTMP,true,true);
            }
            if($option == "profiles"){
                $arrTMP["profiles"] = $this->getProfiles();
                return response::standard(1,"perfiles",$arrTMP,true,true);
            }
            if($option == "saveTag"){
                return $this->saveTag();
            }
            if($option == "suggestion"){
                return $this->suggestUser();
            }
            if($option == "rolAsign"){
                return $this->rolAsign();
            }
            if($option == "getRolSpecific"){
                return $this->getRolSpecific();
            }


            if($option == "getUserSelected"){
                return $this->getUsers();
            }
        }

        if(!empty($this->arrParams["data"])){
            return $this->getUsersReport();
        }

        if(!empty($this->arrParams["online"])){
	        $objModel = users_model::getInstance($this->arrParams);
	        $objView = users_view::getInstance($this->arrParams);
	        $objView->setStrAction($this->strAction);
	        $objView->drawOnline($objModel->getUsersOnline());
	        die();
        }

        $objView = users_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();
    }

    public function rolAsign(){
        $objModel = users_model::getInstance($this->arrParams);
        $intRol = $this->checkParam("rol",false,"");
        $arrParents = array();
        $arrParents["father"] =  $objModel->getRol($intRol);
        $nameSelectedRol = $arrParents["father"][$intRol]["name"];
        $arrParents["childs"] = $objModel->getChildByRol($nameSelectedRol);
        response::standard(1,"Usuarios relacionados obtenidos correctemente.",$arrParents,true,true);
    }

    public function getRolSpecific(){
        $objModel = users_model::getInstance($this->arrParams);
        $strRol = $this->checkParam("srchRol",false,"");
        $strUser = $this->checkParam("cointidity",false,"");
        $arrUser = array();
        $arrUser["users"] = $objModel->getUserCointidityRol($strRol,$strUser);
        response::standard(1,"Usuarios relacionados.",$arrUser,true,true);
    }

    public function getUsersReport(){
        $objModel = users_model::getInstance($this->arrParams);
        $arrTMP = array();
        $arrTMP["users"] = $objModel->getUsersReport();
        response::standard(1,"usuarios",$arrTMP,true,true);
    }

    public function getUsers(){
        $objModel = users_model::getInstance($this->arrParams);
        $uid = $this->checkParam("uidSelected",false,0);
        $arrTMP["users"] = $objModel->getUsers($uid,"","");
        response::standard(1,"usuarios",$arrTMP,true,true);
    }

    public function saveUser(){
        $objModel = users_model::getInstance($this->arrParams);
        $intUser = $this->checkParam("iUserid",false,0); // viene 0
        $strUser = $this->checkParam("iUser",false,'',true); // viene string vacío
        $strPass = $this->checkParam("iPass",false,"");

        if(!$objModel->nameExiste($strUser) || $intUser){
            $arrKey = array();
            $arrValues = array();
            $arrKey["uid"] = $intUser;
            $arrValues["name"] = $strUser;
            $arrValues["nickname"] = $strUser;

            if($objModel->userExist($intUser) && !empty($strPass)){
                $arrValues["password"] = md5($strPass);
            }
            else if(!$objModel->userExist($intUser)){
                if(!empty($strPass)){
                    $arrValues["password"] = md5($strPass);
                }
                else{
                    $objPass = password::getInstance();
	                $strPass = $objPass->generate_humanpass();
                    $arrValues["password"] = md5($strPass);
                }
            }
            if(!empty($this->cfg["users"]["Save_Unencrypted_pwd"])){
                $arrValues["uepassword"]  = "{$strPass}";
            }
            $strTelCel = $this->checkParam("iPhone",false,"");
            $arrValues["nombres"] = $this->checkParam("iNames",false,"", $this->boolUTF8);
            $arrValues["apellidos"] = $this->checkParam("iLast",false,"", $this->boolUTF8);
            $arrValues["realname"] = $arrValues["nombres"] . ", " . $arrValues["apellidos"];
            $arrValues["tel_cel"] = str_replace(["-"," "], "", $strTelCel) ;
            $arrValues["email"] = $this->checkParam("iMail",false,"");
            $arrValues["sex"] = $this->checkParam("chkSex",false,"Male");
            $arrValues["active"] = $this->checkParam("chkActive",false,"Y");
            $arrValues["sex"] = $this->checkParam("chkSex",false,"Male");
            $arrValues["allow_multi_session"] = $this->checkParam("chkMulti",false,"N");
            $swUserType = $this->checkParam("selectRolUser",false,"");
            $strRol = $objModel->getRolById($swUserType);
            $arrValues["swusertype"] = $strRol;

            if($objModel->sql_tableupdate("wt_users",$arrKey,$arrValues)){
                /*OBTENEMOS EL ID DEL INSERT O MODIFICACIÓN*/
                $userid = ($intUser)?$intUser:db_insert_id();

                /*tengo que hacer la asignación de los usuarios*/
                $getExistUserRol = $objModel->getExistUserRol($userid);
                if($getExistUserRol != "0"){
                    $objModel->reAsignRol($userid,$swUserType);
                }
                else{
                    $objModel->asigRol($userid,$swUserType);
                }

                $objModel->deleteFatherAsignUser($userid);
                $objModel->deleteChildAsignUser($userid);

                /*$arrValues["swusertype"] devuelve el unique del rol al que acabamos de asignar al usuario*/
                $strFatherRole =  $objModel->getPositionRol($arrValues["swusertype"]); /*aquí tenemos el string del unique del padre (si tiene)*/
                /*puedo obtener el papá o el rol desde aquí??*/



                if(!empty($this->arrParams["saveRolAsignUser"])){
                    foreach ($this->arrParams["saveRolAsignUser"] AS $val){
                        $val = intval($val);
                        $responseFather = $objModel->getUsersFather($userid,$strFatherRole,$val);
                        $intFather = intval($responseFather);
                        if($val == $intFather){
                            $objModel->asigFather($userid,$intFather);
                        }
                        else{
                            $objModel->asigChild($userid,$val);
                        }
                        unset($val);
                    }
                }

                //Guardo tags
                $objModel->deleteTags($userid);
                if(!empty($this->arrParams["txtTags"])){
                    foreach($this->arrParams["txtTags"] AS $val){
                        $val = intval($val);
                        $objModel->asigTags($userid,$val);
                        unset($val);
                    }
                }

                //Guardo perfil de acceso
                if(!empty($this->arrParams["sltProfiles"])){
                    $profile = intval($this->arrParams["sltProfiles"]);
                    $objModel->asigProfileUser($userid,$profile);
                }
                $arrTMP["userid"] = $userid;

                return response::standard(1,"Usuario guardado correctamente",$arrTMP,$this->boolUTF8,$this->boolPrintJson);
            }
            else{
                return response::standard(0,"Ocurrió un problema al guardar el usuario, por favor intentelo de nuevo.",array(),$this->boolUTF8,$this->boolPrintJson);
            }
        }
        else{
            return response::standard(0,"El usuario ya existe, por favor utilice otro.",array(),$this->boolUTF8,$this->boolPrintJson);
        }
    }

    public function saveTag(){
        $intTag = $this->checkParam("idTag",false,0);
        $strName = $this->checkParam("name", false,"" ,true);
        $strColor = $this->checkParam("color",false,"ffffff");

        $arrKey = array();
        $arrValues = array();

        $arrKey["id"] = $intTag;

        $arrValues["tag"] = $strName;
        $arrValues["color"] = $strColor;

        $objModel = users_model::getInstance($this->arrParams);
        if($objModel->sql_tableupdate("wt_user_tags",$arrKey,$arrValues)){
            response::standard(1,"Datos guardados correctamente",array(),true,true);
        }
        else{
            response::standard(0,"Hubo un problema al guardar, intente de nuevo",array(),true,true);
        }
    }

    public function suggestUser(){
	    $strNames = $this->checkParam("name",false,"",true);
        $strLasts = $this->checkParam("lastname",false,"",true);

	    if(!empty($strNames) && !empty($strLasts)){
		    $strNames = strtolower($strNames);
            $strLasts = strtolower($strLasts);
            $search = array("á", "é", "í", "ó", "ú","ä", "ë", "ï", "ö", "ü","à", "è", "ì", "ò", "ù","ñ", "-", ",", " ", "'", "\"", "\\");
            $replace = array("a", "e", "i", "o", "u","a", "e", "i", "o", "u","a", "e", "i", "o", "u","n", "", "", "", "", "", "");

		    $strNames = str_replace($search, $replace, $strNames);
		    $strLasts = str_replace($search, $replace, $strLasts);

            $arrNames = explode(" ",$strNames);
            $arrLasts = explode(" ",$strLasts);

            $strUser = "";
            //Nombres
            if(!empty($arrNames[0]))$strUser .= $arrNames[0][0];
            if(!empty($arrNames[1]))$strUser .= $arrNames[1][0];
            if(!empty($arrNames[2]))$strUser .= $arrNames[2][0];
            //Apellidos
            if(!empty($arrLasts[0]))$strUser .= $arrLasts[0];
            if(!empty($arrLasts[1]))$strUser .= $arrLasts[1][0];
            if(!empty($arrLasts[2]))$strUser .= $arrLasts[2][0];

            if(!empty($strUser)){
                $boolExist = true;
                $objModel = users_model::getInstance($this->arrParams);
                while($boolExist){
                    $intCount = $objModel->nameExiste($strUser);
                    if(!$intCount) $boolExist = false;
                    else $strUser .= rand(0,9);
                }

                $arr = array();
                $arr["username"] = $strUser;
                $objPass = password::getInstance();
                $arr["password"] = $objPass->generate_humanpass();
                return response::standard(1,"Usuario automático generado correctamente",$arr,true,true);
            }
            return response::standard(0,"Hubo un problema al generar usuario y contraseña, por favor intente de nuevo.",array(),true,true);
        }
        return response::standard(0,"Nombre y/o apellidos se encuentran vacios.",array(),true,true);
    }
}