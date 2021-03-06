<?php
namespace Auth\Form;

use CodeIT\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class RegistrationForm extends Form {

	/**
	 * creates the form
	 * 
	 * @return RegistrationForm
	 */
	public function __construct() {
		parent::__construct();
		
		$this
			->setAttribute('method', 'post')
			->setAttribute('class', 'auth-form')
			->setAttribute('action', URL.'auth/registration')
			
			->add(array(
				'name' => 'name',
				'options' => array(
					'label' => _('Name'),
				),
				'attributes' => array(
					'maxlength' => 35,
					'class' => 'form-control',
				),
			))
			
			->add(array(
				'name' => 'email',
				'options' => array(
					'label' => _('Email'),
				),
				'attributes' => array(
					'class' => 'form-control',
				),
			))

			->add(array(
				'name' => 'phone',
				'options' => array(
					'label' => _('Phone'),
				),
				'attributes' => array(
					'class' => 'form-control',
				),
			))

			->add(array(
				'name' => 'password',
				'type' => 'password',
				'options' => array(
					'label' => _('Password'),
				),
				'attributes' => array(
					'maxlength' => 20,
					'class' => 'form-control',
				),
			))
			
			->add(array(
				'name' => 'confirmpassword',
				'type' => 'password',
				'options' => [
					'label' => _('Confirm Password'),
				],
				'attributes' => array(
					'maxlength' => 20,
					'class' => 'form-control',
				),
			));
			
			$this->add(array(
				'name' => 'birthmonth',
				'type' => 'select',
				'attributes' => array(
					'class' => 'form-control',
				),
				'options' => [
					'label' => _('Birth Month'),
					'options' => array(
						'01' => _('January'),
						'02' => _('February'),
						'03' => _('March'),
						'04' => _('April'),
						'05' => _('May'),
						'06' => _('June'),
						'07' => _('July'),
						'08' => _('August'),
						'09' => _('September'),
						'10' => _('October'),
						'11' => _('November'),
						'12' => _('December'),
					),
				],
			));
			
			$daysList = array();
			for ($day = 1; $day <= 31; $day ++) 
				$daysList[$day] = $day;
			$this->add(array(
				'name' => 'birthday',
				'type' => 'select',
				'attributes' => array(
					'class' => 'form-control',
				),
				'options' => [
					'label' => _('Birth Day'),
					'options' => $daysList,
				],
			));
			
			$yearsList = array();
			$currentYear = (int)date('Y') ;
			for ($year = ($currentYear-20); $year >= ($currentYear - 100); $year --) 
				$yearsList[$year] = $year;
			$this->add(array(
				'name' => 'birthyear',
				'type' => 'select',
				'attributes' => array(
					'class' => 'form-control',
				),
				'options' => [
					'label' => _('Birth Year'),
					'options' => $yearsList,
				],
			))
			
			->add(array(
				'name' => 'submit',
				'attributes' => array(
					'type' => 'submit',
					'value' => _('Sign Up'),
					'class' => 'btn btn-primary',
				))
			)
		;
		
	}

	public function getInpFilter() {
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
		$passLength = array(
			'name' => '\Zend\Validator\StringLength',
			'options' => array (
				'min' => 8,
				'messages' => array(
					\Zend\Validator\StringLength::TOO_SHORT => _("The input is less than 8 characters long"),
				),
			),
		);
				
		$inputFilter
			->add($factory->createInput(array(
				'name' => 'name',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					$notemptyValidator,
					new \Zend\Validator\StringLength(array(
						'min' => 0,
						'max' => 35,
						'message' => _('Name can not be longer than 35 characters'),
					)),
				),
			)))
		
			->add($factory->createInput(array(
				'name' => 'email',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
					array('name' => 'StringToLower'),
				),
				'validators' => array(
					$notemptyValidator,
					new \CodeIT\Validator\EmailSimpleValidator(),
					new \CodeIT\Validator\NotExistValidator(
					new \Application\Model\UserTable(), 
						'email', 
						false,
						false, 
						_("Profile with such email already exists")
					)
				)
			)))

			->add($factory->createInput(array(
				'name' => 'phone',
				'required' => true,
				'validators' => array(
					$notemptyValidator
				)
			)))
			
			->add($factory->createInput(array(
				'name' => 'password',
				'required' => true,
				'filters' => array(
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					$notemptyValidator,
					$passLength,
				),
			)))
			
			->add($factory->createInput(array(
				'name' => 'birthmonth',
				'required' => true,
				'validators' => array(
					new \Application\Lib\Validator\MultiFieldDate([
						'message' => array(_('Birthdate is invalid')),
						'yearFieldName' => 'birthyear',
						'monthFieldName' => 'birthmonth',
						'dayFieldName' => 'birthday'
					]),
				),
			)))
			
			->add($factory->createInput(array(
					'name' => 'confirmpassword',
					'required' => true,
					'filters' => array(
						array('name' => 'StringTrim'),
					),
					'validators' => array(
						$notemptyValidator,
						array(
							'name' => 'Identical',
							'options' => array(
								'token' => 'password',
								'messages' => array(
									\Zend\Validator\Identical::NOT_SAME      => _('Two given password values do not match'),
									\Zend\Validator\Identical::MISSING_TOKEN => _('Two given password values do not match'),
								)
							),
							'break_chain_on_failure' => true,
						),
						$passLength,
					),
				)));

		return $inputFilter;
	}
}
