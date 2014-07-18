<?php
namespace Application\Form;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

abstract class Form extends \Zend\Form\Form {
	
	protected $langs = [];
	
	abstract protected function getInpFilter();

    /**
     * Set data to validate and/or populate elements
     *
     * Typically, also passes data on to the composed input filter.
     *
     * @param  array|\ArrayAccess|Traversable $data
     * @return Form|FormInterface
     * @throws Exception\InvalidArgumentException
     */
    public function setData($data) {
		$this->setInputFilter($this->getInpFilter());
		parent::setData($data);
		return $this;
	}
	
	protected function setLangs() {
		$langTable = new \Application\Model\LangTable();
		$list = $langTable->find([]);
		foreach($list as $item) {
			$this->langs[] = $item['id'];
		}
	}
	
}
