<?php 
defined('IN_APP') or die;

abstract class Model {
	public $id;
	
	public function __construct($data = null) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$this->$k = $v;
			}
		}
	}
	
	public static function getByID($id) {
		return self::query()
			->whereEquals('id', (int)$id)
			->first();
	}
	
	public static function query() {
		return Query::from(static::table());
	}
	
	protected function updateFields($fields, $params = null) {
		if (is_array($fields))
			$fields = $this->prepareFieldArray($fields);
		
		$this->query()
			->whereEquals('id', $this->id)
			->update($fields, $params);
	}
	
	protected function updateAll() {
		$this->updateFields($this->getAllFields());
	}
	
	protected function delete() {
		$this->query()
			->whereEquals('id', $this->id)
			->delete();
		unset($this->id);
	}
	
	protected function insertFields($fields, $params = null) {
		if (is_array($fields))
			$fields = $this->prepareFieldArray($fields);
			
		$this->id = $this->query()
			->insert($fields, $params);
	}
	
	protected function insertAll() {
		$this->insertFields($this->getAllFields());
	}
	
	private function getAllFields() {
		$fields = array();
		foreach (static::table()->columns as $name => $type) {
			if (isset($this->$name))
				$fields[$name] = $this->$name;
		}
		return $fields;
	}
	
	private function prepareFieldArray(array $fields) {
		foreach ($fields as $key => $value) {
			if (is_int($key)) {
				$fields[$value] = $this->$value;
				unset($fields[$key]);
			}
		}
		return $fields;
	}
}

?>