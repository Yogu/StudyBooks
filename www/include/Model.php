<?php 
defined('IN_APP') or die;

class Model {
	public function __construct($data = null) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$this->$k = $v;
			}
		}
	}
}

?>