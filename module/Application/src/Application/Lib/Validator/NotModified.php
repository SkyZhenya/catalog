<?php

namespace Application\Lib\Validator;

use Zend\Validator\AbstractValidator;

class NotModified extends AbstractValidator {

	const IS_MODIFIED = 'isModified';

	protected $comparableTimestamp;

	protected $messageTemplates = array(
		self::IS_MODIFIED  => 'Record has been modified.',
	);

	public function __construct($options = null) {
		parent::__construct($options);
	}

	public function isValid($value) {
		if($value != $this->getComparableTimestamp()) {
			$this->error(self::IS_MODIFIED);
			return false;
		}

		return true;
	}

	protected function setComparableTimestamp($timestamp) {
		$this->comparableTimestamp = $timestamp;
		return $this;
	}

	protected function getComparableTimestamp() {
		return $this->comparableTimestamp;
	}
} 