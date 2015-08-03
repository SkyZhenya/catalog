<?php
namespace Auth\Form;

use Application\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class LoginForm extends Form {

	protected $loginInputFilter;

	public function __construct(){
		parent::__construct();

		$this->setName('login')
		->setAttribute('method', 'post')
		->setAttribute('class', 'auth-form')
		->setAttribute('action', URL.'auth/login')

		->add(array(
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
		))

		->add(array(
			'name' => 'email',
			'options' => array(
				'label' => _('Email'),
			),
			'attributes' => array(
				'type'  => 'text',
				'class' => 'form-control',
			),
		))

		->add(array(
			'name' => 'password',
			'options' => array(
				'label' => _('Password'),
			),
			'attributes' => array(
				'type'  => 'password',
				'class' => 'form-control',
			),
		))

		->add(array(
			'name' => 'rememberme',
			'type' => 'checkbox',
			'options' => array(
				'label' => _('Remember me'),
				'use_hidden_element' => true,
				'checked_value' => 1,
				'unchecked_value' => 0,
			),
		))
		
		->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type'  => 'submit',
				'value' => _('Log In'),
				'class' => 'btn btn-primary',
			),
		));

	}

	protected function getInpFilter() {
		if (!$this->loginInputFilter) {
			$inputFilter = new InputFilter();
			$factory = new InputFactory();
			
			$notemptyValidator = array(
				'name' => 'not_empty',
				'options' => array (
					'messages' => array(
						\Zend\Validator\NotEmpty::IS_EMPTY => _("This field is required"),
					),
				),
				'break_chain_on_failure' => true,
			);
			
			$inputFilter
			->add($factory->createInput(array(
				'name' => 'email',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					$notemptyValidator,
					array (
						'name' => 'Application\Lib\Validator\CustomEmailValidator',
					),
				),
			)))
			->add($factory->createInput(array(
				'name' => 'password',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					$notemptyValidator,
				),
			)));;

			$this->loginInputFilter = $inputFilter;
		}

		return $this->loginInputFilter;
	}

}