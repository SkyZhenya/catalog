<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Application\Model\AppTable;

class LangTable extends AppTable {
	
	public $id=null;
	public $code=null;
	public $locale=null;
	public $name=null;
	public $main=null;

	protected	$goodFields = array(
		'id',
		'name',
		'code',
		'locale',
		'main',
	);

	public function __construct($langId = null) {
		parent::__construct('lang', $langId);
	}

	/**
	* sets Id. Checks whether entry exists.
	*
	* @param int $id
	* @returns item
	*/
	public function setId($id) {
		$item = parent::setId($id);
		$this->code = $item->code;
		$this->locale = $item->locale;
		$this->name = $item->name;
		$this->main = $item->main;
		
		return $item;
	}
	
	/**
	* returns row from db for lang with code $code
	*
	* @param string $code
	* @return \ArrayObject
	*/
	public function getByCode($code) {
		$key = base64_encode($code);
		$item = $this->cacheGet($key);
		if(!$item) {
	    $item = $this->find(array(
	    	array('code', '=', $code),
	    ), 1, 0)->current();
	    if(!$item) {
				throw new \Exception(ucfirst($this->table).' '.$code.' not found');
			}
			$this->cacheSet($key, $item);
		}

		return $item;
	}
	
}
