<?php
namespace Admin\Form;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class TemplateForm extends \Application\Lib\Form\MultilanguageForm {
	/**
	 * @var Zend\InputFilter\InputFilter;
	 */
	protected $inputFilter;
	
	public function __construct() {
		parent::__construct('template');

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
				'label' => _('Title'),
			),
			'attributes' => array(
				'class' => 'input-big',
				'autofocus' => true,
				//'required' => 'required',
			),
		));
		
		$langsTable = new \Application\Model\LangTable();
		$langs = $langsTable->getList();
		$activeLang = \Zend\Registry::get('lang');

		foreach ($langs as $lang){
			
			$this->add(array(
				'name' => 'subject['.$lang->id.']',
				'options' => array(
					'label' => _('Subject'),
				),
				'attributes' => array(
					'class' => 'input-big locfields locfields'.$lang->id,
				),
			));
			
			$this->add(array(
				'name' => 'text['.$lang->id.']',
				'options' => array(
					'label' => _('Text'),
				),
				'attributes' => array(
					'type'  => 'textarea',
					'class' => 'input-big mceEditor input-template-editor locfields locfields'.$lang->id,
				),
			));
		}
		
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
	
	public function getFormInputFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory = new InputFactory();
			
			$inputFilter->add($factory->createInput(array(
				'name' => 'name',
				'required' => true,
				'filters' => array(
					array('name' => 'StringTrim'),
				),
			)));
			
			$langsTable = new \Application\Model\LangTable();
			$langs = $langsTable->getList();
			foreach ($langs as $lang){
				$inputFilter->add($factory->createInput(array(
					'name' => 'subject['.$lang->id.']',
					'required' => true,
					'filters' => array(
						array('name' => 'StringTrim'),
					),
				)));
			}
						
			$this->inputFilter = $inputFilter;
		}
		
		return $this->inputFilter;
	}
	
}
