<?php

define('LOCALE', 'en_US'); // ru_RU for russian
\CodeIT\Utils\Registry::set('lang', 1); // 1 is English, 2 is Rissian

define('BASEDIR', dirname(__DIR__).'/');

$config = require_once BASEDIR . 'config/local.php';

if (defined('JS_COMBINE') && JS_COMBINE) {
	require_once BASEDIR .  'config/static_versions.php';
	foreach ($staticVersions as $name => $version) {
		define(strtoupper($name), $version[0]);
	}
}

define('PASSWORD_HASH_COST', 12); //algorithmic cost that should be used while hashing password
define('REMEMBER_ME_PERIOD', 2592000);

umask(002);
define('TIME', time());

$config = array_merge_recursive($config, [
	'modules' => array(
		'Zend\I18n',
		'Zend\Session',
		'Zend\Form',
		'Zend\InputFilter',
		'Zend\Filter',
		'Zend\Cache',
		'Zend\Db',
		'Zend\Router',
		'CodeIT',
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
