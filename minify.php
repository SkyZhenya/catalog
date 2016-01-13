#!/usr/bin/php
<?php

// Setup autoloading
require 'vendor/autoload.php';
require 'module/Application/src/Application/Lib/MinifyProcessor.php';

chdir(__DIR__);
$configDir = __DIR__ . '/config';
require $configDir . '/local.php';

$staticVersionsFile = $configDir . '/static_versions.php';
if(file_exists($staticVersionsFile)) include $staticVersionsFile;
else $staticVersions = [];

$groups = [
	'css_layout' => [
		'type' => 'css',
		'cacheFile' => 'public/cache/layout{ID}.css',
		'files' => [
			'bootstrap.min.css',
			'style.css',
		],
	],
	'js_layout' => [
		'type' => 'js',
		'cacheFile' => 'public/cache/layout{ID}.js',
		'files' => [
			'jquery-2.1.1.min.js',
			'bootstrap.min.js',
		],
	]
];

$minifyProcessor = new \Application\Lib\MinifyProcessor($staticVersionsFile, $staticVersions, BASE_URL);
$minifyProcessor->execute($groups);
