<?php
namespace Admin\Form;
use Application\Lib\User;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class UserEditForm extends \Application\Form\Form {
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
	
	/**
	* constructor
	* 
	* @param string $level
	* @return UserEditForm
	*/
	public function __construct($action = 'edit') {
		parent::__construct('useredit');

		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'formWrapp ajaxForm userEditForm');
		$this->action = $action;

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
			)
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
			))
		);
	}
	
  	
	public function getInpFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();

			$factory = new InputFactory();


			$inputFilter->add($factory->createInput(array(
				'name' => 'email',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					new \Application\Lib\Validator\CustomEmailValidator(),
					new \Application\Lib\Validator\NotExistValidator(new \Application\Model\UserTable(), 'email', $this->userId, 'id', "E-mail already exists"),
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'name',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
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
			)));
					
			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}
	
	public function setUserId($userId) {
		$this->userId = $userId;
	}
}
