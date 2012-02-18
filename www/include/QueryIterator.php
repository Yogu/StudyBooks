<?php
defined('IN_APP') or die;

class QueryIterator implements Iterator {
	private $query;
	private $sql;
	private $result;
	private $index;
	private $obj;
	private $endReached;
	
	public function __construct(Query $query) {
		$this->query = $query;
		$this->sql = $query->getSQL();
		$this->rewind();
	}
	
	public function current() {
		if ($this->endReached)
			return null;
		
		if (!$this->obj) {
			$result = $this->getResult();
			$arr = mysql_fetch_array($result);
			if ($arr)
				$this->obj = $this->query->getTable()->createObjectFromArray($arr);
			else {
				$this->obj = null;
				$this->endReached = true;
			}
		}
		return $this->obj;
	}
	
	public function key() {
		return $this->index;
	}
	
	public function valid() {
		return $this->current() != null;
	}
	
	public function next() {
		$this->index++;
		$this->obj = null;
	}
	
	public function rewind() {
		$this->index = 0;
		$this->result = null;
		$this->endReached = false;
	}
	
	private function getResult() {
		if (!$this->result)
			$this->result = DataBase::query($this->sql);
		return $this->result;
	}
}