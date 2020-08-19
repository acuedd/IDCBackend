<?php
/**
 * Created by PhpStorm.
 * User: NelsonMatul
 * Date: 26/09/2017
 * Time: 14:58
 */
include_once("core/global_config.php");
include_once("modules/users/objects/user_roles/user_roles_model.php");
include_once("modules/users/objects/user_roles/user_roles_view.php");
include_once 'modules/users/mod_users_controller.php';

class user_roles_controller extends global_config implements window_controller{
    private $strAction = "";
	private $boolPrintJson = false;
	private $boolUTF8 = true;

    public function __construct($arrParams){
        parent::__construct($arrParams);
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

    public function setStrAction($strAction){
        $this->strAction = $strAction;
    }

    public function main(){
        if(!check_user_class($this->config["admmenu"][$this->lang["USER_ROLES"]]["class"]))die($this->lang["ACCESS_DENIED"]);

        if(isset($this->arrParams["op"])) {
            $option = db_escape($this->arrParams["op"]);
            if($option == "updateRoles"){
                return $this->updateRoles();
            }
            if($option == "fatherBranch"){
                return $this->fatherBranch();
            }
            if($option == "getRoles"){
                return $this->getRoles();
            }
            if($option == "getSuggestion"){
                return $this->getSuggestion();
            }
            if($option == "updateNameRol"){
                return $this->updateNameRol();
            }
            if($option == "deleteRol"){
                return $this->deleteRol();
            }
            if($option == "removeFather"){
                return $this->removeFather();
            }
            if($option == "getUsersExistBeforeDelete"){
                return $this->getUsersExistBeforeDelete();
            }
        }
        $objView = user_roles_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();
    }

    public function removeFather(){
        $objModel = users_roles_model::getInstance($this->arrParams);
        $name = $this->checkParam("name", false, "", true);
        if($objModel->removeFather($name)){
            $objModel->removeFatherUser($name);
            response::standard(1,"Padre removido correctamente.", array(),true, true);
        }
    }

    public function updateRoles(){
        $objModel = users_roles_model::getInstance($this->arrParams);
        $strChild = $this->checkParam("child",false,"",false);
        $father = $this->checkParam("newFather",false,"",true);
        $boolDrop = $this->checkParam("boolChange",false,false);
        $arrcollection = array();
        $this->getChilds($strChild,$arrcollection);
        if(isset($arrcollection[$father])){
            response::standard(0,"No se puede realizar la acción ya que pertenece a la misma familia de roles.",array(),true,true);
        }
        else{
            if($boolDrop == true){
                if($objModel->updateFatherRol($strChild,$father)){
                    response::standard(1,"Se ha actualizado correctamente",array(),true,true);
                }
            }
        }
    }

    public function getChilds($strName, &$arrFamily){
        $objModel = users_roles_model::getInstance($this->arrParams);
        $familyRoles = $objModel->getTreeFamily($strName);
        $arrFamily[$strName] = $strName;
        while($rTMP = db_fetch_assoc($familyRoles)){
            $this->getChilds($rTMP["name"],$arrFamily);
        }
    }

    public function getSuggestion(){
        $rolName = $this->checkParam("rolName",false,"",true);
        if(!empty($rolName)){
            $strNameRol = strtolower($rolName);
            $search = array("á", "é", "í", "ó", "ú","ä", "ë", "ï", "ö", "ü","à", "è", "ì", "ò", "ù","ñ", "-", ",", " ", "'", "\"", "\\");
            $replace = array("a", "e", "i", "o", "u","a", "e", "i", "o", "u","a", "e", "i", "o", "u","n", "", "", "", "", "", "");
            $strNameRol = str_replace($search, $replace, $strNameRol);
            $arrNameRol = explode(" ",$strNameRol);
            $strUser = "";
            if(!empty($arrNameRol[0]))$strUser .= $arrNameRol[0];
            if(!empty($arrNameRol[1]))$strUser .= $arrNameRol[1][0];
            if(!empty($arrNameRol[2]))$strUser .= $arrNameRol[2][0];
            if(!empty($strUser)){
                $boolExist = true;
                $objModel = users_roles_model::getInstance($this->arrParams);
                while($boolExist){
                    $intCount = $objModel->nameExiste($strUser);
                    if(!$intCount) $boolExist = false;
                    else $strUser .= rand(0,99);
                }
                $arr = array();
                $arr["uniqueRolName"] = $strUser;
                return response::standard(1,"Rol automático generado correctamente",$arr,true,true);
            }
            return response::standard(0,"Hubo un problema al generar rol, por favor intente de nuevo.",array(),true,true);
        }
    }

    public function updateNameRol(){
        $objModel = users_roles_model::getInstance($this->arrParams);
        $boolNewRol = $this->checkParam("boolNew",false,"",true);
        /*color está aquí ya que sea nuevo o un rol existente siempre se envía el parámetro*/
        $color = $this->checkParam("color",false,"",true);
        /*$boolNewRol devuelve un string no en formato booleano*/
        if($boolNewRol == "true"){
            $descr = $this->checkParam("descr",false,"",true);
            $name = $this->checkParam("name",false,"",true);
            if( $objModel->insertNewRol($name, $descr, $color) ){
                response::standard(1,"Se ha guardado correctamente.", array(),true,true);
            }
            else{
                response::standard(0,"Ha ocurrido un error al guardar rol.",array(),true,true);
            }
        }
        else{
            $nameAfected = $this->checkParam("nameAfected",false,"",true);
            $newName = $this->checkParam("newName",false,"",true);
            $arrKey["id"] = $this->checkParam("idBranch",false,"",true);
            $arrBranch["name_branch"] = $this->checkParam("branch",false,"",true);
            $auxRole = $this->checkParam("roleAux",false,"",true);
            $intBranch = $arrKey["id"];
            if($arrBranch["name_branch"] != ""){
                if($arrKey["id"] == ""){
                    if($objModel->sql_tableupdate("wt_users_branch_rol",$arrKey,$arrBranch,false,false)){
                        $intBranch = db_insert_id();
                    }
                }
            }
            //actualiza el nombre del rol existente
            if($objModel->updateNameRol($newName,$nameAfected,$color,$intBranch,$auxRole)){
                response::standard(1,"Cambios realizados correctamente.",array(),true,true);
            }
            else{
                response::standard(0,"Ha ocurrido un problema, por favor intenetelo de nuevo.",array(),true,true);
            }
        }
    }

    public function getRoles(){
        $objModel = users_roles_model::getInstance($this->arrParams);
        $arrResponse = array();
        $arrResponse["roles"] = $objModel->getRoles();
        response::standard(1,"Roles",$arrResponse,true,true);
    }

    public function deleteRol(){
        $objModel = users_roles_model::getInstance($this->arrParams);
        $toEliminated = $this->checkParam("beforeName",false,"",true);
        if($objModel->eliminatedRol($toEliminated)){
            $objModel->updateFather(0,$toEliminated);
            $objModel->updateRoleAllUser("",$toEliminated);
            response::standard(1,"Eliminado correctamente",array());
        }
        else{
            response::standard(0,"Ha ocurrido un error, por favor intentelo de nuevo.");
        }
    }

    public function fatherBranch(){
        //por el momento solo se envía la respuesta a los roles que son cabeza de rama
        $objModel = users_roles_model::getInstance($this->arrParams);
        $name = $this->checkParam("uniqueName", false,"",true);
        $arrResponse = array();
        $child = $objModel->getChilds($name);
        $father = $objModel->getFather($name);
        $arrResponse["father"] = $father;
        $arrResponse["childs"] = $child;
        if($child && $father == ""){
            response::standard(1, "Área nueva",$arrResponse,true,true);
        }
        else{
            response::standard(1, "Familia",$arrResponse,true,true);
        }
    }

    public function getUsersExistBeforeDelete(){
        $objModel = users_roles_model::getInstance($this->arrParams);
        $toDelete = $this->checkParam("beforeName",false,"",true);
        $arrResponse["total"] = $objModel->getCountUserExist($toDelete);
        response::standard(1, "Usuarios asignados.",$arrResponse,true,true);
    }
}