#!/usr/bin/php
<?php

require 'vendor/autoload.php';

chdir(__DIR__);
$configDir = __DIR__ . '/config';
require $configDir . '/local.php';
$staticVersionsFile = $configDir . '/static_versions.php';
require $staticVersionsFile;

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

function combine($type, $files, $cacheFile) {
	$newestFile = 0;
	$link = str_replace('{ID}', '', $cacheFile);
	$sources = [];
	foreach ($files as $file) {
		if (preg_match('/^https?\:\/\//', $file)) {
			$fileName = 'public/cache/fetched-'.uniqid().'.'.$type;
			copy($file, $fileName);
			$fileMTime = 0;
		} else {
			$fileName = 'public/'.$type.'/'.$file;
			$fileMTime = filemtime($fileName);
		}
		$sources[] = $fileName;
		if ($fileMTime > $newestFile) {
			$newestFile = $fileMTime;
		}
	}
	$crc32 = crc32(implode('::', $sources));
	$cacheFile = str_replace('{ID}', '-'.$crc32, $cacheFile);

	$version = $cacheMTime = $newestFile;
	if (file_exists($cacheFile)) {
		$cacheMTime = filemtime($cacheFile);
	}

	if($newestFile > $cacheMTime || !is_readable($cacheFile)) {
		$version = $newestFile;
		echo "Writing $cacheFile...\n";
		$combined = str_replace('/public', BASE_URL, Minify::combine($sources));
		$fp = fopen($cacheFile, 'w');
		fwrite($fp, $combined);
		fclose($fp);
		@unlink($link);
		link($cacheFile, $link);
	}

	return $version;
}

echo "Minifying start...\n";

if(!is_dir('public/cache')) {
	mkdir('public/cache');
}

foreach ($groups as $groupName => $group) {
	$staticVersions[$groupName] = combine($group['type'], $group['files'], $group['cacheFile']);
}

$f = fopen($staticVersionsFile, 'w');
fwrite($f, "<?php \n\n\$staticVersions = " . var_export($staticVersions, true) . ";\n");
fclose($f);

echo "Minifying end...\n";
