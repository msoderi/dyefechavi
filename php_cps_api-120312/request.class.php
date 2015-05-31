<?php
/**
 * General request class for CPS API
 * @package CPS
 */
  
  
/**
* array - associative or not?
* @param array $array array to check
*/

function is_assoc($array) {
	$assoc = true;
	end($array);
	if (key($array) == count($array) - 1) {
		$assoc = false;
	}
	return $assoc;
}
  
/**
* The Request class contains a single request to CPS storage
* @package CPS
*/
class CPS_Request {
	/**
	* Constructs an instance of the Request class.
	* @param string $command Specifies the command field for the request
	* @param string $requestId The request ID. Can be useful for identifying a particular request in a log file when debugging
	*/
	public function __construct($command, $requestId = '') {
		$this->_command = $command;
		$this->_requestId = $requestId;
		$this->_requestType = NULL;
		$this->_textParams = array();
		$this->_rawParams = array();
		$this->_documents = array();
		$this->_extraXmlParam = NULL;
	}
	
	/**
	* Returns the contents of the request as an XML string
	* @param string &$docRootXpath document root xpath
	* @param string &$docIdXpath document ID xpath
	* @param array &$envelopeParams an associative array of CPS envelope parameters
	* @return string
	*/
	public function getRequestXml(&$docRootXpath, &$docIdXpath, &$envelopeParams, $resetDocIds) {
		unset($this->_requestDom);
		$this->_requestDom = new DomDocument('1.0', 'utf-8');
		$root = $this->_requestDom->createElementNS('www.clusterpoint.com', 'cps:request');
		$this->_requestDom->appendChild($root);
		
		// envelope parameters first
		foreach ($envelopeParams as $name => $value) {
			$root->appendChild($this->_requestDom->createElement('cps:' . $name , $this->getValidXmlValue($value)));
		}
		$contentTag = $root->appendChild($this->_requestDom->createElement('cps:content'));
		
		// content tag text parameters
		foreach ($this->_textParams as $name => $values) {
			if (!is_array($values)) {
				$values[0] = $values;
			}
			foreach ($values as $value) {
				$contentTag->appendChild($this->_requestDom->createElement($name , $this->getValidXmlValue($value)));
			}
		}
		
		// special fields: query, list, ordering
		foreach ($this->_rawParams as $name => $values) {
			if (!is_array($values)) {
				$values[0] = $values;
			}
			foreach ($values as $value) {
				$tag = $this->_requestDom->createElement($name);
				$fragment = $this->_requestDom->createDocumentFragment();
				$fragment->appendXML($value);
				$tag->appendChild($fragment);
				$contentTag->appendChild($tag);
			}
		}
		
		// extra XML content
		if (!is_null($this->_extraXmlParam)) {
			$fragment = $this->_requestDom->createDocumentFragment();
			$fragment->appendXML($this->_extraXmlParam);
			$contentTag->appendChild($fragment);
		}

		// documents, document IDs
		foreach ($this->_documents as $id => $doc) {
			$subdoc = new DOMDocument();
			$subroot = NULL;
			$loadForEach = false;
			if (is_string($doc)) {
				$res = @$subdoc->loadXML($doc);
				if ($res === FALSE) {
					throw new CPS_Exception(array(array('long_message' => 'Invalid request parameter - unable to parse XML', 'code' => ERROR_CODE_INVALID_PARAMETER, 'level' => 'REJECTED', 'source' => 'CPS_API')));
				}
			} else if (is_array($doc)) {
				// associative array
				$loadForEach = true;
			} else if (is_object($doc)) {
				if ($doc instanceof SimpleXMLElement) {
					// SimpleXML
					$loadForEach = true;
				} else if ($doc instanceof DOMNode) {
					// DOM
					$subdoc = $doc;
				} else if ($doc instanceof stdClass) {
					// stdClass
					$loadForEach = true;
				} else {
					throw new CPS_Exception(array(array('long_message' => 'Invalid request parameter', 'code' => ERROR_CODE_INVALID_PARAMETER, 'level' => 'REJECTED', 'source' => 'CPS_API')));
				}
			} else if (is_null($doc)) {
				// just document IDs - no doc integration required
			} else {
				throw new CPS_Exception(array(array('long_message' => 'Invalid request parameter', 'code' => ERROR_CODE_INVALID_PARAMETER, 'level' => 'REJECTED', 'source' => 'CPS_API')));
			}
			
			if (!($subroot = $subdoc->documentElement)) {
				$subroot = $subdoc->createElement($docRootXpath);
				$subdoc->appendChild($subroot);
			}

			if ($loadForEach) {
				CPS_Request::_loadIntoDom($subdoc, $subroot, $doc);
			}
			
			if ($resetDocIds) {
				// integrating ID into the document
				CPS_Request::_setDocId($subdoc, $subroot, $id, $docIdXpath);
			}
			
			//importing subdoc
			$reqNode = $this->_requestDom->importNode($subdoc->documentElement, true);
			$contentTag->appendChild($reqNode);
		}
		$xml = $this->_requestDom->saveXML();
		unset($this->_requestDom);
		return $xml;
	}
	
	/**
	* Returns the string with control characters stripped
	* @param string &$src original string
	* @return string
	*/
	public static function getValidXmlValue(&$src) {
		return strtr($src,
                             "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x0b\x0c\x0e\x0f\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f",
                             '                             ');
	}

	/**
	* returns the command name
	* @return string
	*/
	function getCommand() {
		return $this->_command;
	}
	
	/**
	* sets a request parameter
	* @param string &$name name of the parameter
	* @param mixed &$value value to set. Could be a single string or an array of strings
	*/
	public function setParam($name, $value) {
		if (in_array($name, self::$_textParamNames)) {
			$this->_setTextParam($name, $value);
		} else if (in_array($name, self::$_rawParamNames)) {
			$this->_setRawParam($name, $value);
		} else {
			throw new CPS_Exception(array(array('long_message' => 'Invalid request parameter', 'code' => ERROR_CODE_INVALID_PARAMETER, 'level' => 'REJECTED', 'source' => 'CPS_API')));
		}
	}
	
	/**
	* sets extra XML to send in the content tag of the request. Only use this if you know what you are doing.
	* @param string &$value extra XML as a string
	*/
	public function setExtraXmlParam($value) {
		$this->_extraXmlParam = $value;
	}
	
	/**
	* gets the request id
	* @return string
	*/
	public function getRequestId() {
		return $this->_requestId;
	}
	
	/**
	* sets the request id
	* @param string $requestId
	*/
	public function setRequestId($requestId) {
		$this->_requestId = $requestId;
	}
	
	/**
	* gets the request type
	* @return string
	*/
	public function getRequestType() {
		return $this->_requestType;
	}
	
	/**
	* sets the request type
	* @param string $requestType
	*/
	public function setRequestType($requestType) {
		$this->_requestType = $requestType;
	}
	
	/*
	//**
	* gets a request parameter
	* @param string &$name name of the parameter to get
	//*
	public function getParam(&$name) {
		if (in_array($name, self::$_textParamNames)) {
			return $this->_getTextParam($name);
		} else if (in_array($name, self::$_rawParamNames)) {
			return $this->_getRawParam($name);
		} else {
			// TODO: throw exception
		}
	}*/


	/**#@+
	 * @access private
	 */
	 
	/**
	* sets a text-only request parameter
	* @param string &$name name of the parameter
	* @param mixed &$value value to set. Could be a single string or an array of strings
	*/
	private function _setTextParam(&$name, &$value) {
		if (!is_array($value))
			$value = array($value);
		$this->_textParams[$name] = $value;
	}
	/**
	* sets a raw request parameter
	* @param string &$name name of the parameter
	* @param mixed &$value value to set. Could be a single string or an array of strings
	*/
	private function _setRawParam(&$name, &$value) {
		if (!is_array($value))
			$value = array($value);
		$this->_rawParams[$name] = $value;
	}
	
	/**
	* loads the document into DOM recursively from an array or object/SimpleXMLelement
	* @param DOMDocument &$subdoc into which the nodes are being loaded
	* @param DOMNode &$destNode destination node
	* @param string|array|object|SimpleXMLElement &$srcNode source node
	*/
	private static function _loadIntoDom(&$subdoc, &$destNode, &$srcNode) {
		foreach ($srcNode as $key => $value) {
			$newDestNode = &$subdoc->createElement($key);
			$destNode->appendChild($newDestNode);
			if (is_array($value)) {
				// array - associative or not?
				if (is_assoc($value)) {
					self::_loadIntoDom($subdoc, $newDestNode, $value);
				} else {
					$c = 0;
					foreach ($value as &$v) {
						if ($c) {
							$newDestNode = &$subdoc->createElement($key);
							$destNode->appendChild($newDestNode);
						}
						if (is_string($v) || is_float($v) || is_int($v) || (($v instanceof SimpleXMLElement) && (count($v->children()) == 0))) {
							$textNode = &$subdoc->createTextNode((string) self::getValidXmlValue($v));
							$newDestNode->appendChild($textNode);
						} else {
							self::_loadIntoDom($subdoc, $newDestNode, $v);
						}
/*						$textNode = &$subdoc->createTextNode($v);
						$newDestNode->appendChild($textNode);*/
						++$c;
					}
				}
			} else if (is_object($value) && ($value instanceof SimpleXMLElement)) {
				$domNode = dom_import_simplexml($value);
				$domNode2 = $subdoc->importNode($domNode, true);
				$destNode->replaceChild($domNode2, $newDestNode);
			} else if (is_object($value)) {
				self::_loadIntoDom($subdoc, $newDestNode, $value);
			} else if (is_string($value) || is_float($value) || is_int($value)) {
				$textNode = &$subdoc->createTextNode((string) self::getValidXmlValue($value));
				$newDestNode->appendChild($textNode);
			}
		}
	}
	
	/**
	* sets the docid inside the document
	* @param DOMDocument &$subdoc document where the id should be loaded into
	*/
	private static function _setDocId(&$subdoc, &$parentNode, $id, &$docIdXpath, $curLevel = 0) {
		if ($parentNode->nodeName == $docIdXpath[$curLevel]) {
			if ($curLevel == count($docIdXpath) - 1) {
				// remove all sub-nodes
				$curChild = $parentNode->firstChild;
				while ($curChild) {
					$nextChild = $curChild->nextSibling;
					$parentNode->removeChild($curChild);
					$curChild = $nextChild;
				}
				// set the ID
				$textNode = $subdoc->createTextNode($id);
				$parentNode->appendChild($textNode);
			} else {
				// traverse children
				$found = false;
				$curChild = $parentNode->firstChild;
				while ($curChild) {
					if ($curChild->nodeName == $docIdXpath[$curLevel + 1]) {
						CPS_Request::_setDocId($subdoc, $curChild, $id, $docIdXpath, $curLevel + 1);
						$found = true;
						break;
					}
					$curChild = $curChild->nextSibling;
				}
				if (!$found) {
					$newNode = $subdoc->createElement($docIdXpath[$curLevel + 1]);
					$parentNode->appendChild($newNode);
					CPS_Request::_setDocId($subdoc, $newNode, $id, $docIdXpath, $curLevel + 1);
				}
			}		
		} else {
			throw new CPS_Exception(array(array('long_message' => 'Document root xpath not matching document ID xpath', 'code' => ERROR_CODE_INVALID_XPATHS, 'level' => 'REJECTED', 'source' => 'CPS_API')));
		}
	}
	
	 
	private static $_textParamNames = array(
		'added_external_id',
		'added_id',
		'case_sensitive',
		'cr',
		'deleted_external_id',
		'deleted_id',
		'description',
		'docs',
		'exact-match',
		'facet',
		'fail_if_exists',
		'file',
		'finalize',
		'for',
		'force',
		'force_segment',
		'from',
		'full',
		'group',
		'group_size',
		'h',
		'id',
		'idif',
		'iterator_id',
		'len',
		'message',
		'offset',
		'optimize_to',
		'path',
		'persistent',
		'position',
		'quota',
		'rate2_ordering',
		'rate_from',
		'rate_to',
		'relevance',
		'return_doc',
		'return_internal',
		'sequence_check',
		'stem-lang',
		'step_size',
		'text',
		'type'
	);
	private static $_rawParamNames = array(
		'query',
		'list',
		'ordering'
	);
	private $_requestId;
	private $_requestDom;
	private $_command;
	private $_textParams;
	private $_rawParams;
	private $_extraXmlParam;
	protected $_documents;
	/**#@-*/
}
?>
