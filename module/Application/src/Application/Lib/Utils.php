<?php
namespace Application\Lib;

class Utils {
	static function generatePassword($length = 8) {
		$chars = 'abcdefghijkmnoprqstuvwxyzABCDEFGHIJKLMNPRQSTUVWXYZ23456789';
		$numChars = strlen($chars);

		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$string .= substr($chars, rand(1, $numChars) - 1, 1);
		}
		return $string;
	}

	function stripText($pagetext, $nchars=200, $link=false, $addAnchor=true)	{
		if(isset($nchars)&&(strlen($pagetext)>$nchars)) {
			$border="--xlengine-border--";
			$pagetext=wordwrap($pagetext,$nchars,$border);
			$pt=explode($border,$pagetext);
			if(strlen($pt[0])<=$nchars) {
				$pagetext=$pt[0]."...";
			}
			else {
				$pagetext=substr($pt[0],0,$nchars)."...";
			}
			if($link) {
				if($addAnchor) {
					$link="<A HREF='$link'>read more</A>";
				}
				$pagetext.="&nbsp;$link";    }
		}
		return $pagetext;
	}

	function mod10($txt, $odd=true) {
		$sum = 0;
		$txt = (string)$txt;
		for($i=strlen($txt)-1; $i>-1; $i--) {
			$sum += ($odd ? 3 : 1) * $txt[$i];
			$odd = !$odd;
		}
		return ((10 - $sum % 10) % 10);
	}
	
	function SEOurlEncode($name){
		$values = array(
		"-",
		" ",
		);
		$replace = array(
		"_",
		"-",
		);
		$seoname = str_replace($values, $replace, $name);
		return $seoname;
	}

	function SEOurlDecode($seoname){
		$values = array(
		"-",
		"_",
		);
		$replace = array(
		" ",
		"-",
		);
		$name = str_replace($values, $replace, $seoname);
		return $name;
	}

}
