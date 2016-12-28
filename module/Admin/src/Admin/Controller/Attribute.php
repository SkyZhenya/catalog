<?php

namespace Admin\Controller;

use CodeIT\Controller\AbstractController;
use Application\Model\AttributeTable;
use Application\Model\CategoryTable;

class Attribute extends AbstractController {

	var $form;
	var $attributeTable;
	var $categoryTable;

	public function ready() {
		parent::ready();
		$this->attributeTable = new AttributeTable();
		$this->categoryTable = new CategoryTable();
	}

	public function indexAction() {
		$this->layout()->bodyClass = 'attribute';
		$total = 0;
		$list = $this->categoryTable->find([], 1, 0, null, $total);

		$result = array(
			'canAdd' => $this->user->isAllowed('Admin\Controller\Attribute', 'add'),
			'canDelete' => $this->user->isAllowed('Admin\Controller\Attribute', 'save'),
			'total' => $total,
		);
		$this->renderHtmlIntoLayout('submenu', 'admin/attribute/submenu.phtml', $result);
		return $result;
	}

	
}