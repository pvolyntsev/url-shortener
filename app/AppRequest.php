<?php

/**
 * Request class provides access to HTTP request context
 */
class AppRequest {

	protected $_priority = 'POST,GET,COOKIE,SERVER';

	protected $_post;

	protected $_get;

	protected $_request;

	protected $_cookie;

	function __construct($config = array()) {
		if (isset($config['priority'])) {
			$this->_priority = $config['priority'];
		}
		$this->_priority = preg_split('/,\s+/', strtoupper($this->_priority));

		$this->normalizeRequest();
	}

	/**
	 * Normalizes the request data
	 * This method strips off slashes in request data if get_magic_quotes_gpc() returns true
	 */
	protected function normalizeRequest() {
		if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			if(isset($_GET)) {
				$_GET = $this->stripSlashes($_GET);
			}
			if(isset($_POST)) {
				$_POST = $this->stripSlashes($_POST);
			}
			if(isset($_REQUEST)) {
				$_REQUEST = $this->stripSlashes($_REQUEST);
			}
			if(isset($_COOKIE)) {
				$_COOKIE = $this->stripSlashes($_COOKIE);
			}
		}
		if(isset($_GET)) {
			$this->_get = $_GET;
		}
		if(isset($_POST)) {
			$this->_post = $_POST;
		}
		if(isset($_REQUEST)) {
			$this->_request = $_REQUEST;
		}
		if(isset($_COOKIE)) {
			$this->_cookie = $_COOKIE;
		}
		unset($_GET); unset($_POST); unset($_COOKIE); unset($_request);
	}

	/**
	 * Strips slashes from input data
	 * This method is applied when magic quotes is enabled
	 * @param mixed $data input data to be processed
	 * @return mixed processed data
	 */
	public function stripSlashes(&$data) {
		if(is_array($data)) {
			if(count($data) == 0) {
				return $data;
			}
			$keys = array_map('stripslashes', array_keys($data));
			$data = array_combine($keys, array_values($data));
			return array_map(array($this, 'stripSlashes'), $data);
		} else {
			return stripslashes($data);
		}
	}

	/**
	 * Returns value of variables being received by HTTP request in order of $this->_priority
	 * @param string $name
	 * @param mixed|null $default
	 * @return mixed
	 */
	public function getVar($name, $default = null) {
		foreach($this->_priority as $method) {
			if ('GET' == $method || 'POST' == $method || 'REQUEST' == $method || 'COOKIE' == $method) {
				$method = "_{$method}";
				$var = $this->{$method};
				if (isset($var[$name])) {
					return $var[$name];
				}
			}
		}
		return $default;
	}

	/**
	 * Returns the named GET parameter value
	 * If the GET parameter does not exist, the second parameter to this method will be returned
	 * @param string $name the GET parameter name
	 * @param mixed|null $default the default parameter value if the GET parameter does not exist
	 * @return mixed|null the GET parameter value
	 */
	public function getQueryVar($name, $default = null) {
		if (isset($this->_get[$name])) {
			return $this->_get[$name];
		}
		return $default;
	}

	/**
	 * Set the named value as GET parameter
	 * @param string $name the GET parameter name
	 * @param mixed $value the GET parameter value
	 */
	public function addQueryVar($name, $value) {
		$this->_get[$name] = $value;
	}

	/**
	 * Returns the named POST parameter value
	 * If the POST parameter does not exist, the second parameter to this method will be returned
	 * @param string $name the POST parameter name
	 * @param mixed|null $default the default parameter value if the POST parameter does not exist
	 * @return mixed|null the POST parameter value
	 */
	public function getPostVar($name, $default = null) {
		if (isset($this->_post[$name])) {
			return $this->_post[$name];
		}
		return $default;
	}

	/**
	 * Returns the named cookie parameter value
	 * @param string $name the cookie name
	 * @return mixed|null the cookie value
	 */
	public function getCookie($name) {
		if (isset($this->_cookie[$name])) {
			return $this->_cookie[$name];
		}
		return null;
	}

	/**
	 * Returns the request type, such as GET, POST, HEAD, PUT, DELETE
	 * @return string request type, such as GET, POST, HEAD, PUT, DELETE
	 */
	public function getRequestMethod() {
		return strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET');
	}

	/**
	 * Returns part of the request URL that is after the question mark
	 * @return string part of the request URL that is after the question mark
	 */
	public function getQueryString() {
		return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING']:'';
	}

	/**
	 * Returns the currently requested URL
	 * @return string part of the request URL after the host info
	 */
	public function getUrl() {
		return $this->getRequestUri();
	}

	/**
	 * Returns the request URI portion for the currently requested URL
	 * @return string the request URI portion for the currently requested URL
	 */
	public function getRequestUri() {
		static $requestUri;
		if (!isset($requestUri)) {
			if(isset($_SERVER['REQUEST_URI'])) {
				$requestUri = $_SERVER['REQUEST_URI'];
				if(!empty($_SERVER['HTTP_HOST'])) {
					if(strpos($requestUri, $_SERVER['HTTP_HOST']) !== false) {
						$requestUri = preg_replace('/^\w+:\/\/[^\/]+/', '', $requestUri);  // remove host name "????://????/"
					}
				} else {
					$requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri); // remove host name "????://????/"
				}
			}
		}
		return $requestUri;
	}

	/**
	 * Return true if request was sent by HTTP method 'POST'
	 * @return bool true if request sent by HTTP method 'POST'
	 */
	public function isPost() {
		return 'POST' == $this->getRequestMethod();
	}

	/**
	 * Return true if request was sent by javascript
	 * @return bool true if request sent by javascript
	 */
	public function isAjaxRequest() {
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

	public function getBaseUrl() {
		$protocol = $_SERVER['HTTPS'] ? 'https' : 'http';
		$host = $_SERVER['SERVER_NAME'];
		$port =
			(
				('http' == $protocol && 80==$_SERVER['SERVER_PORT'])
				||
				('https' == $protocol && 443==$_SERVER['SERVER_PORT'])
			) ? '' : (':'.$_SERVER['SERVER_PORT']);

		return "{$protocol}://{$host}{$port}";
	}
}