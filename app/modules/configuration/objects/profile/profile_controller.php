<?php
include_once("core/global_config.php");
include_once("modules/configuration/objects/profile/profile_model.php");
include_once("modules/configuration/objects/profile/profile_view.php");
class profile_controller extends global_config implements window_controller
{
    private $boolPrintJson = false;
    private $boolUTF8 = true;
    private $strAction = "";

    public function setStrAction($strAction)
    {
        $this->strAction = $strAction;
    }

    public function setBoolPrintJson($boolPrintJson)
    {
        $this->boolPrintJson = $boolPrintJson;
    }

    public function setBoolUTF8($boolUTF8)
    {
        $this->boolUTF8 = $boolUTF8;
    }
    public function main()
    {
        if (!check_user_class($this->config["admmenu"][$this->lang["CONFIGURATION_COLORS"]]["class"])) die($this->lang["ACCESS_DENIED"]);
        if($this->checkParam("op") != ""){
            $this->setBoolPrintJson(true);
            $this->setBoolUTF8(true);
            $option = $this->checkParam('op');
            if($option == "getByType"){
                return $this->getByType();
            }
            if($option == "getMenuItems"){
                return $this->getMenuItems();
            }
            if($option == "updateCaptionAdmin"){
                return $this->updateCaptionAdmin();
            }
            if($option == "moveMenuItemOrder"){
                return $this->moveMenuItemOrder();
            }
            if($option == "deleteMenuItem"){
                return $this->deleteMenuItem();
            }
            if($option == "updateMenuItem"){
                return $this->updateMenuItem();
            }
            if($option == "getAllCaptions"){
                return $this->getAllCaptions();
            }
            if($option == "saveColor"){
                return $this->saveColor();
            }
            if($option == "saveImage"){
                return $this->saveImage();
            }
            if($option == "deleteColor"){
                return $this->deleteColor();
            }
            if($option == "setDefault"){
                return $this->setDefault();
            }
            if($option == "deleteImage"){
                return $this->deleteImage();
            }
            if($option == "getImageCorreo"){
                return $this->getImageCorreo();
            }
            if ($option == "saveImageCorreo"){
                return $this->saveImageCorreo();
            }
            if ($option == "deleteImageCorreo"){
                return $this->deleteImageCorreo();
            }
            if ($option == "saveAllow"){
                return $this->saveAllow();
            }
            if ($option == "saveNewRegister"){
                return $this->saveNewRegister();
            }
            if ($option == "deleteLinkCorreo"){
                return $this->deleteLinkCorreo();
            }
            if ($option == "EditRegister"){
                return $this->EditRegister();
            }
            if ($option == "editData"){
                return $this->editData();
            }
            if ($option == "getTitle"){
                return $this->getTitle();
            }

            return false;
        }

        $objModel = profile_model::getInstance();
        $objView = profile_view::getInstance($this->arrParams);
        $objView->setStrAction($this->strAction);
        $objView->draw();
    }
    public function saveColor(){
        global $cfg;
        $style = "";
        $arrColor = json_decode($this->arrParams["color"], true);
        $menu_background_color = !empty($arrColor["color-menu"]["color"]) ? $arrColor["color-menu"]["color"] : "";
        $menu_color_deg = !empty($arrColor["color-deg"]["color"]) ? $arrColor["color-deg"]["color"] : "";
        $background = $menu_color_deg && $menu_background_color ? "linear-gradient($menu_background_color, $menu_color_deg)" : $menu_background_color;
        if(!empty($arrColor)){
            foreach($arrColor as $key => $color){
                $colorts = $color["color"];
                $style .= ".$key"."_color{color: $colorts;}\n";
                $style .= ".$key"."_background{background: $colorts;}\n";
                $menu_hover = $key==="color-menu-active" ? $colorts : "rgba(0,0,0, .7)";
                if($key==="color-menu-active"){
                    $style .= ".menu-open > a, .sidebar-menu > .header{background: $colorts !important;} .treeview > a > span:hover{background: $colorts !important;} .treeview>a:hover{background: $colorts !important;} .treeview-menu > li > a:hover{background: $colorts !important;} \n";
                }
                else if($key==="color-menu"){
                    $style .= ".main-sidebar{ background: $background !important;} .navbar-static-top, .fa-bars{ background: $menu_background_color !important;} .main-header > a{ background: none !important;}\n";
                }
                else if($key==="color-title"){
                    $style .= ".title-page{background: $colorts !important;}\n";
                }
                else if($key==="color-title-text") {
                    $style .= ".title-page{color: $colorts !important;}\n";
                }
                else if($key==="color-menu-elements"){
                    $style .= ".treeview>ul{ background: $colorts !important;}\n";
                }
                else if($key==="color-menu-text"){
                    $style .= ".treeview > a, .user-menu > a, .treeview-menu > li > a, .sidebar-menu > .header{color: $colorts !important;} .treeview-menu > li > a:hover{background: $menu_hover !important; color: rgba(255,255,255, .7) !important;}\n";
                }
                $arrColor = [];
                $arrColor["id"] = $color["id"];
                $arrKey["id"] = $color["id"];
                $arrColor["color"] = $colorts;
                $this->sql_tableupdate('wt_profile_configuration', $arrKey, $arrColor);
            }
        }
        else{
            return response::standard(0, "No hay datos para guardar", [], $this->boolUTF8, $this->boolPrintJson);
        }
        $file = fopen("themes/".$cfg["core"]["theme"]."/css/custom_styles.css", "w") or die("no se puede abrir");
        $text = $style;
        /*$real_file = */
        fwrite($file, $text);
        fclose($file);
        return response::standard(1, "Actualizado", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function setDefault(){
        global $cfg;
        $type = !empty($this->arrParams["is"]) ? $this->arrParams["is"] : "";
        $model = new profile_model();
        if($type === 'color'){
            if(copy("themes/".$cfg["core"]["theme"]."/css/default_styles.css", "themes/".$cfg["core"]["theme"]."/css/custom_styles.css")){
                $model->setDefault('color');
                return response::standard(1, "Se ha completado con éxito", [], $this->boolUTF8, $this->boolPrintJson);
            }
            return response::standard(0, "Error", [], $this->boolUTF8, $this->boolPrintJson);
        }
            else{
            $images = $model->getAllByType('image');
            if($images){
                foreach($images as $image){
                    if(file_exists($image["path"])){
                        unlink($image["path"]);
                    }
                }
                $model->setDefault('image');
                return response::standard(0, "Se ha completdo con éxito", [], $this->boolUTF8, $this->boolPrintJson);
            }
            return response::standard(0, "Error", [], $this->boolUTF8, $this->boolPrintJson);
        }
    }
    public function saveImage(){
        global $cfg;
        $image = $this->arrParams["image"];
        $id = $this->arrParams["id"];
        $strPrincipalPath = "var/configuration/theme/".$cfg["core"]["theme"];
        $strPath = $strPrincipalPath."/images/";
        if(!is_dir($strPath)){
            mkdir($strPath, 0777);
        }
        $route = $this->returnUrlImageSave($strPath, $image);
        $model = new profile_model($this->arrParams);
        if($path = $model->getPathById($id)){
            $this->deleteImageInProject($path);
        }
        if($route){
            $arrKey = [];
            $arrKey["id"] = $id;
            $arrImage = [];
            $arrImage["id"] = $id;
            $arrImage["path"] = $route;
            $query = $this->sql_tableupdate('wt_profile_configuration', $arrKey, $arrImage);
            if($query){
                return response::standard(1, "Guardado Correctamente", [$arrImage], $this->boolUTF8, $this->boolPrintJson);
            }
            return response::standard(0, "Error al guardar", [], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Ocurrio un error", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function deleteImage(){
        $model = new profile_model($this->arrParams);
        $id = $this->arrParams["id"];
        $consulta = $model->getPathById($id);
        if($consulta){
            $model->setNullField($id, 'path');
            $this->deleteImageInProject($consulta);
            return response::standard(1, "Eliminada", [$consulta], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "No hay datos que mostrar", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function deleteImageInProject($path){
        if(file_exists($path)){
            unlink($path);
        }
    }
    public function deleteColor(){
        $id = $this->arrParams["id"];
        if($id){
            $model = new profile_model($this->arrParams);
            $func = $model->setNullField($id, 'color');
            if($func){
                return response::standard(1, "Se ha eliminado con éxito", [], $this->boolUTF8, $this->boolPrintJson);
            }
        }
        return response::standard(0, "No hay id", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function getTitle()
    {
        $id = $this->arrParams["id"];
        if ($id){
            $model = profile_model::getInstance($this->arrParams);
            $consulta = $model->getTitle($id);
            if($consulta){
                return response::standard(1, "Los datos han sido obtenidos", ["data" => $consulta], $this->boolUTF8, $this->boolPrintJson);
            }
            return response::standard(0, "Sin datos", [], $this->boolUTF8, $this->boolPrintJson);
        }

    }
    public function getMenuItems(){
        $model = profile_model::getInstance($this->arrParams);
        $menuLinks = $model->getMenuItemsDB();
        return response::standard(1, "data obtenida", ["data" => $menuLinks], $this->boolUTF8, $this->boolPrintJson);
    }
    public function moveMenuItemOrder(){
        $position_id = $this->arrParams["id"];
        $item_principal_order = $this->arrParams["original_pos"];
        $position_order = $this->arrParams["order_menu"];
        $model = profile_model::getInstance($this->arrParams);

        $pos_wished = $model->getMenuItemBy('order_menu', $position_order);
        if($model->updateMenuOrder($position_id, $pos_wished["order_menu"]) && $model->updateMenuOrder($pos_wished["id"], $item_principal_order)){
            return response::standard(1, "Los datos se han actualizado", [], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Ocurrio un error", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function updateMenuItem(){
        $data = [];
        $arrKey["id"] = isset($this->arrParams["id"]) && $this->arrParams["id"] ? $this->arrParams["id"] : null;
        $data["title"] = $this->arrParams["title"] ? $this->checkParam('title', false, '', true) : "";
        $data["url"] = $this->arrParams["url"];
        $data["icon"] = $this->arrParams["icon"];
        $data["blank"] = isset($this->arrParams["blank"]) && $this->arrParams["blank"] ? 1 : '0';
        $data["order_menu"] = isset($this->arrParams["order_menu"]) && $this->arrParams["order_menu"] ? $this->arrParams["order_menu"] : null;
        if(empty($data["order_menu"])){
            $model = profile_model::getInstance($this->arrParams);
            $max_order = $model->getMaxOrder(false);
            $data["order_menu"] = $max_order + 1;
        }
        $data["available"] = isset($this->arrParams["available"]) && $this->arrParams["available"] ? 1 : '0';
        if($this->sql_tableupdate('wt_menu_links', $arrKey, $data)){
            return response::standard(1, "Los datos se han actualizado", [], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Ocurrio un error", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function deleteMenuItem(){
        $id_menu_link = $this->arrParams["id"];
        $model = profile_model::getInstance($this->arrParams);
        if($model->deleteMenuItemDB($id_menu_link)){
            return response::standard(1, 'Se ha eliminado con éxito', [], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, 'Ocurrio un error al eliminar el registro', [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function getAllCaptions(){
        $model = profile_model::getInstance($this->arrParams);
        $captions = $model->getAllCaptionsDB();
        return response::standard(1, "Los datos han sido obtenidos correctamente", ["data" => $captions], $this->boolUTF8, $this->boolPrintJson);
    }
    public function updateCaptionAdmin(){
        $captionKey["id"] = $this->arrParams["id"];
        $arrData = [];
        $arrData["id"] = $this->arrParams["id"];
        $arrData["content"] = $this->arrParams["content"] ? $this->checkParam('content', false, '', true) : "";
        if($this->sql_tableupdate('wt_caption_info', $captionKey, $arrData)){
            return response::standard(1, "actualizado", [], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "error", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function getByType(){
        $type = $this->arrParams["type"];
        $model = profile_model::getInstance($this->arrParams);
        $consulta = $model->getAllByType($type);
        if($consulta){
            return response::standard(1, "Los datos han sido obtenidos", ["data" => $consulta], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Sin datos", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function getImageCorreo(){
        $model = profile_model::getInstance($this->arrParams);
        $consulta = $model->getDataInfoCorreo();
        if($consulta){
            return response::standard(1, "Los datos han sido obtenidos", ["data" => $consulta], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Sin datos", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function returnUrlImageSave($strPath, $strFile)
    {
        $strFullPathImage = "";
        if(!chmod($strPath,0777 )){
            chmod($strPath,0777 );
        }
        $upload = new \Delight\FileUpload\Base64Upload();
        $upload->withTargetDirectory("{$strPath}");
        $strImage = preg_replace('#^data:image/\w+;base64,#i', '', $strFile);
        $upload->withData($strImage);
        $upload->withFilenameExtension("png");
        $strUrlResult = "{$upload->getTargetDirectory()}/";
        $strUrlResult .= $upload->getTargetFilename();

        try {
            $uploadedFile = $upload->save();
            $strUrlResult .= "{$uploadedFile->getFilename()}.";
            $strUrlResult .= $uploadedFile->getExtension();
            $strFullPathImage = $strUrlResult;
        }
        catch (\Delight\FileUpload\Throwable\Error $e) {
            $this->addError("No se pudo guardar la imagen. {$e->getMessage()}");
        }

        return $strFullPathImage;
    }
    public function saveNewRegister()
    {
        $title = $this->arrParams["title"];
        $description = $this->arrParams["description"];
        $link = $this->arrParams["link"];
        $imgMail = $this->arrParams["image"];
        $Social = "Footer";
        $strPath = "var/configuration/theme/";
        if(!is_dir($strPath)) mkdir(0777);
        $strLocationImg = $this->returnUrlImageSave($strPath, $imgMail);
        if($strLocationImg){
            $arrKey = array(
                "id" => $this->checkParam("intID", false, 0)
            );
            $arrField = array();
            $arrField["position_Image"] = $Social;
            $arrField["title"] = $title;
            $arrField["description"] = $description;
            $arrField["link"] = $link;
            $arrField["path"] = $strLocationImg;
            if($this->sql_tableupdate('wt_Profile_Configuration_Correo', $arrKey, $arrField)){
                return response::standard(1, "Imagen Guardada Correctamente", $arrField, $this->boolUTF8, $this->boolPrintJson);
            }
            return response::standard(0, "Error al guardar", array(), $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Ocurrio un error", array(), $this->boolUTF8, $this->boolPrintJson);
    }
    public function saveImageCorreo(){
        $imageCorreo = $this->arrParams["image"];
        $id = $this->arrParams["id"];
        $strPath = "var/configuration/theme/";
        if(!is_dir($strPath)) mkdir(0777);

        $routes = $this->returnUrlImageSave($strPath, $imageCorreo);
        $model = new profile_model($this->arrParams);
        if($path = $model->getPathImageCorreo($id)){
            $this->deleteImageInProjectCorreo($path);
        }
        if($routes){
            $arrKey = [];
            $arrKey["id"] = $id;
            $arrImage = [];
            $arrImage["id"] = $id;
            $arrImage["path"] = $routes;
            $query = $this->sql_tableupdate('wt_Profile_Configuration_Correo', $arrKey, $arrImage);
            if($query){
                return response::standard(1, "Imagen Guardada Correctamente", [$arrImage], $this->boolUTF8, $this->boolPrintJson);
            }
            return response::standard(0, "Error al guardar", [], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Ocurrio un error", [], $this->boolUTF8, $this->boolPrintJson);

    }
    public function deleteLinkCorreo()
    {
        $model = new profile_model($this->arrParams);
        $id = $this->arrParams["id"];
        $consulta = $model->getLinkImageCorreo($id);
        if($consulta){
            $model->deletTabNew($id);
            $this->deletLinkInproject($consulta);
            return response::standard(1, "Eliminada", [$consulta], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "No hay datos que mostrar", [], $this->boolUTF8, $this->boolPrintJson);

    }
    public function editData(){
        $title = $this->arrParams["title"];
        $description = $this->arrParams["description"];
        $link = $this->arrParams["link"];
        $id = $this->arrParams["id"];
        if ($id){
            $arrData = array();
            $arrKey["id"] = $id;
            $arrData["title"] = $title;
            $arrData["description"] = $description;
            $arrData["link"] = $link;

            if ($this->sql_tableupdate('wt_Profile_Configuration_Correo', $arrKey, $arrData)) {
                return response::standard(1, "Datos guardados correctamente", [], $this->boolUTF8, $this->boolPrintJson);
            }
            return response::standard(0, "Error al guardar", array(), $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Error", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function EditRegister(){
        $title = $this->arrParams["title"];
        $description = $this->arrParams["description"];
        $link = $this->arrParams["link"];
        $id = $this->arrParams["id"];
        $imgMail = $this->arrParams["image"];
        $strPath = "var/configuration/theme/";
        if(!is_dir($strPath)) mkdir(0777);
        $strLocationImg = $this->returnUrlImageSave($strPath, $imgMail);
        if($strLocationImg) {
            $arrData = array();
            $arrKey["id"] = $id;
            $arrData["title"] = $title;
            $arrData["description"] = $description;
            $arrData["link"] = $link;
            $arrData["path"] = $strLocationImg;
            if ($this->sql_tableupdate('wt_Profile_Configuration_Correo', $arrKey, $arrData)) {
                return response::standard(1, "Datos guardados correctamente", [], $this->boolUTF8, $this->boolPrintJson);
            }
            return response::standard(0, "Error al guardar", array(), $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Error", [], $this->boolUTF8, $this->boolPrintJson);
    }
    public function deleteImageCorreo(){
        $model = new profile_model($this->arrParams);
        $id = $this->arrParams["id"];
        $consulta = $model->getPathImageCorreo($id);
        if($consulta){
            $model->setNullFieldCorreo($id, 'path');
            $this->deleteImageInProjectCorreo($consulta);
            return response::standard(1, "Eliminada", [$consulta], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "No hay datos que mostrar", [], $this->boolUTF8, $this->boolPrintJson);
    }

    public function deletLinkInproject($link){
        if(file_exists($link)){
            unlink($link);
        }
    }

    public function deleteImageInProjectCorreo($path){
        if(file_exists($path)){
            unlink($path);
        }
    }
    public function saveAllow(){
        $id = $this->checkParam('idPage', false, '', true);
        $isAllow = $this->checkParam("allow", false, '0', true);
        $arr = [];
        $arr["id"] = $id;
        $arr["allow"] = $isAllow;
        $arrKey = $this->arrParams["idPage"];
        $model = profile_model::getInstance($this->arrParams);
        if($model->updateStatusShow($arr["id"], $arr["allow"])){
            return response::standard(1, "actualizado", [], $this->boolUTF8, $this->boolPrintJson);
        }
        return response::standard(0, "Ocurrio un error", [], $this->boolUTF8, $this->boolPrintJson);
    }
}
