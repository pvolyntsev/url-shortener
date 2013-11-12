<?php

/**
 * Router class provides methods to decompose URL to controller/action/parameters and back
 */
class AppRouter {

	protected $_rules = array();

	function __construct($config) {
		if (isset($config['router'])) {
			if (isset($config['router']['rules'])) {
				foreach($config['router']['rules'] as $mask => $rule) {
					$this->_rules[$mask] = $rule;
				}
			}
		}
	}

	public function parseRequestUrl($url) {

		// remove leading and trailing slashes
		$url = trim($url, '/');

		$rule = $this->getMatchedRule($url);
		if (!is_array($rule) || (is_array($rule) && count($rule)<2)) {
			App::response()->sendNotFoundAndExit();
		}

		$rule[] = array(); // sizeof must me not less than 3
		list($controller, $action, $parameters) = $rule;
		$controller = App::camelize($controller, true).'Controller';
		$action = App::camelize($action, false).'Action';

		// insert parsed parameters into query variables
		foreach($parameters as $key => $value) {
			App::request()->addQueryVar($key, $value);
		}

		return array(
			'controller' => $controller,
			'action' => $action,
			'parameters' => $parameters,
		);
	}

	protected function getMatchedRule($url) {
		foreach($this->_rules as $mask => $rule) {
			if ('' == $mask) { // empty mask meen empty URL
				if ($mask == $url) {
					return $rule;
				}
			} else { // match using regular expression
				if (preg_match($mask, $url, $matches)) {
					if (isset($rule[2])) {
						// rule looks like array('controller', 'action', array of parameters)
						// parameters values can be replaces with values that were found with regexp
						foreach($rule[2] as $key => &$value) {
							if (':' == $value{0}) {
								$matchGroup = substr($value, 1);
								$value = isset($matches[$matchGroup]) ? $matches[$matchGroup] : '';
							}
						}
					}
					return $rule;
				}
			}
		}
		return false;
	}

	public function createUrl($controller, $action, $parameters = array()) {
		// TODO Yii has enought good logic to produce custom URLS
		// TODO here is no logic at all

		return App::request()->getBaseUrl().'/'.$parameters['url'];
	}

}