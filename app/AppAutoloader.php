<?php

/**
 * Class to load other classes
 */
class AppAutoloader {

	protected $_imports = array();

	protected $_classMap = array();

	function __construct($config = array()) {
		if (isset($config['autoloader'])) {

			// Folders to scan for class definitions
			if (isset($config['autoloader']['import'])) {
				foreach($config['autoloader']['import'] as $path) {
					$this->import($path);
				}
			}

			// Paths to class definitions
			if (isset($config['autoloader']['map'])) {
				foreach($config['autoloader']['map'] as $className => $filePath) {
					$this->addClassMap($className, $filePath);
				}
			}
		}
		spl_autoload_register(array($this, 'loadClass'));

		#var_dump($this);
	}

	/**
	 * Scan folder with relative path $path for PHP files and register them in $_classMap
	 * @param string $path relative path to folder to scan for class definition files
	 */
	public function import($path) {
		if (isset($this->_imports[$path])) { // skip already scanned folder
			return;
		}
		$files = glob(App::basePath().$path);
		foreach($files as $filePath) {
			if (preg_match('/([^\/]+)\.php$/', $filePath, $m)) {
				$className = $m[1];
				$this->addClassMap($className, $filePath);
			}
		}
		$this->_imports[$path] = true; // mark path as scanned
	}

	/**
	 * Add classname and filepath into the list of class definitions
	 * @param string $className Name of the class
	 * @param string $filePath Relative path to PHP file with definition of class $className
	 */
	public function addClassMap($className, $filePath) {
		$filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);
		$this->_classMap[$className] = $filePath;
	}

	/**
	 * Load PHP class definition
	 * @param $className
	 * @return bool true if class loaded, false otherwise
	 */
	public function loadClass($className) {
		if ('App' == substr($className, 0, 3)) { // base classes are in '/app' folder
			$classPath = App::basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$className.'.php';
			require_once($classPath);
		} elseif (isset($this->_classMap[$className])) { // other classes must be imported
			$classPath = $this->_classMap[$className];
			require_once($classPath);
		}
		return class_exists($className, false);
	}

	/**
	 * Check for class definition exists
	 * @param $className
	 * @return bool true if definition found, false otherwise
	 */
	public function classDefined($className) {
		if ('App' == substr($className, 0, 3)) { // base classes are in '/app' folder
			$classPath = App::basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.$className.'.php';
			return file_exists($classPath);
		} elseif (isset($this->_classMap[$className])) { // other classes must be imported
			$classPath = $this->_classMap[$className];
			return file_exists($classPath);
		}
		return false;
	}
}