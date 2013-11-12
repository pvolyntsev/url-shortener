<?php

/**
 * The class is provides 'view' part of MVC pattern
 *
 * It can be used to generate viewable content based on data from controller
 *
 * @usage $view = new AppView('file'); $view->someVar = 'value'; $view->render();
 *
 * To render view create file
 * /views/file.phtml
 * <?php
 *
 * // Print value from view
 * echo $this->someVar;
 */
class AppView {

	private $_viewPath;

	private $_data = array();

	function __construct($viewPath) {
		$this->setViewPath($viewPath);
	}

	public function setViewPath($viewPath) {
		$this->_viewPath = $viewPath;
	}

	function __get($name) {
		return isset($this->_data[$name]) ? $this->_data[$name] : null;
	}

	function __set($name, $value) {
		$this->_data[$name] = $value;
	}

	public function render() {
		$viewFilePath = App::basePath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $this->_viewPath.'.phtml';
		if (!file_exists($viewFilePath)) {
			trigger_error('View file not found: "' . $viewFilePath . '"');
			return false;
		}
		ob_start();
		include($viewFilePath);
		return ob_get_clean();
	}

	public function getData() {
		return $this->_data;
	}
}