<?php

define('LOCALE', 'en_US'); // ru_RU for russian
Zend\Registry::set('lang', 1); // 1 is English, 2 is Rissian

define('BASEDIR', dirname(__DIR__).'/');

$config = require_once BASEDIR . 'config/local.php';

define('PASSWORD_HASH_COST', 12); //algorithmic cost that should be used while hashing password
define('REMEMBER_ME_PERIOD', 2592000);

Zend\Registry::set('dbConfig', array(
	'host' => $config['database']['host'],
	'dbname' => $config['database']['name'],
	'driver' => 'Pdo',
	'dsn' => 'mysql:dbname='.$config['database']['name'].';host='.$config['database']['host'],
	'username' => $config['database']['user'],
	'password' => $config['database']['password'],
	'driver_options' => array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
	),
));

umask(002);
define('TIME', time());

$config = array_merge_recursive($config, [
	'modules' => array(
		'Application',		
		'Admin',
		'Auth',	
		'Tools',
	),
	'module_listener_options' => array(
		'config_glob_paths'    => array(
			'config/autoload/{,*.}{global,local}.php',
		),
		'module_paths' => array(
			'./module',
			'./vendor',
		),
	)
]);

return $config;
