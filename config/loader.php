<?php

/**
 * Registering an autoloader
 */
$loader = new \Phalcon\Loader();

// $loader->registerNamespaces(array(
//     'InfluxDB' => $config->application->influxdbDir
// ));

$loader->registerDirs(
    array(
        $config->application->modelsDir
    )
)->register();

require_once __DIR__ . '/../vendor/autoload.php';
