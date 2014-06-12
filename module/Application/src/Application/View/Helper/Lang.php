<?php
namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Model\LangTable;

class Lang extends AbstractHelper {
	
	/**
	* returns langTable with a currently active language
	* 
	* @return \Application\Model\LangTable
	*/
	public function __invoke() {
		try {
			$lang = \Zend\Registry::get('lang');
			$langTable = new LangTable($lang);
		}
		catch(\Exception $e) {}
		return $langTable;
	}
}
