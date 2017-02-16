<?php

namespace Admin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use CodeIT\Form\Form;

class ProductEditForm extends Form {

	const MAX_FILE_SIZE = 10485760; // 10 Mb

	protected $inputFilter;

	public function __construct($action = 'edit', $category = null) {
		parent::__construct('product');

		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'formWrapp ajaxForm userEditForm');
		$this->setAttribute('style', 'height: 2000px');
		$this->setAttribute('enctype', 'multipart/form-data');
		$this->setAttribute('onsubmit', 'return avatarEditor.beforeSubmitAvatar();');

	

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
				'label' => _('Product Name'),
			),
			'attributes' => array(
				'required' => 'required',
				'class' => 'input-big',
				'id' => 'name',
			),
		));

		$this->add(array(
			'name' => 'price',
			'options' => array(
				'label' => _('Product Price'),
			),
			'attributes' => array(
				'required' => 'required',
				'class' => 'input-big',
				'id' => 'price',
			),
		));

		$this->add(array(
			'name' => 'manufacturer',
			'options' => array(
				'label' => _('Product manufacturer'),
			),
			'attributes' => array(
				'required' => 'required',
				'class' => 'input-big',
				'id' => 'manufacturer',
			),
		));

		$this->add(array(
			'name' => 'description',
			'type' => '\Zend\Form\Element\Textarea',
			'options' => array(
				'label' => _('Description'),
			),
			'attributes' => array(
				'required' => 'required',
				'class' => 'input-big',
				'id' => 'description',
			),
		));

		$this->add(array(
			'name' => 'categoryId',
			'type' => '\Zend\Form\Element\Select',
			'options' => array(
				'empty_option' => 'Choose category',
				'label' => _('Category'),
			),
			'attributes' => array(
				'required' => 'required',
				'id' => 'categoryId',
				'class' => 'input-big',
				'options' => $category,
				'onchange' => 'attributeFields()',
			),
		));

		$this->add(array(
			'name' => 'attributeValue',
			'options' => array(
				'label' => _('Property for category'),
			),
			'attributes' => array(
				'class' => 'input-big',
				'id' => 'attrValue',
			),
		));

		$this->add(array(
			'name' => 'attributeValueNew',
			'options' => array(
				'label' => _('Property for category'),
			),
			'attributes' => array(
				'class' => 'input-big',
				'id' => 'attrValue',
			),
		));

		$this->add(array(
			'name' => 'avatar',
			'type' => 'file',
			'attributes' => array(
				'onchange' => 'avatarEditor.showFile(this);',
				'accept' => "image/*",
				'id' => 'avatar',
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
			'name' => 'photo',
			'type' => 'file',
			'attributes' => array(
				'onchange' => 'photoEditor.showFile(this);',
				'accept' => "image/*",


			),
		))

			->add(array(
				'name' => 'removePhoto',
				'type' => 'hidden',
				'attributes' => array(
					'value' => '0',
				),
		));

		$this->add(array(
			'name' => 'addPhoto',
			'type' => '\Zend\Form\Element\Button',
			'options' => array (
				'label' => _('+'),
			),
			'attributes' => array(
				'value' => _('+'),
				'id' => 'addPhoto',
			),
		));

		$this->add(array(
			'name' => 'flag',
			'type' => '\Zend\Form\Element\Select',
			'options' => array(
				'label' => _('State'),
			),
			'attributes' => array(
				'class' => 'chosen-select level-select',
				'options' => array(
					"in edit" => _('In edit'),
					"finished" => _('Finished'),
				),
			),

		));

		$this->add(array(
			'name' => 'showProduct',
			'type' => '\Zend\Form\Element\Button',
			'options' => array (
				'label' => _('Show product on page'),
			),
			'attributes' => array(
				'id' => 'showProduct',
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
				'onclick' => "common.cancelChanges(".'"'.htmlspecialchars(addslashes(URL.'admin/product')).'")',
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

			$inputFilter->add($factory->createInput(array(
				'name' => 'price',
				'required' => true,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					$notemptyValidator,
					new \Zend\Validator\Digits(),
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'description',
				'required' => false,
				'filters' => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			)));

			$inputFilter->add($factory->createInput(array(
				'name' => 'categoryId',
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

			$this->inputFilter = $inputFilter;
		}
		return $this->inputFilter;
	}

}