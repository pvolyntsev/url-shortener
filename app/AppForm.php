<?php

/**
 * Form class provides operations with HTML forms
 *
 * Form is one of models used in MVC pattern
 */
abstract class AppForm {

	/**
	 * @var AppView
	 */
	protected $view;

	/**
	 * Original unmodified form values
	 * @var array
	 */
	protected $_unfilteredValues = array();

	/**
	 * Form values after filtration and validation
	 * @var array
	 */
	protected $_values = array();

	/**
	 * Validation messages
	 * @var array
	 */
	protected $_validation = array();

	/**
	 * URI to submit form
	 * @var string
	 */
	protected $_action;

	/**
	 * Form submit method
	 * @var string
	 */
	protected $_method = 'GET';

	protected function init() {
		$this->_action = App::request()->getRequestUri();
		$viewPath = 'forms' . DIRECTORY_SEPARATOR . App::uncamelize(preg_replace('/Form$/', '', get_class($this)));
		$this->view = new AppView($viewPath);
	}

	abstract public function getRules();

	public function getId() {
		return 'form-'.App::uncamelize(preg_replace('/Form$/', '', get_class($this)));
	}

	public function setUnfilteredValue($name, $value) {
		$rules = $this->getRules();
		if (isset($rules[$name])) {
			$this->_unfilteredValues[$name] = $value;
		}
	}

	public function getUnfilteredValue($name) {
		$rules = $this->getRules();
		if (isset($rules[$name])) {
			return isset($this->_unfilteredValues[$name]) ? $this->_unfilteredValues[$name] : null;
		}
		return null;
	}

	public function setValue($name, $value) {
		$this->setUnfilteredValue($name, $value); // set unfiltered value
		$value = $this->filter($name, $value); // ... then filter

		// save filtered value
		$this->_values[$name] = $value;

		$this->validate($name, $value); // ... and validate value
	}

	public function getValue($name) {
		$rules = $this->getRules();
		if (isset($rules[$name], $this->_values[$name])) {
			return $this->_values[$name];
		}
		return null;
	}

	public function getLabel($name) {
		$rules = $this->getRules();
		if (isset($rules[$name], $rules[$name]['label'])) {
			return $rules[$name]['label'];
		}
		return $name;

	}

	protected function filter($name, $value) {
		$rules = $this->getRules();
		if (!isset($rules[$name])) {
			return false;
		}
		$rule = $rules[$name];
		if (isset($rule['filter'])) {
			if (!is_array($rule['filter'])) { // if filter is a string like 'Alpha,Striptags'
				// make array
				$rule['filter'] = preg_split('/,\s+/', $rule['filter']);
			}
			// every element of $rule['filter'] is a class name of filter
			foreach($rule['filter'] as $key => $filter) {
				// convert filter class name to filter instance
				// if fail, remove filter and show warning message
				if (!is_object($filter)) {
					$filterInstance = $this->getFilter($filter);
					if (is_null($filterInstance)) {
						unset($rule['filter'][$key]);
						trigger_error('Filter "' . $filter . '" not found', E_USER_WARNING);
						continue;
					}
					$rule['filter'][$key] = $filterInstance;
				} else {
					$filterInstance = $filter;
				}

				// filter value
				$value = $filterInstance->filter($value);
			}
		}
		return $value;
	}

	protected function validate($name, $value) {
		$rules = $this->getRules();
		if (!isset($rules[$name])) {
			return false;
		}
		$rule = $rules[$name];

		$this->_validation[$name] = array();
		if (!isset($rule['validate'])) {
			return true;
		}

		$label = $this->getLabel($name);
		foreach($rule['validate'] as $key => $validator) {
			if (is_string($validator) && 'required' == $validator) {
				if (''==$value || is_null($value)) {
					$this->_validation[$name]['required'] = '"' . $label . '" could not be empty';
					break; // stop on first fail
				}
				continue;
			} elseif (!is_object($validator)) {
				$validatorInstance = $this->getValidator($validator);
				if (is_null($validatorInstance)) {
					unset($rule['validate'][$key]);
					trigger_error('Validator "' . (is_string($validator) ? $validator : current($validator)) . '" not found', E_USER_WARNING);
					continue;
				}
				$rule['filter'][$key] = $validatorInstance;
			} else {
				$validatorInstance = $validator;
			}
			if (!$validatorInstance->isValid($value)) {
				$this->_validation[$name][get_class($validatorInstance)] = $validatorInstance->getMessage();
				break; // stop on first fail
			}
		}

		return empty($this->_validation[$name]);
	}

	/**
	 * Retuns validation status for one element or even all form elements
	 * @param string|null $name
	 * @return bool if $name is given return true if form element is valid
	 */
	public function isValid($name = null) {
		if (is_null($name)) {
			foreach($this->_validation as $name => $messages) {
				if (!empty($messages)) {
					return false;
				}
			}
			return true;
		} else {
			return empty($this->_validation[$name]);
		}
	}

	/**
	 * Returns list of validation messages if any
	 * @param $name
	 * @return array|bool array of messages or false otherwise
	 */
	public function getMessages($name) {
		return isset($this->_validation[$name]) ? $this->_validation[$name] : false;
	}

	/**
	 * Returns the instance of filter
	 * @param string $filterName class name of filter
	 * @return object|null Filter instance or null if fail
	 */
	protected function getFilter($filterName) {
		$filterClassName = $filterName;
		if (App::autoloader()->classDefined($filterClassName)) {
			// TODO check instanсeof
			return new $filterClassName;
		} else {
			trigger_error('Filter "' . $filterClassName . '" not defined', E_USER_WARNING);
		}
		return null;
	}

	/**
	 * Returns the instance of validator
	 *
	 * NOTE Use "%s" in invalid message to substinute with value bing validate
	 *
	 * @param string|array $validator Class name or array like that : array('ClassName', 'InvalidMessage').
	 * @return object|null Validator object or fail if fail
	 */
	protected function getValidator($validator) {
		if (is_array($validator)) {
			$validator[] = '';
			list($validatorClassName, $invalidMessage) = $validator;
		} else {
			$validatorClassName = $validator;
			$invalidMessage = 'Incorrect value "%s"';
		}
		if (App::autoloader()->classDefined($validatorClassName)) {
			$validator = new $validatorClassName;
			$validator->setMessage($invalidMessage);
			// TODO check instanсeof
			return $validator;
		} else {
			trigger_error('Validator "' . $validatorClassName . '" not defined', E_USER_WARNING);
		}
		return null;
	}

	/**
	 * Return form significant data without rendering
	 * @return array
	 */
	public function getData() {
		return array(
			'values' => $this->_values,
			'messages' => $this->_validation,
		);
	}

	/**
	 * Render form content
	 * @return bool|string
	 */
	public function __toString() {
		$this->init();
		$this->view->action = $this->_action;
		$this->view->method = $this->_method;
		$this->view->form = $this;
		return $this->view->render();
	}
}