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
	 * @var \Application\Lib\Redis
	 */
	protected static $redis = null;

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
	 * List of fields getting from join, used for avoid dublicated fields
	 * 
	 * @var string
	 */
	protected $findFields='*';

	
	/**
	 * List of fields from DB table
	 * 
	 * @var array
	 */
	protected $goodFields = array();
	/**
	 * List of fields for data with localized values
	 * 
	 * @var array()
	 */
	protected $localFields = array();
	
	/**
	 * Group by condition for find() method
	 * 
	 * @var string
	 */
	protected $groupBy;
	
	/**
	 * Having condition for find() method
	 * 
	 * @var string
	 */
	protected $having;
	
	/**
	 * Table "ID" field name
	 */
	const ID_COLUMN = 'id';

	/**
	 * creates table and sets id if neccessary
	 * @param string $tableName
	 * @param int $id
	 * @param mixed $databaseSchema
	 * @param ResultSet $selectResultPrototype
	 * @return {\Zend\Db\TableGateway\TableGateway|ResultSet}
	 */
	public function __construct($tableName, $id=null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
		try {
			$adapter = \Zend\Registry::get('dbAdapter');
		}
		catch(\Exception $e) {
			// create adapter
			if(defined('DEBUG_SQL') && DEBUG_SQL) {
				$adapter = new \BjyProfiler\Db\Adapter\ProfilingAdapter(\Zend\Registry::get('dbConfig'));
				$adapter->setProfiler(new \BjyProfiler\Db\Profiler\Profiler);
				$adapter->injectProfilingStatementPrototype();
			}
			else {
				$adapter = new \Zend\Db\Adapter\Adapter(\Zend\Registry::get('dbConfig'));
			}

			\Zend\Registry::set('dbAdapter', $adapter);
		}

		$result = parent::__construct($tableName, $adapter, $databaseSchema, $selectResultPrototype);
		$this->lang = \Zend\Registry::get('lang');

		if(!self::$redis) {
			try {
				self::$redis = \Zend\Registry::get('redis');
			}
			catch(\Exception $e) {
				self::$redis = new \Application\Lib\Redis();
				\Zend\Registry::set('redis', self::$redis);
			}
		}

		if(!$this->locTable) $this->locTable = $this->table.'local';
		if($id) {
			$this->setId($id);
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
	 * searches for items, fetching them by ::get()
	 * 
	 * @param array $params, e.g. array('id', '>=', '135')
	 * @param int $limit, set to 0 or false to no limit
	 * @param int $offset
	 * @param string $orderBy
	 * @param int &$total will be set to total count found
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function find($params, $limit=0, $offset=0, $orderBy=false, &$total=null) {
		$ids = $this->findSimple($params, $limit, $offset, $orderBy, $total, ['id'])->toArray();
		$ids = array_column($ids, 'id');
		return $this->mget($ids);
	}

	/**
	 * searches for items, returning them as object of current class
	 * 
	 * @param array $params, e.g. array('id', '>=', '135')
	 * @param int $limit, set to 0 or false to no limit
	 * @param int $offset
	 * @param string $orderBy
	 * @param int &$total will be set to total count found
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function findObjects($params, $limit=0, $offset=0, $orderBy=false, &$total=null){
		$ids = $this->findSimple($params, $limit, $offset, $orderBy, $total, [static::ID_COLUMN])->toArray();
		$ids = array_column($ids, static::ID_COLUMN);
		$result = [];
		$className = get_class($this);
		foreach($ids as $id){
			if($id > 0){
				$result[] = new $className($id);
			}
		}
		return $result;
	}
	
	/**
	 * searches for items and returns \Zend\Db\ResultSet\ResultSet
	 * 
	 * @param array $params, e.g. arrat('id', '>=', '135')
	 * @param int $limit, set to 0 or false to no limit
	 * @param int $offset
	 * @param string $orderBy
	 * @param int &$total will be set to total count found
	 * @param array $columns fileds which should be inclued in the result
	 * @return \Zend\Db\ResultSet\ResultSet
	 */
	public function findSimple($params, $limit=0, $offset=0, $orderBy=false, &$total=null, $columns=['id']) {
		$where = $this->buildWhere($params);
		if(!is_null($total)) {
			$total = $this->count($where);
		}

		return $this->query($this->buildSelectQuery($where, $limit, $offset, $orderBy, $columns));
	}

	/**
	 * Build where condition string from the $params array
	 * 
	 * @param array $params
	 * @return string
	 */
	protected function buildWhere($params) {
		$whereParams = $this->processWhereParams($params);

		if(!empty($whereParams))
			return 'where '.implode(' AND ', $whereParams);

		return '';
	}
	
	/**
	 * Build the select query from arguments
	 * 
	 * @param string $where
	 * @param integer $limit
	 * @param integer $offset
	 * @param string $orderBy
	 * @param array $columns
	 * @return string
	 */
	protected function buildSelectQuery($where, $limit=0, $offset=0, $orderBy=false, $columns=['id'], $join=false) {
		return 'select '.$this->buildSelect($columns).' from `'.$this->table.'` '.$this->findJoin.' '.
			$where.
			($join ? $join : '').
			($this->getGroupBy() ? $this->getGroupBy() : '').
			' '.($this->getHaving() ? $this->getHaving() : '').
			($orderBy ? ' order by '.$orderBy : '').
			($limit ? ' limit '.((int)$offset) .', '.((int)$limit) : '');
	}
	
	/**
	 * Build a select condition using the $columns
	 * 
	 * @param type $columns
	 * @return type
	 */
	protected function buildSelect($columns) {
		$tColumns=[];
		foreach($columns as $alias => $column) {
			$selectColumn = '';
			if (is_object($column) && get_class($column) == 'Zend\Db\Sql\Expression') {
				$expression = $column->getExpression();//
				$selectColumn = $expression;
			}
			elseif (is_array($column)) {
				$selectColumn = '`'.$column[0].'`.'.$column[1].''; //to get columns from different tables when join
			} else {
				if ($column == '*')
					$selectColumn = '`'.$this->table.'`.*';//to get all table columns
				else 
					$selectColumn = '`'.$this->table.'`.`'.$column.'`';
			}
			if (is_string($alias) && !empty($alias)) {
				$selectColumn .= ' AS '.$alias;
			}
			$tColumns[] = $selectColumn;
		}
		
		return implode(', ', $tColumns);
	} 
	
	/**
	 * process array of where paramethers and 
	 * convert to array of paramethers as strings 
	 * @param array $params
	 * @return array
	 */
	protected function processWhereParams($params) {
		$platform = $this->getAdapter()->getPlatform();
		$whereParams = array();
		foreach($params as $param) {
			$whereParams []= $this->prepareParam($param) ;
		}
		return $whereParams;
	}
	
	/*
	 * process condition array into string
	 * @param array $param
	 */
	protected function prepareParam($param) {
		$platform = $this->getAdapter()->getPlatform();
		if ($param instanceof \Zend\Db\Sql\Expression) {
			$set = $param->getExpression();
		} elseif (is_string($param)) {
			$set = $param;
		} else {
			if (is_object($param[0]) && get_class($param[0]) == 'Zend\Db\Sql\Expression') {
				$expression = $param[0]->getExpression();
				$param[0] = $expression;
			}
			elseif (strpos($param[0], '.') === false) {
				$param[0] = $platform->quoteIdentifierChain($param[0]);
			} else {
				$param[0] = substr_replace($param[0], "`", strpos($param[0], '.')+1, 0).'`';
			}
			$set = $param[0] . ' ' . $param[1] . ' ';
			if (strtolower($param[1]) == 'in') {
				if (is_array($param[2])) {
					$set .= '(';
					$list = array();
					foreach ($param[2] as $par) {
						$list[] = $this->quoteValue($par);
					}
					$set .= implode(',', $list) . ')';
				} else {
					$set .= '(\'' . $this->quoteValue($param[2]) . '\')';
				}
			}
			elseif (isset($param[2])){
				$set .= $this->quoteValue($param[2]);
			} else {
				$set .= 'NULL';
			}
			if(count($param) > 3){
				$set = '('.$set;
				for ($index = 3; $index < count($param); $index++) {
					if(!is_array($param[$index])){
						throw new \Exception('4th and next params must be array');
					}
					$set .= ' '.array_splice($param[$index], 0, 1)[0].' ';
					$set .= $this->prepareParam($param[$index]);
				}
				$set .= ')';
			}
		}
		return $set;
	}
	
	/**
	 * Returns the row counts by the $params
	 *
	 * @param type $params
	 * @return type
	 */
	public function count($params = '') {
		$where = '';
		if (is_array($params))
			$where = $this->buildWhere($params);
		elseif (is_string($params))
			$where = $params;
		
		return $this->query('select count(*) cnt from `'.$this->table.'` '.$this->findJoin.' '.$where. ($this->getGroupBy() ? $this->getGroupBy() : '').' '.($this->getHaving() ? $this->getHaving() : ''))->current()->cnt;
	}
	
	/*
	 * set group by closer
	 * @param string $having
	 */
	public function setGroupBy($group)
	{
		$this->groupBy = $group;
	}

	/*
	 * get group by closer
	 * 
	 * @return string
	 */
	public function getGroupBy()
	{
		return $this->groupBy;
	}
	
	/**
	 * set having closer
	 * 
	 * @param string $having
	 */
	public function setHaving($having)
	{
		$this->having = $having;
	}

	/**
	 * get having closer
	 * 
	 * @return string $having
	 */
	public function getHaving()
	{
		return $this->having;
	}
	
	/**
	 * creates item, sets id.
	 *
	 * @param array $params
	 * @return id
	 */
	public function create($params) {
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
		return $this->{static::ID_COLUMN};
	}

	/**
	 * sets Id. Checks whether entry exists.
	 *
	 * @param int $id
	 * @returns item
	 */
	public function setId($id) {
		$this->{static::ID_COLUMN } = $id;
		$item = $this->get($id);

		foreach($item as $field => $value) {
			if(property_exists($this, $field)) {
				$this->$field = $value;
			}
		}

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
		$row = $this->select(array(static::ID_COLUMN => $id))->current();
		if(isset($row->{static::ID_COLUMN}) && $row->{static::ID_COLUMN} > 0){
			$row->{static::ID_COLUMN} = (int)$row->{static::ID_COLUMN};
		}
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
		$this->update($data, array(static::ID_COLUMN  => $this->id));
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
		if(is_array($where) && isset($where[static::ID_COLUMN]) && is_numeric($where[static::ID_COLUMN])) {
			$this->cacheDelete($where[static::ID_COLUMN]);
		}
		$this->cacheDelete('list');
	}


	/**
	 * deletes record by id, removes cached data
	 * 
	 * @param mixed $id
	 * @returns altered rows
	 */
	public function deleteById($id) {
		$rowsAffected = $this->delete(array(static::ID_COLUMN => $id));
		$this->cacheDelete($id);
		$this->cacheDelete('list');

		return $rowsAffected;
	}
	
	/**
	 * deletes item
	 *
	 * @param Where|\Closure|string|array $where: Item ID or expression
	 * @return bool: true on OK, false on item not found
	 */
	public function delete($where) {
		if(is_numeric($where)) {
			$result = parent::delete(array(static::ID_COLUMN => $where));
			if($result)
				$this->cacheDelete($where);
		}
		else {
			$result = parent::delete($where);
		}

		return (bool)$result;
	}

	/**
	 * get cached value
	 *
	 * @param string $name
	 * @return string
	 */
	/**
	 * get cached value
	 *
	 * @param string $name
	 * @return string
	 */
	public function cacheGet($key) {
		//\Application\Lib\Logger::write("AppTable::cacheGet($key [{$this->table}])");
		return self::$redis->get('table.'.$this->table.'.'.$key);
	}

	/**
	 * assigns a value to a specified cached param
	 *
	 * @param string $name param name
	 * @param string $value param value
	 * @param int $timeout TTL in seconds
	 * @param int $try
	 * @return bool true on success, false on fail MAX_TRIES times
	 */
	public function cacheSet($key, $value, $timeout = 0, $try=0) {
		//\Application\Lib\Logger::write("AppTable::cacheSet($key [{$this->table}])");
		return self::$redis->set('table.'.$this->table.'.'.$key, $value, $timeout, $try);
	}

	/**
	 * deletes cached value
	 *
	 * @param string $key
	 */
	public function cacheDelete($key) {
		//\Application\Lib\Logger::write("AppTable::cacheDelete($key [{$this->table}])");
		return self::$redis->deleteCache('table.'.$this->table.'.'.$key);
	}

	/**
	 * deletes cached values with keys that start with name
	 *
	 * @param string $keyMask
	 */
	public function cacheDeleteByMask($keyMask) {
		return self::$redis->deleteByMask('table.'.$this->table.'.'.$keyMask);
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
		$params = (array)$params;
		foreach($params as $key => $field) {
			if(!in_array($key, $this->goodFields)) {
				unset($params[$key]);
			}
			else {
				if($field===false){
					$params[$key] = 0;
				}
			}
		}
		return $params;
	}

	/**
	 * returns rows from db with specified id
	 *
	 * @param array $ids
	 * @return \ArrayObject
	 */
	public function mget($ids) {
		$keys = [];
		foreach($ids as $id) {
			$keys[]='table.'.$this->table.'.'.$id;
		}
		$values = self::$redis->mget($keys);
		$result = [];
		foreach($ids as $num => $id) {
			if(isset($values[$num]) && !empty($values[$num])) {
				$result[$id] = $values[$num];
			}
			else {
				$result[$id] = $this->get($id);
			}
		}

		return $result;
	}

	public function setFindFields($fields) {
		$this->findFields = $fields;
		return $this;
	}

	public function setFindJoin($join) {
		$this->findJoin = $join;
		return $this;
	}

	public function save() {
		$data = (array) $this;
		if ($this->{static::ID_COLUMN} > 0) {
			$this->set($data);
		} else {
			unset($data[static::ID_COLUMN]);
			$this->create($data);
		}
		$this->getUncached($this->{static::ID_COLUMN});
		return $this->{static::ID_COLUMN};
	}

	public function refresh() {
		$row = $this->getUncached($this->id);
		$this->setId($this->id);
	}

}
