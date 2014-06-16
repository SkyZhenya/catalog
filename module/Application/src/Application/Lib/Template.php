<?php
namespace Application\Lib;
use \Application\Model\TemplateTable;

class Template extends TemplateTable {
	
  public function prepareMessage($name = '', $params = array()) {
  	$template = $this->getByNameWithLang($name);
  	$params['SITE_NAME'] = SITE_NAME;
  	$params['URL'] = URL;
  	$variables = array();
		foreach($params as $key => $value){
			$variables[] = '{'.$key.'}';
		}
		$message = array(
			'subject' => str_replace($variables, $params, $template->subject),
			'text' => str_replace($variables, $params, $template->text),
		);
		
		return $message;
  }

}
