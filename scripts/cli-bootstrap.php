<?php

/**
 * CLI Bootstrap
 */
error_reporting(E_ALL);
set_time_limit(0);
/**
 * Read the configuration
 */
define('APP_PATH', dirname(dirname(__FILE__)));

$config = include APP_PATH . "/config/config.php";
/**
 * Include the loader
 */
require APP_PATH . "/config/loader.php";
/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */

/**
 * Include the application services
 */
require APP_PATH . "/config/services.php";