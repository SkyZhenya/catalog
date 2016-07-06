<?php
namespace Admin\Form;

use Application\Lib\User;
use CodeIT\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class UserEditForm extends Form {
	/**
	 * @var Zend\InputFilter\InputFilter;
	 */
	protected $inputFilter;

	/**
	 * 
	 * @var string
	 */
	private $action;

	/**
	 * 
	 * @var int
	 */
	private $userId;

	const MAX_FILE_SIZE = 10485760; // 10 Mb

	/**
	 * constructor
	 * 
	 * @param string $level
	 * @return UserEditForm
	 */
	public function __construct($action = 'edit', $userId = null) {
		parent::__construct('useredit');

		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'formWrapp ajaxForm userEditForm');
		$this->setAttribute('enctype', 'multipart/form-data');
		$this->setAttribute('onsubmit', 'return avatarEditor.beforeSubmitAvatar();');
		$this->action = $action;
		$this->userId = $userId;

		$this->add(array(
			'name' => 'csrf',
			'type' => 'Zend\Form\Element\Csrf',
			'options' => array(
				'csrf_options' => array(
					'messages' => array(
						\Zend\Validator\Csrf::NOT_SAME => _('The form submitted did not originate from the expected site'),
					),
					'timeout' => null,
				),
			),
		));

		$this->add(array(
			'name' => 'id',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'updated',
			'attributes' => array(
				'type' => 'hidden',
			),
		));

		$this->add(array(
			'name' => 'name',
			'options' => array(
				'label' => _('User Name'),
			),
			'attributes' => array(
				'required' => 'required',
				'class' => 'input-big',
			),
		));

		$this->add(array(
			'name' => 'email',
			'options' => array(
				'label' => _('E-Mail'),
			),
			'attributes' => array(
				'required' => 'required',
				'class' => 'input-big',
			)
		));

		$this->add(array(
			'name' => 'phone',
			'options' => array(
				'label' => _('Phone'),
			),
			'attributes' => array(
				'class' => 'input-big',
			)
		));

		$this->add(array(
			'name' => 'level',
			'type' => '\Zend\Form\Element\Select',
			'options' => array(
				'label' => _('Access Level'),
			),
			'attributes' => array(
				'required' => 'required',
				'class' => 'chosen-select level-select',
				'options' => array(
					'user' => _('User'),
					'admin' => _('Admin'),
				),
			)
		));

		$this->add(array(
			'name' => 'pass',
			'type' => 'password',
			'options' => array(
				'label' => _('Password'),
			),
			'attributes' => array(
				'class' => 'input-big',
			),
		));


		$this->add(array(
			'name' => 'active',
			'type' => '\Zend\Form\Element\Checkbox',
			'options' => array(
				'label' => _('Account is active'),
			),
			'attributes' => array(
				'value' => 1,
				'class' => 'styled changeabledata_check',
				'data-active' => _('Account is Active'),
				'data-inactive' => _('Account is Inactive'),
			)
		));


		$this->add(array(
			'name' => 'avatar',
			'type' => 'file',
			'attributes' => array(
				'onchange' => 'avatarEditor.showFile(this);',
				'accept' => "image/*",
			),
		))

		->add(array(
			'name' => 'removeAvatar',
			'type' => 'hidden',
			'attributes' => array(
				'value' => '0',
			),
		));

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => _('Save'),
			))
		);

		$this->add(array(
			'name' => 'cancel',
			'type' => '\Zend\Form\Element\Button',
			'options' => array(
				'label' => _('Cancel'),
			),
			'attributes' => array(
				'value' => _('Cancel'),
				'class' => 'clear-btn popup_cancel',
				'onclick' => "common.cancelChanges(".'"'.htmlspecialchars(addslashes(URL.'admin/user')).'")',
			))
		);
	}


	public function getInpFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();

			$factory = new InputFactory();

			$notemptyValidator = array(
				'name' => 'notEmpty',
				'options' => array (
					'messages' => array(
						\Zend\Validator\NotEmpty::IS_EMPTY => _("This field is required"),
					),
				),
				'break_chain_on_failure' => true,
			);

			$inputFilter->add($factory->createInput(array(
				'name' => 'email',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					$notemptyValidator,
					new \CodeIT\Validator\EmailSimpleValidator(),
					new \CodeIT\Validator\NotExistValidator(new \Application\Model\UserTable(), 'email', $this->userId, 'id', "E-mail already exists"),
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'name',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					$notemptyValidator,
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'pass',
				'required' => ($this->action == 'edit')? false : true,
				'filters' => array(
					array('name' => 'StringTrim'),
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'level',
				'required' => true,
				'validators' => array(
					$notemptyValidator,
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'avatar',
				'required' => false,
				'validators' => array(
					array(
						'name' => '\Zend\Validator\File\Extension',
						'options' => array (
							'extension' => array('png', 'jpg', 'jpeg'),
							'messages' => array(
								\Zend\Validator\File\Extension::FALSE_EXTENSION => "You can upload jpg, jpeg, png files only",
							),
						),
						'break_chain_on_failure' => true,
					),
					new \Zend\Validator\File\Size(array(
						'max' => self::MAX_FILE_SIZE,
						'messages' => array(
							\Zend\Validator\File\Size::TOO_BIG => _('Your image should be less than 10 Mb'),
						)
					)),
				),
			)));

			$inputFilter->add([
				'name' => 'updated',
				'allow_empty' => ($this->action == 'edit')? false : true,
				'validators' => [
					['name' => 'Application\Lib\Validator\NotModified', 'options' => [
						'comparableTimestamp' => $this->getUpdated(),
						'messages' => [\Application\Lib\Validator\NotModified::IS_MODIFIED => _('The data was changed, please start edit once again')],
						],],
				],
			]);

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}
}
