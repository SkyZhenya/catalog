<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use CodeIT\Controller\AbstractController;
use Application\Model\ProductTable;
use Application\Model\AttributeTable;
use Application\Model\AttributeProductTable;
use Application\Model\CategoryTable;

class Compare extends AbstractController {

	private $productTable;
	private $attributeTable;
	private $attributeProductTable;
	private $categoryTable;

	public function ready() {
		parent::ready();
		$this->productTable = new ProductTable();
		$this->attributeTable = new AttributeTable();
		$this->attributeProductTable = new AttributeProductTable();
		$this->categoryTable = new CategoryTable();

	}

	public function indexAction() {
		//$product = $this->getCompareProduct();
		$compareCategory = $this->compareCategory();
		$result = new ViewModel(array(
			//'product' => $product,
			'category' => $compareCategory,
		));
		return $result;
	}

	public function addCompareAction() {
		$response = $this->getResponse();

		$productId = $this->request->getPost('productId');
		$categoryId = $this->request->getPost('categoryId');
		
		if(isset($_SESSION['compare'][$categoryId]) && count($_SESSION['compare'][$categoryId]) < 5 ){
			$_SESSION['compare'][$categoryId][] = $productId;
			$_SESSION['compare'][$categoryId] = array_unique($_SESSION['compare'][$categoryId]);
		} else if(count($_SESSION['compare'][$categoryId]) >= 5) {
			//throw new \Exception("В сравнении не может быть больше 5 товаров!");
			header('Content-Type: application/json');
			echo json_encode('>5');
		} else {
			$_SESSION['compare'][$categoryId] = [];
			$_SESSION['compare'][$categoryId][] = $productId;
		}
		$response->setStatusCode(200);
		return $response;
	}

	public function getProductAction() {
		$response  = $this->getResponse();
		
		$params = [];
		$product = [];
		$categoryId = $this->request->getPost('categoryId');
		$productId = $_SESSION['compare'][$categoryId];
		if(!empty($productId)){
			$params[] = ['id', 'IN', $productId];
			$paramsValue[] = ['productId', 'IN', $productId];
		}
		$product = $this->productTable->find($params);
		
		foreach ($productId as $key=>$id) {
			$attributeValue = $this->getAttributeValue($id);
			$product[$id]['value'] = $attributeValue;
		}
		
		$attributeName = $this->getAttributeName($categoryId);
		foreach($attributeName as $item){
			$product['attributeName'][$item['id']] = $item['name'];	
		}
		

		header('Content-Type: application/json');
		echo json_encode($product);


		$response->setStatusCode(200);
		return $response;
	}

	public function getAttributeName($categoryId) {
		$attribute = $this->attributeTable->find(["categoryId=$categoryId"]);
		return $attribute;
	}

	public function getAttributeValue($productId) {
		
		$attributeValue = [];
		$attributeValues = $this->attributeProductTable->find(["productId=$productId"]);
		
		foreach ($attributeValues as $item) {
			//var_dump($item['attributeId']);
			$attributeValue[$item['attributeId']] = $item['value'];
		}
//		die;
		return $attributeValue;
	}

	public function compareCategory() {
		$category = [];
		if(isset($_SESSION['compare'])){
			$categoryId = $_SESSION['compare'];
			foreach ($categoryId as $key=>$value) {
				$categories = $this->categoryTable->find(["id=$key"]);
				$category[]= array_shift($categories);
			}
		}
		return $category;
	}

	public function deleteProductAction() {
		$response  = $this->getResponse();

		$categoryId = $this->request->getPost('categoryId');
		$productId = $this->request->getPost('productId');
		foreach($_SESSION['compare'][$categoryId] as $key=>$item) {
			if($item == $productId){
				unset($_SESSION['compare'][$categoryId][$key]);
			}
		}
		if(empty($_SESSION['compare'][$categoryId])) {
				unset($_SESSION['compare'][$categoryId]);
				$flag = 'emptyCategory';
				header('Content-Type: application/json');
				echo json_encode($flag);
		}

		if(empty($_SESSION['compare'])) {
				unset($_SESSION['compare']);
		}
		
		$response->setStatusCode(200);
		return $response;
	}

}
