<?php
namespace Application\Lib\Validator;

use Zend\Validator\AbstractValidator;

class NotExistValidator extends AbstractValidator{

	private $model;
	private $field;
	private $id;
	private $idFiledName;

	const EXIST = 'aexist';

	protected $messageTemplates = array(
		self::EXIST => 'aexist',
	);

	public function __construct($model, $field, $id = false, $idFiledName = false, $message = "Item with the same value already exists") {
		parent::__construct();
		$this->messageTemplates = array(
			self::EXIST => $message,
		);
		$this->setMessage($message);
		$this->model = $model;
		$this->id = $id;
		$this->field = $field;
		$this->idFiledName = $idFiledName ? $idFiledName : 'id';
	}

	public function isValid($value) {
		$where = array(array($this->field, '=', $value));
		if($this->id) {
			$where[] = array($this->idFiledName, '!=', $this->id);
		}

		if($this->model->find($where)){
			$this->error(self::EXIST);
			return false;
		}
		return true;
	}
}
