<?php

/**
 * Response class provides HTTP response
 */
class AppResponse {

	protected $_responseCode = '200 OK';

	protected $_headers = array();

	protected $_buffering = false;

	public function enableBuffering() {
		if (!$this->_buffering) {
			ob_start();
			$this->_buffering = true;
		}
	}

	public function disableBuffering() {
		if ($this->_buffering) {
			echo ob_get_clean();
			$this->_buffering = false;
		}
	}

	/**
	 * Send HTTP 404 Page Not Found response and finish process
	 */
	public function sendNotFoundAndExit() {
		$this->sendNotFound();
		$this->doExit();
	}

	/**
	 * Redirect request to another URL and finish process
	 * @param $url URL to redirect
	 * @param int $httpCode HTTP code, 307 Temporary Redirect
	 */
	public function redirectAndExit($url, $httpCode = 307) {
		$this->redirect($url, $httpCode);
		$this->doExit();
	}

	/**
	 * Redirect request to another URL
	 * @param $url URL to redirect
	 * @param int $httpCode HTTP code, 307 Temporary Redirect
	 */
	public function redirect($url, $httpCode = 307) {
		$this->_responseCode = $httpCode . ' ' . $this->getHttpHeader($httpCode);
		$this->_headers['Location'] = $url;
	}

	/**
	 * Send HTTP 404 Page Not Found response
	 */
	public function sendNotFound() {
		$this->_responseCode = '404 ' . $this->getHttpHeader(404);
		echo include_once(App::basePath(). DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'error' . DIRECTORY_SEPARATOR . '404.phtml');
	}

	/**
	 * Send all HTTP headers
	 */
	public function sendHeaders() {
		if (!headers_sent()) {
			header('HTTP/1.0 ' . $this->_responseCode);
			foreach($this->_headers as $header => $value) {
				header("{$header}: {$value}");
			}
		}
	}

	/**
	 * Put content into response stream
	 */
	public function sendContent() {
		$this->disableBuffering();
	}

	public function doExit() {
		$this->sendHeaders();
		$this->sendContent();
		exit();
	}

	/**
	 * Return correct message for each known http error code
	 * @param integer $httpCode error code to map
	 * @param string $replacement replacement error string that is returned if code is unknown
	 * @return string the textual representation of the given error code or the replacement string if the error code is unknown
	 */
	protected function getHttpHeader($httpCode, $replacement = '') {
		$httpCodes = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			118 => 'Connection timed out',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			210 => 'Content Different',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			310 => 'Too many Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested range unsatisfiable',
			417 => 'Expectation failed',
			418 => 'I’m a teapot',
			422 => 'Unprocessable entity',
			423 => 'Locked',
			424 => 'Method failure',
			425 => 'Unordered Collection',
			426 => 'Upgrade Required',
			449 => 'Retry With',
			450 => 'Blocked by Windows Parental Controls',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway ou Proxy Error',
			503 => 'Service Unavailable',
			504 => 'Gateway Time-out',
			505 => 'HTTP Version not supported',
			507 => 'Insufficient storage',
			509 => 'Bandwidth Limit Exceeded',
		);
		if(isset($httpCodes[$httpCode])) {
			return $httpCodes[$httpCode];
		} else {
			return $replacement;
		}
	}
}