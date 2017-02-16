<?php

namespace Admin\Form;

use CodeIT\Form\MultilanguageForm;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use CodeIT\Form\Form;

class CategoryEditForm extends Form {

	protected $inputFilter;

	public function __construct() {
		parent::__construct('template');

		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'formWrapp');
		

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
			'name' => 'name',
			'options' => array(
				'label' => _('Category Name'),
			),
			'attributes' => array(
				'required' => 'required',
				'class' => 'input-big',
			),
		));

		$this->add(array(
			'name' => 'addAtributes',
			'type' => '\Zend\Form\Element\Button',
			'options' => array (				
				'label' => _('Add property'),
			),
			'attributes' => array(
				'value' => _('AddAtributes'),
				'id' => 'addAtributes',
			),
		));

		$this->add(array(
			'name' => 'attributeName',
			'options' => array(
				'label' => _('Property for category'),
			),
			'attributes' => array(
				'class' => 'input-big',
				'id' => 'attrName',
			),
		));

		$this->add(array(
			'name' => 'attributeType',
			'options' => array(
				'label' => _('data type'),
			),
			'attributes' => array(
				'required' => 'required',
				'class' => 'chosen-select type-select',
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
				'onclick' => "common.cancelChanges(".'"'.htmlspecialchars(addslashes(URL.'admin/category')).'")',
			))
		);
	}


	protected  function getInpFilter() {
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

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}

}