<?php

/**
 * Validator prototype
 *
 * URL validator check form element value to be correct URL using complex regular expression
 * Regular expression is from @see http://mathiasbynens.be/demo/url-regex
 *     see URL validation results in column "@diegoperini"
 */
class Url {

	/**
	 * Message if value is invalid
	 * @var string
	 */
	protected $_invalidMessage = 'Incorrect value "%s"';

	/**
	 * Value to check
	 * @var mixed
	 */
	protected $_value;

	/**
	 * Result of value check
	 * @var bool true if value is invalid
	 */
	protected $_isValid = true;

	/**
	 * Text for message if value is not valid
	 * A message can have substitution '%S' which will be replaced with validated value
	 * @param string $invalidMessage
	 */
	public function setMessage($invalidMessage) {
		$this->_invalidMessage = $invalidMessage;
	}

	/**
	 * Do validation and return validation result
	 * @return bool true if value is valid
	 */
	public function isValid($value) {
		$this->_value = $value;
		$this->_isValid = preg_match('_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS', $value);
		return $this->_isValid;
	}

	/**
	 * Return text message if value is not valid of false otherwise
	 * @return string|bool
	 */
	public function getMessage() {
		if ($this->_isValid) {
			return false;
		} else {
			return sprintf($this->_invalidMessage, $this->_value);
		}
	}
}
