<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;

class TemplateTable extends AppTable {
	protected	$goodFields = array(
		'id',
		'name',
	);
	
	protected $localFields = array(
		'subject',
		'text',
	);

	public function __construct($id=null) {
		$this->locTable = 'templatelocal';
		parent::__construct('template');
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

			$res = $this->execute($select);
			$this->cacheSet('list', $res);
		}
		return $res;
	}
	
	/**
	* returns row from db with specified slug
	*
	* @param string $name
	* @param int $lang
	* @return \ArrayObject
	*/
	public function getByNameWithLang($name) {
		$key = base64_encode($name);
		$item = $this->cacheGet($key);
		if(!$item) {
	    $row = $this->find(array(
	    	array('name', '=', $name),
	    ), 1, 0)->current();
	    $item = false;
	    if ($row){
				$item = $this->get($row->id);
	    }
			$this->cacheSet($key, $item);
		}

		return $item;
	}
	
	/**
	* returns row from db with specified id
	*
	* @param int $id
	* @return \ArrayObject
	*/
	public function getUncached($id) {
		$item = parent::getUncached($id);
		$localData = $this->getLocalData(array(
			'where'=>array(
					'id'=>$id,
					'lang'=>$this->lang,
			)
		));
		if (isset($localData[0])){
			$localData=$localData[0];
		}
		$item->subject = (isset($localData['subject'])? $localData['subject'] : '');
		$item->text = (isset($localData['text'])? $localData['text'] : '');
    return $item;
	}
	
	/**
	* returns row from db with specified id and localData
	*
	* @param int $id
	* @return \ArrayObject
	*/
	public function fullLocalData($id) {
	  $row = parent::getUncached($id);
	  //get translations
		$select = $this->getSelect($this->locTable);
		$select->where(array(
			'id'=> $id,
		));
		$list = $this->execute($select);
		foreach($list as $locItem){
			foreach ( $this->localFields as $field){
				if (!isset($row->{$field}) || !is_array($row->{$field})){
					//echo 'array';
					$row->{$field} = array();
				}
				//var_dump($row->{$field});
				$row->{$field}[$locItem->lang] = $locItem->$field;
			}
		}
		return $row;
	}
	
	/**
	* sets data for current id
	*
	* @param array $data
	*/
	public function set($data) {
		$this->cacheDelete(base64_encode($this->name));

		$localData = $this->chooseLocalData($data);
    	$this->updateLocData($localData);
    	return parent::set($data);
	}
	
	/**
   * Inserts a record
   *
   * @param array $set
   * @return int last insert Id
   */
	public function insert($set) {
		$localData = $this->chooseLocalData($set);
		$id = parent::insert($set);
		$set = $this->removeUnnecessaryFields($set);
		foreach($localData as $i=>$locItem){
			$localData[$i]['id'] = $id;
		}
		$this->setId($id);
		$this->updateLocData($localData);
		return $id;
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