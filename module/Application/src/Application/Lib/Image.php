<?php
  
namespace Application\Lib;

/**
 * Exports some image processing methods
 */
class Image {
	
	/**
	* list of md5 codes of images to exclude (like FB default avatars) 
	* 
	* @var mixed
	*/
	static $exclusions = [
		'517d3357f71886953d7f2ace7ecb09e0', //FB big man avatar
		'3c8edaf4784c1d88187c17fef2ca5a36', //FB big woman avatar
	];
	
	/**
	 * open image file and return GD resource
	 * 
	 * @param string $srcPath
	 * @throws 10001 on Source image file could not be read
	 * @throws 10002 on Source image cannot be opened as JPEG, GIF or PNG
	 */
	static function openImage($srcPath) {
		if(!is_readable($srcPath)) {
			throw new \Exception(_('Source image file could not be read ('.$srcPath.')'), 10001);
		}
		
		$source = @imagecreatefromjpeg($srcPath);
		if (!$source) {
			$source = @imagecreatefrompng($srcPath);
		}
		if (!$source) {
			$source = @imagecreatefromgif($srcPath);
		}
		
		if(!$source) {
			throw new \Exception(_('Wrong file format'), 10002);
		}
		
		$exif = @exif_read_data($srcPath);
		if($exif && isset($exif['Orientation'])) {
			switch($exif['Orientation']) {
				case 3: $source = imagerotate($source, 180, 0); break;
				case 6: $source = imagerotate($source, 270, 0); break;
				case 8: $source = imagerotate($source, 90, 0); break;
			}
		}
		
		return $source;
	}
	
	/**
	 * create thumbnail from image file
	 * 
	 * @param string $srcPath
	 * @param string $dstPath
	 * @param int $width
	 * @param int $height
	 * @param string $method: fill/resize
	 * @throws 10001 on Source image file could not be read
	 * @throws 10002 on Source image cannot be opened as JPEG or PNG
	 * @throws 10003 on Source image seems to be too small
	 * @throws 10004 on Destination image cannot be saved
	 */
	static function resize($srcPath, $dstPath, $width, $height, $method='fill') {
		$source = self::openImage($srcPath);
		self::resizeResource($source, $dstPath, $width, $height, $method);
	}
	
	/**
	 * create thumbnail from image resource
	 * 
	 * @param resource $source
	 * @param string $dstPath
	 * @param int $width
	 * @param int $height
	 * @param string $method: fill/resize
	 * @throws 10003 on Source image seems to be too small
	 * @throws 10004 on Destination image cannot be saved
	 */
	static function resizeResource($source, $dstPath, $width, $height, $method='fill') {
		$picX = imagesx($source); $picY = imagesy($source);
		
		if(!$picX || !$picY) {
			throw new \Exception('Source image seems to be too small', 10003);
		}
		
		$idealRatio = $width/$height;
		if($method == 'fill') {
			$myH = $height; $myW = $width;
			if($picX/$picY > $idealRatio) {
				$srcY = 0; $srcX = round($picX - $picY*$width/$height)/2;
				$srcH = $picY; $srcW = $picY * $width/$height;
			}
			else {
				$srcX = 0; $srcY = round($picY - $picX*$height/$width)/2;
				$srcW = $picX; $srcH = $picX * $height/$width;
			}
		}
		else { // just resize
			if($picX/$picY > $idealRatio) {
				$myW = min($picX, $width);
				$myH = $myW * $picY / $picX;
				$srcY = 0; $srcX = 0; $srcH = $picY; $srcW = $picX;
			}
			else {
				$myH = min($picY, $height);
				$myW = $myH * $picX / $picY;
				$srcX = 0; $srcY = 0; $srcW = $picX; $srcH = $picY;
			}
		}
		
		$thumb = imagecreatetruecolor($myW, $myH);
		imagecopyresampled($thumb, $source, 0, 0, $srcX, $srcY, $myW, $myH, $srcW, $srcH);

		$result = @imagejpeg($thumb, $dstPath, 96);
		imagedestroy($thumb);
		if(!$result) {
			throw new \Exception('Destination image '.$dstPath.' cannot be saved', 10003);
		}
	}
	
	static function simpleImageUpload($from, $to){
	  $in = fopen($from, "rb");
	  $out = fopen($to, "wb");
	  while ($chunk = fread($in, 8192)){
	  	fwrite($out, $chunk, 8192);
	  }
	  fclose($in);
	  fclose($out);
	}

	static function checkForExclusion($img) {
		$hash = md5_file($img);
		if(!in_array($hash, self::$exclusions)) { 
			return true; 
		}
		return false;
	}
}
