<?php

namespace Application\Model;

use CodeIT\Model\CachedTable;

class FavouriteProductTable extends CachedTable {

	public $id;
	public $userId;
	public $productId;

	protected $goodFields = array(
		'id',
		'listId',
		'productId',
	);

	public function __construct($id = null) {
		parent::__construct('favouriteProduct', $id);
	}

	public function create($data) {
		$this->startTransaction();
		$uid = parent::create([
			'listId' => $data['listId'],
			'productId' => $data['productId'],
		]);
		$this->commit();

		return $uid;
	}

}
