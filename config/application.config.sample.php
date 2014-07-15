<?php

define('LOCALE', 'en_US'); // ru_RU for russian
Zend\Registry::set('lang', 1); // 1 is English, 2 is Rissian

define('DATABASE_NAME', 'codeit_adminka');
define('DATABASE_HOST', 'dcodeit.net');
define('DATABASE_SALT', 'jh^lP0)z,Zjw#4082lk<NxW');
define('URL', 'http://dcodeit.net/codeit-project/public/');
define('MEMCACHE_ENABLED', false);
define('MEMCACHE_NAMESPACE', 'codeit-project');
define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_DEBUG', false);

define('DEBUG', true);

define('SUPPORT_EMAIL', 'natali.ringel@codeit.com.ua');
define('SITE_NAME', 'CodeIT Carcass');

define('BASEDIR', dirname(__FILE__).'/../');

Zend\Registry::set('dbConfig', array(
	'host' => DATABASE_HOST,
	'dbname' => DATABASE_NAME,
	'driver' => 'Pdo',
	'dsn' => 'mysql:dbname='.DATABASE_NAME.';hostname='.DATABASE_HOST,
	'username' => 'ifix',
	'password' => 'ifix',
	'driver_options' => array(
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
	),
));

umask(002);
define('TIME', time());

return array(
    'modules' => array(
        'Application',		
        'Admin',		
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
);
