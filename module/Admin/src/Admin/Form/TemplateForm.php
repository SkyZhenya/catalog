<?php
namespace Admin\Form;

use CodeIT\Form\MultilanguageForm;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class TemplateForm extends MultilanguageForm {
	/**
	 * @var Zend\InputFilter\InputFilter;
	 */
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
				'onclick' => "common.cancelChanges(".'"'.htmlspecialchars(addslashes(URL.'admin/template')).'")',
				'class' => 'clear-btn',
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
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					$notemptyValidator,
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
					'validators' => array(
						$notemptyValidator,
					),
				)));

				$inputFilter->add($factory->createInput(array(
					'name' => 'text['.$lang->id.']',
					'required' => true,
					'filters' => array(
						array('name' => 'StringTrim'),
					),
					'validators' => array(
						$notemptyValidator,
					),
				)));
			}

			$inputFilter->add([
				'name' => 'updated',
				'allow_empty' => false,
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
