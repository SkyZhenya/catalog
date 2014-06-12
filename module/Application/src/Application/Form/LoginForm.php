<?php
namespace Application\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Application\Form\CustomDecorator;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class LoginForm extends Form {

	protected $loginInputFilter;
	
  public function __construct(){
    parent::__construct();

    $this->setName('login')
			->setAttribute('method', 'post')
		    
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
								'class' => 'input-big',
								'required' => 'required',
			      ),
			  ))
			  
			->add(array(
			      'name' => 'password',
			      'options' => array(
			        'label' => _('Password'),
			      ),
			      'attributes' => array(
			          'type'  => 'password',
								'class' => 'input-big',
								'required' => 'required',
			      ),
			  ))
				
			  ->add(array(
			      'name' => 'submit',
			      'attributes' => array(
			          'type'  => 'submit',
			          'value' => _('Log In'),
			      ),
			  ));

    }
    
    public function getLoginInputFilter() {
			if (!$this->loginInputFilter) {
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
						new \Zend\Validator\NotEmpty(array(
							'messages' => array(
								\Zend\Validator\NotEmpty::IS_EMPTY => _("Value is required and can't be empty"),
							),
						)),
						array (
							'name' => 'Application\Lib\Validator\CustomEmailValidator',
						),
					),
				)));

				$this->loginInputFilter = $inputFilter;
			}

			return $this->loginInputFilter;
		}

}