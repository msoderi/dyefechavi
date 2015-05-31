<?php
/**
 * General response class for CPS API
 * @package CPS
 */

 
/**
* These define the document return types for document responses
* @see CPS_SearchResponse::getDocuments(), CPS_LookupResponse::getDocuments()
*/
define('DOC_TYPE_SIMPLEXML', 1);
define('DOC_TYPE_ARRAY', 2);
define('DOC_TYPE_STDCLASS', 3);
 
 /**
* The Response class contains a response received from CPS storage
* @package CPS
*/
class CPS_Response {
	/**
	* Constructs an instance of the Response class.
	* @param CPS_Connection &$connection CPS connection object
	* @param CPS_Request &$request The original request that the response is to
	* @param string &$responseXml The raw XML response
	* @param bool $noCdata set this to TRUE if you want cdata returned as text
	*/
	public function __construct(CPS_Connection &$connection, CPS_Request &$request, &$responseXml, $noCdata = FALSE) {
		try {
			$this->_simpleXml = @new SimpleXMLElement($responseXml, ($noCdata ? LIBXML_NOCDATA : 0));
		} catch (Exception $e) {
			throw new CPS_Exception(array(array('long_message' => 'Invalid response XML', 'code' => ERROR_CODE_INVALID_RESPONSE, 'level' => 'ERROR', 'source' => 'CPS_API')), $e);
		}
		$cpsChildren = $this->_simpleXml->children('www.clusterpoint.com');
		if (count($cpsChildren) == 0) {
			throw new CPS_Exception(array(array('long_message' => 'Invalid response XML', 'code' => ERROR_CODE_INVALID_RESPONSE, 'level' => 'ERROR', 'source' => 'CPS_API')));
		}
		$this->_storageName = (string) $cpsChildren->storage;
		$this->_command = (string) $cpsChildren->command;
		$this->_seconds = (float) $cpsChildren->seconds;
		$this->_errors = $cpsChildren->error;
		$errors = array();
		foreach ($this->_errors as $error) {
			$error = $error->children();
			if (($error->level == 'REJECTED') || ($error->level == 'FAILED') || ($error->level == 'ERROR') || ($error->level == 'FATAL')) {
				$errorArray = array(
					'long_message' => (string) $error->message,
					'short_message' => (string) $error->text,
					'code' => (int) $error->code,
					'source' => (string) $error->source,
					'level' => (string) $error->level
				);
				if (isset($error->document_id))
					$errorArray['document_id'] = (string) $error->document_id;
				$errors[] = $errorArray;
			}
		}
		if (count($errors) > 0) {
			throw new CPS_Exception($errors);
		}
		$this->_documents = array();
		$this->_facets = array();
		$this->_textParams = array();
		$this->_words = array();
		$this->_contentArray = array();
		$this->_paths = array();
		if (isset($cpsChildren->content)) {
			$cpsContent = $cpsChildren->content->children();
			
			$docs = NULL;
			$saveDocs = true;
			switch ($this->_command) {
			case 'update':
			case 'delete':
			case 'insert':
			case 'partial-replace':
			case 'replace':
			case 'lookup':
				$docs = $cpsContent;
				if ($this->_command != 'lookup') {
					$saveDocs = false;
				}
			case 'search':
			case 'retrieve':
			case 'retrieve-last':
			case 'retrieve-first':
			case 'list-last':
			case 'list-first':
			case 'similar':
				if (is_null($docs)) {
					if (isset($cpsContent->results)) {
						$docs = $cpsContent->results[0];
					} else {
						$docs = array();
					}
				}
				// extracting documents / document IDs
				$idPath = $connection->getDocumentIdXpath();
				
				$curpos = 0;
				foreach ($docs as $rootTag => $doc) {
					if ($rootTag == $idPath[0]) {
						$id = '';
						$cur = $doc;
						for ($i = 1; $i < count($idPath); ++$i) {
							if (!isset($cur->$idPath[$i])) {
								break;
							}
							if ($i == count($idPath) - 1) {
								$id = (string) $cur->$idPath[$i];
							} else {
								$cur = $cur->$idPath[$i];
							}
						}
						if (strlen($id) > 0) {
							$index = $id;
						} else {
							$index = $curpos;
						}
						$this->_documents[$index] = ($saveDocs ? $doc : NULL);
					}
					++$curpos;
				}
				break;
			case 'alternatives':
				if (isset($cpsContent->alternatives_list)) {
					foreach ($cpsContent->alternatives_list->alternatives as $alt) {
						$alts = array();
						foreach ($alt->word as $word) {
							$alts[(string) $word] = array('h' => (string) $word['h'], 'idif' => (string) $word['idif'], 'cr' => (string) $word['cr'], 'count' => (string) $word['count']);
						}
						$this->_words[(string) $alt->to] = $alts;
					}
				}
				break;
			case 'list-words':
				if (isset($cpsContent->list)) {
					foreach ($cpsContent->list as $list) {
						$words = array();
						foreach ($list->word as $word) {
							$words[(string) $word] = (string) $word['count'];
						}
						$this->_words[(string) $list['to']] = $words;
					}
				}
				break;
			case 'status':
			case 'list-paths':
				$this->_contentArray = CPS_Response::simpleXmlToArray($cpsContent);
				break;
			default:
				// no special parsing
			}
			
			if (($this->_command == 'search') || ($this->_command == 'list-facets')) {
				if (isset($cpsContent->facet)) {
					foreach ($cpsContent->facet as $facet) {
						$path = (string) $facet['path'];
						$terms = array();
						foreach ($facet->term as $term) {
							if ($this->_command == 'search') {
								$terms[(string) $term] = intval($term['hits']);
							} else if ($this->_command == 'list-facets') {
								$terms[] = (string) $term;
							}
						}
						$this->_facets[$path] = $terms;
					}
				}
			}

			
			$this->_textParams = array();
			foreach ($cpsContent as $name => $value) {
				if (in_array($name, self::$_textParamNames)) {
					$this->_textParams[$name] = (string) $value;
				}
			}
		}
	}
	
	/**
	* Returns the documents from the response
	* @return array
	*/
	public function getRawDocuments($returnType) {
		if ($returnType == DOC_TYPE_ARRAY) {
			$res = array();
			foreach ($this->_documents as $key => $value) {
				$res[$key] = CPS_Response::simpleXmlToArray($value);
			}
			return $res;
		} else if ($returnType == DOC_TYPE_STDCLASS) {
			$res = array();
			foreach ($this->_documents as $key => $value) {
				$res[$key] = CPS_Response::simpleXmlToStdClass($value);
			}
			return $res;
		}
		return $this->_documents;
	}
	
	/**
	* Returns the facets from the response
	* @return array
	*/
	public function getRawFacets() {
		return $this->_facets;
	}
	
	/**
	* Returns the alternatives from the response
	* @return array
	*/
	public function getRawWords() {
		return $this->_words;
	}
	
	/**
	* Returns a response parameter
	* @param $name parameter name
	* @return string
	*/
	public function getParam($name) {
		if (in_array($name, self::$_textParamNames)) {
			return $this->_textParams[$name];
		} else {
			throw new CPS_Exception(array(array('long_message' => 'Invalid response parameter', 'code' => ERROR_CODE_INVALID_PARAMETER, 'level' => 'REJECTED', 'source' => 'CPS_API')));
		}
	}
	
	/**
	* Returns the time that it took to process the request in the CPS engine
	* @return float
	*/
	public function getSeconds() {
		return $this->_seconds;
	}
	
	/**#@+
	 * @access private
	 */
	
	/**
	* Converts SimpleXMLElement to an array
	*/
	static function simpleXmlToArray(SimpleXMLElement &$source) {
		$res = array();
		$children = false;
		foreach ($source as $key => $value) {
			if (isset($res[$key])) {
				if (is_string($res[$key]) || (is_array($res[$key]) && is_assoc($res[$key]))) {
					$res[$key] = array($res[$key]);
				}
				$res[$key][] = CPS_Response::simpleXmlToArray($value);
			} else {
				$res[$key] = CPS_Response::simpleXmlToArray($value);
			}
			$children = true;
		}
		if (!$children)
			return (string) $source;
		return $res;
	}
	
	/**
	* Converts SimpleXMLElement to stdClass
	*/
	static function simpleXmlToStdClass(SimpleXMLElement &$source) {
		$res = new StdClass;
		$children = false;
		foreach ($source as $key => $value) {
			if (isset($res->$key)) {
				if (!is_array($res->$key)) {
					$res->$key = array($res->$key);
				}
				$ref = &$res->$key;
				$ref[] = CPS_Response::simpleXmlToStdClass($value);
			} else {
				$res->$key = CPS_Response::simpleXmlToStdClass($value);
			}
			$children = true;
		}
		if (!$children)
			return (string) $source;
		return $res;
	}
	 
	/**
	* Returns the cps:content tag
	* @param int $type defines which datatype the returned documents will be in. Default is DOC_TYPE_ARRAY, another possible value is DOC_TYPE_ARRAY
	* @return array|SimpleXMLElement
	*/
	public function getContentArray($type = DOC_TYPE_ARRAY) {
		if ($type == DOC_TYPE_SIMPLEXML) {
			$cpsChildren = $this->_simpleXml->children('www.clusterpoint.com');
			if (isset($cpsChildren->content)) {
				return $cpsChildren->content;
			} else {
				return NULL;
			}
		} else if ($type == DOC_TYPE_ARRAY) {
			return $this->_contentArray;
		}
	}
	 
	 private static $_textParamNames = array(
		'hits',
		'from',
		'to',
		'found',
		'real_query',
		'iterator_id',
	);
	private $_simpleXml;
	private $_storageName;
	private $_command;
	private $_seconds;
	private $_errors;
	private $_hits;
	private $_found;
	private $_from;
	private $_to;
	protected $_documents;
	protected $_facets;
	protected $_textParams;
	protected $_words;
	protected $_paths;
	protected $_contentArray;
	/**#@-*/
}
?>