<?php
namespace Application\Lib;
use \Application\Model\TemplateTable;

class Template extends TemplateTable {
	
  public function prepareMessage($name = '', $params = array()) {
  	$template = $this->getByNameWithLang($name);
  	$params['SITE_NAME'] = SITE_NAME;
  	$params['URL'] = URL;
  	$params['EMAIL_CONTENT_URL'] = EMAIL_CONTENT_URL;
  	$variables = array();
		foreach($params as $key => $value){
			$variables[] = '{'.$key.'}';
		}
		$baseTemplate = file_get_contents(BASEDIR.'/module/Application/view/email/template.html');
		$template->text = str_replace('{EMAIL_CONTENT}', $template->text, $baseTemplate);
		$message = array(
			'subject' => str_replace($variables, $params, $template->subject),
			'text' => str_replace($variables, $params, $template->text),
		);
		
		return $message;
  }

}
