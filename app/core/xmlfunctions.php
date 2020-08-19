<?php
//<?xml version='1.0' encoding='ISO-8859-1'
// ToDo: TIENE EL BUG QUE LOS NODOS TIENEN QUE TENER NOMBRES DISTINTOS
// ToDo: la clase XMLNode no tiene una funcion que devuelva un hijo.  Esta debe tener en consideracion que pueden haber N hijos con el mismo nombre.

/**
 * @return string
 * @desc Corrige los datos enviados por Flash a $_POST
*/
function getXMLPostFromFlash() {
	$s = '';
	while(list($key, $val) = each($_POST)) {
		$s .= $key . '=' . $val;
	}
	$s = str_replace('_', ' ', stripslashes($s));
	return ($s);
}


/**
 * @return void
 * @param XMLNode $actualNode
 * @param strArray $data
 * @param integer $dataPos
 * @desc Funcion recursiva que construye el árbol de nodos a partir de un array de texto.
*/
function buildXMLNode(&$actualNode, &$data, $dataPos) {
    $shortTag = false;
    $useText = false;
    $tempNode = false;

    $boolDataPosExists = isset($data[$dataPos]); //Verifica que la posicion buscada exista
	$strTrimmedData = ($boolDataPosExists)?trim($data[$dataPos]):""; // Posision libre de caracteres extraños, util para hacer verificaciones del tipo de nodo con substrings

    if ($boolDataPosExists && $strTrimmedData== "/{$actualNode->name}>") { //VERIFICO QUE NO SEA EL FIN DEL NODO ABIERTO
        $tempNode = &$actualNode->parentNode; // Me regreso al nodo padre porque pueden quedar hermanitos
    }
    else if ($boolDataPosExists) {
        if (strpos($strTrimmedData,"=") === false) {
        	//SI NO TIENE ATRIBUTOS
            if (substr($strTrimmedData, -2) == "/>") {
            	// Si es un short tag - no puede tener texto
                $arrExplode = explode("/>", $data[$dataPos]);
                $data[$dataPos] = ""; //Para liberar memoria
                $tempNode = new XMLNode(trim($arrExplode[0]), "");
                $shortTag = true;

                $arrExplode = false; //Para liberar memoria
            }
            else {
                $arrExplode = explode(">", $data[$dataPos]);
                $data[$dataPos] = ""; //Para liberar memoria

                // Verifico si hay texto
				if (isset($arrExplode[1])) {
					$strText = trim($arrExplode[1]);
					if (!empty($strText)) {
						$strText = $arrExplode[1];
					}
					else {
						$strText = "";
					}
				}
				else {
					$strText = "";
				}

                $tempNode = new XMLNode(trim($arrExplode[0]), "", $strText);

				$strText = false; //Para liberar memoria
                $arrExplode = false; //Para liberar memoria
            }
        }
        else {
        	//SI EL NODO TIENE ATRIBUTOS
        	// Para asaegurar que la busqueda de " " no de problemas si el XML se genero con nuestro objeto con \n en vez de " " entre los atributos
        	$data[$dataPos] = str_replace("\"\r\n", "\" ", $data[$dataPos]);
        	$data[$dataPos] = str_replace("\"\n\r", "\" ", $data[$dataPos]);
        	$data[$dataPos] = str_replace("\"\n", "\" ", $data[$dataPos]);
        	$data[$dataPos] = str_replace("\"\r", "\" ", $data[$dataPos]);

        	// Obtengo correctamente el nombre del nodo
        	$intPosBlank = strpos($data[$dataPos]," ");
        	if ($intPosBlank === false) $intPosBlank = strlen($data[$dataPos]);
            $intPosClose = strpos($data[$dataPos],">");
            if ($intPosClose === false) $intPosClose = strlen($data[$dataPos]);
            $intPosCloseSrt = strpos($data[$dataPos],"/");
            if ($intPosCloseSrt === false) $intPosCloseSrt = strlen($data[$dataPos]);

            $intTMP = ($intPosBlank < $intPosClose)?$intPosBlank:$intPosClose;
            $intTMP = ($intTMP < $intPosCloseSrt)?$intTMP:$intPosCloseSrt;
            $strTMPName = substr($data[$dataPos],0,$intTMP);

            if (substr($strTrimmedData, -2) == "/>") { //VERIFICO SI SE CIERRA CON SHORT TAG AL NO TENER HIJOS NI TEXTO
                $tempNode = new XMLNode($strTMPName, substr($strTrimmedData, strlen($strTMPName)+1, -2));
                $shortTag  = true;
            }
            else {
            	// No es short tag, puede tener hijos y texto
				$arrTMP = explode(">", $data[$dataPos]);
				$data[$dataPos] = $arrTMP[0].">"; // Dejo en $data[$dataPos] solo el nombre y los atributos
				$arrTMP[0] = "";//Para liberar memoria

				// Verifico si hay texto
				if (isset($arrTMP[1])) {
					$strText = trim($arrTMP[1]);
					if (!empty($strText)) {
						$strText = $arrTMP[1];
					}
					else {
						$strText = "";
					}
				}
				else {
					$strText = "";
				}
            	$tempNode = new XMLNode($strTMPName, substr($data[$dataPos], strlen($strTMPName)+1, -1), $strText);

				$arrTMP = false; //Para liberar memoria
				$strText = false; //Para liberar memoria
            }
        }

        if ($tempNode !== false) $actualNode->appendChild($tempNode); //AGREGO EL NODO RECIEN CREADO AL ARBOL

        if ($shortTag) {
        	//SI SE USO SHORT TAG ENTONCES DECLARO A TEMPNODE COMO EL NODO ACTUAL PARA ENVIARLO DE NUEVO COMO PARAMETRO PARA BUSCAR UN HERMANO
            $tempNode = &$actualNode;
        }

    }

    if ($dataPos < count($data) - 2) {
    	if (isset($data[$dataPos])) $data[$dataPos] = false; //Para liberar memoria
        buildXMLNode($tempNode, $data, $dataPos+1);
    }
}


/**
 * @return string
 * @param XMLNode $actualNode
 * @desc Funcion recursiva que construye la cadena de texto que representa el arbol de XML
*/
/*
function getXMLString($actualNode)
{
    $s = "<".$actualNode->name;
    if (isset($actualNode->attributes)) {
        foreach ($actualNode->attributes as $key => $value) {
            $s .= " " . $key . '="' . $value . '"';
        }
    }
    if ($actualNode->hasChildren()) {
        $s .=  ">\n";
        foreach ($actualNode->children as $hijo) {
            $s .=  getXMLString($hijo);
        }
        $s .= "</" . $actualNode->name . ">\n";
    }
    elseif (strlen($actualNode->text)>0) {
    	$s .= ">".$actualNode->text."</".$actualNode->name.">\n";
    }
    else {
        $s .=  "/>\n";
    }
    return ($s);
}
*/

/**
 * @return strArray
 * @param string $values
 * @desc Funcion que construye el array de atributos a partir de una cadena de caracteres
*/
function buildXMLAttributes($values) {
    $attributes = array();

    $values = trim($values);

    if (empty($values)) {
        return false;
    }

    while (!empty($values)) {
        $pos = strpos($values,"=");
        $attName =  substr($values,0,$pos);
        $posI = strpos($values,'"')+1;
        $posE = strpos($values,'"',$posI);
        $attValue = substr($values,$posI,$posE-$posI);

        $attributes[$attName] = htmlspecialchars_decode($attValue);

        $values = trim(substr($values,$posE+1));
    }
    return $attributes;
}

//Definición de la clase XMLNode
class XMLNode {
	// Estas las quite para que solo estuvieran si tienen contenido...
    var $name = null;
    var $attributes = null;
    var $text = null;
    var $intNextChildID = 0;
    var $children = false;
    var $childrenNames = false;
    var $parentNode = null;

    /**
    * @return XMLNode
    * @param string $newName
    * @param string $attributesList
    * @param string $strText
    * @desc Constructor de la clase.
    */
    function __construct($newName, $attributesList="", $strText="") {
        $this->name = $newName;

        $arrTMP = buildXMLAttributes($attributesList);
        $attributesList = false; //Para liberar memoria
        if ($arrTMP) $this->attributes = $arrTMP;
        $arrTMP = false;  //Para liberar memoria

        if (strlen($strText)>0) {
        	$this->text = htmlspecialchars_decode($strText);
        	$strText = false; //Para liberar memoria
        }
    }

    // CHILD FUNCTIONS
    function addChild($newName, $attributesList="", $strText="") {
		if (!is_array($this->children)) {
			$this->children = array();
		}
    	$this->children[$this->intNextChildID] = new XMLNode($newName, $attributesList, $strText);
    	$this->childrenNames[$newName][] = $this->intNextChildID;
    	$this->children[$this->intNextChildID]->parentNode = &$this;
        $this->intNextChildID++;

        return $this->intNextChildID-1;
    }

    /**
    * @return void
    * @param XMLNode $child
    * @desc Agrega un hijo al nodo
    */
    function appendChild(&$child) {
		if (!is_array($this->children)) {
			$this->children = array();
		}
    	$this->children[$this->intNextChildID] = &$child;
    	$this->childrenNames[$child->name][] = $this->intNextChildID;
    	$this->children[$this->intNextChildID]->parentNode = &$this;
        $this->intNextChildID++;

        return $this->intNextChildID-1;
    }

    /**
    * @return false
    * @param string $childName
    * @desc Elimina un hijo del nodo
    */
    function deleteChild($childName, $intInstance = 0) {
        if (isset($this->childrenNames[$childName][$intInstance])) {
            unset ($this->children[$this->childrenNames[$childName][$intInstance]]);
        }
        return false;
    }

    /**
    * @return boolean
    * @desc Verifica si el nodo tiene hijos o no
    */
    function hasChildren() {
        if ($this->children && count($this->children)>0) {
            return (true);
        }
        else {
            return (false);
        }
    }

    /**
    * @return int
    * @desc Retorna numero de hijos de un nodo, sino tiene hijos devuelve 0
    */
    function intChildrenCount() {
        if ($this->hasChildren()) {
            return count($this->children);
        }
        else {
            return 0;
        }
    }


    //ATTRIBUTES FUNCTIONS
    /**
    * @return void
    * @param string $name
    * @param string $value
    * @desc Agrega un atributo a la lista de atributos
    */
    function addAttribute($name, $value) {
    	if (is_null($this->attributes)) $this->attributes = array();
        $this->attributes[$name] = $value;
    }

    /**
    * @return false
    * @param string $name
    * @desc Elimina un atributo
    */
    function deleteAttribute($name) {
        if (isset($this->attributes[$name])) {
            unset ($this->attributes[$name]);
            return true;
        }
        return false;
    }

    function getAttribute($name) {
    	return (isset($this->attributes[$name]))?$this->attributes[$name]:false;
    }

    //TEXT FUNCTIONS
    function setInternalText($strText) {
        $this->text = $strText;
    }
    function getInternalText() {
        return $this->text;
    }

    /**
    * @return string
    * @desc Imprime un nodo en pantalla.
    */
    function printNode() {
        $result = "<hr>";
        $result .= "<b>" . $this->name . "</b><br>";
        $result .= "Parent : ";
        if (isset($this->parentNode)) {
            $result .= $this->parentNode->name."<br>";
        }
        else {
            $result .= "ROOT NODE<br>";
        }
        $result .= "Attributes: ";
        if (isset($this->attributes)) {
            foreach ($this->attributes as $key => $value) $result .=  $key. "=". $value . "  ";
        }
        else {
            $result .= " None";
        }
        $result .= "<br><b>Children:";
        if (isset($this->children)) {
            foreach ($this->children as $hijo) {
                $result .= $hijo->name . ";";
            }
        }
        if (isset($this->children)) {
            $result .= "</b><br>";
            foreach ($this->children as $hijo) {
                $hijo->PrintNode();
            }
        }
        else {
            $result .= " None</b><br>";
        }
        $result .= "<b>Fin de nodo " . $this->name . "</b><HR>";
        return $result;
    }
   	/**
	 * @return string
	 * @param boolean $boolAddNewLines Indica si se agragan \n entre los atributos
	 * @param boolean $boolShortSyntaxBaseNodes true=> los nodos sin hijos y sin texto los tira con la sintaxis corta <nodo/>, falso => <nodo></nodo>
	 * @desc Funcion recursiva que construye la cadena de texto que representa el arbol de XML
	*/
    function toString($boolAddNewLines = false, $boolShortSyntaxBaseNodes = true) {
        $strTMP = ($boolAddNewLines)?"\n":" ";
    	$s = "<{$this->name}";
	    if (isset($this->attributes)) {
	        foreach ($this->attributes as $key => $value) {
	        	$strEscaped = htmlspecialchars($value);
	            $s .= "{$strTMP}{$key}=\"{$strEscaped}\"";
	        }
	    }

	    if ($this->hasChildren()) {
	        $s .=  ">\n";
	        foreach ($this->children as $hijo) {
	            $s .=  $hijo->toString($boolAddNewLines, $boolShortSyntaxBaseNodes);
	        }
	        $s .= "</{$this->name}>\n";
	    }
	    elseif (strlen($this->text)>0) {
	    	$strEscaped = htmlspecialchars($this->text);
	    	$s .= ">{$strEscaped}</{$this->name}>\n";
	    }
	    elseif ($boolShortSyntaxBaseNodes) {
	        $s .=  " />\n";
	    }
        else {
            $s .= "></{$this->name}>\n";
        }
	    return $s;
    }
}

// Definicion de la clase XMLObject
class XMLObject {
    var $name;
    var $rootNode;
    var $strError;
    /**
    * @return XMLObject
    * @param string $strBuild
    * @param boolean $boolWithAttributes Para indicar que es un objeto XML con atributos (formato descontinuado) o un XML sin atributos (mas formal)
    * @param string $strEncoding Encoding
    * @param boolean $boolRemoveXMLHeader para quitar el encabezado <?xml
    * @desc Constructor de la clase XMLOBJECT
    */
    function __construct($strBuild, $boolWithAttributes = true, $strEncoding = "ISO-8859-1", $boolRemoveXMLHeader = false) {
        if (empty($strBuild)) return;
		//20110824 AG: El trim de abajo si ayudaba porque los nodos vienen con un \n desde su creacion... solo voy a quitar los \n despues de >
		$strBuild = trim($strBuild);
        $strBuild = str_replace(">\r\n<", "><", $strBuild);
        $strBuild = str_replace(">\n\r<", "><", $strBuild);
        $strBuild = str_replace(">\n<", "><", $strBuild);
        $strBuild = str_replace(">\r<", "><", $strBuild);

        $boolIsUTF8 = ($strEncoding == "UTF-8");

        if ($boolWithAttributes) {
            $temp = explode("<",$strBuild);
            $strBuild = false; //Para liberar memoria...

            //20111222 AG: Veo si la primera linea es <?xml ... para ignorarla
            if (strstr($temp[1], "?xml") !== false && $boolRemoveXMLHeader) {
				$arrTMP = $temp;
				$temp = array();
				while ($arrItem = each($arrTMP)) {
					if ($arrItem["key"] == 0) continue;

					$temp[$arrItem["key"]-1] = $arrItem["value"];
					$arrTMP[$arrItem["key"]] = false; //Para liberar memoria;
				}
				$arrTMP = false;
            }

            /*
            //20110818 AG: Quito el trim... me da la impresion que no es necesario realmente y solo se caga en la descarga de chunks de archivos binarios
            while ($arrElement = each($temp)) {
                $temp[$arrElement["key"]] = trim($arrElement["value"]);
            }
            reset($temp);
            $arrElement = false;  //Para liberar memoria...
            */

            //20110818 AG: Originalmente estaba asi, pero esto daba el problema que le ponia al name TODO el contenido del strBuild hasta el segundo <
            //$this->name = substr($temp[1],0,strlen($temp[1])-1);

            $strTrimmedData = trim($temp[1]); // Posision libre de caracteres extraños, util para hacer verificaciones del tipo de nodo con substrings

            $intPosBlank = strpos($temp[1]," ");
            if ($intPosBlank === false) $intPosBlank = strlen($temp[1]);
            $intPosClose = strpos($temp[1],">");
            if ($intPosClose === false) $intPosClose = strlen($temp[1]);
            $intPosCloseSrt = strpos($temp[1],"/");
            if ($intPosCloseSrt === false) $intPosCloseSrt = strlen($temp[1]);

            $intTMP = ($intPosBlank < $intPosClose)?$intPosBlank:$intPosClose;
            $intTMP = ($intTMP < $intPosCloseSrt)?$intTMP:$intPosCloseSrt;

            $this->name = substr($temp[1],0,$intTMP);

            $shortTag = false;
            $useText = false;
            if (strpos($strTrimmedData,"=") === false) {
                //SI LA RAIZ NO TIENE ATRIBUTOS

                //VERIFICO SI LA RAIZ ES DEFINIDA CON UN SHORT TAG
                if (substr($strTrimmedData, -2) == "/>") {
                    $this->rootNode = new XMLNode($this->name, "");
                    $shortTag = true;
                }
                else {
                	// No es short tag, puede tener hijos y texto
				    $arrTMP = explode(">", $temp[1]);
				    $temp[1] = $arrTMP[0].">"; // Dejo en $temp[1] solo el nombre
				    $arrTMP[0] = "";//Para liberar memoria

				    // Verifico si hay texto
				    if (isset($arrTMP[1])) {
				    	$strText = trim($arrTMP[1]);
				    	if (!empty($strText)) {
							$strText = $arrTMP[1];
				    		$useText = true;
				    	}
						else {
							$strText = "";
						}
				    }
				    else {
						$strText = "";
				    }

                    $this->rootNode = new XMLNode($this->name, "", $strText);

					$arrTMP = false; //Para liberar memoria
					$strText = false; //Para liberar memoria
                }
            }
            else {
            	// SI tengo atributos

                //VERIFICO SI LA RAIZ ES DEFINIDA CON UN SHORT TAG
                if (substr($strTrimmedData, -2) == "/>") {
                	// Si es con short tag => no tiene hijos ni texto...
                    $this->rootNode = new XMLNode($this->name, substr($strTrimmedData, strlen($this->name) + 1, -2));
                    $shortTag = true;
                }
                else {
                	// No es short tag, puede tener hijos y texto
				    $arrTMP = explode(">", $temp[1]);
				    $temp[1] = $arrTMP[0].">"; // Dejo en $temp[1] solo el nombre y los atributos
				    $arrTMP[0] = "";//Para liberar memoria

				    // Verifico si hay texto
				    if (isset($arrTMP[1])) {
				    	$strText = trim($arrTMP[1]);
				    	if (!empty($strText)) {
							$strText = $arrTMP[1];
				    		$useText = true;
				    	}
						else {
							$strText = "";
						}
				    }
				    else {
						$strText = "";
				    }

                    $this->rootNode = new XMLNode($this->name, substr($temp[1], strlen($this->name) + 1, -1), $strText);

					$arrTMP = false; //Para liberar memoria
					$strText = false; //Para liberar memoria
                }
            }

            //SI NO ESTA DEFINIDA CON SHORT TAG Y NO TIENE TEXTO SIGINIFICA QUE TIENE HIJOS. ENTONCES SE LLAMA LA FUNCION RECURSIVA buildXMLNode
            $cont = 2;
            if (isset($temp[0])) $temp[0] = false; //Para liberar memoria
            if (isset($temp[1])) $temp[1] = false; //Para liberar memoria

            /*
            20111213 AG: La verificacion if (!$shortTag && !$useText) solo la hace para el primer nodo, como para evitar la recursion cuando no es necesaria.
            		     No estoy seguro que tan necesario sea hacer esto...
            */
            if (!$shortTag && !$useText) buildXMLNode($this->rootNode, $temp, $cont);
            $temp = false; //Para liberar memoria
        }
        else {
        	$arrValues = array();
        	$arrIndex = array();

            $objParser = xml_parser_create($strEncoding);
            xml_parser_set_option($objParser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($objParser, XML_OPTION_SKIP_WHITE, 1);
            //xml_parser_set_option($objParser, XML_OPTION_TARGET_ENCODING, $strEncoding);
            xml_parse_into_struct($objParser, $strBuild, $arrValues, $arrIndex);
            $strBuild = false;//Para liberar memoria...

            $intError = xml_get_error_code($objParser);
            xml_parser_free($objParser);
            $objParser = false;

            if ($intError) {
				$this->strError = xml_error_string($intError);
				return false;
            }

            $objCurrDad = false;
            while ($arrItem = each($arrValues)) {
                if ($arrItem["value"]["type"] == "open") {
                    // Si estoy abriendo un nivel
                    if ($objCurrDad === false && $arrItem["value"]["level"] == 1) {
                        $this->rootNode = new XMLNode($arrItem["value"]["tag"], "", "");
                        $objCurrDad = &$this->rootNode;
                    }
                    else {
                        $objCurrDad = &$objCurrDad->children[$objCurrDad->addChild($arrItem["value"]["tag"])];
                    }
                }
                else if ($arrItem["value"]["type"] == "complete") {
                    // Si es un elemento "simple" (sin hijos)
                    if ($objCurrDad === false && $arrItem["value"]["level"] == 1) {
                        $this->rootNode = new XMLNode($arrItem["value"]["tag"], "", (isset($arrItem["value"]["value"]))?(($boolIsUTF8)?utf8_decode($arrItem["value"]["value"]):$arrItem["value"]["value"]):"");
                        //$this->rootNode = new XMLNode($arrItem["value"]["tag"], "", (isset($arrItem["value"]["value"]))?$arrItem["value"]["value"]:"");
                        $objCurrDad = &$this->rootNode;
                    }
                    else {
                        $objCurrDad->addChild($arrItem["value"]["tag"], "", (isset($arrItem["value"]["value"]))?(($boolIsUTF8)?utf8_decode($arrItem["value"]["value"]):$arrItem["value"]["value"]):"");
                        //$objCurrDad->addChild($arrItem["value"]["tag"], "", (isset($arrItem["value"]["value"]))?$arrItem["value"]["value"]:"");
                    }
                }
                else if ($arrItem["value"]["type"] == "close") {
                    // Si estoy cerrando un nivel
                    $objCurrDad = &$objCurrDad->parentNode;
                }
            }
        }
    }

    /**
    * @return void
    * @param string $strPath Camino para llegar al nodo
    * @param unknown $strAttributeName Nombre del atributo, si es empty entonces se modifica el TEXTO
    * @param unknown $strValue Valor a poner
    * @desc Con esto modifico un elemento o atributo dentro de un XML...
    */
    function ModifyElement($strPath="", $strAttributeName = "", $strValue = "") {
    	$objTMP = & $this->rootNode;
    	$arrPath = explode("/",$strPath);
    	$boolOK = true;
    	while ($boolOK && ($arrTMP = each($arrPath))) {
    		$arrPathInstance = explode("[", $arrTMP["value"]);
    		if (isset($arrPathInstance[1])) {
    			$arrPathInstance[1] = substr($arrPathInstance[1], 0, -1);
    		}
    		else {
    			$arrPathInstance[1] = 0;
    		}

    		if (strlen($arrTMP["value"])==0 || !isset($objTMP->children[$objTMP->childrenNames[$arrPathInstance[0]][$arrPathInstance[1]]])) {
    			$boolOK = false;
    		}
    		else {
    			$objTMP = & $objTMP->children[$objTMP->childrenNames[$arrPathInstance[0]][$arrPathInstance[1]]];
    		}
    	}
    	if (empty($strAttributeName)) {
    		$objTMP->text = $strValue;
    	}
    	else {
    		$objTMP->attributes[$strAttributeName] = $strValue;
    	}
    }

    /**
    * @return void
    * @param string $strPath Camino para llegar al nodo
    * @desc Con esto ubico un elemento dentro de un XML...
    */
    function GetElement($strPath="") {
        $objTMP = & $this->rootNode;
        $arrPath = explode("/",$strPath);
        $boolOK = true;
        while ($boolOK && ($arrTMP = each($arrPath))) {
            $arrPathInstance = explode("[", $arrTMP["value"]);
            if (isset($arrPathInstance[1])) {
                $arrPathInstance[1] = substr($arrPathInstance[1], 0, -1);
            }
            else {
                $arrPathInstance[1] = 0;
            }

            if (strlen($arrTMP["value"])==0 || !isset($objTMP->children[$objTMP->childrenNames[$arrPathInstance[0]][$arrPathInstance[1]]])) {
                $boolOK = false;
            }
            else {
                $objTMP = & $objTMP->children[$objTMP->childrenNames[$arrPathInstance[0]][$arrPathInstance[1]]];
            }
        }

        if ($boolOK) {
            return $objTMP;
        }
        else {
            return false;
        }
    }

    /**
    * @return string
    * @desc Devuelve un string que describe el objeto
    */
    function toString() {
        return $this->rootNode->toString();
    }
}
?>