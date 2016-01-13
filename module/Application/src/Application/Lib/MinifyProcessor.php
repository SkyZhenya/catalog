<?php
namespace Application\Lib;

class MinifyProcessor {

	private $oldStaticVersions = [];
	private $baseURL;
	private $staticVersionsFile;

	/**
	 * @param string $staticVersionsFile - file path
	 * @param array $staticVersions
	 * @return MinifyController
	 */
	public function __construct($staticVersionsFile, $staticVersions, $baseURL) {
		$this->staticVersionsFile = $staticVersionsFile;
		$this->oldStaticVersions = $staticVersions;
		$this->baseURL = $baseURL;
	}

	/**
	 * @param string $type (example - css, js)
	 * @param array $files
	 * @param string $cacheFile - file path mask
	 * @param string $groupName (example - css_layout)
	 * @returns array
	 */
	private function combine($type, $files, $cacheFileMask, $groupName) {
		$newestFile = 0;
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
		

		$version = 0;
		$oldFile = false;
		if(isset($this->oldStaticVersions[$groupName])) {
			$version = $this->oldStaticVersions[$groupName][0];
			$oldFile = str_replace('{ID}', '-'.$version, $cacheFileMask);
		}
		
		$cacheMTime = $newestFile;
		$crc32 = crc32(implode('::', $sources));

		if (file_exists($oldFile)) {
			$cacheMTime = filemtime($oldFile);
		}

		if($newestFile > $cacheMTime || !is_readable($oldFile)) {//first creation or files content was changed
			$version = $newestFile;
			$cacheFile = str_replace('{ID}', '-'.$version, $cacheFileMask);
			$this->writeFile($cacheFile, $sources, $oldFile);
		}
		elseif($this->oldStaticVersions[$groupName][1] != $crc32) {//if list of files was changed
			$version = time();
			$cacheFile = str_replace('{ID}', '-'.$version, $cacheFileMask);
			$this->writeFile($cacheFile, $sources, $oldFile);
		}

		return [$version, $crc32];
	}
	
	/**
	 * @param string $cacheFile
	 * @param array $sources
	 * @param string $oldFile
	 */
	private function writeFile($cacheFile, $sources, $oldFile = false) {
		echo "Writing $cacheFile...\n";
		$combined = str_replace('/public', $this->baseURL, \Minify::combine($sources));
		$fp = fopen($cacheFile, 'w');
		fwrite($fp, $combined);
		fclose($fp);
		
		if($oldFile) {
			@unlink($oldFile);
		}
	}
	
	/**
	 * @param array $groups
	 */
	public function execute($groups) {
		echo "Minifying start...\n";

		if(!is_dir('public/cache')) {
			mkdir('public/cache');
		}

		foreach ($groups as $groupName => $group) {
			$staticVersions[$groupName] = $this->combine($group['type'], $group['files'], $group['cacheFile'], $groupName);
		}

		$f = fopen($this->staticVersionsFile, 'w');
		fwrite($f, "<?php \n\n\$staticVersions = " . var_export($staticVersions, true) . ";\n");
		fclose($f);

		echo "Minifying end...\n";
	}

}