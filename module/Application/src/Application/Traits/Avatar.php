<?php
namespace Application\Traits;

use \Application\Lib\Image;

//avatar management for any object type
trait Avatar {
	
	/**
	 * returns avatar urls and file paths by object $id
	 * 
	 * @param int $id
	 */
	public function getAvatar($id) {
		$avatarLastMod = 0;
		$udir = $this->getImageDir($id);
		$result = [];
		if(is_readable($udir['dir']."/avatar.jpg")) {
			$avatarLastMod = filemtime($udir['dir']."/avatar.jpg");
			$t = "?t=" . $avatarLastMod;

			$result['avatars'][0] = $udir['href']."/avatar.jpg" . $t;
			$result['avatarsPaths'][0] = $udir['dir']."/avatar.jpg";
			foreach($this->avatarSizes as $sizes) {
				if(!is_readable($udir['dir']."/avatar{$sizes[0]}.jpg")) {
					// resize
					Image::resize($udir['dir']."/avatar.jpg", $udir['dir']."/avatar{$sizes[0]}.jpg",$sizes[0], $sizes[1]);
				}
				$result['avatars'][$sizes[0]] = $udir['href']."/avatar{$sizes[0]}.jpg" . $t;
				$result['avatarsPaths'][$sizes[0]] = $udir['dir']."/avatar{$sizes[0]}.jpg";
			}
			return $result;
		}
		else {
			throw new \Exception('no avatar');
		}	
	}
	
	/**
	* create thumbnail from image
	* 
	* @param string $srcPath
	* @param int $id UserId
	* @throws 10000 on user id = 0
	* @throws 10001 on Source image file could not be read
	* @throws 10002 on Source image cannot be opened as JPEG or PNG
	* @throws 10003 on Source image seems to be too small
	* @throws 10004 on Destination image cannot be saved
	*/
	public function setAvatar($srcPath, $id=false) {
		if(!$id) {
			$id = $this->id;
		}

		$source = Image::openImage($srcPath);
		$path = $this->getImageDir($id);
		$dstPath = $path['dir'].'/avatar.jpg';
		if(!copy($srcPath, $dstPath)) {
			throw new \Exception('Destination image '.$dstPath.' cannot be saved', 10003);
		}

		foreach($this->avatarSizes as $sizes) {
			Image::resizeResource($source, $path['dir'].'/avatar'.$sizes[0].'.jpg', $sizes[0], $sizes[1]);
		}

		$userObject = $this->get($id);

		$udir = $this->getImageDir($id);
		$avatarLastMod = TIME;
		$userObject->avatarType = 'normal';
		$t = "?t=" . TIME;
		$userObject->avatars[0] = $udir['href']."/avatar.jpg" . $t;
		$userObject->avatarsPaths[0] = $udir['dir']."/avatar.jpg";
		foreach($this->avatarSizes as $sizes) {
			$userObject->avatars[$sizes[0]] = $udir['href']."/avatar{$sizes[0]}.jpg"  . $t;
			$userObject->avatarsPaths[$sizes[0]] = $udir['dir']."/avatar{$sizes[0]}.jpg";
		}

		$this->cacheSet($id, $userObject, 0);

		imagedestroy($source);
	}
	
	/**
	* returns images directory
	* 
	* @param int $id
	* @throws 10000 on user id = 0
	* @returns array of 'href' and 'dir': http and fs paths correspondingly 
	*/
	function getImageDir($id) {
		$objectName = $this->table;
		
		if(!$id) {
			throw new \Exception('NULL ' .$objectName. 'passed, integer > 0 expected', 10000);
		}

		$id = (int)$id; // security measure
		$hash=substr($id, 0, 3);
		if(!is_dir(BASEDIR . "public/".$objectName."/$hash/$id")) {
			@mkdir(BASEDIR . "public/".$objectName."/$hash/$id", 0775, true);
		}
		return [
			'href' =>  URL . $objectName."/$hash/$id",
			'dir' =>  realpath(BASEDIR . "public/".$objectName."/$hash/$id"),
		];
	}
	
	/**
	 * removes avatar images
	 * 
	 * @param int $id
	 */
	public function removeImages($id) {
		$udir = $this->getImageDir($id);
		$files = array_diff(scandir($udir['dir']), array('.','..'));
		foreach ($files as $file) {
			@unlink($udir['dir']."/$file");
		}
		@rmdir($udir['dir']);
	}
}
