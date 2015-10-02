<?php

defined('APP_PATH') || define('APP_PATH', realpath('.'));

return new \Phalcon\Config(array(

    'database' => array(
        'adapter'    => 'Mysql',
        'host'       => 'localhost',
        'username'   => '',
        'password'   => '',
        'dbname'     => 'ttnm',
        'charset'    => 'utf8',
    ),

    'application' => array(
        'modelsDir'      => APP_PATH . '/models/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'viewsDir'       => APP_PATH . '/views/',
        'influxdbDir'   => APP_PATH . '/vendor/',
        'tasksDir'      => APP_PATH . '/tasks/',
        'baseUri'        => '/',
    ),

    'monitor' => array(
        'localTimezone' => 'Europe/Amsterdam',
        'influxTimezone' => 'UTC',
        'influxHost' => 'croft.thethings.girovito.nl',
        'influxPort' => 8086,
        'downStatusOffset' => '-2 hours',
        'maxSinceOffset' => '-4 hours',
        'beanstalkHost'  => '127.0.0.1',
        'beanstalkPort'  => 11300,
        'slackHook' => 'https://hooks.slack.com/services/<slackIntegrationKey>'
    )
));
