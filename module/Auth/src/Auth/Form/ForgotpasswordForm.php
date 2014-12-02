<?php
namespace Auth\Form;

use \Application\Form\Form;
use Zend\Form\Element;
use Application\Form\CustomDecorator;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class ForgotpasswordForm extends Form {

	protected $inputFilter;

	public function __construct(){
		parent::__construct();

		$this->setName('forgotpassword')
		->setAttribute('method', 'post')
		->setAttribute('class', 'form-horizontal auth-form')

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
				'label' => _('Your Email'),
			),
			'attributes' => array(
				'type'  => 'text',
				'class' => 'form-control',
			),
		))

		->add(array(
			'name' => 'newpassword',
			'options' => array(
				'label' => _('New Password'),
			),
			'attributes' => array(
				'type'  => 'password',
				'class' => 'form-control',
				'maxlength' => 20,
			),
		))

		->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type'  => 'submit',
				'value' => _('Reset'),
				'class' => 'btn btn-primary',
			),
		));

	}

	public function getInpFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory = new InputFactory();
			
			$notemptyValidator = array(
				'name' => 'not_empty',
				'options' => array (
					'messages' => array(
						\Zend\Validator\NotEmpty::IS_EMPTY => "This field is required",
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
					array (
						'name' => 'Application\Lib\Validator\CustomEmailValidator',
					),
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'newpassword',
				'required' => true,
				'filters' => array(
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					$notemptyValidator,
					array(
						'name' => '\Zend\Validator\StringLength',
						'options' => array (
							'min' => 8,
							'max' => 20,
							'messages' => array(
								\Zend\Validator\StringLength::TOO_SHORT => "The input is less than 8 characters long",
								\Zend\Validator\StringLength::TOO_LONG => "The input is longer than 20 characters",
							),
						),
					),
				),
			)));

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}

}