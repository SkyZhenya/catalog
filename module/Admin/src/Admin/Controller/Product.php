<?php
namespace Admin\Controller;

use CodeIT\Controller\AbstractController;
use Application\Model\ProductTable;
use Application\Model\CategoryTable;
use Application\Model\AttributeTable;
use Application\Model\AttributeProductTable;
use Zend\View\Model\ViewModel;


class Product extends AbstractController {

	var $form;
	var $productTable;
	var $categoryTable;
	var $attributeTable;
	var $attributeProductTable;
	var $img;


	public function ready() {
		parent::ready();
		$this->productTable = new ProductTable();
		$this->categoryTable = new CategoryTable();
		$this->attributeTable = new AttributeTable();
		$this->attributeProductTable = new AttributeProductTable();
		
	}

	public function indexAction() {
		$this->layout()->bodyClass = 'product';
		$total = 0;
		$list = $this->productTable->find([], 1, 0, null, $total);

		$result = array(
			'canAdd' => $this->user->isAllowed('Admin\Controller\Product', 'add'),
			'canDelete' => $this->user->isAllowed('Admin\Controller\Product', 'save'),
			'total' => $total,
		);
		$this->renderHtmlIntoLayout('submenu', 'admin/product/submenu.phtml', $result);
		return $result;
	}

	public function getForm($action = 'edit', $id = null) {
		if(is_null($this->form)) {
			$this->form = new \Admin\Form\ProductEditForm($action, $id);
		}
		return $this->form;
	}

	public function addAction() {
		$this->setBreadcrumbs(['product' => 'Product',], true);
		$category = $this->getCategory();
		$form = $this->getForm('add', $category);

		if ($this->request->isPost()) {
			$data = array_merge_recursive($this->request->getPost()->toArray(), $this->request->getFiles()->toArray());
			$form->setData($data);
			if($form->isValid()){
				$data = $form->getData();
				
				$avatar = array_shift($_FILES);
				$photo = $_FILES;
				unset($photo['photo']);
				$attribute['value'] = $data['attributeValueNew'];
				$id = $this->productTable->insert($data);
				foreach ($photo as  $value) {
					$this->productTable->setImage($value['tmp_name'], $id, $value['name']);
				}
				if (!empty($data['avatar']['tmp_name'])) {
					$this->productTable->setAvatar($data['avatar']['tmp_name'], $id);
				}
				foreach($attribute['value'] as $key=>$item) {
					if(!empty($item)){
						$dataValue = [];
						$dataValue['value'] = $item;
						$dataValue['productId'] = $id;
						$dataValue['attributeId'] = $key;
						$attr = $this->attributeProductTable->insert($dataValue);
					}
				}
				$this->redirect()->toUrl(URL.'admin/product/');
			}
		}

		$form->get('avatar')->setValue(null);
		$result =  new ViewModel(array(
			'form' => $form,
			'title' => _('Add New Product'),
		));
		$result->setTemplate('admin/product/edit.phtml');
		return $result;
	}

	public function editAction() {
		$id = $this->params('id', 0);
//		var_dump($id);
		$this->setBreadcrumbs(['product' => 'Product',], true);
		$category = $this->getCategoryProduct($id);
		$form = $this->getForm('edit', $category);
		$value = $this->getAttributeValue($id);
				
		try {
			$product = $this->productTable->setId($id);
			$image = $this->productTable->getImage($product->id);
			$product['img'] = $image;
			$form->setData($product);

		}
		catch(\Exception $e) {
			return $this->notFoundAction();
		}
		
		if($this->request->isPost()) {
			$data = array_merge_recursive($this->request->getPost()->toArray(), $this->request->getFiles()->toArray());
			$form->setData($data);
			if($form->isValid()) {
				$data = $form->getData();
				//var_dump($data); die;
				$avatar = array_shift($_FILES);
				$photo = $_FILES;
				unset($photo['photo']);
				foreach ($photo as  $value) {
					$this->productTable->setImage($value['tmp_name'], $id, $value['name']);
				}
				
				$data['removeAvatar'] = (int)$data['removeAvatar'];
				if (!empty($data['removeAvatar'])) {
					$this->productTable->removeImages($id);
				}
				$rez = $this->productTable->set($data);
				
				if (!empty($data['avatar']['tmp_name'])) {
						$this->productTable->setAvatar($data['avatar']['tmp_name'], $id);
					}
				if(!empty($data['attributeValue'])){
					$attribute['value'] = $data['attributeValue'];
					foreach($attribute['value'] as $key=>$item){
						if(!empty($item)){
							$dataValue = [];
							$dataValue['value'] = $item;
							$this->attributeProductTable->setId($key);
							$this->attributeProductTable->set($dataValue);
						}
						
					}
				}

				if(!empty($data['attributeValueNew'])){
					$attribute['valueNew'] = $data['attributeValueNew'];
					//var_dump($attribute['valueNew']); die;
					foreach($attribute['valueNew'] as $key=>$item) {
						$dataValueNew = [];
						$dataValueNew['value'] = $item;
						$dataValueNew['productId'] = $id;
						$dataValueNew['attributeId'] = $key;
						//var_dump($dataValueNew);
						$this->attributeProductTable->insert($dataValueNew);
					}
				//die;
				}
				
			}
			$this->redirect()->toUrl(URL.'admin/product/');
		}
		$form->get('avatar')->setValue(null);
		
		$result =  new ViewModel(array(
			'value' => $value,
			'form' => $form,
			'title' => _('Add New Product'),
			'item' => $product,
			'category' => $category,
		));
		$result->setTemplate('admin/product/edit.phtml');
		return $result;
	}

	public function listAction() {
		$count = (int)$this->params()->fromQuery('count', 50);
		$pos = (int)$this->params()->fromQuery('posStart', 0);
		$params = $this->resolveParams();
		$orderby = $this->resolveOrderby();
		$total = 0;
		$list = $this->productTable->find($params, $count, $pos, $orderby, $total);
		$result = new ViewModel(array(
			'pos' => $pos,
			'list' => $list,
			'total' => $total,
			'isAllowedDelete' => $this->user->isAllowed('Admin\Controller\Category', 'delete'),
		));
		$result->setTerminal(true);
		return $result;
	}

	public function deleteAction() {
		$id = $this->request->getPost('id', 0);
		$this->productTable->delete($id);
		return $this->getResponse()->setContent('OK');
	}

	public function deleteValueAction() {
		$id = $this->request->getPost('id', 0);
		$this->attributeProductTable->delete($id);
		return $this->getResponse()->setContent('OK');
	}

	public function deletePhotoAction() {
		$data = $this->request->getPost();
		$id = $data['id'];
		$name = basename($data['urlPhoto']);
		$name = array_shift(explode('?', $name));

		$this->productTable->removePhoto($id, $name);
	
		return $this->getResponse()->setContent('OK');
	}

	public function getCategory() {
		$category = $this->categoryTable->find([]);
		$categories = [];
		foreach ($category as $item) {
			$categories[$item->id] = $item->name;
		}
		return $categories;
	}

	public function getCategoryProduct($productId) {
		$this->categoryTable->setFindJoin("INNER JOIN product as p ON category.id=p.categoryId WHERE p.id=$productId");
		$category = $this->categoryTable->find([]);
		$categories = [];
		foreach ($category as $item) {
			$categories[$item->id] = $item->name;
		}
		return $categories;
	}

	public function getAttributeAction() {
		$response = $this->getResponse();
		$id = $this->request->getPost()->toArray();
		$id = array_shift($id);
		$attr = $this->attributeTable->find(["categoryId=$id"]);
		
		header('Content-Type: application/json');
		echo json_encode($attr);
		$response->setStatusCode(200);
		return $response;
	}

	public function getAttributeValue($id) {
		$params = ["id=$id"];
		$total = 0;
		$category = $this->productTable->findSimple($params, 0, 0, false, $total, ['categoryId'])->toArray();
		$categoryId = array_shift($category);
		$categoryId = array_shift($categoryId);
		$orderBy = 'name';
		$this->attributeTable->setFindJoin("LEFT JOIN attributeProduct as ap ON attribute.id=ap.attributeId AND ap.productId=$id");
		$value = $this->attributeTable->findSimple(["categoryId=$categoryId"], 0, 0, $orderBy, $total, ['attributeValue' => ['ap', 'value'],'valueId'=>['ap', 'id'], '*'])->toArray();
		return $value;
	}

	protected function resolveParams() {
		$params = array();
		$flName = $this->params()->fromQuery('flName');
		if(trim($flName) !== '') {
			$params []= array('name', 'LIKE', "{$flName}%");
		}
		return $params;
	}

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