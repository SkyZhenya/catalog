<?php

define('DATABASE_NAME', 'sstsafety');
define('DATABASE_HOST', 'dcodeit.net');
define('DATABASE_SALT', 'jh^lP0)z,Zjw#4082lk<NxW');
define('URL', 'http://dcodeit.net/sstsafety-natali/public/');
define('MEMCACHE_ENABLED', true);
define('MEMCACHE_NAMESPACE', 'sstsafety');
define('MEMCACHE_HOST', '127.0.0.1');
define('MEMCACHE_DEBUG', false);
define('DOMAIN', 'registrera.sstnet.se');

define('DEBUG', true);

define('SUPPORT_EMAIL', 'sstsafety@null.dcodeit.net');
define('ADMIN_EMAIL', 'natali.ringel@codeit.com.ua');
define('SITE_NAME', 'SST Safety');

define('API_URL', 'https://api.sstsafety.com/');

define('BASEDIR', dirname(__FILE__).'/../');

//Should we send SMS and email to owner or not
define('OWNER_NOTIFICATION_ENABLED', false );

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
