<?php
namespace Application\Model;

class CachedTable extends AppTable {

	/**
	 * @var \Application\Lib\Redis
	 */
	protected static $redis = null;

	/**
	 * creates table and sets id if neccessary
	 * @param string $tableName
	 * @param int $id
	 * @param mixed $databaseSchema
	 * @param ResultSet $selectResultPrototype
	 * @return {\Zend\Db\TableGateway\TableGateway|ResultSet}
	 */
	public function __construct($tableName, $id=null) {
		if(!self::$redis) {
			self::$redis = \Utils\Registry::get('redis');
		}

		parent::__construct($tableName, $id);
	}

	/**
	 * returns row from db with specified id
	 *
	 * @param int $id
	 * @param bool $publicOnly remove private fields
	 * @return \ArrayObject
	 */
	public function get($id, $publicOnly=false) {
		$row = $this->cacheGet($id);

		if(!$row) {
			$lockName = $this->getLockName($id);
			$this->getLock($lockName, false, 10);
			// check for data once more
			$row = $this->cacheGet($id);
			if(!$row) {
				$row = $this->getUncached($id);
				$this->cacheSet($id, $row);
			}
			$this->releaseLock($lockName);
		}

		if($publicOnly) {
			$row = $this->removePrivateFields($row);
		}

		return $row;
	}

	/**
	 * return unique lock name for record id
	 * 
	 * @param int $id
	 */
	private function getLockName($id) {
		return SITE_NAME.'.'.$this->table.'.'.$id;
	}

	/**
	 * returns row from db with specified id
	 *
	 * @param int $id
	 * @return \ArrayObject
	 */
	public function getUncached($id) {
		return parent::get($id);
	}

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
	 * returns rows from db with specified id
	 *
	 * @param array $ids
	 * @param bool $publicOnly remove private fields
	 * @return \ArrayObject
	 */
	public function mget($ids, $publicOnly=false) {
		$keys = [];
		foreach($ids as $id) {
			$keys[]='table.'.$this->table.'.'.$id;
		}
		$values = self::$redis->mget($keys);
		$result = [];
		foreach($ids as $num => $id) {
			if(isset($values[$num]) && !empty($values[$num])) {
				if($publicOnly) {
					$result[$id] = $this->removePrivateFields($values[$num]);
				}
				else {
					$result[$id] = $values[$num];
				}
			}
			else {
				try {
					$result[$id] = $this->get($id, $publicOnly);
				}
				catch(\Exception $e) {
					// echo "Bad id: $id\n";
					$this->delete($id);
				}
			}
		}

		return $result;
	}

	/**
	 * Inserts a record
	 *
	 * @param array $set
	 * @return int last insert Id
	 */
	public function insert($set) {
		parent::insert($set);
		//$this->cacheDelete('list');
		return $this->lastInsertValue;
	}

	/**
	 * searches for items, fetching them by ::get()
	 * 
	 * @param array $params, e.g. arrat('id', '>=', '135')
	 * @param int $limit, set to 0 or false to no limit
	 * @param int $offset
	 * @param string $orderBy
	 * @param int &$total will be set to total count found
	 * @param bool $publicOnly should we return full data or non-private fields only
	 * @return \ArrayObject
	 */
	public function find($params, $limit=0, $offset=0, $orderBy=false, &$total=null, $publicOnly=false) {
		$ids = $this->findSimple($params, $limit, $offset, $orderBy, $total, [static::ID_COLUMN])->toArray();
		$ids = array_column($ids, 'id');
		return $this->mget($ids, $publicOnly);
	}

	/**
	 * Update
	 *
	 * @param  array $params
	 * @param  string|array|closure $where
	 * @param  bool $clearCache
	 * @return int affected rows
	 */
	public function update($params, $where = null, $clearCache=true) {
		$result = parent::update($params, $where);
		if($clearCache && is_array($where) && isset($where['id']) && is_numeric($where['id'])) {
			$this->cacheDelete($where['id']);
		}
		$this->cacheDelete('list');

		return $result;
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
	 * deletes record by id, removes cached data
	 * 
	 * @param mixed $id
	 * @returns altered rows
	 */
	public function deleteById($id) {
		$rowsAffected = parent::delete([static::ID_COLUMN => $id]);
		$this->cacheDelete($id);
		$this->cacheDelete('list');

		return $rowsAffected;
	}

	/**
	 * returns full items list
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param int $total callback value: total items
	 * @return ResultSet
	 */
	public function getList($limit=false, $offset=0, &$total=null) {
		$column = static::ID_COLUMN;
		$select = $this->getSelect($this->table);
		//$select->columns([$column]);
		if($limit !== false) {
			$select->limit($limit);
		}
		if($offset) {
			$select->offset($offset);
		}

		$list = $this->execute($select);
		unset($select);

		$res = array();
		foreach($list as $item) {
			$res[$item->$column] = $this->get($item->$column);
		}

		if(!is_null($total)) {
			$total = $this->query('select count(*) cnt from '.$this->table)->current()->cnt;
		}

		return $res;
	}

	/**
	 * sets data for current id
	 *
	 * @param array $data
	 * @param int $id
	 * @param bool $setDataToObject perform setId() call after update
	 */
	public function set($data, $id=false, $setDataToObject=true) {
		parent::set($data, $id, false);
		$myId = $this->id;
		if($id) {
			$myId = $id;
		}
		$this->cacheDelete($myId);
		if(($myId == $this->id) && $setDataToObject)
			$this->setId($this->id);
	}

}
