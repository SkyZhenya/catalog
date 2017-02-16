<?php
namespace Application\Controller;

use CodeIT\Controller\AbstractController;
use Application\Model\ProductTable;
use Application\Model\FavouriteProductTable;
use Application\Model\ListTable;
use Zend\View\Model\ViewModel;

class Favourite extends AbstractController {

	private $productTable;
	private $favouriteProductTable;
	private $listTable;


	public function ready() {
		parent::ready();
		$this->productTable = new ProductTable();
		$this->favouriteProductTable = new FavouriteProductTable();
		$this->listTable = new ListTable();
	}

	public function indexAction() {
		$list = $this->getList();
		$result = new ViewModel(array(
			'list' => $list,
		));
		return $result;
	}

	public function addListAction(){
		$response = $this->getResponse();
		$url = $this->getRequest()->getServer('HTTP_REFERER');
		$name = $this->request->getPost('name');
		$name = htmlspecialchars($name);
		$userId = $this->user->id;

		if (isset($userId) && !empty($name)){
			$data['userId'] = $userId;
			$data['name'] = $name;
			$this->listTable->insert($data);
		}
		$rez = $this->getList();
		header('Content-Type: application/json');
		echo json_encode($rez);


		$response->getHeaders()->addHeaderLine('Location', $url);
		$response->setStatusCode(200);
		return $response;
	}

	public function deleteListAction() {
		$response = $this->getResponse();
		$url = $this->getRequest()->getServer('HTTP_REFERER');
		$id = $this->request->getPost('id');
		$this->listTable->delete($id);
		$response->getHeaders()->addHeaderLine('Location', $url);
		$response->setStatusCode(200);
		return $response;
	}

	public function getList() {
		$userId = $this->user->id;
		$params = [];
		if(isset($userId)){
			$params[] = "userId=$userId";
		}
		$list = $this->listTable->find($params);
		return $list;
	}

	public function getContentAction() {
		$response = $this->getResponse();
		$listId = $this->request->getPost('listId');
		$params = [];
		$total = 0;
		if(isset($listId)){
			$params[] = "listId=$listId";
			$productId = $this->favouriteProductTable->findSimple($params, 0, 0, false, $total,['productId'])->toArray();
		}
		$product = [];
		foreach ($productId as $key=>$value){
			$param = [];
			$param[] = "id=".array_shift($value);
			$products = $this->productTable->find($param);
			$product[$key] = array_shift($products);
		}
		
		header('Content-Type: application/json');
		echo json_encode($product);

		$response->setStatusCode(200);
		return $response;
	}

	public function addFavouriteAction() {
		$response = $this->getResponse();
		$productId = $this->request->getPost('productId');
		$listId = $this->request->getPost('listId');
		$url = $this->getRequest()->getServer('HTTP_REFERER');
		$userId = $this->user->id;
		$favouriteCheck = $this->checkFavourite($productId, $listId);
		if (isset($userId) && empty($favouriteCheck)){
			$data['listId'] = $listId;
			$data['productId'] = $productId;
			
			$this->favouriteProductTable->insert($data);
		} else {
			header('Content-Type: application/json');
			echo json_encode('is');
		}

		$response->getHeaders()->addHeaderLine('Location', $url);
		$response->setStatusCode(200);
		return $response;
	}

	public function checkFavourite($productId, $listId) {
		$params = [];
		if(isset($productId)) {
			$params[] = ['productId', '=' , $productId];
		}
		if(isset($listId)) {
			$params[] = ['listId', '=', $listId];
		}
		$favorite = $this->favouriteProductTable->find($params);
		return $favorite;
	}

	public function deleteAction() {
		$response = $this->getResponse();
		$url = $this->getRequest()->getServer('HTTP_REFERER');
		$prodId = $this->request->getPost('productId');
		$listId = $this->request->getPost('listId');
//		var_dump($listId);
		$params = [];
		$params[] = "productId=$prodId";
		$params[] = "listId=$listId";
		$this->favouriteProductTable->delete($params);
		$response->getHeaders()->addHeaderLine('Location', $url);
		$response->setStatusCode(301);
		return $response;
	}

}