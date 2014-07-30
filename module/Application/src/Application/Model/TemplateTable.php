<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;

class TemplateTable extends LocalizableTable {
	
	/**
	 * List of fields from DB table
	 * 
	 * @var array
	 */
	protected $goodFields = array(
		'id',
		'name',
	);
	
	/**
	 * List of fields for data with localized values
	 * 
	 * @var array()
	 */
	protected $localFields = array(
		'subject',
		'text',
	);

	public function __construct($id=null) {
		parent::__construct('template');
	}

	
	/**
	 * delete data from DB and from cache
	 * 
	 * @param mixed $where
	 * @return int
	 */
	public function delete($where) {
		if(is_numeric($where)) { // clear cache
			try {
				$item = $this->get($id);
				$this->cacheDelete(base64_encode($item->name));
			}
			catch (\Exception $e) {}
		}
		
		return parent::delete($where);
	}
	

}