<?php

$config = include('app/config/app.php');

require_once('app/App.php');
App::run($config);
