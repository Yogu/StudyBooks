<?php
defined('IN_APP') or die;

class Node {
	public $id;
	public $parentID;
	public $isLeaf;
	public $type;
	public $createTime;
	public $editTime;
	public $title;
	
	public static function getList($parentID, $condition = '', $offset = 0, $count = null, $params = array()) {
		if ($count === null)
			$count = 2147483647;
		$result = DataBase::query(
			"SELECT id, isLeaf, type, title, ".
				"UNIX_TIMESTAMP(createTime) AS createTime, ".
				"UNIX_TIMESTAMP(editTime) AS editTime ".
			"FROM ".DataBase::table('Nodes')." ".
			"WHERE parentID = #0".($condition ? " AND $condition " : ' ').
			"LIMIT $offset, $count", 
			array_merge(array($parentID), $params));
		$list = array();
		while ($item = mysql_fetch_object($result)) {
			$node = new Node();
			$node->id = $item->id;
			$node->parentID = $parentID;
			$node->isLeaf = $item->isLeaf;
			$node->type = $item->type;
			$node->createTime = $item->createTime;
			$node->editTime = $item->editTime;
			$node->title = $item->title;
			$list[] = $node;
		}
		return $list;
	}
	
	public static function getSingle($condition, $params = null) {
		$list = self::getList($condition, 0, 1, $params);
		return count($list) ? $list[0] : null;
	}
	
	public static function getByID($id) {
		return self::getSingle('WHERE id = #0', (int)$id);
	}
	
	public function insertAsLast() {
		$this->order = self::getMaxOrder($this->parentID);
		$this->insert();
	}
	
	private function insert() {
		DataBase::query(
			"INSERT INTO ".DataBase::table('Nodes')." ".
			"SET parentID = #0, isLeaf = #1, type = #2, createTime = NOW(), editTime = NOW() ",
			array($this->parentID, $this->isLeaf, $this->type));
		$this->id = DataBase::getInsertID();
		$this->createTime = time();
		$this->editTime = time();
	}
	
	public function setTitle($title) {
		DataBase::query(
			"UPDATE ".DataBase::table('Nodes')." ".
			"SET title = #0, editTime = NOW() ".
			"WHERE id = #1",
			array($title, $this->id));
		$this->title = $title;
		$this->editTime = time();
	}
	
	public function moveUp($delta = 1) {
		if (!$this->id)
			throw new Exception("Must be inserted first");
		
		//    0 1 2 3
		//    A B C D   move D up with delta = 2
		// -> A D B C 
		$target = $this->order - $delta;
		if ($target < 0)
			$target = 0;
			
		// Move B and C (in exmample above) downwards
		DataBase::query(
			"UPDATE ".DataBase::table('Nodes')." ".
			"SET order = order + 1 ".
			"WHERE parentID = #0".
				"AND order >= #1 AND order < #2 ",
			array($this->parentID, $target, $this->order));
			
		// Move D upwards
		DataBase::query(
			"UPDATE ".DataBase::table('Nodes')." ".
			"SET order = $target ".
			"WHERE id = #0",
			array($this->id));
		$this->order = $target;
	}
	
	public function moveDown($delta = 1) {
		if (!$this->id)
			throw new Exception("Must be inserted first");
		
		//    0 1 2 3
		//    A B C D   move B down with delta = 2
		// -> A C D B 
		$target = $this->order + $delta;
		$max = self::getMaxOrder($this->parentID);
		$target = min($target, $max);
			
		// Move C and D (in exmample above) upwards
		DataBase::query(
			"UPDATE ".DataBase::table('Nodes')." ".
			"SET order = order - 1 ".
			"WHERE parentID = #0".
				"AND order > #1 AND order <= #2 ",
			array($this->parentID, $this->order, $target));
			
		// Move B downwards
		DataBase::query(
			"UPDATE ".DataBase::table('Nodes')." ".
			"SET order = $target ".
			"WHERE id = #0",
			array($this->id));
		$this->order = $target;
	}
	
	public function delete() {
		//    0 1 2 3
		//    A B C D   remove B
		// -> A C D
		
		// Move C and D (in example above) upwards
		DataBase::query(
			"UPDATE ".DataBase::table('Nodes')." ".
			"SET order = order - 1 ".
			"WHERE parentID = #0".
				"AND order > #",
			array($this->parentID, $this->order));
			
		// Delete B
		DataBase::query(
			"DELETE FROM ".DataBase::table('users')." ".
			"WHERE id = #0",
			$this->id);
		unset($this->id);
	}
	
	public function getChildren() {
		if (!$id)
			throw new Exception("This node is not inserted");
			
		return self::getList($this->id);
	}
	
	public function createChildHeading($title) {
		if (!$id)
			throw new Exception("This node is not inserted");
		
		$node = new Node();
		$node->parentID = $id;
		$node->type = 'heading';
		$node->isLeaf = false;
		$node->title = $title;
		return $node;
	}
	
	public function createChildLeaf($type) {
		if (!$id)
			throw new Exception("This node is not inserted");
		
		$node = new Node();
		$node->parentID = $id;
		$node->type = $type;
		$node->isLeaf = true;
		return $node;
	}
	
	public static function createRootNode($title) {
		$node = new Node();
		$node->parentID = 0;
		$node->type = 'heading';
		$node->isLeaf = false;
		$node->title = $title;
		return $node;
	}
	
	private static function getMaxOrder($parentID) {
		$result = DataBase::query(
			"SELECT MAX(order) AS maxOrder ".
			"FROM ".DataBase::table('Nodes')." ".
			"WHERE parentID = #0",
			array($parentID));
		if ($result) {
			list($maxOrder) = mysql_fetch_array($result);
			return $maxOrder;
		} else
			return null;
	}
}

