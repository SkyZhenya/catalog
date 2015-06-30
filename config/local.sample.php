<?php

define('URL', 'http://dcodeit.net/codeit-carcass-natali/public/');

define('DEBUG', true);

define('SUPPORT_EMAIL', 'natali.ringel@codeit.com.ua');
define('SITE_NAME', 'CodeIT Carcass');

define('FACEBOOK_APP_ID', 1474956179385717);
define('FACEBOOK_SECRET', '1c04d56fb1175ef42ca12730fce45255');

define('GA_APP_ID', '928400678967-5phs21q39knkqavk6qi2234unm04gav4.apps.googleusercontent.com');
define('GA_SECRET', 'jmJuDR4spQmHNJ70fldJB-Jj');


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
