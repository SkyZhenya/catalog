<?php
define('IS_CLI', php_sapi_name() == 'cli');

define('DOMAIN', 'dcodeit.net');
define('BASE_URL', '/codeit-carcass-natali/public'); //set to '/' on live
//define('HTTP_SCHEMA', 'http'); // uncomment if you need concrete scheme
define('DEFAULT_HTTP_SCHEMA', 'http');

if (!defined('HTTP_SCHEMA') && !IS_CLI) {
 	define('HTTP_SCHEMA', $_SERVER['REQUEST_SCHEME']);
} else {
	define('HTTP_SCHEMA', DEFAULT_HTTP_SCHEMA);
}

define('URL', HTTP_SCHEMA . '://' . DOMAIN . BASE_URL . '/');

define('DEBUG', true);

define('SUPPORT_EMAIL', 'natali.ringel@codeit.com.ua');
define('SITE_NAME', 'CodeIT Carcass');

define('FACEBOOK_APP_ID', 1474956179385717);
define('FACEBOOK_SECRET', '1c04d56fb1175ef42ca12730fce45255');

define('GA_APP_ID', '928400678967-5phs21q39knkqavk6qi2234unm04gav4.apps.googleusercontent.com');
define('GA_SECRET', 'jmJuDR4spQmHNJ70fldJB-Jj');

//define('SESSION_NAME', '');//do not need this constant on production

return [
	'database' => [
		'host' => 'dcodeit.net',
		'name' => 'codeit-carcass',
		'user' => 'ifix',
		'password' => 'ifix',
	],
	'redis' => [
		'enabled' => true,
		'host' => '127.0.0.1',
		'db' => 0,
		'namespace' => 'codeit-carcass',
		'debug' => false,
	],
];
