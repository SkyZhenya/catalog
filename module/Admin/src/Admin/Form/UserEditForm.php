<?php
namespace Admin\Form;
use Application\Lib\User;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class UserEditForm extends \Zend\Form\Form {
	/**
	 * @var Zend\InputFilter\InputFilter;
	 */
	protected $inputFilter;
	
	/**
	* constructor
	* 
	* @param string $level
	* @return UserEditForm
	*/
	public function __construct() {
		parent::__construct('useredit');

		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'line-form');

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
			'name' => 'clear2',
			'type' => '\Application\Lib\Form\HTML',
			'attributes' => array(
				'value' => '<div class="clear"></div>',
			),
		));
		
		$this->add(array(
			'name' => 'password',
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
			'name' => 'clear',
			'type' => '\Application\Lib\Form\HTML',
			'attributes' => array(
				'value' => '',
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
				'onclick' => 'close_fancybox();',
				'class' => 'clear-btn',
			))
		);
	}
	
  	
	public function getFormInputFilter($action = 'edit') {
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
					new \Application\Lib\Validator\ExistValidator(new \Application\Model\UserTable(), 'email', $this->get('id')->getValue(), 'id', "E-mail already exists"),
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
				'name' => 'password',
				'required' => ($action == 'edit')? false : true,
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
}
