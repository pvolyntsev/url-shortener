<?php

return array(
	'applicationName' => 'URL shortener',

	// Configuration for autoloader component
	'autoloader' => array(

		// Autoloader class by default is "AppAutoloader"
		#'class' => 'AppAutoloader',

		// Folders to scan for class definitions
		'import' => array(
			'/models/*',
			'/models/Form/Filter/*',
			'/models/Form/Validate/*',
		),

		// Paths to class definitions
		'map' => array(
			'FormController' => 'controllers/FormController.php',
			'RedirectorController' => 'controllers/RedirectorController.php',
		)
	),

	// Configuration for database component
	'db' => array(
		'class' => 'AppPdoConnection',
		'connectionString' => 'mysql:host=127.0.0.1;dbname=url',
		'emulatePrepare' => true,
		'username' => 'url_rw',
		'password' => 'user-password',
		'charset' => 'utf8',
	),

	// Configuration for URL rounting component
	'router' => array(
		// Router class by default is "AppRouter"
		#'class' => 'AppRouter',

		// Incoming URL mathing rules
		'rules' => array(
			// if request URI is empty
			'' => array('Form', 'form'),

			// if request URI looks like /JSDjuoh3
			'/^(?<url>[a-zA-Z0-9]{1,6})$/' => array('Redirector', 'redirect', array('url' => ':url')),
		),
	),
);