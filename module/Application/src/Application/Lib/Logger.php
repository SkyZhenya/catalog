<?php
namespace Application\Lib;  

class Logger {
	/**
	* writes string to log
	* 
	* @param string $string
	*/
	static public function write($string) {
    $fp = @fopen(BASEDIR.'.log/messages.log','a+');
    @fwrite($fp, date('d.m.Y H:i:s: ').print_r($string,true));
    @fwrite($fp,"\n");
    @fclose($fp);
	}

	static public function backtrace() {
		$r=debug_backtrace();
		array_shift($r);
		$fp = @fopen(BASEDIR.'.log/messages.log','a+');
    @fwrite($fp, date('d.m.Y H:i:s').": DEBUG BACKTRACE:\n");
		foreach($r as $l) {
			@fwrite($fp, "{$l['file']} line: {$l['line']} class: {$l['class']} function: {$l['function']} \n");
//			self::dump($l['args']);
		}
    @fwrite($fp, '- end debug backtrace -');
	}

	function dump($var) {
	  if(DEBUG)
		  var_dump($var,true);
	}
}

?>
