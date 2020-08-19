<?php

/**
 * Created by PhpStorm.
 * User: alexf
 * Date: 8/02/2017
 * Time: 09:05
 */
include_once("core/global_config.php");
include_once("modules/users/objects/myaccount/myaccount_model.php");
include_once("modules/users/objects/myaccount/myaccount_view.php");
class myaccount_controller extends global_config implements window_controller {

    private $strAction;
    private $boolUTF8 =true;
    private $boolPrintJson = false;

	/**
	 * @return bool
	 */
	public function isBoolUTF8()
	{
		return $this->boolUTF8;
	}

	/**
	 * @param bool $boolUTF8
	 */
	public function setBoolUTF8( $boolUTF8)
	{
		$this->boolUTF8 = $boolUTF8;
	}

	/**
	 * @return bool
	 */
	public function isBoolPrintJson()
	{
		return $this->boolPrintJson;
	}

	/**
	 * @param bool $boolPrintJson
	 */
	public function setBoolPrintJson( $boolPrintJson)
	{
		$this->boolPrintJson = $boolPrintJson;
	}

    public function __construct($arrParams = array()){
        parent::__construct($arrParams);
    }

    public function setStrAction($strAction){
        $this->strAction = $strAction;
    }

    public function main(){
	    if(isset($this->arrParams["op"])) {
		    $option = db_escape($this->arrParams["op"]);
		    if ($option == "avatar") {
			    $this->get_avatar();
		    }
	    }

        if(!isset($_SESSION["wt"]["logged"]) || $_SESSION["wt"]["logged"] != 1)die($this->lang["ACCESS_DENIED"]);

        if(isset($this->arrParams["op"])){
	        $this->setBoolPrintJson(true);
	        $this->setBoolUTF8(true);
            $option = db_escape($this->arrParams["op"]);
            if($option == "save"){
                $this->save_user();
            }
            if($option == "device"){
                $this->save_device();
            }
            if($option == "sAvatar"){
	            return $this->save_avatar(true);
            }
            if($option == "pass"){
                return $this->updatePass();
            }
            if($option == "getRoles"){
                return $this->getRoles();
            }
            if($option == "getUsersByRol"){
                return $this->getUsersByRol();
            }
            if($option == "getFamily"){
	            return response::standard(1,"Datos obtenidos correctamente.", $this->getFamily(),true,true);
            }
            if($option == "saveChild"){
                $this->saveChild();
            }
            if($option == "deleteUserForFather"){
                return $this->deleteUserForFather();
            }
            if($option == "asiggnRoleAux"){
                $this->asiggnRoleAux();
            }
            if($option == "getUserAux"){
                $this->getUserAux();
            }
            if($option == "removeRoleAux"){
                return $this->removeRoleAux();
            }
            if($option == "getFamilyByRolAux"){
                $this->getFamilyByRolAux();
            }
            return;
        }

        $objView = myaccount_view::getInstance($this->arrParams);
        $objModel = myaccount_model::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->setArrPaises($objModel->getPaises());
        $objView->setArrDevices($objModel->getDevices($_SESSION["wt"]["uid"]));
        $infoUser = $objModel->getUsers($_SESSION["wt"]["uid"],[
            "uid","nombres","apellidos","nickname","email","sex","country","tel_cel"
        ]);
	    utf8_encode_array($infoUser);
        $objView->setInfoUser($infoUser);
        $objView->draw();
    }

    public function save_user(){
        $objModel = myaccount_model::getInstance($this->arrParams);
		$intID = isset($this->arrParams["user"]["userid"])?$this->arrParams["user"]["userid"]:0;

        $strTelCel = $this->checkParam("iCelular",false,"");
        $arrKey = array();
        $arrKey["uid"] = $this->checkParam("uid",false,$intID);
        $arrValues = array();
        $arrValues["nombres"] = $this->checkParam("iNombres");
        $arrValues["apellidos"] = $this->checkParam("iApellidos");
        $arrValues["apellidos"] = $this->checkParam("iApellidos");
        $arrValues["nickname"] = $this->checkParam("iUsual");
        $arrValues["realname"] = $this->checkParam("iApellidos") . "," .$this->checkParam("iNombres");
        $arrValues["email"] = $this->checkParam("iCorreo");
        $arrValues["sex"] = $this->checkParam("sSexo");
        $arrValues["country"] = $this->checkParam("sPais");
        $arrValues["tel_cel"] = str_replace(["-"," "], "", $strTelCel) ;

        if($objModel->sql_tableupdate("wt_users",$arrKey,$arrValues)){
            return response::standard(1,"Datos guardados exitosamente",array(),$this->boolUTF8, $this->boolPrintJson);
        }
        else{
	        return response::standard(0,"Hubo un problema al guardar los datos, intente de nuevo",array(),$this->boolUTF8, $this->boolPrintJson);
        }
    }

	public function save_device(){
		$strCampo = $this->checkParam("campo");
		$strValue = $this->checkParam("valor");
		if(!empty($strCampo)){
			$objModel = myaccount_model::getInstance($this->arrParams);
			if($strCampo == "activo" && $strValue == "Y"){
				if($this->cfg["core"]["limit_webservice_devices"]){
					if(count($objModel->getDevices($_SESSION["wt"]["uid"],"Y"))){
						return response::standard(0,"Ya se encuentra un dispositivo activo",array(),true,true);
					}
				}
			}

			$arrKey = array();
			$arrKey["id"] = $this->checkParam("device",false,0);
			$arrValues = array();
			if($strCampo == "eliminado"){
				$arrValues["activo"] = "N";
			}
			$arrValues[$strCampo] = $strValue;

			if($objModel->sql_tableupdate("wt_webservices_devices",$arrKey,$arrValues)){
				response::standard(1,"Datos guardados correctamente",array(),true,true);
			}
			else{
				response::standard(0,"Hubo un problema al guardar los cambios",array(),true,true);
			}
		}
		else{
			response::standard(0,"Hubo un problema al guardar los cambios",array(),true,true);
		}
	}

    public function save_avatar(){
        if(isset($_FILES["iImage"]) && isset($_FILES["iImage"]["tmp_name"])){
            $filename = $_FILES['iImage']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
			$arrExtensions = array(
				"gif" => 1,"png"=> 1,"jpg"=> 1,
				"GIF" => 1,"PNG"=> 1,"JPG"=> 1,
			);
            if( !array_key_exists($ext,$arrExtensions)) {
            	$this->addError("Formato de imagen inválido");
            }
            else{
                $intUID = $this->checkParam("uid",false,$_SESSION["wt"]["uid"]);
                $tempFile = $_FILES['iImage']['tmp_name'];
                $strContents = file_get_contents($tempFile);
                $strContents = db_escape($strContents);
                $objMoel = myaccount_model::getInstance($this->arrParams);
                if(!$objMoel->save_avatar($intUID,$strContents)){
	                $this->addError("Hubo un problema al guardar la imagen, por favor intenta de nuevo");
                }
            }
        }
        else{
        	$this->addError("No existe archivo para cargar á");
        }

	    if($this->hasError()){
		    return response::standard(0,$this->getErrors("string"),array(),$this->boolUTF8,$this->boolPrintJson);
	    }
	    else{
		    return response::standard(1,"Imagen guardada exitosamente",array(),$this->boolUTF8,$this->boolPrintJson);
	    }
    }

    public function get_avatar(){
        $intUid = $this->checkParam("uid",false,0);
        if($intUid){
            $objModel = myaccount_model::getInstance($this->arrParams);
            $strContent = $objModel->getContentAvatar($intUid);
            if(!empty($strContent)){
                header("Content-Type: image/png");
                echo $strContent;
            }
            else{
                $arrInfoUser = $objModel->getUsers($intUid,"sex");
                $strSex = $arrInfoUser["sex"];
                header("Content-Type: image/jpg");
                if(!empty($strSex)){
                    echo file_get_contents(strGetCoreImageWithPath("user_{$strSex}.jpg"));
                }
                else{
                    echo file_get_contents(strGetCoreImageWithPath("user_Male.jpg"));
                }
            }
        }
        else{
            header("Content-Type: image/png");
            echo file_get_contents(strGetCoreImageWithPath("imagennodisponibleGray.jpg"));
        }
    }

    public function updatePass(){
        $intUid = $this->checkParam("uid",false,$_SESSION["wt"]["uid"]);
        $strPass = $this->checkParam("pass");
        if(!empty($intUid) && !empty($strPass)){
            $arrKey = array();
            $arrKey["uid"] = $intUid;
            $arrValues = array();
            $arrValues["password"] = md5($strPass);
            if(!empty($this->cfg["users"]["Save_Unencrypted_pwd"])){
                $arrValues["uepassword"]  = "{$strPass}";
            }
            $objModel = myaccount_model::getInstance($this->arrParams);
            if($objModel->sql_tableupdate("wt_users",$arrKey,$arrValues)){
                return response::standard(1,"Datos guardados correctamente",array(),$this->boolUTF8,$this->boolPrintJson);
            }
            else{
	            return response::standard(0,"No se guardaron los cambios, intente de nuevo",array(),$this->boolUTF8,$this->boolPrintJson);
            }
        }
        else{
	        return response::standard(0,"Faltan parametros",array(),$this->boolUTF8,$this->boolPrintJson);
        }
    }

    public function getRoles(){
        $objModel = myaccount_model::getInstance($this->arrParams);
        $userID = $_SESSION["wt"]["uid"];
        $arrResponse = array();
        $roleByID = $objModel->getRoleByUID($userID);
        $arrRole = $objModel->getInfoRoleByUID($userID);
        $arrResponse["roles"] = $objModel->getChildAndRoleAuxByRol($roleByID);
        $arrResponse["roles"]["roleUser"] = $arrRole["descr"];
        return response::standard(1,"Usuarios obtenidos correctamente",$arrResponse,$this->boolUTF8,$this->boolPrintJson);
    }

    public function getUsersByRol(){
        $objModel = myaccount_model::getInstance($this->arrParams);
        $strRol = $this->checkParam("nameRol",false,"",true);
        $arrResponse = array();

        $arrResponse["usersByRol"][$strRol] = $objModel->getUsersByRol($strRol);
        return response::standard(1,"Usuario obtenido correctamente.",$arrResponse,$this->boolUTF8,$this->boolPrintJson);
    }

    public function getFamily(){
        $objModel = myaccount_model::getInstance($this->arrParams);
        $uid = $_SESSION["wt"]["uid"];
        $arrResponse = array();
        $arrResponse["iAm"] = $this->processUserInfo($objModel->getUsers($uid,array(
	        "uid","nombres","apellidos","swusertype","father"
        )),true);
	    $arrResponse["childs"] = $this->processUserInfo($objModel->getUsers(0,array(
		    "uid","nombres","apellidos","swusertype","father"
	    ),array( "father" => [ "term"=> "{$uid}"] ),true));

        if($arrResponse["iAm"]["father"]){
	        $arrResponse["father"] =  $this->processUserInfo($objModel->getUsers($arrResponse["iAm"]["father"],array(
		        "uid","nombres","apellidos","swusertype","father"
	        )),true);
        }
        else{
	        $arrResponse["father"] = $this->processUserInfo(array());
        }

	    if(!empty($arrResponse["father"]["uid"])){
		    $arrResponse["brothers"] = $this->processUserInfo($objModel->getUsers(0, array("uid","nombres","apellidos","swusertype","father"),
			    array(
			    	"father" => [ "term"=> "{$arrResponse["father"]["uid"]}"]
		        ),
			    true));
		    //debug::drawdebug($objModel->getDebug());
	    }
	    else{
		    $arrResponse["brothers"] = array();
	    }

        $uid = $_SESSION["wt"]["uid"];
        $intIDUserFather = $objModel->getIDUserAuxByFather($uid);
        $arrResponse["userAux"] = array();
        if(!empty($intIDUserFather)){
            $arrReturnUserAux = $objModel->getUserAuxByUserID($intIDUserFather);
            if(!empty($arrReturnUserAux["nombres"])){
                $arrResponse["userAux"][] = $arrReturnUserAux;
            }
        }


        $userAuxFather = $objModel->getUserAuxMyFather($this->getParam("swusertype",$arrResponse["father"],"" ));
        if($userAuxFather){
            /*Primero tenemos que comprobar si el papá del usuario tiene asignado un rol auxiliar*/
            /*SI ES ASÍ:
             * Entonces tenemos que ir a consultar si este ya tiene asignado un usuario con este rol auxiliar, y obtenerlo, luego obtener toda la familia de su papá
             * */
            $userAux = $objModel->getMyUserAux($arrResponse["father"]["uid"]);
            if($userAux){
                $arrResponse["userAuxMyFather"] = $this->processUserInfo($objModel->getUsers($userAux,array(
		            "uid","nombres","apellidos","swusertype","father"
	            )),true);
            }
        }
        return $arrResponse;
    }

    public function processUserInfo($arrData,$boolOne = false){
	    $strBase = core_getBaseDir("N");
	    $strURL = $strBase. "adm_main.php?mde=users&wdw=myaccount&op=avatar&uid=";
	    $arrResponse = array();
    	if($arrData){
    		if(!$boolOne){
			    foreach($arrData AS $key => $value){
			    	$arrTMP = array();
				    $arrTMP["uid"] =$value["uid"];
				    $arrTMP["nombres"] =$value["nombres"];
				    $arrTMP["apellidos"] =$value["apellidos"];
				    $arrTMP["swusertype"] =$value["swusertype"];
				    $arrTMP["father"] =$value["father"];
				    $arrTMP["avatar"] =  $strURL . $value["uid"];
				    array_push($arrResponse, $arrTMP);
			    }
		    }
		    else{
			    $arrResponse["uid"] = $this->getParam("uid",$arrData);
			    $arrResponse["nombres"] = $this->getParam("nombres",$arrData);
			    $arrResponse["apellidos"] = $this->getParam("apellidos",$arrData);
			    $arrResponse["swusertype"] = $this->getParam("swusertype",$arrData);
			    $arrResponse["father"] = $this->getParam("father",$arrData);
			    $arrResponse["avatar"] = $strURL . $arrData["uid"];
		    }

	    }
		return $arrResponse;
    }

    public function saveChild(){
        $objModel = myaccount_model::getInstance($this->arrParams);
        $arrKey["uid"] = $this->checkParam("uidChild",false,"",true);
        $arrFields["father"] = (isset($_SESSION["wt"]["uid"]))?$_SESSION["wt"]["uid"]:$this->arrParams["user"]["userid"];
        if($objModel->sql_tableupdate("wt_users",$arrKey,$arrFields)){
            return response::standard(1,"Usuario asignado correctamente",array(),$this->boolUTF8,$this->boolPrintJson);
        }
	    return response::standard(0,"No se ha podido asignar al usuario");
    }

    public function asiggnRoleAux(){
        $arrKey = array();
        $arrKey["id"] = 0;
        $arrData["id_user_role_aux"] = $this->checkParam("idUserRolAux",false,"",true);
        $arrData["id_user"] = (isset($_SESSION["wt"]["uid"]))?$_SESSION["wt"]["uid"]:$this->arrParams["user"]["userid"];
        if($this->sql_tableupdate("wt_users_role_aux",$arrKey,$arrData)){
            return response::standard(1,"Usuario asignado correctamente",array(),$this->boolUTF8,$this->boolPrintJson);
        }
        return response::standard(0,"No se ha podido asignar al usuario");
    }

    public function getUserAux(){
        $objModel = myaccount_model::getInstance($this->arrParams);
        $uid = $_SESSION["wt"]["uid"];
        $intIDUserFather = $objModel->getIDUserAuxByFather($uid);
        $arrReturnUserAux = $objModel->getUserAuxByUserID($intIDUserFather);
        $arrResponse["userAux"] = array();
        if(!empty($arrReturnUserAux["nombres"])){
            $arrResponse["userAux"][] = $arrReturnUserAux;
        }

        $arrResponse["roles_aux"] = $objModel->getUserAux();
        response::standard(1,"Usuario obtenido correctamente.",$arrResponse,true,true);
    }

    public function deleteUserByRole()
    {
        $idUserFatherAux = $this->checkParam("idUserFatherAux",false,"",true);
        if(!empty($idUserFatherAux)){
            return $this->removeRoleAux();
        }
        else{
            return $this->deleteUserForFather();
        }
    }

    public function deleteUserForFather(){
        $objModel = myaccount_model::getInstance($this->arrParams);
        $userID = $this->checkParam("uid",false,0,true);

        if(!empty($userID)){
            $arrDelete = $objModel->deleteUserFatherByUID($userID);
            if($arrDelete){
                return response::standard(1,"Usuario desasignado correctamente",array(),$this->boolUTF8,$this->boolPrintJson);
            }
        }
        return response::standard(0,"No se realizaron sus cambios, por favor intententelo de nuevo.", array(), $this->boolUTF8, $this->boolPrintJson);
    }

    public function removeRoleAux(){
        $objModel = myaccount_model::getInstance($this->arrParams);
        $idUserFatherAux = $this->checkParam("idUserFatherAux",false,"",true);

        if(!empty($idUserFatherAux)){
            $arrDelete = $objModel->deleteUserAuxByUserFather($idUserFatherAux);
            if($arrDelete){
                return response::standard(1,"Cambios realizados correctamente.",array(),$this->boolUTF8,$this->boolPrintJson);
            }
        }
        return response::standard(0,"No se realizaron sus cambios, por favor intententelo de nuevo.", array(), $this->boolUTF8, $this->boolPrintJson);
    }

    public function getFamilyByRolAux(){
        $objModel = myaccount_model::getInstance($this->arrParams);
        $arrResponse["family"] = $objModel->getInfoFamilyByRolAux($_SESSION["wt"]["uid"]);
        response::standard(1,"Información de usuario obtenida correctamente.",$arrResponse,true,true);
    }

    public function getMyAccount(){
		$intUserId = (isset($_SESSION["wt"]["uid"]))?$_SESSION["wt"]["uid"]:$this->arrParams["user"]["userid"];
	    $objModel = myaccount_model::getInstance($this->arrParams);
	    $roleByID = $objModel->getRoleByUID($intUserId);
    	$arrCountries = $objModel->getPaises();
    	$arrDevices = $objModel->getDevices($intUserId);
		$arrUserInfo = $objModel->getUsers($intUserId,array("name","swusertype","class","nombres","apellidos","realname","nacimiento","sex","country","email","tel_cel as celphone","dateregistered","dateactivated","lastvisit","father"));
	    $arrResponse = array("data"=> array(
						        "countries"=>(count($arrCountries))?$arrCountries:"",
								"devices"=> (count($arrDevices))?$arrDevices:"",
							    "user" => (count($arrUserInfo))?$arrUserInfo: "",
		                        "family" => $this->getFamily(),
		                        "rolesAllowed" => $objModel->getChildAndRoleAuxByRol($roleByID),
						        ));
    	return response::standard(1,"",$arrResponse, $this->boolUTF8, $this->boolPrintJson);
    }

    public function assignRole()
    {
		include_once "modules/users/objects/user_roles/user_roles_model.php";
		$objRolModel = users_roles_model::getInstance(array());
		$intIdUserType = $this->checkParam("idRole",false,0);
		$intUserIdAssign = $this->checkParam("useridToAssign",false,0);
		
		$arrRole = $objRolModel->getInfoByField("id_usertype",$intIdUserType);
		if($objRolModel->isAuxiliar($arrRole["name"])){
			$this->arrParams["idUserRolAux"] = $intUserIdAssign;
			return $this->asiggnRoleAux();
		}
	    else {
	    	$this->arrParams["uidChild"] = $intUserIdAssign;
			return $this->saveChild();
	    }
    }
}