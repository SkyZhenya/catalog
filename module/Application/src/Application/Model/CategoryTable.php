<?php
namespace Application\Model;

use CodeIT\Model\CachedTable;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Debug;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;

class CategoryTable extends CachedTable {

	public $id;
	public $name;

	protected $goodFields = array(
		'id',
		'name',
	);

	public function __construct($id = null) {
		parent::__construct('category', $id);
	}

	public function create($data)
	{
		$this->startTransaction();
		$uid = parent::create([
			'name' => $data['name'],
		]);
		$this->commit();

		return $uid;
	}

	public function delete($id){
		$item = $this->get($id);
		$this->cacheDelete(base64_encode($item->name));
		return parent::delete($id);
	}

	public function getCategoryById() {
		TODO();
	}

}