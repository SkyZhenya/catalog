<?php
namespace Application\Controller;

use Zend\Paginator\Paginator;
use Application\Lib\Paginator\Adapter\AppTableAdapter;
use Zend\View\Model\ViewModel;
use CodeIT\Controller\AbstractController;
use Application\Model\ProductTable;
use Application\Model\CategoryTable;
use Application\Model\AttributeTable;
use Application\Model\AttributeProductTable;
use Application\Model\FavouriteProductTable;
use Application\Model\ListTable;




class Product extends AbstractController {

	private $productTable;
	private $attributeProductTable;
	private $attributeTable;
	private $categoryTable;
	private $favouriteProductTable;
	private $listTable;

	public function ready() {
		parent::ready();
		$this->productTable = new ProductTable();
		$this->attributeProductTable = new AttributeProductTable();
		$this->attributeTable = new AttributeTable();
		$this->categoryTable = new CategoryTable();
		$this->favouriteProductTable = new FavouriteProductTable();
		$this->listTable = new ListTable();
	}

	public function indexAction() {
		$categoryId = $this->params('category');
		$sort = $this->params('sort');
		$priceFrom = null;
		$priceTo = null;
		if(isset($_POST['priceFrom'])){
			$priceFrom = $_POST['priceFrom'];
		}
		if(isset($_POST['priceTo'])){
			$priceTo = $_POST['priceTo'];
		}
		$result = new ViewModel(array(
			'product' => $this->getProduct($categoryId, $sort, $priceFrom, $priceTo),
			'category' => $this->getCategoy(),
			'categoryId' => $categoryId,
			'sortBy' => $sort,
		));
		return $result;
	}
	
	public function getProduct($categoryId, $sort, $priceFrom, $priceTo) {
		
		$params = [];
		$orderBy = false;
		$params[] = ['flag', '=', 'finished'];
		if (!empty($categoryId)){
			$params[] = ['categoryId','=', $categoryId];
		}
		if(!empty($sort)) {
			if($sort == 'new'){
				$orderBy = 'id desc';
			} elseif ($sort == 'name'){
				$orderBy = 'name asc';
			} elseif($sort == 'popular') {				
//				$popular = $this->favouriteProductTable->getAdapter()->query('SELECT productId, COUNT(productId) as total FROM favouriteProduct GROUP BY productId ORDER BY total DESC');
//				$popular = $popular->execute();
//				$popular = iterator_to_array($popular);
//				var_dump($popular);
//				$idP = [];
//				foreach($popular as $key=>$item){
//					$idP[] = $item['productId'];
//				}
//				$params[] = ['id', 'IN', $idP];

				$this->productTable->setFindJoin("RIGHT JOIN (SELECT productId, COUNT(productId) as total FROM favouriteProduct GROUP BY productId ORDER BY total DESC) fp ON fp.productId=product.id");
				$orderBy = 'total desc';
			}
		}
		if (!empty($priceFrom) && is_numeric($priceFrom)){
			$params[] = "$priceFrom<=price";

		}
		if(!empty($priceTo) && is_numeric($priceTo)){
			$params[] = "$priceTo>=price";
		}
		$paginator = new Paginator(new AppTableAdapter($this->productTable, $params, $orderBy));
		$paginator->setItemCountPerPage(6);
		$paginator->setCurrentPageNumber($this->params('page', 1));
		$paginator->getCurrentItems();
		//var_dump($paginator);
		return $paginator;
	}

	public function getCategoy() {
		$category = $this->categoryTable->find([]);
		return $category;
	}

	public function getAttribute() {
		$attribute = $this->attributeTable->find([]);
		return $attribute;
	}

	public function getAttributeProduct() {
		$attributeProduct = $this->attributeProductTable->find([]);
		return $attributeProduct;
	}

	public function productAction() {
		$prodId = $this->params('id');
		$userLevel = $this->user->level;
		$userId = $this->user->id;
		
		$params = [];
		$total = 0;
		if (isset($prodId)){
			$params[] = "id=$prodId";
		}
		$product = $this->productTable->find($params);
		$product = array_shift($product);
		$this->attributeProductTable->setFindJoin("INNER JOIN attribute as att ON att.id=attributeProduct.attributeId");
		$attribute = $this->attributeProductTable->findSimple(["productId=$prodId"], 0, 0, false, $total, ['attributeName' => ['att', 'name'], 'attributeType' => ['att', 'type'], '*']);

		if ($product->flag == 'in edit' && ($userLevel != 'admin' && $userLevel != 'manager')) {
			$this->notFoundAction();
		}
		$list = [];
		$product['image'] = $this->productTable->getImage($prodId);
		if(isset($userId)){
			$list = $this->listTable->find(["userId=$userId"]);
		}
		
		$result = new ViewModel(array(
			'product' => $product,
			'attribute' => $attribute,
			'list' => $list,
			'userId' => $userId,
		));
		return $result;		
	}


	public function searchAction() {
		$response = $this->getResponse();
		$value = $this->request->getPost()->toArray();
		$value = htmlspecialchars($value['inp']);
		
		$params = [];
		if(!empty($value)){
			$params[] = 'name LIKE "%'.$value.'%"';
		}
		$product = $this->productTable->find($params);

		header('Content-Type: application/json');
		echo json_encode($product);
		
		$response->setStatusCode(200);
		return $response;
	}

}