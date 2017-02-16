<?php
namespace Application\Model;

use CodeIT\Model\CachedTable;

class AttributeProductTable extends CachedTable {

	public $id;
	public $productId;
	public $attributeId;
	public $value;

	protected $goodFields = array(
		'id',
		'productId',
		'attributeId',
		'value',
	);

	public function __construct($id = null) {
		parent::__construct('attributeProduct', $id);
	}

	public function create($data) {
		$this->startTransaction();
		$uid = parent::create([
			'productId' => $data['productId'],
			'attributeId' => $data['attributeId'],
			'value' => $data['value'],
		]);
		$this->commit();
		return $uid;
	}

}