<?php
namespace Admin\Controller;

use CodeIT\Controller\AbstractController;
use Application\Model\CategoryTable;
use Application\Model\AttributeTable;
use Application\Lib\AppController;
use Application\Lib\User as UserLib;
use Zend\View\Model\ViewModel;

class Category extends AbstractController {

	var $form;
	var $categoryTable;
	var $attributeTable;

	public function ready() {
		parent::ready();
		$this->categoryTable = new CategoryTable();
		$this->attributeTable = new AttributeTable();
	}

	public function indexAction() {
		$this->layout()->bodyClass = 'category';
		$total = 0;
		$list = $this->categoryTable->find([], 1, 0, null, $total);

		$result = array(
			'canAdd' => $this->user->isAllowed('Admin\Controller\Category', 'add'),
			'canDelete' => $this->user->isAllowed('Admin\Controller\Category', 'save'),
			'total' => $total,
		);
		$this->renderHtmlIntoLayout('submenu', 'admin/category/submenu.phtml', $result);
		return $result;
	}

	public function getForm($action = 'edit', $id = null) {
		if(is_null($this->form)) {
			$this->form = new \Admin\Form\CategoryEditForm($action, $id);
		}
		return $this->form;
	}

	public function addAction() {
		$this->setBreadcrumbs(['category' => 'Category',], true);
		$form = $this->getForm('add');

		if($this->request->isPost()) {
			$data = array_merge_recursive($this->request->getPost()->toArray(), $this->request->getFiles()->toArray());
			$form->setData($data);
			if ($form->isValid()) {
				$data = $form->getData();
				$attribute['name'] = $data['attributeName'];
				$id = $this->categoryTable->insert($data);
				$attribute['categoryId'] = $id;
				foreach ($attribute['name'] as $item){
					$dataAtr=[];
					$dataAtr['name'] = $item;
					$dataAtr['categoryId'] = $id;
					$attr = $this->attributeTable->insert($dataAtr);
				}
			}
			$this->redirect()->toUrl(URL.'admin/category/');
		}

		$result =  new ViewModel(array(
			'form' => $form,
			'title' => _('Add New Category'),
		));
		$result->setTemplate('admin/category/edit.phtml');
		return $result;
	}

	public function editAction() {
		$id = $this->params('id',0);

		$this->setBreadcrumbs(['category' => 'Category',], true);
		$form = $this->getForm('edit', $id);
		$canEdit = $this->user->isAllowed('Admin\Controller\Category', 'save');
		$attribute = $this->getAttribute($id);
		//var_dump($attribute); die;
		try {
			$category = $this->categoryTable->setId($id);
			$form->setData($category);
		}
		catch(\Exception $e) {
			return $this->notFoundAction();
		}

		if($this->request->isPost()){
			if($canEdit) {
				$data = $this->request->getPost()->toArray();
				$form->setData($data);
				if ($form->isValid()) {
					$data = $form->getData();
					//var_dump($data); die;
					$attribute['name'] = $data['attributeName'];
					$rez = $this->categoryTable->set($data);
					foreach ($attribute['name'] as $key => $item){
						$dataAtr=[];
						$dataAtr['name'] = $item;
						$dataAtr['categoryId'] = $id;
						//var_dump($key);
						if($key > 0) {
							$this->attributeTable->setId($key);
							$this->attributeTable->set($dataAtr);
						} else {
							$this->attributeTable->insert($dataAtr);
						}
					}
					$this->redirect()->toUrl(URL.'admin/category/');
				}
				
			}
			else {
				$this->error = _('You do not have enough permissions to make changes');
			}
		}
		return  new ViewModel(array(
			'attribute' => $attribute,
			'form' => $form,
			'error' => $this->error,
			'item' => $category,
		));

	}

	public function deleteAction() {
		$id = (int)$this->request->getPost('id', 0);
		$this->categoryTable->delete($id);
		return $this->getResponse()->setContent('OK');
	}

	public function deleteAttributeAction() {
		$id = $this->request->getPost('id', 0);
		$this->attributeTable->delete($id);
		return $this->getResponse()->setContent('OK');
	}

	public function listAction() {
		$count = (int)$this->params()->fromQuery('count', 50);
		$pos = (int)$this->params()->fromQuery('posStart', 0);
		$total = 0;
		$list = $this->categoryTable->find([], $count, $pos, false, $total);
		$result = new ViewModel(array(
			'pos' => $pos,
			'list' => $list,
			'total' => $total,
			'isAllowedDelete' => $this->user->isAllowed('Admin\Controller\Category', 'delete'),
		));
		$result->setTerminal(true);
		return $result;
	}

	public function getAttribute($id) {
		$params = ["categoryId=$id"];
		$attribute = $this->attributeTable->find($params);
		return $attribute;
	}
}