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

class ProductTable extends CachedTable {

	use ProductConf;
	use \Application\Traits\Avatar;
	use \Application\Traits\Imaging;

	/**
	 * avatar type, can be default or normal
	 *
	 * @var string
	 */
	public $avatarType;

	/**
	 * array of links to avatars
	 *
	 * @var array
	 */
	public $avatars;

	public $id;
	public $name;
	public $price;
	public $manufacturer;
	public $description;
	public $categoryId;
	public $flag;

	protected $goodFields = array(
		'id',
		'name',
		'price',
		'manufacturer',
		'description',
		'categoryId',
		'flag'
	);

	public function __construct($id = null) {
		parent::__construct('product', $id);
	}

	public function getUncached($id) {
		$row = parent::getUncached($id);

		$row->avatarType = 'normal';
		try {
			$imagesInfo = $this->getAvatar($id);
			$row->avatars = $imagesInfo['avatars'];
			$row->avatarsPaths = $imagesInfo['avatarsPaths'];
		}
		catch(\Exception $e) {
			// set default avatar
			$row->avatarType = 'default';

			foreach($this->avatarSizes as $sizes) {
				$row->avatars[$sizes[0]] = URL . 'images/product/default'.$sizes[0].'.jpg';
			}
		}

		return $row;
	}

	public function create($data) {
		$this->startTransaction();
		$uid = parent::create([
			'name' => $data['name'],
			'price' => $data['price'],
			'manufacturer' => $data['manufacturer'],
			'description' => $data['description'],
			'categoryId' => $data['categoryId'],
			'flag' => $data['flag'],
		]);
		$this->commit();

		return $uid;
	}

	public function delete($id){
		$this->removePhoto($id, $name);
		$this->removeImages($id);
		$this->removeDir($id);
		return (bool)parent::delete(array('id' => $id));
	}

	
}