<?php
/**
 * Connection class for CPS API
 * @package CPS
 */ 
 
/**
* Including internals
*/
require_once dirname(__FILE__) . '/internals/lib_cps2_helper.inc.php';
require_once dirname(__FILE__) . '/internals/lib_http.inc.php';
 
/**
* The connection class - represents a connection to Clusterpoint Storage
* @package CPS
*/
class CPS_Connection {
	/**
	* Constructs an instance of the Connection class. Note that it doesn't
	* necessarily make a connection to CPS when the constructor is called.
	* @param string $connectionString Specifies the connection string, such as tcp://127.0.0.1:5550
	* @param string $storageName The name of the storage you want to connect to
	* @param string $username Username for authenticating with the storage
	* @param string $password Password for this user
	* @param string $documentRootXpath Document root tag name. Default is "document"
	* @param string $documentIdXpath Document ID xpath. Default is "document/id"
	*/
	public function __construct($connectionString, $storageName, $username, $password, $documentRootXpath = 'document', $documentIdXpath = '//document/id') {
		$this->_storageName = $storageName;
		$this->_username = $username;
		$this->_password = $password;
		$this->_documentRootXpath = $documentRootXpath;
		$this->_documentIdXpath = preg_split("#[/]+#", $documentIdXpath, -1, PREG_SPLIT_NO_EMPTY);		
		$this->_connectionString = $this->_parseConnectionString($connectionString);
		
		$this->_applicationId = 'CPS_PHP_API';
		$this->_connection = NULL;
		$this->_debug = false;
		$this->_resetDocIds = true;
		$this->_noCdata = false;
	}
			
	/**
	* Sends the request to CPS
	* @param CPS_Request &$request An object of the class Request
	*/
	public function sendRequest(CPS_Request &$request) {
		$requestXml = $this->_renderRequest($request);
		if ($this->_debug) {
			echo 'Sending: <br /><pre>' . htmlspecialchars($requestXml) . '</pre>';
		}
		$time_start = microtime(true);
		if ($this->_connectionString['type'] == 'socket') {
			$rawResponse = cps2_exchange($this->_connectionString['host'], $this->_connectionString['port'], $requestXml, $this->_storageName);
		} else {
			// TODO: use curl?
			$rawResponse = http_data(http_post($this->_connectionString['url'], $requestXml, false, 'Recipient: ' . str_replace(array("\r" , "\n"), '', $this->_storageName) . "\r\n"));
		}
		$time_end = microtime(true);
		$this->_lastRequestDuration = $time_end - $time_start;
		if ($this->_debug) {
			echo 'Received: <br /><pre>' . htmlspecialchars($rawResponse) . '</pre>';
		}
		switch ($request->getCommand()) {
		case 'search':
			return new CPS_SearchResponse($this, $request, $rawResponse, $this->_noCdata);
		case 'update':
		case 'delete':
		case 'replace':
		case 'partial-replace':
		case 'insert':
			return new CPS_ModifyResponse($this, $request, $rawResponse, $this->_noCdata);
		case 'alternatives':
			return new CPS_AlternativesResponse($this, $request, $rawResponse, $this->_noCdata);
		case 'list-words':
			return new CPS_ListWordsResponse($this, $request, $rawResponse, $this->_noCdata);
		case 'status':
			return new CPS_StatusResponse($this, $request, $rawResponse, $this->_noCdata);
		case 'retrieve':
		case 'list-last':
		case 'list-first':
		case 'retrieve-last':
		case 'retrieve-first':
		case 'lookup':
		case 'similar':
			return new CPS_LookupResponse($this, $request, $rawResponse, $this->_noCdata);
		case 'search-delete':
			return new CPS_SearchDeleteResponse($this, $request, $rawResponse, $this->_noCdata);
		case 'list-paths':
			return new CPS_ListPathsResponse($this, $request, $rawResponse, $this->_noCdata);
		case 'list-facets':
			return new CPS_ListFacetsResponse($this, $request, $rawResponse, $this->_noCdata);
		default:
			return new CPS_Response($this, $request, $rawResponse, $this->_noCdata);
		}
	}
	
	/**
	* Sets the application ID for the request
	*
	* This method can be used to set a custom application ID for your requests.
	* This can be useful to identify requests sent by a particular application
	* in a log file
	*
	* @param string &$applicationId
	*/
	public function setApplicationId(&$applicationId) {
		$this->_applicationId = $applicationId;
	}
	
	/**
	* Toggles the CDATA integration flag
	*
	* Set to true, if you want CDATA values to be converted to text
	*
	* @param bool $v
	*/
	public function setNoCdata($v) {
		$this->_noCdata = $v;
	}
	
	/**
	* Sets the debugging mode
	* @param int $debugMode
	*/
	public function setDebug($debugMode) {
		$this->_debug = $debugMode;
	}
	
	/**
	* Enables or disables resetting document IDs when modifying. On by default.
	* You should change this setting if You plan to insert documents with auto-incremented IDs
	* or with IDs already integrated into the document
	* @param bool $resetIds
	*/
	public function setDocIdResetting($resetIds) {
		$this->_resetDocIds = $resetIds;
	}
	
	/**#@+
	 * @access private
	 */
	 
	/**
	* Renders the request as XML
	*
	* @param Request &$request
	* @return string The full XML request
	*/
	 
	private function _renderRequest(CPS_Request &$request) {
		$envelopeFields = array(
			'storage' => $this->_storageName,
			'user' => $this->_username,
			'password' => $this->_password,
			'command' => $request->getCommand(),
		);
		if (strlen($request->getRequestId()) > 0)
			$envelopeFields['requestid'] = $request->getRequestId();
		if (strlen($this->_applicationId) > 0)
			$envelopeFields['application'] = $this->_applicationId;
		if (!is_null($reqType = $request->getRequestType()))
			$envelopeFields['type'] = $reqType;
		return $request->getRequestXml($this->_documentRootXpath, $this->_documentIdXpath, $envelopeFields, $this->_resetDocIds);
	}
	 
	/**
	* returns the document root xpath
	*/
			
	function getDocumentRootXpath() {
		return $this->_documentRootXpath;
	}
	
	/**
	* returns the document ID xpath
	* @return array
	*/
	function getDocumentIdXpath() {
		return $this->_documentIdXpath;
	}
	
	/**
	* returns the duration (in seconds) of the last request, as measured on the client side
	* @return array
	*/
	function getLastRequestDuration() {
		return $this->_lastRequestDuration;
	}
	
	/**
	* returns an array with parsed connection string data
	*/
	private function _parseConnectionString($string) {
		$res = array();
		if (($string == '') || ($string == 'unix://')) {
			// default connection
			$res['type'] = 'socket';
			$res['host'] = 'unix:///usr/local/cps2/storages/' . str_replace('/', '_', $this->_storageName) . '/storage.sock';
			$res['port'] = 0;
		} else if (strncmp($string, 'http://', 7) == 0) {
			// HTTP connection
			$res['type'] = 'http';
			$res['url'] = $string;
		} else if (strncmp($string, 'unix://', 7) == 0) {
			// Unix socket
			$res['type'] = 'socket';
			$res['host'] = $string;
			$res['port'] = 0;
		} else if (strncmp($string, 'tcp://', 6) == 0) {
			// TCP socket
			$res['type'] = 'socket';
			$uc = parse_url($string);
			if (!isset($uc['host'])) {
				throw new CPS_Exception(array(array('long_message' => 'Invalid connection string', 'code' => ERROR_CODE_INVALID_CONNECTION_STRING, 'level' => 'REJECTED', 'source' => 'CPS_API')));
			}
			$res['host'] = $uc['host'];
			if (isset($uc['port'])) {
				$res['port'] = $uc['port'];
			} else {
				$res['port'] = 5550;
			}
		} else {
			throw new CPS_Exception(array(array('long_message' => 'Invalid connection string', 'code' => ERROR_CODE_INVALID_CONNECTION_STRING, 'level' => 'REJECTED', 'source' => 'CPS_API')));
		}
		return $res;
	}
	
	private $_connectionString;
	private $_storageName;
	private $_username;
	private $_password;
	private $_documentRootXpath;
	private $_documentIdXpath;
	private $_applicationId;
	private $_connection;
	private $_debug;
	private $_resetDocIds;
	private $_lastRequestDuration;
	private $_noCdata;
	
	/**#@-*/
}
?>