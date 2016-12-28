<?php
namespace Application\Model;

use CodeIT\Model\CachedTable;

class AttributeTable extends CachedTable {
	
	protected $goodFields = array(
		'id',
		'name',
		'categoryId',
	);

	public function __construct($id = null) {
		parent::__construct('attribute', $id);
	}

	public function create($data)
	{
		$this->startTransaction();
		$uid = parent::create([
			'name' => $data['name'],
			'categoryId' => $data['categoryId'],
		]);
		$this->commit();

		return $uid;
	}
}

