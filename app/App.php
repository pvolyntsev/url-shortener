<?php

/**
 * @method static AppAutoloader autoloader()
 * @method static AppRequest request()
 * @method static AppResponse response()
 * @method static AppRouter router()
 * @method static AppPdoConnection db()
 */
class App {

	protected static $_config = array();

	protected static $_registry = array();

	/**
	 * @param $config array
	 */
	public static function run($config) {
		static::$_config = $config;

		// initialize error handling
		register_shutdown_function(array('App', 'onFatalError'));
		set_exception_handler(array('App', 'onException'));
		set_error_handler(array('App', 'onError'));

		$response = App::response(); // init response
		$response->enableBuffering(); // start output buffering

		// initialize class Autoloader
		$autoloader = static::autoloader();

		// initialize Router
		$router = static::router();
		$parsedUrl = $router->parseRequestUrl(App::request()->getUrl());
		$controllerClassName = $parsedUrl['controller'];
		$actionMethodName = $parsedUrl['action'];
		$parameters = $parsedUrl['parameters'];

		if (!$autoloader->classDefined($controllerClassName)) {
			trigger_error('Controller "' . $controllerClassName . '" not found');
			App::response()->sendNotFoundAndExit();
		}

		/** @var $controller AppController */
		$controller = new $controllerClassName;
		$controller->setAction($actionMethodName);
		$controller->setParameters($parameters);

		// get response from action
		$actionContent = $controller->run();

		// get layout from controller
		$layout = $controller->getLayout();
		if ($layout) { // if layout not disabled ( @see AppController::disableLayout() )
			$layoutView = new AppView('layouts/'.$layout);
			$layoutView->title = static::$_config['applicationName'];
			$layoutView->content = $actionContent; // insert action response into page layout
			// render page
			echo $layoutView->render();
		}

		// send response to client
		App::response()->sendContent();
	}

	/**
	 * Returns instance of component named $name
	 * @param $name
	 */
	public static function __callStatic($name, $arguments){
		if (!isset(static::$_registry[$name])) {
			// If exists config[ComponentName][ClassName], use that classname
			if (isset(static::$_config[$name], static::$_config[$name]['class'])) {
				$className = static::$_config[$name]['class'];
			} else {
				// if exists file "App{ComponentName}" in current folder, use classname = "App{$name}"
				// the only one reason to do this - auto load auto AppAutoloader and AppRouter
				$className = 'App'.ucfirst($name);
				$defaultClassPath = dirname(__FILE__).DIRECTORY_SEPARATOR.$className.'.php';
				if (file_exists($defaultClassPath)) {
					require_once($defaultClassPath);
				} else {
					// By default use given $name as classname
					$className = $name;
				}
			}
			$instance = new $className(static::$_config);

			static::$_registry[$name] = $instance;
		}
		return static::$_registry[$name];
	}

	public static function onError($errno, $errstr, $errfile, $errline) {
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting
			return;
		}

		echo '<pre>';
		debug_print_backtrace();
		echo '</pre>';
		switch ($errno) {
			case E_USER_ERROR:
				echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
				echo "  Fatal error on line $errline in file $errfile";
				echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				echo "Aborting...<br />\n";
				App::response()->sendContent();
				exit(1);
				break;

			case E_USER_WARNING:
				echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
				break;

			case E_USER_NOTICE:
				echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
				break;

			default:
				echo "Unknown error type: [$errno] $errstr<br />\n";
				break;
		}
		return false;
	}

	public static function onFatalError() {
		$error = error_get_last();
		if (E_ERROR == $error['type']) { // PHP Fatal Error
			$msg = 'FATAL ERROR : ' . date("Y-m-d H:i:s (T)") . " \"{$error['message']}\" in file {$error['file']} at line {$error['line']}";
			echo include_once(static::basePath().DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'error'.DIRECTORY_SEPARATOR.'500.phtml');
			echo $msg;
			App::response()->sendContent();
		}
	}

	public static function onException(Exception $e) {
		$msg = (string)$e;
		echo $msg;
	}

	/**
	 * @return string
	 */
	public static function basePath() {
		static $basePath;
		if (!isset($basePath)) {
			$basePath = realpath(dirname(dirname(__FILE__)));
		}
		return $basePath;
	}

	/**
	 * @param $string
	 * @param string $splitter
	 * @return string
	 */
	public static function uncamelize($string, $splitter="_") {
		$string = preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', $splitter.'$0', $string));
		return strtolower($string);
	}

	/**
	 * @param $string
	 * @param bool $capitalizeFirstCharacter
	 * @return mixed
	 */
	public static function camelize($string, $capitalizeFirstCharacter = false) {
		$string = str_replace(' ', '', ucwords(str_replace(array('-','_'), ' ', $string)));
		if (!$capitalizeFirstCharacter) {
			$string[0] = strtolower($string[0]);
		}
		return $string;
	}
}