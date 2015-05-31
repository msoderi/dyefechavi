<?php
/**
 * Request and response classes for all commands in the CPS API
 * @package CPS
 */
 
/**
* request/response includes
*/

require_once dirname(__FILE__) . '/request.class.php';
require_once dirname(__FILE__) . '/response.class.php';
 
/**
* Escapes <, > and & characters in the given term for inclusion into XML (like the search query). Also wraps the term in XML tags if xpath is specified.
* Note that this function doesn't escape the @, $, " and other symbols that are meaningful in a search query. If You want to escape input that comes directly
* from the user and that isn't supposed to contain any search operators at all, it's probably better to use {@link CPS_QueryTerm}
* @param string $term the term to be escaped (e.g. a search query term)
* @param string $xpath an optional xpath, to be specified if the search term is to be searched under a specific xpath
* @param bool $escape an optional parameter - whether to escape the term's XML
* @see CPS_QueryTerm
*/
function CPS_Term($term, $xpath = '', $escape = TRUE) {
	$prefix = ' ';
	$postfix = ' ';
	if (strlen($xpath) > 0) {
		$tags = explode('/', $xpath);
		foreach ($tags as $tag) {
			if (strlen($tag) > 0) {
				$prefix .= '<' . $tag . '>';
				$postfix = '</' . $tag . '>' . $postfix;
			}
		}
	}
	return $prefix . ($escape ? htmlspecialchars($term, ENT_NOQUOTES) : $term) . $postfix;
}

/**
* Escapes <, > and & characters, as well as @"{}()=$~+ (search query operators) in the given term for inclusion into the search query.
* Also wraps the term in XML tags if xpath is specified.
* @param string $term the term to be escaped (e.g. a search query term)
* @param string $xpath an optional xpath, to be specified if the search term is to be searched under a specific xpath
* @param string $allowed_symbols a string containing operator symbols that the user is allowed to use (e.g. ")
* @see CPS_Term
*/
function CPS_QueryTerm($term, $xpath = '', $allowed_symbols = '') {
	$newTerm = '';
	$len = strlen($term);
	for ($x = 0; $x < $len; ++$x) {
		switch ($term[$x]) {
		case '@':
		case '$':
		case '"':
		case '=':
		case '>':
		case '<':
		case ')':
		case '(':
		case '{':
		case '}':
		case '~':
		case '+':
			if (strstr($allowed_symbols, $term[$x]) === FALSE)
				$newTerm .= '\\';
		default:
			$newTerm .= $term[$x];
		}
	}
	return CPS_Term($newTerm, $xpath);
}

/**
* Converts a given query array to a query string
* @param array $array the query array
* @return string
*/
function CPS_QueryArray($array) {
	$r = '';
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$r .= CPS_Term(CPS_QueryArray($value), $key, false);
		} else {
			$r .= CPS_Term($value, $key);
		}
	} 
	return $r;
} 

/**
* Returns an ordering string for sorting by relevance
* @see CPS_SearchRequest::setOrdering()
* @param string $ascdesc optional parameter to specify ascending/descending order. By default most relevant documents are returned first
*/

function CPS_RelevanceOrdering($ascdesc = '') {
	return '<relevance>' . htmlspecialchars($ascdesc, ENT_NOQUOTES) . '</relevance>';
}

/**
* Returns an ordering string for sorting by a numeric field
* @see CPS_SearchRequest::setOrdering()
* @param string $tag the xpath of the tag by which You wish to perform sorting
* @param string $ascdesc optional parameter to specify ascending/descending order. By default ascending order is used.
*/

function CPS_NumericOrdering($tag, $ascdesc = 'ascending') {
	return '<numeric>' . CPS_Term($ascdesc, $tag) . '</numeric>';
}


/**#@+
 * @access private
 */
function CPS_GenericDistanceOrdering($type, $array, $ascdesc) {
	$res = '<distance type="' . htmlspecialchars($type) . '" order="' . htmlspecialchars($ascdesc) . '">';
	foreach ($array as $path => $value) {
		$res .= CPS_Term($value, $path);
	}
	$res .= '</distance>';
	return $res;
}

/**#@-*/

/**
* Returns an ordering string for sorting by distance from a latitude/longitude coordinate pair
* @see CPS_SearchRequest::setOrdering()
* @param array $array an associative array with tag xpaths as keys and centerpoint coordinates as values. Should contain exactly two elements - latitude first and longitude second.
* @param string $ascdesc optional parameter to specify ascending/descending order. By default ascending order is used.
*/

function CPS_LatLonDistanceOrdering($array, $ascdesc = 'ascending') {
	return CPS_GenericDistanceOrdering('latlong', $array, $ascdesc);
}

/**
* Returns an ordering string for sorting by distance from specified coordinates on a geometric plane
* @see CPS_SearchRequest::setOrdering()
* @param array $array an associative array with tag xpaths as keys and centerpoint coordinates as values.
* @param string $ascdesc optional parameter to specify ascending/descending order. By default ascending order is used.
*/
function CPS_PlaneDistanceOrdering($array, $ascdesc = 'ascending') {
	return CPS_GenericDistanceOrdering('plane', $array, $ascdesc);
}
 
// Search
 
/**
* The CPS_SearchRequest class is a wrapper for the Request class
* @package CPS
* @see CPS_SearchResponse
*/
class CPS_SearchRequest extends CPS_Request {
	/**
	* Constructs an instance of the CPS_SearchRequest class.
	* @param array|string $query The query array/string. see {@link CPS_SearchRequest::setQuery()} for more info.
	* @param int $offset Defines the number of documents to skip before including them in the results
	* @param int $docs Maximum document count to retrieve
	* @param array $list Listing parameter - an associative array with tag xpaths as keys and listing options (yes | no | snippet | highlight) as values
	*/
	public function __construct($query, $offset = NULL, $docs = NULL, $list = NULL) {
		parent::__construct('search');
		$this->setQuery($query);
		if (!is_null($offset))
			$this->setOffset($offset);
		if (!is_null($docs))
			$this->setDocs($docs);
		if (!is_null($list))
			$this->setList($list);
	}
	
	/**
	* Sets the search query.
	*
	* Example usage:
	* <code>$r->setQuery('(' . CPS_Term('predefined_term', '/generated_fields/type/') . CPS_QueryTerm($user_supplied_terms, '/searchable_fields/text') . ')');</code>
	* or
	* <code>$r->setQuery(array('tags' => array('title' => 'Title', 'text' => 'Text')));</code>
	* or
	* <code>$r->setQuery(array('tags/title' => 'Title', 'tags/text' => 'Text'));</code>
	* @param array|string $value The query array/string.
	* If the string form is used, all <, > and & characters that aren't supposed to be XML tags, should be escaped (e.g. with {@link CPS_Term} or {@link CPS_QueryTerm});
	* @see CPS_QueryTerm, CPS_Term
	*/
	public function setQuery($value) {
		if (is_array($value)) {
			$this->setParam('query', CPS_QueryArray($value));
		} else {
			$this->setParam('query', $value);
		}
	}
	
	/**
	* Sets the maximum number of documents to be returned
	* @param int $value maximum number of documents
	*/
	public function setDocs($value) {
		$this->setParam('docs', $value);
	}
	
	/**
	* Sets the number of documents to skip in the results
	* @param int $value number of results to skip
	*/
	public function setOffset($value) {
		$this->setParam('offset', $value);
	}
	
	/**
	* Sets the paths for facets
	* @param string|array $value a single path as a string or an array of paths
	*/
	public function setFacet($value) {
		$this->setParam('facet', $value);
	}
	
	/**
	* Sets the stemming language
	* @param string $value 2-letter language ID
	*/
	public function setStemLang($value) {
		$this->setParam('stem-lang', $value);
	}
	
	/**
	* Sets the exact match option
	* @param string $value Exact match option : text, binary or all
	*/
	public function setExactMatch($value) {
		$this->setParam('exact-match', $value);
	}
	
	/**
	* Sets grouping options
	* @param string $tagName name of the grouping tag
	* @param int $count maximum number of documents to return from each group
	*/
	public function setGroup($tagName, $count) {
		$this->setParam('group', $tagName);
		$this->setParam('group_size', $count);
	}
	
	/**
	* Defines which tags of the search results should be listed in the response
	* @param array $array an associative array with tag xpaths as keys and listing options (yes, no, snippet or highlight) as values
	*/
	public function setList($array) {
		$listString = '';
		foreach ($array as $key => $value) {
			$listString .= CPS_Term($value, $key);
		}
		$this->setParam('list', $listString);
	}
	
	/**
	* Defines the order in which results should be returned.
	* @param string|array $order either a single sorting string or an array of those. Could be conveniently generated with ordering macros,
	* e.g. $q->setOrdering(array(CPS_NumericOrdering('user_count', 'desc'), CPS_RelevanceOrdering())) will sort the documents in descending order
	* according to the user_count, and if user_count is equal will sort them by relevance.
	* @see CPS_RelevanceOrdering, CPS_NumericOrdering, CPS_LatLonDistanceOrdering, CPS_PlainDistanceOrdering
	*/
	public function setOrdering($order) {
		if (is_array($order)) {
			$order = implode('', $order);
		}
		$this->setParam('ordering', $order);
	}
}

/**
* The CPS_SearchResponse class is a wrapper for the Response class
* @package CPS
* @see CPS_SearchRequest
*/
class CPS_SearchResponse extends CPS_Response {
	/**
	* Returns the documents from the response as an associative array, where keys are document IDs and values area document contents
	* @param int $type defines which datatype the returned documents will be in. Default is DOC_TYPE_SIMPLEXML, other possible values are DOC_TYPE_ARRAY and DOC_TYPE_STDCLASS
	* @return array
	*/
	public function getDocuments($type = DOC_TYPE_SIMPLEXML) {
		return parent::getRawDocuments($type);
	}
	
	/**
	* Returns the facets from the response in a form of a multi-dimensional associative array, e.g. array('category' => array('Sports' => 15, 'News' => 20));
	* @return array
	*/
	public function getFacets() {
		return parent::getRawFacets();
	}
	
	/**
	* Returns the number of documents returned
	* @return int
	*/
	public function getFound() {
		return $this->getParam('found');
	}
	
	/**
	* Returns the total number of hits - i.e. the number of documents in a storage that match the request
	* @return int
	*/
	public function getHits() {
		return $this->getParam('hits');
	}
	
	/**
	* Returns the position of the first document that was returned
	* @return int
	* @see CPS_SearchRequest::setOffset(), CPS_SearchRequest::setDocs()
	*/
	public function getFrom() {
		return $this->getParam('from');
	}
	
	/**
	* Returns the position of the last document that was returned
	* @see CPS_SearchRequest::setOffset(), CPS_SearchRequest::setDocs()
	* @return int
	*/
	public function getTo() {
		return $this->getParam('to');
	}
}

/**#@+
 * @access private
 */

/**
* The CPS_ModifyRequest class is a wrapper for the Request class
* @package CPS
* @see CPS_ModifyResponse
*/
class CPS_ModifyRequest extends CPS_Request {
	/**
	* Constructs an instance of the CPS_ModifyRequest class.
	* Possible parameter sets:<br />
	* 3 parameters - ($command, $id, $document), where $id is the document ID and $document is its contents<br />
	* 2 parameters - ($command, $array), where $array is an associative array with document IDs as keys and document contents as values<br />
	* @param string $command name of the command
	* @param string|array $arg1 Either the document id or the associative array of ids => docs
	* @param mixed|null $arg2 Either the document contents or NULL
	*/
	public function __construct($command, $arg1, $arg2) {
		parent::__construct($command);
		if (is_array($arg1)) {
			if (!is_null($arg2)) {
				throw new CPS_Exception(array(array('long_message' => 'Invalid request parameter', 'code' => ERROR_CODE_INVALID_PARAMETER, 'level' => 'REJECTED', 'source' => 'CPS_API')));
			}
			// single argument - an associative array
			$this->_documents = $arg1;
		} elseif (!is_null($arg2)) {
			// two arguments - first is the id, second is the document content
			$this->_documents = array ($arg1 => $arg2);
		} else {
			throw new CPS_Exception(array(array('long_message' => 'Invalid request parameter', 'code' => ERROR_CODE_INVALID_PARAMETER, 'level' => 'REJECTED', 'source' => 'CPS_API')));
		}
	}
}

/**
* The CPS_ListLastRetrieveFirstRequest class is a wrapper for the Request class for list-last, list-first, retrieve-last, and retrieve-first requests
* @package CPS
* @see CPS_LookupResponse
*/
class CPS_ListLastRetrieveFirstRequest extends CPS_Request {
	/**
	* Constructs an instance of the CPS_ListLastRetrieveFirstRequest class.
	* @param string $command command name
	* @param int $offset offset
	* @param int $docs max number of docs to return
	*/
	public function __construct($command, $offset, $docs, $list=NULL) {
		parent::__construct($command);
		if (strlen($offset) > 0) {
			$this->setParam('offset', $offset);
		}
		if (strlen($docs) > 0) {
			$this->setParam('docs', $docs);
		}
		if (!is_null($list))
			$this->setList($list);
	}
	
	/**
	* Defines which tags of the search results should be listed in the response
	* @param array $array an associative array with tag xpaths as keys and listing options (yes, no, snippet or highlight) as values
	*/
	public function setList($array) {
		$listString = '';
		foreach ($array as $key => $value) {
			$listString .= CPS_Term($value, $key);
		}
		$this->setParam('list', $listString);
	}
}

/**#@-*/

/**
* The CPS_InsertRequest class is a wrapper for the Response class for the insert command
* @package CPS
* @see CPS_ModifyResponse
*/
class CPS_InsertRequest extends CPS_ModifyRequest {
	/**
	* Constructs an instance of the CPS_InsertRequest class.
	* Possible parameter sets:<br />
	* 2 parameters - ($id, $document), where $id is the document ID and $document are its contents<br />
	* 1 parameter - ($array), where $array is an associative array with document IDs as keys and document contents as values<br />
	* @param string|array $arg1 Either the document id or the associative array of ids => docs
	* @param mixed|null $arg2 Either the document contents or NULL (can also be omitted)
	*/
	public function __construct($arg1, $arg2=NULL) {
		parent::__construct('insert', $arg1, $arg2);
	}
}

/**
* The CPS_UpdateRequest class is a wrapper for the Response class for the update command
* @package CPS
* @see CPS_ModifyResponse
*/
class CPS_UpdateRequest extends CPS_ModifyRequest {
	/**
	* Constructs an instance of the CPS_UpdateRequest class.
	* Possible parameter sets:<br />
	* 2 parameters - ($id, $document), where $id is the document ID and $document are its contents<br />
	* 1 parameter - ($array), where $array is an associative array with document IDs as keys and document contents as values<br />
	* @param string|array $arg1 Either the document id or the associative array of ids => docs
	* @param mixed|null $arg2 Either the document contents or NULL (can also be omitted)
	*/
	public function __construct($arg1, $arg2=NULL) {
		parent::__construct('update', $arg1, $arg2);
	}
}

/**
* The CPS_ReplaceRequest class is a wrapper for the Response class for the replace command
* @package CPS
* @see CPS_ModifyResponse
*/
class CPS_ReplaceRequest extends CPS_ModifyRequest {
	/**
	* Constructs an instance of the CPS_ReplaceRequest class.
	* Possible parameter sets:<br />
	* 2 parameters - ($id, $document), where $id is the document ID and $document are its contents<br />
	* 1 parameter - ($array), where $array is an associative array with document IDs as keys and document contents as values<br />
	* @param string|array $arg1 Either the document id or the associative array of ids => docs
	* @param mixed|null $arg2 Either the document contents or NULL (can also be omitted)
	*/
	public function __construct($arg1, $arg2=NULL) {
		parent::__construct('replace', $arg1, $arg2);
	}
}

/**
* The CPS_PartialReplaceRequest class is a wrapper for the Response class for the partial-replace command
* @package CPS
* @see CPS_ModifyResponse
*/
class CPS_PartialReplaceRequest extends CPS_ModifyRequest {
	/**
	* Constructs an instance of the CPS_PartialReplaceRequest class.
	* Possible parameter sets:<br />
	* 2 parameters - ($id, $document), where $id is the document ID and $document are its partial contents with only those fields set that You want changed.<br />
	* 1 parameter - ($array), where $array is an associative array with document IDs as keys and replaceable document contents as values<br />
	* @param string|array $arg1 Either the document id or the associative array of ids => docs
	* @param mixed|null $arg2 Either the replaceable document contents or NULL (can also be omitted)
	*/
	public function __construct($arg1, $arg2=NULL) {
		parent::__construct('partial-replace', $arg1, $arg2);
	}
}

/**
* The CPS_DeleteRequest class is a wrapper for the Response class for the delete command
* @package CPS
* @see CPS_ModifyResponse
*/
class CPS_DeleteRequest extends CPS_ModifyRequest {
	/**
	* Constructs an instance of the CPS_DeleteRequest class.
	* @param string|array $arg1 Either the document id as string or an array of document IDs to be deleted
	*/
	public function __construct($arg1) {
		$nextArg = array();
		if (is_string($arg1)) {
			$nextArg = array($arg1 => NULL);
		} else if (is_array($arg1)) {
			foreach ($arg1 as $value) {
				$nextArg[$value] = NULL;
			}
		}
		parent::__construct('delete', $nextArg, NULL);
	}
}

// Modify response
/**
* The CPS_ModifyResponse class is a wrapper for the Response class for insert, update, delete, replace and partial-replace commands
* @package CPS
* @see CPS_InsertRequest, CPS_UpdateRequest, CPS_DeleteRequest, CPS_ReplaceRequest, CPS_PartialReplaceRequest
*/
class CPS_ModifyResponse extends CPS_Response {
	/**
	* Returns an array of IDs of documents that have been successfully modified
	* @return array
	*/
	public function getModifiedIds() {
		return array_keys(parent::getRawDocuments(NULL));
	}
}

/**
* The CPS_AlternativesRequest class is a wrapper for the Response class for the alternatives command
* @package CPS
* @see CPS_AlternativesResponse
*/
class CPS_AlternativesRequest extends CPS_Request {
	/**
	* Constructs an instance of CPS_AlternativesRequest
	*
	* @param string $query see {@link setQuery}
	* @param float $cr see {@link setCr}
	* @param float $idif see {@link setIdif}
	* @param float $h see {@link setH}
	*/
	public function __construct($query, $cr = NULL, $idif = NULL, $h = NULL) {
		parent::__construct('alternatives');
		$this->setQuery($query);
		if (!is_null($cr))
			$this->setCr($cr);
		if (!is_null($idif))
			$this->setIdif($idif);
		if (!is_null($h))
			$this->setH($h);
	}
	
	/**
	* Sets the search query.
	*
	* Example usage:
	* <code>$r->setQuery('(' . CPS_Term('predefined_term', '/generated_fields/type/') . CPS_QueryTerm($user_supplied_terms, '/searchable_fields/text') . ')');</code>
	* @param string $value The query string.
	* All <, > and & characters that aren't supposed to be XML tags, should be escaped (e.g. with {@link CPS_Term} or {@link CPS_QueryTerm});
	* @see CPS_QueryTerm, CPS_Term
	*/
	public function setQuery($value) {
		$this->setParam('query', $value);
	}
	
	/**
	* Minimum ratio between the occurrence of the alternative and the occurrence of the search term.
	* If this parameter is increased, less results are returned while performance is improved.
	* @param float $value
	*/
	public function setCr($value) {
		$this->setParam('cr', $value);
	}
	
	/**
	* A number that limits how much the alternative may differ from the search term,
    * the greater the idif value, the greater the allowed difference.
    * If this parameter is increased, more results are returned while performance is decreased.
	* @param float $value
	*/
	public function setIdif($value) {
		$this->setParam('idif', $value);
	}
	
	/**
	* A number that limits the overall estimate of the quality of the alternative,
    * the greater the cr value and the smaller the idif value, the greater the h value.
    * If this parameter is increased, less results are returned while performance is improved.
	* @param float $value
	*/
	public function setH($value) {
		$this->setParam('h', $value);
	}
}

/**
* The CPS_AlternativesResponse class is a wrapper for the Response class for the alternatives command
* @package CPS
* @see CPS_AlternativesRequest
*/
class CPS_AlternativesResponse extends CPS_Response {
	/**
	* Gets the spelling alternatives to the specified query terms
	*
	* Returns an associative array, where keys are query terms and values are associative arrays
	* with alternative spellings as keys and arrays of the metrics of these spellings as values
	* @return array
	*/
	public function getWords() {
		return parent::getRawWords();
	}
}

/**
* The CPS_ListWordsRequest class is a wrapper for the Response class for the list-words command
* @package CPS
* @see CPS_ListWordsResponse
*/
class CPS_ListWordsRequest extends CPS_Request {
	/**
	* Constructs an instance of CPS_ListWordsRequest
	*
	* @param string $query see {@link setQuery}
	*/
	public function __construct($query) {
		parent::__construct('list-words');
		if (is_array($query))
			$query = implode(' ', $query);
		$this->setQuery($query);
	}
	
	/**
	* Sets the query
	*
	* @param string|array $value a single term with a wildcard as a string or an array of terms
	*/
	public function setQuery($value) {
		$this->setParam('query', $value);
	}
}

/**
* The CPS_ListWordsResponse class is a wrapper for the Response class for the list-words command
* @package CPS
* @see CPS_ListWordsRequest
*/
class CPS_ListWordsResponse extends CPS_Response {
	/**
	* Returns words matching the given wildcard
	*
	* Returns an associative array, where keys are given wildcards and values are associative arrays with matching words as keys and
	* their counts as values
	* @return array
	*/
	public function getWords() {
		return parent::getRawWords();
	}
}

/**
* The CPS_StatusRequest class is a wrapper for the Response class for the status command
* @package CPS
* @see CPS_StatusResponse
*/
class CPS_StatusRequest extends CPS_Request {
	/**
	* Constructs an instance of CPS_StatusRequest
	*/
	public function __construct() {
		parent::__construct('status');
	}
}

/**
* The CPS_StatusResponse class is a wrapper for the Response class for the status command
* @package CPS
* @see CPS_StatusRequest
*/
class CPS_StatusResponse extends CPS_Response {
	/**
	* Returns an associative array, which contains the status information
	* @param int $type defines which datatype the returned documents will be in. Default is DOC_TYPE_ARRAY, other possible values are DOC_TYPE_ARRAY and DOC_TYPE_STDCLASS
	* @return array
	*/
	public function getStatus($type = DOC_TYPE_ARRAY) {
		return parent::getContentArray($type);
	}
}

/**
* The CPS_RetrieveRequest class is a wrapper for the Request class
* @package CPS
* @see CPS_LookupResponse
*/
class CPS_RetrieveRequest extends CPS_Request {
	/**
	* Constructs an instance of the CPS_RetrieveRequest class.
	* @param string|array $id Either the document id as string or an array of document IDs to be retrieved
	*/
	public function __construct($id) {
		parent::__construct('retrieve');
		if (is_string($id)) {
			$this->_documents = array($id => NULL);
		} else if (is_array($id)) {
			$this->_documents = array();
			foreach ($id as $value) {
				$this->_documents[$value] = NULL;
			}
		}
	}
}

/**
* The CPS_LookupRequest class is a wrapper for the Request class
* @package CPS
* @see CPS_LookupResponse
*/
class CPS_LookupRequest extends CPS_Request {
	/**
	* Constructs an instance of the CPS_LookupRequest class.
	* @param string|array $id Either the document id as string or an array of document IDs to be retrieved
	* @param array $list listing parameters - an associative array with xpaths as values and snippeting options (yes | no | snippet | highlight) as values
	*/
	public function __construct($id, $list=NULL) {
		parent::__construct('lookup');
		if (is_string($id)) {
			$this->_documents = array($id => NULL);
		} else if (is_array($id)) {
			$this->_documents = array();
			foreach ($id as $value) {
				$this->_documents[$value] = NULL;
			}
		}
		if (!is_null($list))
			$this->setList($list);
	}
	/**
	* Defines which tags of the search results should be listed in the response
	* @param array $array an associative array with tag xpaths as keys and listing options (yes, no, snippet or highlight) as values
	*/
	public function setList($array) {
		$listString = '';
		foreach ($array as $key => $value) {
			$listString .= CPS_Term($value, $key);
		}
		$this->setParam('list', $listString);
	}
}

/**
* The CPS_ListLastRequest class is a wrapper for the Request class for list-last requests
* @package CPS
* @see CPS_LookupResponse
*/
class CPS_ListLastRequest extends CPS_ListLastRetrieveFirstRequest {
	/**
	* Constructs an instance of the CPS_ListLast class.
	* @param array $list an associative array with tag xpaths as keys and listing options (yes, no, snippet or highlight) as values
	* @param int $offset offset
	* @param int $docs max number of docs to return
	*/
	public function __construct($list, $offset='', $docs='') {
		parent::__construct('list-last', $offset, $docs, $list);
	}
}

/**
* The CPS_ListFirstRequest class is a wrapper for the Request class for list-first requests
* @package CPS
* @see CPS_LookupResponse
*/
class CPS_ListFirstRequest extends CPS_ListLastRetrieveFirstRequest {
	/**
	* Constructs an instance of the CPS_ListFirst class.
	* @param array $list an associative array with tag xpaths as keys and listing options (yes, no, snippet or highlight) as values
	* @param int $offset offset
	* @param int $docs max number of docs to return
	*/
	public function __construct($list, $offset='', $docs='') {
		parent::__construct('list-first', $offset, $docs, $list);
	}
}

/**
* The CPS_RetrieveLastRequest class is a wrapper for the Request class for retrieve-last requests
* @package CPS
* @see CPS_LookupResponse
*/
class CPS_RetrieveLastRequest extends CPS_ListLastRetrieveFirstRequest {
	/**
	* Constructs an instance of the CPS_RetrieveLast class.
	* @param int $offset offset
	* @param int $docs max number of docs to return
	*/
	public function __construct($offset='', $docs='') {
		parent::__construct('retrieve-last', $offset, $docs);
	}
}

/**
* The CPS_RetrieveFirstRequest class is a wrapper for the Request class for retrieve-first requests
* @package CPS
* @see CPS_LookupResponse
*/
class CPS_RetrieveFirstRequest extends CPS_ListLastRetrieveFirstRequest {
	/**
	* Constructs an instance of the CPS_RetrieveFirst class.
	* @param int $offset offset
	* @param int $docs max number of docs to return
	*/
	public function __construct($offset='', $docs='') {
		parent::__construct('retrieve-first', $offset, $docs);
	}
}

/**
* The CPS_LookupResponse class is a wrapper for the Response class for replies to retrieve, list-last, list-first, retrieve-last, and retrieve-first commands
* @package CPS
* @see CPS_RetrieveRequest, CPS_ListLastRequest, CPS_ListFirstRequest, CPS_RetrieveLastRequest, CPS_RetrieveFirstRequest
*/
class CPS_LookupResponse extends CPS_Response {
	/**
	* Returns the documents from the response as an associative array, where keys are document IDs and values area document contents
	* @param int $type defines which datatype the returned documents will be in. Default is DOC_TYPE_SIMPLEXML, other possible values are DOC_TYPE_ARRAY and DOC_TYPE_STDCLASS
	* @return array
	*/
	public function getDocuments($type = DOC_TYPE_SIMPLEXML) {
		return parent::getRawDocuments($type);
	}

	/**
	* Returns the number of documents returned
	* @return int
	*/
	public function getFound() {
		return $this->getParam('found');
	}
		
	/**
	* Returns the position of the first document that was returned
	* @return int
	* @see CPS_SearchRequest::setOffset(), CPS_SearchRequest::setDocs()
	*/
	public function getFrom() {
		return $this->getParam('from');
	}
	
	/**
	* Returns the position of the last document that was returned
	* @see CPS_SearchRequest::setOffset(), CPS_SearchRequest::setDocs()
	* @return int
	*/
	public function getTo() {
		return $this->getParam('to');
	}
}

/**
* The CPS_SearchDeleteRequest class is a wrapper for the Request class for the search-delete command
* @package CPS
* @see CPS_SearchDeleteResponse
*/
class CPS_SearchDeleteRequest extends CPS_Request {
	/**
	* Constructs an instance of the CPS_SearchDeleteRequest class.
	* @param string $query The query string. see {@link CPS_SearchDeleteRequest::setQuery()} for more info.
	*/
	public function __construct($query) {
		parent::__construct('search-delete');
		$this->setQuery($query);
	}
	
	/**
	* Sets the search query.
	*
	* Example usage:
	* <code>$r->setQuery('(' . CPS_Term('predefined_term', '/generated_fields/type/') . CPS_QueryTerm($user_supplied_terms, '/searchable_fields/text') . ')');</code>
	* @param string $value The query string.
	* All <, > and & characters that aren't supposed to be XML tags, should be escaped (e.g. with {@link CPS_Term} or {@link CPS_QueryTerm});
	* @see CPS_QueryTerm, CPS_Term
	*/
	public function setQuery($value) {
		$this->setParam('query', $value);
	}
	
	/**
	* Sets the stemming language
	* @param string $value 2-letter language ID
	*/
	public function setStemLang($value) {
		$this->setParam('stem-lang', $value);
	}
	
	/**
	* Sets the exact match option
	* @param string $value Exact match option : text, binary or all
	*/
	public function setExactMatch($value) {
		$this->setParam('exact-match', $value);
	}
}

/**
* The CPS_SearchDeleteResponse class is a wrapper for the Response class
* @package CPS
* @see CPS_SearchDeleteRequest
*/
class CPS_SearchDeleteResponse extends CPS_Response {
	/**
	* Returns the total number of hits - i.e. the number of documents erased
	* @return int
	*/
	public function getHits() {
		return $this->getParam('hits');
	}
}

/**
* The CPS_ListPathsRequest class is a wrapper for the Response class for the list-paths command
* @package CPS
* @see CPS_ListPathsResponse
*/
class CPS_ListPathsRequest extends CPS_Request {
	/**
	* Constructs an instance of CPS_ListPathsRequest
	*/
	public function __construct() {
		parent::__construct('list-paths');
	}
}

/**
* The CPS_ListPathsResponse class is a wrapper for the Response class for the list-paths command
* @package CPS
* @see CPS_ListPathsRequest
*/
class CPS_ListPathsResponse extends CPS_Response {
	/**
	* Returns an array of paths
	* @return array
	*/
	public function getPaths() {
		$content = parent::getContentArray();
		if (isset($content['paths']['path'])) {
			return $content['paths']['path'];
		} else {
			return array();
		}
	}
}

/**
* The CPS_ListFacetsRequest class is a wrapper for the Response class for the list-facets command
* @package CPS
* @see CPS_ListFacetsResponse
*/
class CPS_ListFacetsRequest extends CPS_Request {
	/**
	* Constructs an instance of the CPS_ListFacetsRequest class.
	* @param array|string $paths A single facet path as string or an array of paths to list the facet terms from
	*/
	public function __construct($paths) {
		parent::__construct('list-facets');
		$this->setParam('path', $paths);
	}
}

/**
* The CPS_ListFacetsResponse class is a wrapper for the Response class for the list-facets command
* @package CPS
* @see CPS_ListFacetsRequest
*/

class CPS_ListFacetsResponse extends CPS_Response {
	
	/**
	* Returns the facets from the response in a form of a multi-dimensional associative array, e.g. array('category' => array('Sports' => '', 'News' => ''));
	* @return array
	*/
	public function getFacets() {
		return parent::getRawFacets();
	}
}

/**
* The CPS_SimilarDocumentRequest class is a wrapper for the Response class for the similar command for documents
* @package CPS
* @see CPS_LookupResponse
*/
class CPS_SimilarDocumentRequest extends CPS_Request {
	/**
	* Constructs an instance of the CPS_SimilarDocumentRequest class.
	* @param string $docid ID of the source document - the one that You want to search similar documents to
	* @param int $len number of keywords to extract from the source
	* @param int $quota minimum number of keywords matching in the destination
	* @param int $offset number of results to skip before returning the following ones
	* @param int $docs number of documents to retrieve
	* @param string $query an optional query that all found documents have to match against
	*/
	public function __construct($docid, $len, $quota, $offset=NULL, $docs=NULL, $query=NULL) {
		parent::__construct('similar');
		$this->setParam('id', $docid);
		$this->setParam('len', $len);
		$this->setParam('quota', $quota);
		if (!is_null($docs))
			$this->setParam('docs', $docs);
		if (!is_null($offset))
			$this->setParam('offset', $offset);
		if (!is_null($query))
			$this->setParam('query', $query);
	}
}

/**
* The CPS_SimilarTextRequest class is a wrapper for the Response class for the similar command for text
* @package CPS
* @see CPS_LookupResponse
*/
class CPS_SimilarTextRequest extends CPS_Request {
	/**
	* Constructs an instance of the CPS_SimilarTextRequest class.
	* @param string $text A chunk of text that the found documents have to be similar to
	* @param int $len number of keywords to extract from the source
	* @param int $quota minimum number of keywords matching in the destination
	* @param int $offset number of results to skip before returning the following ones
	* @param int $docs number of documents to retrieve
	* @param string $query an optional query that all found documents have to match against
	*/
	public function __construct($text, $len, $quota, $offset=NULL, $docs=NULL, $query=NULL) {
		parent::__construct('similar');
		$this->setParam('text', $text);
		$this->setParam('len', $len);
		$this->setParam('quota', $quota);
		if (!is_null($docs))
			$this->setParam('docs', $docs);
		if (!is_null($offset))
			$this->setParam('offset', $offset);
		if (!is_null($query))
			$this->setParam('query', $query);
	}
}
?>
