<?php
defined('IN_APP') or die;

class Table {
	public $className;
	public $tableName;
	public $columns;
	public $autoAssignEnabled;
	
	public function __construct($className, $tableName, array $columns) {
		foreach ($columns as $k => $v) {
			if (is_int($k)) {
				unset($columns[$k]);
				$columns[$v] = $v;
			}
		}
		
		$this->className = $className;
		$this->tableName = $tableName;
		$this->columns = $columns;
	}
}
