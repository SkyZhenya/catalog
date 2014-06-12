<?php

namespace Application\Lib;
/**
* Formats project-specific strings, e.g. phone numbers
*/
class Format {
	
	/**
	* returns phone as full 11-digit E.164 format
	* 
	* @param string $phone
	* @return string E.164
	*/
	static public function phoneFormat($phone) {
		$phone = str_replace(array('-', ' '), array('', ''), $phone);
		if((strlen($phone) == 10) && ($phone[0] == '0')) {
			return preg_replace('/^0/', '46', $phone);
		}

		return $phone;
	}
	
}
