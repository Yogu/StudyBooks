<?php
defined('IN_APP') or die;

class Table {
	public $className;
	public $tableName;
	public $columns;
	public $autoAssignEnabled;
	
	public function __construct($className, $tableName, array $columns) {
		foreach ($columns as $k => &$v) {
			if (is_int($k)) {
				unset($columns[$k]);
				$columns[$v] = $v;
			} else {
				$name = Strings::leftOf($v, ':');
				$type = Strings::rightOfFirst($v, ':');
				if (!$name) {
					$name = $k;
					$v = $name.':'.$type;
				}
			}
		}
		
		$this->className = $className;
		$this->tableName = $tableName;
		$this->columns = $columns;
	}
	
	public function createObjectFromArray($data) {
		// MySQL also returns numeric keys for all the values
		foreach ($data as $k => $v) {
			if (is_int($k))
				unset($data[$k]);
		}
		
		$class = $this->className;
		$obj = new $class($data);
		return $obj;
	}
}
