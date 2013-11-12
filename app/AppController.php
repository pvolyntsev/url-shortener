<?php

/**
 * Controller class provides transfer data from HTTP request into models and rendering HTML responce
 *
 * Controller is one of parts used in MVC pattern
 */
abstract class AppController {

	/**
	 * @var AppView
	 */
	protected $view;

	/**
	 * Name of method that will serve request
	 * @var string
	 */
	protected $_actionMethod = '';

	/**
	 * Extra parameters from request URI string
	 * @var array
	 */
	protected $_parameters = array();

	/**
	 * Name of page layout
	 * Action can change layout using $this->setLayout('??');
	 * @var string
	 */
	protected $_layout = 'default';

	/**
	 * Internal use: true if action method called render()
	 * @var bool
	 */
	protected $_doRender = false;

	protected function init() {
		$this->view = new AppView('');
	}

	public function setAction($action) {
		$this->_actionMethod = $action;
	}

	public function setParameters($parameters = array()) {
		$this->_parameters = $parameters;
	}

	public function getLayout() {
		return $this->_layout;
	}

	public function setLayout($layout) {
		$this->_layout = $layout;
	}

	public function disableLayout() {
		$this->_layout = null;
	}

	public function run() {
		$this->init();
		if (empty($this->_actionMethod)) {
			trigger_error('Action not defined in class '.get_class($this).'::run()', E_USER_ERROR);
			App::response()->sendNotFoundAndExit();
		}
		if (!method_exists($this, $this->_actionMethod)) {
			trigger_error('Action method "' . $this->_actionMethod . '" not exit in class '.get_class($this).'::run()', E_USER_ERROR);
			App::response()->sendNotFoundAndExit();
		}

		$this->{$this->_actionMethod}();
		if ($this->_doRender) { // if action method called render()
			return $this->view->render(); // return rendered content
		} else {
			return $this->view->getData(); // return all variables
		}
	}

	/**
	 * Render results of action
	 * @param string $viewPath
	 */
	protected function render($viewPath = '') {
		// (!) Not render here, just prepare
		$this->_doRender = true;
		if ('' == $viewPath) {
			$controllerViewPath = App::uncamelize(preg_replace('/Controller$/', '', get_class($this)));
			$actionViewPath = App::uncamelize(preg_replace('/Action$/', '', $this->_actionMethod));
			$viewPath = $controllerViewPath . DIRECTORY_SEPARATOR . $actionViewPath;
		}
		$this->view->setViewPath($viewPath);
	}
}