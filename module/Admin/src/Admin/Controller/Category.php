<?php
namespace Admin\Controller;

use CodeIT\Controller\AbstractController;
use Application\Model\CategoryTable;
use Application\Model\AttributeTable;
use Application\Lib\AppController;
use Application\Lib\User as UserLib;
use Zend\View\Model\ViewModel;
use Zend\Filter\StripTags;

class Category extends AbstractController {

	var $form;
	var $categoryTable;
	var $attributeTable;
	var $filter;

	public function ready() {
		parent::ready();
		$this->categoryTable = new CategoryTable();
		$this->attributeTable = new AttributeTable();
		$this->filter = new StripTags();
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
			$data = $this->request->getPost()->toArray();
			$form->setData($data);
			if ($form->isValid()) {
				$data = $form->getData();
				$id = $this->categoryTable->insert($data);
				if(!empty($data['attributeName'])){
					$attribute['name'] = $data['attributeName'];
					$attribute['type'] = $data['attributeType'];
					$attribute['categoryId'] = $id;
					foreach ($attribute['name'] as $key => $item){
						htmlspecialchars($item);
						$this->filter->filter($item);
						$dataAtr=[];
						$dataAtr['type'] = $attribute['type'][$key];
						$dataAtr['name'] = $item;
						$dataAtr['categoryId'] = $id;
						$attr = $this->attributeTable->insert($dataAtr);
					}
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
					if(!empty($data['attributeName'])){
						$attribute['name'] = $data['attributeName'];
						$attribute['type'] = $data['attributeType'];
						foreach ($attribute['name'] as $key => $item){
							htmlspecialchars($item);
							$this->filter->filter($item);
							$dataAtr=[];
							$dataAtr['name'] = $item;
							$dataAtr['categoryId'] = $id;
							$dataAtr['type'] = $attribute['type'][$key];
							if($key > 0) {
								$this->attributeTable->setId($key);
								$this->attributeTable->set($dataAtr);
							} else {
								$this->attributeTable->insert($dataAtr);
							}
						}
					}
					$rez = $this->categoryTable->set($data);
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
		$params = $this->resolveParams();
		$orderby = $this->resolveOrderby();
		$total = 0;
		$list = $this->categoryTable->find($params, $count, $pos, $orderby, $total);
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

	protected function resolveParams() {
		$params = array();

		$flName = $this->params()->fromQuery('flName');
		if(trim($flName) !== '') {
			$params []= array('name', 'LIKE', "{$flName}%");
		}

		return $params;
	}

	/**
	 * return orderby rule for list ordering
	 *
	 * @return string
	 */
	protected function resolveOrderby() {
		$orderby='id';

		if(isset($_GET['orderby'])) {
			switch($_GET['order']) {
				case 'asc': $orderdir='asc'; break;
				default: $orderdir='desc';
			}
			switch($_GET['orderby']) {
				case 1: $orderby="id"; break;
				case 2: $orderby="name"; break;
				default: $orderby="id"; break;
			}
			$orderby.=' '.$orderdir;
		}

		return $orderby;
	}
}