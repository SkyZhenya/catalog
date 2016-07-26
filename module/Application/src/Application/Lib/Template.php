<?php
namespace Application\Lib;

use Application\Model\TemplateTable;
use CodeIT\Model\Exception\ItemNotFoundException;

class Template extends TemplateTable {

	/**
	 * prepare message and subject by template name
	 * replace shortcodes by values from $params
	 * 
	 * @param string $name
	 * @param array $params
	 */
	public function prepareMessage($name = '', $params = []) {
		$template = $this->getByNameWithLang($name);
		if (empty($template))
			throw new ItemNotFoundException(_('Template does not exist'), 2001);
		$params['SITE_NAME'] = SITE_NAME;
		$params['URL'] = URL;
		$variables = array();
		foreach($params as $key => $value) {
			$variables[] = '{'.$key.'}';
		}
		$message = [
			'subject' => str_replace($variables, $params, $template->subject),
			'text' => str_replace($variables, $params, $template->text),
		];

		return $message;
	}

}
