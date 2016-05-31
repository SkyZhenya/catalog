<?php
define('IS_CLI', php_sapi_name() == 'cli');

define('DOMAIN', 'dcodeit.net');
define('BASE_URL', '/carcass/public'); //leave it empty on live
//define('HTTP_SCHEME', 'http'); // uncomment if you need concrete scheme
define('DEFAULT_HTTP_SCHEME', 'http');

if (!defined('HTTP_SCHEME') && !IS_CLI) {
	define('HTTP_SCHEME', $_SERVER['REQUEST_SCHEME']);
} else {
	define('HTTP_SCHEME', DEFAULT_HTTP_SCHEME);
}

define('URL', HTTP_SCHEME . '://' . DOMAIN . BASE_URL . '/');

define('DEBUG', true);

define('SUPPORT_EMAIL', 'test@example.com');
define('SITE_NAME', 'CodeIT Carcass');

define('FACEBOOK_APP_ID', 1474956179385717);
define('FACEBOOK_SECRET', '1c04d56fb1175ef42ca12730fce45255');

define('GA_APP_ID', '928400678967-5phs21q39knkqavk6qi2234unm04gav4.apps.googleusercontent.com');
define('GA_SECRET', 'jmJuDR4spQmHNJ70fldJB-Jj');

//define('SESSION_NAME', '');//do not need this constant on production

define('JS_COMBINE', true);
define('GZIP_OUTPUT', true);

return [
	'database' => [
		'host' => 'dcodeit.net',
		'name' => 'codeit-carcass',
		'user' => 'ifix',
		'password' => 'ifix',
	],
	'cache' => [
		'enabled' => true,
		'namespace' => 'codeit-carcass',
	],
	'redis' => [
		'host' => '127.0.0.1',
		'port' => 6379,
		'db' => 0,
		'options' => [
			\Redis::OPT_SERIALIZER => \Redis::SERIALIZER_IGBINARY,
		],
	],
];
