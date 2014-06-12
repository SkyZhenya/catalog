<?php

namespace Application\Lib;

/**
* Date/Time conversion helper
*/
class Time {
	function formatTime($unixTime=false, $long='') {
		switch($long){
			case 'icsshort':
				$format = 'His';
				break;
			case 'short':
				$format = 'd.m.Y H:i';
				break;
			case 'exshort':
				$format = 'H:i';
				break;
			case 'long_eu':
				$format = 'Y-m-d H:i';
				break;
			default://exlong
				$format = 'jS \of F Y H:i';
				break;
		}
		
	  if(!$unixTime) {
	    return "N/A";
	  } else {
	    return date($format, $unixTime);
	  }
	}

	function formatDate($unixTime=false, $long='short') {
		switch($long){
			case 'icsshort':
				$format = 'Ymd';
				break;
			case 'short':
				$format = 'd.m.Y';
				break;
			case 'exshort':
				$format = 'd M Y';
				break;
			case 'long':
				$format = 'd F Y';
				break;
			case 'week':
				$format = 'D d M Y';
				break;
			case 'striped':
				$format = 'd-m-Y';
				break;
			case 'long_eu':
				$format = 'Y-m-d';
				break;
			default://exlong
				$format = 'jS \of F Y';
				break;
		}
		
	  if(!$unixTime) {
	    return "N/A";
	  } else {
	    return date($format, $unixTime);
	  }
	}
}
?>
