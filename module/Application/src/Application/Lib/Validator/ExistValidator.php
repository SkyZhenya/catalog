<?php
namespace Application\Lib\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Db\Sql\Expression;

class ExistValidator extends AbstractValidator {

	/**
	 * @var \Application\Model\AppTable
	 */
	private $model;
	private $field;

	const NOTEXIST = 'aexist';

	protected $messageTemplates = array(
		self::NOTEXIST => 'aexist',
	);

	/**
	 * @param \Application\Model\AppTable $model
	 * @param string $field
	 * @param string $message
	 */
	public function __construct(\Application\Model\AppTable $model, $field, $message = 'Item is not found') {
		parent::__construct();
		$this->messageTemplates = array(
			self::NOTEXIST => $message,
		);
		$this->setMessage($message);
		$this->model = $model;
		$this->field = $field;
	}

	public function isValid($value) {
		if($this->model->select([$this->field => $value])->count()) {
			return true;
		}
		
		$this->error(self::NOTEXIST);
		return false;
	}
}
