<?php
namespace Application\Lib\Validator;

use Zend\Validator\AbstractValidator;

class SimpleEmailValidator extends AbstractValidator{

	const INVALID_FORMAT = 'invalidFormat';

	protected $messageTemplates = array(
		self::INVALID_FORMAT => "Not a valid email format",
	);

	/**
	* Returns array of validation failure messages
	*
	* @return array
	*/
	public function getMessages()
	{
		$result = parent::getMessages();
		if (!empty($result)) {
			$result = array(
				'invalidFormat' => _("Not a valid email format"),
			);
		}
		return $result;
	}

	public function isValid($value){
		//set validation for each email part
		$user = '[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+';
		$domain = '.+';

		if(empty($value) || ! preg_match("/^$user@($domain)$/", $value)){
			$this->error(self::INVALID_FORMAT);
			return false;
		}
		return true;
	}

}
