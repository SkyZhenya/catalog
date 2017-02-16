<?php
namespace Application\Traits;

use Application\Lib\Image;

trait Imaging {

	public function getImage($id) {
		$udir = $this->getImageDirectory($id);
		$result = [];
		
		foreach($this->avatarSizes as $sizes) {
			$files[$sizes[0]] = scandir($udir['dir']."/$sizes[0]s");
			$origin[] = scandir($udir['dir']."/origin");
			unset($origin[0][0]);
			unset($origin[0][1]);
			
			unset($files[$sizes[0]][0]);
			unset($files[$sizes[0]][1]);
		
			foreach($files[$sizes[0]] as $name){
			if(is_readable($udir['dir']."/$sizes[0]s/". $name)){
				$imageLastMod = filemtime($udir['dir']."/$sizes[0]s/".$name);
				$t = "?t=" . $imageLastMod;

				$result['image'][$sizes[0]][] = $udir['href']."/$sizes[0]s/".$name . $t;
				$result['imagePaths'][$sizes[0]][] = $udir['dir']."/$sizes[0]s/".$name;

				}
			}
		}
		foreach ($origin[0] as $name){
				
				$result['image']['origin'][] = $udir['href']."/origin/".$name;
			}
		
		return $result;
	}

	public function setImage($srcPath, $id, $name) {
		if(!$id) {
			$id = $this->id;
		}
		
		$path = $this->getImageDirectory($id);
		$dstPath = $path['dir'].'/origin/'. $name;
		if(!copy($srcPath, $dstPath)) {
			throw new \Exception('Destination image '.$dstPath.' cannot be saved', 10003);
		}

		foreach($this->avatarSizes as $sizes) {
			Image::resize($srcPath, $path['dir']."/$sizes[0]s/".$name, $sizes[0], $sizes[1]);
		}

		$userObject = $this->get($id);
		

		$udir = $this->getImageDirectory($id);
		
		$avatarLastMod = TIME;
		$userObject->photosType = 'normal';
		$t = "?t=" . TIME;

		foreach($this->avatarSizes as $sizes) {
			$userObject->avatars[$sizes[0]] = $udir['href']."/$sizes[0]s/". $name . $t;
			$userObject->avatarsPaths[$sizes[0]] = $udir['dir']."/$sizes[0]s/". $name;
		}
		
		$this->cacheSet($id, $userObject, 0);

	}

	public function getImageDirectory($id) {
		
		$objectName = $this->table;

		if(!$id) {
			throw new \Exception('NULL ' .$objectName. 'passed, integer > 0 expected', 10000);
		}
		
		foreach ($this->avatarSizes as $sizes) {
			$id = (int)$id; // security measure
			if(!is_dir(BASEDIR . "public/".$objectName."/$id"."/$sizes[0]s")) {
				@mkdir(BASEDIR . "public/".$objectName."/$id"."/$sizes[0]s", 0775, true);
				
			}
		}
		@mkdir(BASEDIR . "public/".$objectName."/$id"."/origin", 0775, true);
		return [
			'href' =>  URL . $objectName."/$id",
			'dir' =>  realpath(BASEDIR . "public/".$objectName."/$id"),
		];
	}

	public function removePhoto($id, $name) {
		$path = $this->getImageDirectory($id);

		foreach ($this->avatarSizes as $sizes) {
			@unlink($path['dir']."/$sizes[0]s/".$name);
			@unlink($path['dir']."/$name");
		}
	}
	

	public function removeDir($id) {
		$path = $this->getImageDirectory($id);
		$this->delTree($path['dir']);
	}

	public function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
		  (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}

}