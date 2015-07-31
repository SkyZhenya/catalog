<?php

namespace Application\Lib;

/**
* Date/Time conversion helper
*/
class Time {
	
	const FORMAT_ICSSHORT = 'icsshort';
	const FORMAT_SHORT = 'short';
	const FORMAT_EXSHORT = 'exshort';
	const FORMAT_LONG_EU = 'long_eu';
	const FORMAT_LONG = 'long';
	const FORMAT_WEEK = 'week';
	const FORMAT_STRIPPED = 'striped';

	function formatTime($unixTime=false, $long='', $default = 'N\A') {
		if (!$unixTime)
			return $default;

		switch($long){
			case self::FORMAT_ICSSHORT:
				$format = 'His';
				break;
			case self::FORMAT_SHORT:
				$format = 'd.m.Y H:i';
				break;
			case self::FORMAT_EXSHORT:
				$format = 'H:i';
				break;
			case self::FORMAT_LONG_EU:
				$format = 'Y-m-d H:i';
				break;
			default://exlong
				$format = 'jS \of F Y H:i';
				break;
		}

		return date($format, $unixTime);
	}

	function formatDate($unixTime=false, $long='short', $default = 'N\A') {
		if (!$unixTime)
			return $default;

		switch($long){
			case self::FORMAT_ICSSHORT:
				$format = 'Ymd';
				break;
			case self::FORMAT_SHORT:
				$format = 'd.m.Y';
				break;
			case self::FORMAT_EXSHORT:
				$format = 'd M Y';
				break;
			case self::FORMAT_LONG:
				$format = 'd F Y';
				break;
			case self::FORMAT_WEEK:
				$format = 'D d M Y';
				break;
			case self::FORMAT_STRIPPED:
				$format = 'd-m-Y';
				break;
			case self::FORMAT_LONG_EU:
				$format = 'Y-m-d';
				break;
			default://exlong
				$format = 'jS \of F Y';
				break;
		}

		return date($format, $unixTime);

	}
}
