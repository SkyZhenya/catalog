<?php
namespace Application\Lib;

use Intervention\Image\ImageManager;
use Intervention\Image\Image as InterventionImage;

/**
 * Exports some image processing methods
 */
class Image {
	
	/**
	 * list of md5 codes of images to exclude (like FB default avatars) 
	 * 
	 * @var mixed
	 */
	protected static $exclusions = [
		'517d3357f71886953d7f2ace7ecb09e0', //FB big man avatar
		'3c8edaf4784c1d88187c17fef2ca5a36', //FB big woman avatar
	];
	
	/**
	 * open image file and return GD resource
	 * 
	 * @param string $srcPath
	 * @throws Exception code 10001 on Source image file could not be read
	 * @throws Exception code 10002 on Source image cannot be opened as JPEG, GIF or PNG
	 * @returns \Intervention\Image\Image
	 */
	public static function openImage($srcPath) {
		if(!is_readable($srcPath) && !preg_match('#^https?\:\/\/#', $srcPath)) {
			throw new \Exception(_('Source image file could not be read ('.$srcPath.')'), 10001);
		}

		$imageManager = new ImageManager();
		$source = $imageManager->make($srcPath);
		$exif = @exif_read_data($srcPath);
		if($exif && isset($exif['Orientation'])) {
			switch($exif['Orientation']) {
				case 3: $source->rotate(180, 0); break;
				case 6: $source->rotate(270, 0); break;
				case 8: $source->rotate(90, 0); break;
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
	 * @throws \Intervention\Image\Exception\NotWritableException on Destination image cannot be saved
	 */
	public static function resize(string $srcPath, string $dstPath, int $width, int $height, string $method='fill') {
		$source = self::openImage($srcPath);
		self::resizeResource($source, $dstPath, $width, $height, $method);
	}
	
	/**
	 * create thumbnail from image resource and save as file
	 * 
	 * @param \Intervention\Image\Image $source
	 * @param string $dstPath
	 * @param int $width
	 * @param int $height
	 * @param string $method: fill/resize
	 * @throws Exception code 10003 on Source image seems to be too small
	 * @throws \Intervention\Image\Exception\NotWritableException on Destination image cannot be saved
	 */
	public static function resizeResource(InterventionImage $source, string $dstPath, int $width, int $height, string $method='fill') {
		$thumb = self::resizeResourceAndReturn($source, $width, $height, $method);
		$thumb->save($dstPath, 96);
	}

	/**
	 * create thumbnail from image resource and return resized resource
	 * 
	 * @param \Intervention\Image\Image $source
	 * @param string $dstPath
	 * @param int $width
	 * @param int $height
	 * @param string $method: fill/resize
	 * @returns \Intervention\Image\Image $thumbnail
	 * @throws Exception code 10003 on Source image seems to be too small
	 */
	protected static function resizeResourceAndReturn(InterventionImage $source, int $width, int $height, string $method='fill') {
		$picX = $source->getWidth(); $picY = $source->getHeight();
		
		if(!$picX || !$picY) {
			throw new \Exception('Source image seems to be too small', 10003);
		}

		$thumb = clone $source;
		if($method == 'fill') {
			$thumb->fit($width, $height, null, 'top');
		}
		else { // just resize
			$thumb->resize($width, $height);
		}

		return $thumb;
	}

	public static function simpleImageUpload($from, $to){
	  $in = fopen($from, "rb");
	  $out = fopen($to, "wb");
	  while ($chunk = fread($in, 8192)){
	  	fwrite($out, $chunk, 8192);
	  }
	  fclose($in);
	  fclose($out);
	}

	public static function checkForExclusion($img) {
		$hash = md5_file($img);
		if(!in_array($hash, self::$exclusions)) { 
			return true; 
		}
		return false;
	}
}
