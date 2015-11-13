<?php
date_default_timezone_set('UTC');

//----------------------------
// DATABASE CONFIGURATION
//----------------------------

/*

Valid types (adapters) are Postgres & MySQL:

'type' must be one of: 'pgsql' or 'mysql' or 'sqlite'

*/
$config = require_once '../config/local.php';
$baseDir = dirname(__DIR__).'/';

define('RUCKUSING_SCHEMA_TBL_NAME', 'migrations_info');
define('RUCKUSING_TS_SCHEMA_TBL_NAME', 'migrations');

return array(
    'db' => array(
        'development' => array(
            'type' => 'mysql',
            'host' => $config['database']['host'],
            'port' => 3306,
            'database' => $config['database']['name'],
            'user' => $config['database']['user'],
            'password' => $config['database']['password'],
        ),

    ),
    'migrations_dir' => array('default' => $baseDir . 'db'. DIRECTORY_SEPARATOR . 'migrations'),
    'db_dir' => $baseDir . 'db',
    'log_dir' => $baseDir . 'db'. DIRECTORY_SEPARATOR . 'logs',
    //'ruckusing_base' => $baseDir . 'vendor/ruckusing-migrations'
);
