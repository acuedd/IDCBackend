<?php
include_once("core/miniMain.php");
abstract class global_config {
    /* Almacena las variables de configuracion para poder usarlas dentro de la clase
     * @var array
     * @access protected
     */

    /**
     * Para tener una copia de los langs, cfg y config
     *
     * @var mixed
     */
    protected $lang = array();
    protected $cfg = array();
    protected $config = array();
    protected $arrParams;
    protected $objConection = false;

    /**
     * Array de mensajes de error.
     *
     * @var array
     * @access protected
     */
    protected $arrErrorMsgs = false;
    
    // Funciones de variables
    public function setObjConection($objRemoteConection = false){
        $this->objConection = $objRemoteConection;     
    }
    
    function __construct(&$arrParams = "") {
        global $lang, $cfg, $config;

        $this->lang = &$lang;
        $this->cfg = &$cfg;
        $this->config = &$config;
        $this->setArrParams($arrParams);
    }

    public function setArrParams(&$arrDatos) {
        $this->arrParams = $arrDatos;
    }

    /**
	 * Current debug string (manipulated by debug/appendDebug/clearDebug/getDebug/getDebugAsXMLComment)
	 *
	 * @var string
	 * @access private
	 */
	private $debug_str = "";
	/**
	 * the debug level for this instance
	 *
	 * @var    integer
	 * @access private
	 */
	private $debugLevel = 0;

	/**
	* Define el nivel de debug
	*
	* @param    int    $intLevel    Debug level 0-9, where 0 turns off
	* @access    public
	*/
	public function setDebugLevel($intLevel) {
		$this->debugLevel = $intLevel;
	}
	/**
	* gets the debug level for this instance
	*
	* @return    int    Debug level 0-9, where 0 turns off
	* @access    public
	*/
	public function getDebugLevel() {
		global $config;
		if(!empty($config["debug"])){
			$this->setDebugLevel(100);
		}
		return $this->debugLevel;
	}

	/**
	* Agrega una linea de Debug
	*
	* @param    string $strDebugString debug data
	* @access   protected
	*/
	protected function appendDebug($strDebugString){
		if ($this->getDebugLevel() > 0) {
			// it would be nice to use a memory stream here to use
			// memory more efficiently
			$this->debug_str .= getmicrotime().' '.get_class($this).": {$strDebugString}\n";
		}
	}
	/**
	* Limpia la info de debug
	*
	* @access   public
	*/
	public function clearDebug() {
		$this->debug_str = '';
	}
	/**
	* gets the current debug data for this instance
	*
	* @return   string debug data
	* @access   public
	*/
	public function getDebug() {
		return $this->debug_str;
	}
    
    //Manejo de errores
    /**
     * Agrega un error al array de errores
     *
     * @param string $strMsg
     * @access protected
     */
	protected function addError($strMsg, $strKey = "", $boolNoRepeat = false) {
        if(isset($this->lang[$strMsg])){
            $strCode = sqlGetValueFromKey("SELECT error_code FROM wt_error_code WHERE lang_key = '{$strMsg}'");
            if($strCode) $strKey = $strCode;
            $strMsg = $this->lang[$strMsg];
        }

		if(!empty($strKey)){
			if($boolNoRepeat){
				$this->arrErrorMsgs[$strKey] = $strMsg;
			}
			else{
				$this->arrErrorMsgs[$strKey][] = $strMsg;
			}
		}
        else $this->arrErrorMsgs[] = $strMsg;

    }

    /**
     * Ordena los mensajes de error.
     */
    protected function sortErrorsByText() {
        if ($this->hasError()) {
            sort($this->arrErrorMsgs);
        }
    }

    /**
     * Indica si hay errores
     *
     * @access public
     * @return boolean
     */
    public function hasError($strKey = "") {
        if(!empty($strKey)){
            return (isset($this->arrErrorMsgs[$strKey]) && is_array($this->arrErrorMsgs[$strKey]) && (count($this->arrErrorMsgs[$strKey]) > 0));
        }
        else return (is_array($this->arrErrorMsgs) && (count($this->arrErrorMsgs) > 0));
    }

    /**
     * Devuelve la lista de errores.  Soporta ya sea como array o como string separado por $varModeHelper.
     *
     * @param string $strMode Modo en que se devolveran los datos.  array|string
     * @param mixed $varModeHelper para apoar el modo en que se devuelven los datos.  Si es string, es el separador entre un error y otro.
     * @return mixed
     */
    public function getErrors($strMode = "array", $varModeHelper = false, $strKey = "") {
        if(!empty($strKey)){
            if (!$this->hasError($strKey))
                return false;
            if ($strMode == "string") {
                if ($varModeHelper == false)
                    $varModeHelper = ", ";
                return implode($varModeHelper, $this->arrErrorMsgs[$strKey]);
            }
            else {
                return $this->arrErrorMsgs[$strKey];
            }
        }
        else{
            if (!$this->hasError())
                return false;
            if ($strMode == "string") {
                if ($varModeHelper == false)
                    $varModeHelper = ", ";

                $strVar = "";
                foreach ($this->arrErrorMsgs as $element) {
                    if(is_array($element) && (count($element)>0)){
                        foreach($element as $element2){
                            $strVar .=  (empty($strVar))? $element2 : $varModeHelper . $element2;
                        }
                    }
                    else{
                        $strVar .= (empty($strVar))? $element : $varModeHelper . $element;    
                    }                    
                }
                return $strVar;                                               
            }
            else {
                return $this->arrErrorMsgs;
            }
        }
    }

    /* Funciones para db */
    /**
     * Funcion que construye y ejecuta los queries para actualizar o insertar data.
     *
     * @param string $strTable Nombre de la tabla a afectar
     * @param array $arrKey Array con las llaves de la tabla campo=>value
     * @param array $arrFields Array con los datos a actualizar campo=>value
     * @param mixed $arrExtraInsertFields Array con campos extras a agregar si es un insert (dateregistered, por ejemplo)
     * @param boolean $boolForceReplace obliga a hacer un replace into
     */
    protected function sql_tableupdate($strTable, $arrKey, $arrFields, $arrExtraInsertFields = false, $boolForceReplace = false) {
        if (!$boolForceReplace) {
            $strWhere = "1";
            foreach ($arrKey AS $key => $value){
                $strValue = db_escape($value);
                $strWhere .= " AND {$key} = '{$strValue}'";
            }

            // Primero veo si el dato ya existe
            $strQuery = "SELECT COUNT(*) AS conteo
                         FROM {$strTable}
                         WHERE {$strWhere}";
            $this->appendDebug($strQuery);
            $intNumRows = sqlGetValueFromKey($strQuery,false,false,true,$this->objConection);
        } else {
            $intNumRows = 0;
        }
        if ($intNumRows == 0) {
            // Insert
            $arrAllFields = array_merge($arrKey, $arrFields);
            if (is_array($arrExtraInsertFields)) {
                $arrAllFields = array_merge($arrAllFields, $arrExtraInsertFields);
            }

            $strFields = "";
            $strValues = "";
            foreach ($arrAllFields AS $key => $value){
                $strValue = db_escape($value,$this->objConection);
                $strFields .= ", `{$key}`";
                if($strValue == "NULL" || ((preg_match("/^[A-Z]+[\(\)]/",$strValue)) === 1)){
                    $strValues .= ", {$strValue}";
                }
                else{
                    $strValues .= ", '{$strValue}'";
                }
            }
            $strFields = substr($strFields, 2);
            $strFields = str_replace("'","",$strFields);
            $strValues = substr($strValues, 2);

            $strCommand = ($boolForceReplace) ? "REPLACE" : "INSERT";
            $strQuery = "{$strCommand} INTO {$strTable}
                         ({$strFields})
                         VALUES
                         ({$strValues})";
	        $this->appendDebug($strQuery);
            db_query($strQuery,true,$this->objConection);
            return true;
        } else if ($intNumRows == 1) {
            // Update
            $strSet = "";
            foreach($arrFields AS $key => $value){
                $strValue = db_escape($value);
                if($strValue == "NULL" || ((preg_match("/^[A-Z]+[\(\)]/",$strValue)) === 1)){
                    $strSet .= ", `{$key}` = {$strValue}";
                }
                else{
                    $strSet .= ", `{$key}` = '{$strValue}'";
                }
            }
            $strSet = substr($strSet, 2);
            $strQuery = "UPDATE {$strTable}
                         SET {$strSet}
                         WHERE {$strWhere}";
            //debug::drawdebug($strQuery,"es update");die;
	        $this->appendDebug($strQuery);
            db_query($strQuery,true, $this->objConection);
            return true;
        }
        else {
            // ERROR
            return false;
        }
    }

    /* Funcion para eliminar datos de las tablas
     * @param array() $arrTables["nombreTabla"]["key_tabla"] = valor
     */
    protected function sql_deleteData($arrTables) {
        if (is_array($arrTables) && count($arrTables) > 0) {
			foreach($arrTables AS $key => $value){
                $strWhere = "";
                if (is_array($value) && count($value)) {
                	foreach($value AS $key2 => $value2){
		                $strWhere .= (empty($strWhere)) ? "{$key2}='{$value2}'" : " AND {$key2}='{$value2}'";
	                }
                    $strQuery = "DELETE FROM {$key} WHERE {$strWhere}";
                	$this->appendDebug($strQuery);
                    db_query($strQuery,true,$this->objConection);
                }
            }
        }
    }

	/** Esta funcion valida si existe la posición dentro de un arreglo pero realiza una validación pero realiza una validación de tipo de dato que se obtiene
	 * @param $strTerm
	 * @param bool $arrParams
	 * @param string $default
	 * @param bool $boolUTF8
	 * @return type
	 */
    public function checkParam($strTerm, $arrParams = false, $default  = "", $boolUTF8 = false){
        if(!$arrParams) $arrParams = $this->arrParams;
        return global_function::getParam($arrParams, $strTerm, $default, $boolUTF8);
    }

	/**
	 * Esta funcion es para obtener valores de un array y que valide si existe o no el dato. NO realiza validaciones
	 * @param $strTerm
	 * @param bool $arrParams
	 * @param string $default
	 * @return type
	 */
    public function getParam($strTerm, $arrParams = false, $default  = ""){
	    if(!$arrParams) $arrParams = $this->arrParams;
	    return global_function::getParam($arrParams, $strTerm, $default, false, false);
    }

    /**
     * Metodo que permite obtener los token gcm(google cloud messaging)
     *
     * @param string $users cadena de enteros de usuarios
     * @return string cadena de tokens gcm
     */
    public function getDeviceByUser( $users = "", $boolOnlyOne = false){
        $strFilter = "";
        if(!empty($users))$strFilter = " AND userid IN({$users})";

	    $strRestrict = "";
	    if($boolOnlyOne)$strRestrict = " LIMIT 0,1";

        $strQuery = "SELECT token_gcm FROM wt_webservices_devices WHERE activo = 'Y' {$strFilter} ORDER BY last_use DESC {$strRestrict}";
        $qTMP = db_query($strQuery);
        $strTokens = "";
        if(db_num_rows($qTMP)){
            while($rTMP = db_fetch_assoc($qTMP)){
	            $strTokens .= (empty($strTokens))?$rTMP["token_gcm"]:",{$rTMP["token_gcm"]}";
                unset($rTMP);
            }
            db_free_result($qTMP);
        }
        return $strTokens;
    }

	protected function checkUptoDate($strDate, $strTime, $strInventario) {
		$boolOK = false;
		if (!empty($strDate) && !empty($strTime)) {
			$arrDate = explode("-", $strDate);
			if (checkDate($arrDate[1], $arrDate[2], $arrDate[0])) {
				$strQuery = "SELECT IF ('{$strDate}' < fecha ,
                                        'TRUE',
                                        IF('{$strDate}' = fecha,
                                            IF('{$strTime}' < hora, 'TRUE', 'FALSE'),
                                        'FALSE')
                                    ) AS last_update
                            FROM wt_catalogos_last_update
                            WHERE table_name = '{$strInventario}'";
				$strChanges = sqlGetValueFromKey($strQuery, true,false,true,$this->objConection);
				if ($strChanges == "TRUE")
					$boolOK = true;
			}
		}
		return $boolOK;
	}

    public function returnUrlImageSave($strPath, $strFile)
    {
        $strFullPathImage = "";
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
}

/**
 * Simple template engine class (use [@tag] tags in your templates).
 *
 * @link http://www.broculos.net/ Broculos.net Programming Tutorials
 * @author Nuno Freitas <nunofreitas@gmail.com>
 * @version 1.0
 */
class Template{
    /**
     * The filename of the template to load.
     *
     * @access protected
     * @var string
     */
    protected $file;

    /**
     * An array of values for replacing each tag on the template (the key for each value is its corresponding tag).
     *
     * @access protected
     * @var array
     */
    protected $values = array();

    /**
     * Creates a new Template object and sets its associated file.
     *
     * @param string $file the filename of the template to load
     */
    public function __construct($file) {
        $this->file = $file;
    }

    /**
     * Sets a value for replacing a specific tag.
     *
     * @param string $key the name of the tag to replace
     * @param string $value the value to replace
     */
    public function set($key, $value) {
        $this->values[$key] = $value;
    }

    /**
     * Outputs the content of the template, replacing the keys for its respective values.
     *
     * @return string
     */
    public function output() {
        /**
         * Tries to verify if the file exists.
         * If it doesn't return with an error message.
         * Anything else loads the file contents and loops through the array replacing every key for its value.
         */
        if (!file_exists($this->file)) {
            return "Error loading template file ($this->file).<br />";
        }
        $output = file_get_contents($this->file);

        foreach ($this->values as $key => $value) {
            $tagToReplace = "[@$key]";
            $output = str_replace($tagToReplace, $value, $output);
        }

        return $output;
    }

    /**
     * Merges the content from an array of templates and separates it with $separator.
     *
     * @param array $templates an array of Template objects to merge
     * @param string $separator the string that is used between each Template object
     * @return string
     */
    static public function merge($templates, $separator = "\n") {
        /**
         * Loops through the array concatenating the outputs from each template, separating with $separator.
         * If a type different from Template is found we provide an error message.
         */
        $output = "";

        foreach ($templates as $template) {
            $content = (get_class($template) !== "Template")
                ? "Error, incorrect type - expected Template."
                : $template->output();
            $output .= $content . $separator;
        }

        return $output;
    }
}

interface window_controller{
    public function setStrAction( $strAction );
    public function main();
    public function setBoolPrintJson( $boolPrintJson );
    public function setBoolUTF8( $boolUTF8 );
}

interface window_model{
	public static function getInstance($arrParams);
}

interface window_view{
    public function setStrAction( $strAction );
    public function draw();
}