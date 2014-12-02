<?php

define('LOCALE', 'en_US'); // ru_RU for russian
Zend\Registry::set('lang', 1); // 1 is English, 2 is Rissian

define('DATABASE_NAME', 'codeit_adminka');
define('DATABASE_HOST', 'dcodeit.net');
define('URL', 'http://dcodeit.net/codeit-carcass/public/');
define('REDIS_ENABLED', false);
define('REDIS_NAMESPACE', 1); // database integer number
define('REDIS_HOST', 'localhost');
define('REDIS_DEBUG', false);

define('DEBUG', true);

define('SUPPORT_EMAIL', 'natali.ringel@codeit.com.ua');
define('SITE_NAME', 'CodeIT Carcass');

define('BASEDIR', dirname(__FILE__).'/../');

define('PASSWORD_HASH_COST', 12); //algorithmic cost that should be used while hashing password

define('FACEBOOK_APP_ID', 1474956179385717);
define('FACEBOOK_SECRET', '1c04d56fb1175ef42ca12730fce45255');

define('GA_APP_ID', '928400678967-5phs21q39knkqavk6qi2234unm04gav4.apps.googleusercontent.com');
define('GA_SECRET', 'jmJuDR4spQmHNJ70fldJB-Jj');

define('REMEMBER_ME_PERIOD', 2592000);

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
