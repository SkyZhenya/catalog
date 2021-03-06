<?php
namespace Application\Model;

use CodeIT\Model\AppTable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class LangTable extends AppTable {

	/**
	 * Language shortcode
	 * 
	 * @var string
	 */
	public $code;
	
	/**
	 * Locale name for this language
	 * 
	 * @var mixed
	 */
	public $locale;
	
	/**
	 * Language title
	 * 
	 * @var string
	 */
	public $name;
	
	/**
	 * Is language main and default
	 * 
	 * @var integer
	 */
	public $main;
	
	/**
	 * List of fields from DB table
	 * 
	 * @var array
	 */
	protected $goodFields = array(
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
