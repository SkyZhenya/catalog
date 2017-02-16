<?php

namespace Application\Lib\Paginator\Adapter;

use Zend\Paginator\Adapter\AdapterInterface;
use CodeIT\Model\AppTable;
//use Application\Model\AppTable;

class AppTableAdapter implements AdapterInterface
{
	protected $count;
	
	protected $model;
	
	protected $params;
	
	protected $orderBy = false;

	public function __construct(AppTable $model, $params = [], $orderBy = false) {
		$this->model = $model;
		$this->params = $params;		
		$this->orderBy = $orderBy;
	}
	
	public function getItems($offset, $itemCountPerPage) {
		$count = ($this->count !== null) ? null : 0;
		return $this->getModel()->find($this->params, $itemCountPerPage, $offset, $this->orderBy, $count);
	}
	
	public function count() {
		if ($this->count === null)
			$this->count = $this->getModel()->count($this->params);

		return $this->count;
	}
	
	protected function getModel() {
		return $this->model;
	}
}