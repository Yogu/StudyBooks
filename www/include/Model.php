<?php 
defined('IN_APP') or die;

class Model {
	public function save() {
		if ($this->id)
			$this->update();
		else
			$this->insert();
	}
	
	public function insertQuery($values) {
		
	}
	
	/**
	 * Returns the formatted table name (DataBase::table() already called)
	 */
	public abstract function table();
}

?>