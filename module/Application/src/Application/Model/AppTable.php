<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql;

abstract class AppTable extends TableGateway {
	protected $id;

	/**
	* Language Id
	*
	* @var int
	*/
	protected $lang=1;

	/**
	* @var \Application\Lib\Memcache
	*/
	protected static $memCachedGlobal = null;

	/**
	* Table with local data
	*
	* @var string
	*/
	protected $locTable;

	/**
	* Join clause for find() method, used for local tables
	* 
	* @var string
	*/
	protected $findJoin='';
	
	/**
	* Join clause for findWithoutLangLimitation() method, used for search in local tables without language limitation
	* 
	* @var string
	*/
	protected $baseJoin='';
	
	/**
	* List of fields getting from join, used for avoid dublicated fields
	* 
	* @var string
	*/
	protected $findFields='*';
	
	/**
	* List of fields for data with localized values
	* 
	* @var array()
	*/
	protected $localFields = array();

	// creates table and sets id if neccessary
	public function __construct($tableName, $id=null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
		try {
			$adapter = \Zend\Registry::get('dbAdapter');
		}
		catch(\Exception $e) {
			// create adapter
			$adapter = new \Zend\Db\Adapter\Adapter(\Zend\Registry::get('dbConfig'));
			\Zend\Registry::set('dbAdapter', $adapter);
		}

		$result = parent::__construct($tableName, $adapter, $databaseSchema, $selectResultPrototype);
		if($id) {
			$this->setId($id);
		}

		$this->lang = \Zend\Registry::get('lang');

		if(!self::$memCachedGlobal) {
			self::$memCachedGlobal = new \Application\Lib\Memcache();
		}

		return $result;
	}

  public function __get($property) {
		try {
			return parent::__get($property);
		}
		catch(\Exception $e) {
			$getter = "get".ucfirst($property);
			if(isset($this->member[$property])) {
				if(is_callable($this->member[$getter])) {
					return $this->$getter;
				}
				else {
					return $this->member[$property];
				}
			}
		}
		
		return null;
  }

	public function __set($property, $value) {
    if ($this->featureSet->canCallMagicSet($property)) {
    	return $this->featureSet->callMagicSet($property, $value);
    }

    $this->member[$property] = $value;
	}

	/**
	* returns new \Zend\Db\Sql\Select
	*
	* @param null|string $table table name
	* @return \Zend\Db\Sql\Select
	*/
	protected function getSelect($table = null) {
		return new \Zend\Db\Sql\Select($table);
	}

	/**
	* runs SQL query
	*
	* @param \Zend\Db\Sql\AbstractSql $sql Select, Insert, Update etc.
	* @param array $params
	* @return ResultSet | last insert id | affected rows
	*/
	protected function execute(\Zend\Db\Sql\AbstractSql $sql, $params=array()) {
		try {
			$statement = $this->adapter->createStatement();
			$sql->prepareStatement($this->adapter, $statement);
			//echo $sql->getSqlString($this->adapter->platform);
			//\Zend\Debug::dump($statement);

			$resultSet = new ResultSet();
			$dataSource = $statement->execute($params);
			if($sql instanceof \Zend\Db\Sql\Insert) {
				return $dataSource->getGeneratedValue();
			}
			elseif($sql instanceof \Zend\Db\Sql\Update) {
				return $dataSource->getAffectedRows();
			}
			$resultSet->initialize($dataSource);
			return $resultSet;
		}
		catch(\Exception $e) {
			if(DEBUG) {
				$previousMessage = '';
				if($e->getPrevious()) {
					$previousMessage = ': '.$e->getPrevious()->getMessage();
				}
				throw new \Exception('SQL Error: '.$e->getMessage().$previousMessage."<br>
					SQL Query was:<br><br>\n\n".$sql->getSqlString($this->adapter->platform));
				//\Zend\Debug::dump($e);
			}
		}
		return array();
	}

	/**
	* makes and executes SQL query
	*
	* @param string $query
	* @param mixed $params
	* @return ResultSet
	*/
	protected function query($query, $params=false) {
		if(!$params) {
			$params = \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE;
		}
		try {
			$resultSet = $this->adapter->query($query, $params);
		}
		catch(\Exception $e) {
			if(DEBUG) {
				$previousMessage = '';
				if($e->getPrevious()) {
					$previousMessage = ': '.$e->getPrevious()->getMessage();
				}
				throw new \Exception('SQL Error: '.$e->getMessage().': '.$previousMessage."<br>
					SQL Query was:<br><br>\n\n".$query."<br>params: ".print_r($params, true));
				//\Zend\Debug::dump($e);
			}
		}
		return $resultSet;
	}

	/**
	* Aquires lock (mutex)
	*
	* @param string $name
	* @param array $params
	* @param int $timeout
	*/
	protected function getLock($name, $params=false, $timeout=10) {
		$resultSet = $this->query("select GET_LOCK('$name', $timeout) as res", $params);
		$result = $resultSet->current()->res;
		if(!$result) {
			throw new \Exception('Could not obtain lock on '.$name);
		}
	}

	/**
	* releases lock (mutex) obtained by getLock
	*
	* @param string $name
	* @param array $params
	*/
	protected function releaseLock($name, $params=false) {
		$this->query("select RELEASE_LOCK('$name')", $params);
	}

	/**
	* starts transaction
	*/
	public function startTransaction() {
		$this->query('start transaction');
	}

	/**
	* commits transaction
	*/
	public function commit() {
		$this->query('commit');
	}

	/**
	* rollbacks transaction
	*/
	public function rollback() {
		$this->query('rollback');
	}

  /**
   * Inserts a record
   *
   * @param array $set
   * @return int last insert Id
   */
	public function insert($set) {
		$set = $this->removeUnnecessaryFields($set);
		if(parent::insert($set)) {
			$this->cacheDelete('list');
			return $this->lastInsertValue;
    }
    throw new \Exception('Insert to "'.$this->table.'" failed. Set was '.print_r($set, true));
	}

	public function soaparray($param) {
		if(is_array($param)) return $param;
		if(is_null($param)) return array();
		return array($param);
	}

	/**
	* searches for items
	* 
	* @param array $params, e.g. arrat('id', '>=', '135')
	* @param int $limit, set to 0 or false to no limit
	* @param int $offset
	* @param string $orderBy
	* @param int &$total will be set to total count found
	* @return \Zend\Db\ResultSet\ResultSet
	*/
	public function find($params, $limit=50, $offset=0, $orderBy=false, &$total=null) {
		$platform = $this->getAdapter()->getPlatform();
		$whereParams = array();
		foreach($params as $param) {
			$set = $platform->quoteIdentifierChain($param[0]) . $param[1];
			if (isset($param[2])){
				$set .= $this->quoteValue($param[2]);
			}
			$whereParams []= $set ;
		}

		$where = '';
		if(!empty($whereParams)) {
			$where = 'where '.implode(' AND ', $whereParams);
		}

		if(!is_null($total)) {
			$total = $this->query('select count(*) cnt from `'.$this->table.'` '.$this->findJoin.' '.$where)->current()->cnt;
		}
		return $this->query('select '.$this->findFields.' from `'.$this->table.'` '.$this->findJoin.' '.
			$where.
			($orderBy ? ' order by '.$orderBy : '').
			($limit ? ' limit '.((int)$offset) .', '.((int)$limit) : '')
		);
	}

	/**
	* creates item, sets id.
	*
	* @param array $params
	* @return id
	*/
  public function create($params) {
		foreach($params as $key => $field) {
			if(!in_array($key, $this->goodFields)) {
				unset($params[$key]);
			}
		}
  	$id = $this->insert($params);
		$this->setId($id);
		return $id;
  }

  /**
  * returns current id
  *
  * @return $id int
  */
	public function getId() {
		return $this->id;
	}

	/**
	* sets Id. Checks whether entry exists.
	*
	* @param int $id
	* @returns item
	*/
	public function setId($id) {
		$this->id = $id;
		$item = $this->get($id);
		return $item;
	}

	/**
	* returns row from db with specified id
	*
	* @param int $id
	* @return \ArrayObject
	*/
	public function get($id) {
		$row = $this->cacheGet($id);
		if(!$row) {
	    $row = $this->getUncached($id);
			$this->cacheSet($id, $row);
		}

		return $row;
	}

	/**
	* returns row from db with specified id
	*
	* @param int $id
	* @return \ArrayObject
	*/
	public function getUncached($id) {
	  $row = $this->select(array('id' => $id))
    				    ->current();
  	//\Zend\Debug\Debug::dump($row);
  	//die;
		if(!$row) {
			throw new \Exception(ucfirst($this->table).' '.$id.' not found');
		}

		return $row;
	}

	/**
	* sets data for current id
	*
	* @param array $data
	*/
	public function set($data) {
    $this->update($data, array('id' => $this->id));
    $this->cacheDelete($this->id);
    $this->setId($this->id);
	}

  /**
   * Update
   *
   * @param  array $params
   * @param  string|array|closure $where
   * @return int
   */
	public function update($params, $where = null) {
		$params = $this->removeUnnecessaryFields($params);
		parent::update($params, $where);
		$this->cacheDelete('list');
	}

	
	/**
	* deletes record by id, removes cached data
	* 
	* @param mixed $id
	* @returns altered rows
	*/
	public function deleteById($id) {
		$rowsAffected = $this->delete(array('id' => $id));
		$this->cacheDelete($id);
		$this->cacheDelete('list');
		
		return $rowsAffected;
	}
	
	/**
	* get cached value
	*
	* @param string $name
	* @return string
	*/
	public function cacheGet($key) {
		if(MEMCACHE_ENABLED) {
			return self::$memCachedGlobal->get('table.'.$this->table.'.'.$key);
		}

		return false;
	}

	/**
	* assigns a value to a specified cached param
	*
	* @param string $name param name
	* @param string $value param value
	* @param int $timeout
	* @param int $try
	* @return bool true on success, false on fail MAX_TRIES times
	*/
	public function cacheSet($key, $value, $flag = false, $timeout = 0,  $try=0) {
		if(MEMCACHE_ENABLED) {
			return self::$memCachedGlobal->set('table.'.$this->table.'.'.$key, $value, $flag, $timeout, $try);
		}

		return true;
	}

	/**
	* deletes cached value
	*
	* @param string $name
	*/
	public function cacheDelete($key){
		if(MEMCACHE_ENABLED) {
			return self::$memCachedGlobal->deleteCache('table.'.$this->table.'.'.$key);
		}

		return true;
	}

	/**
	* returns full items list
	*
	* @param int $limit
	* @param int $offset
	* @return ResultSet
	*/
	public function getList($limit=false, $offset=0) {
		$res = $this->cacheGet('list');
		if(!$res) {
	    $select = $this->getSelect($this->table);
			if($limit !== false) {
				$select->limit($limit);
			}
			if($offset) {
				$select->offset($offset);
			}

			$list = $this->execute($select);
			$res = array();
			foreach($list as $item) {
				$res[$item->id] = $this->get($item->id);
			}
			$this->cacheSet('list', $res);
		}
		return $res;
	}
	
	public function getColumn($query, $params=array()){
		$q = (array)$this->query($query, $params)->current();
		return current($q);
	}

		/**
		* replace for bad platform function
		* 
		* @param string $value
		*/
		function quoteValue($value) {
			$res = str_replace('\\', '\\\\', $value);
			$res = str_replace('\'', '\\\'', $res);
			return '\'' . $res . '\'';
		}
		
		protected function removeUnnecessaryFields($params){
			foreach($params as $key => $field) {
				if(!in_array($key, $this->goodFields)) {
					unset($params[$key]);
				}
			}
			return $params;
		}
		
		/**
		* get local data for localized items
		* 
		* @param mixed $data
		*/
		public function getLocalData($sets){
			$resultSet = new \Zend\Db\Sql\Select;
			$resultSet->columns(array('*'));
			$resultSet->from(array('cl'=>$this->locTable)
			);
			//include all where settings
			if (isset($sets['where']) && is_array($sets['where'])){
					$resultSet->where($sets['where']);
				}
			//set user's limit if it's nessesary
			$limit = NULL;
			if (isset($sets['limit'])){
				$limit = $sets['limit'];
				$resultSet->limit($limit);
			}
			
			//set user's offset if it's nessesary
			$offset = NULL;
			if (isset($sets['offset'])){
				$offset = $sets['offset'];
				$resultSet->offset($offset);
			}
			

			$results = $this->execute($resultSet)->toArray();
			return $results;
		}
		
		/**
		* update or insert local data for localized items
		* 
		* @param mixed $data
		*/
		public function updateLocData($data){
			$id = $this->id;
			foreach ($data as $catloc){
				$sets = array(
						'where'=>array(
								'id'=>$id,
								'lang'=>$catloc['lang']
						)
				);
				$list = $this->getLocalData($sets);
				$catloc['id']=$id;
				if (count($list)){
					$this->updateLocItem($catloc);
				} else {
					$this->insertLocItem($catloc);
				}
        $this->cacheDelete($id."_".$catloc['lang']);
			}
		}

		public function updateLocItem($data){
			$update = new  \Zend\Db\Sql\Update;
			$update->table($this->locTable);
			$update->set($data);
			$where_condition = array(
					'id'=>$data['id'],
					'lang'=>$data['lang']
			);
			$update->where($where_condition);
			$results = $this->execute($update);
		}

		public function insertLocItem($data){
			$sql = new  \Zend\Db\Sql\Insert;
			$sql->into($this->locTable);
			$sql->values($data);
			$results = $this->execute($sql);
		}
		
		/**
		* Method returns just array of localized data
		* 
		* @param mixed $data
		*/
		protected function chooseLocalData(&$data){
		$localFields = $this->localFields;
		$localData = array();
		foreach ($localFields as $field){
			if (isset($data[$field]) && is_array($data[$field])){
				foreach ($data[$field] as $lang=>$val){
					if (!isset($localData[$lang])){
						$localData[$lang] = array(
							'lang' => $lang,
						);
					}
					$localData[$lang][$field] = $val;
				}
				unset($data[$field]);
			}
		}
		return $localData;
	}

}
