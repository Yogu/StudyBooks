<?php
defined('IN_APP') or die;

class Node extends Model {
	public $id;
	public $parentID;
	public $order;
	public $depth;
	public $isLeaf;
	public $type;
	public $createTime;
	public $editTime;
	public $title;
	
	private $content;
	private $dbTitle;
	
	public static function table() {
		static $table;
		if (!isset($table)) {
			$table = new Table("Node", "Nodes", array(
				'id',
				'parentID',
				'order',
				'isLeaf',
				'depth',
				'type',
				'createTime' => ':time',
				'editTime' => ':time',
				'title'));
		}
		return $table;
	}
	
	public function __construct($data = null) {
		parent::__construct($data);
		$this->dbTitle = $this->title;
	}
	
	public static function getByID($id) {
		return Query::from(self::table())
			->whereEquals('id', (int)$id)
			->first();
	}
	
	/**
	 * Treats the whole tree as a list and inserts this node in the given depth after a reference
	 * node, keeping the depth of every other node.
	 * 
	 * Example: let the letters be nodes, this method can change
	 * 
	 * A           A
	 *  B    to     B
	 *   C   ==>    N
	 *  D            C
	 *              D
	 *              
	 * When adding N after B with secondary depth (depth = 2). Note that C has changed its parent.
	 * 
	 * TODO: Make this function shorter.
	 * 
	 * @param Node $reference the node after which to add
	 * @param int $depth the target depth of this node
	 */
	public function insertAsElementAfter(Node $reference, $depth) {
		if ($this->isLeaf) {
			if ($reference->isLeaf) {
				$this->parentID = $reference->parentID;
				$this->depth = $reference->depth;
				$this->insertAt($reference->order + 1);
			} else {
				$this->parentID = $reference->id;
				$this->depth = $reference->depth + 1;
				$this->insertAt(0);
			}
		} else {
			// Can't create children of leaves
			if ($reference->isLeaf)
				$depth = min($depth, $reference->depth);
			else
				$depth = min($depth, $reference->depth + 1);
			$this->depth = $depth;
			
			if ($depth > $reference->depth) { // Reference is NO leaf (see above)
				$this->parentID = $reference->id;
				
				// Don't use insertAt(0), this would move the new children of this node
				$this->order = 0;
				$this->insert(); 
				
				// Leaf-children of reference become children of this node
				DataBase::query(
					"UPDATE ".DataBase::table('Nodes')." ".
					"SET parentID = #0 ".
					"WHERE parentID = #1 ".
						"AND isLeaf ",
					array($this->id, $reference->id));
			} elseif ($depth == $reference->depth) {
				$this->parentID = $reference->parentID;
				$this->insertAt($reference->order + 1);
					
				if ($reference->isLeaf) {
					// Leaf-children of parent, beginning below reference, become children of new node
					DataBase::query(
						"UPDATE ".DataBase::table('Nodes')." ".
						"SET parentID = #0 ".
						"WHERE parentID = #1 ".
							"AND isLeaf ".
							"AND `order` > #2",
						array($this->id, $this->parentID, $reference->order));
				} else { // reference is not a leaf
					// All children of reference become children of this node
					DataBase::query(
						"UPDATE ".DataBase::table('Nodes')." ".
						"SET parentID = #0 ".
						"WHERE parentID = #1",
						array($this->id, $reference->id));
				}
			} else if ($depth >= 0) {
				// A         A
				//  B         B
				//   C   =>  N    <- this node
				//  D         1   <- dummy node has to be created
				// E           C
				//            D
				//           E
	
				$node = $reference;
				// second item of array: order of first following child
				if ($reference->getChildren()->count())
					$nodesWithFollowingChildren = array(array($reference, 0));
				else
					$nodesWithFollowingChildren = array();
				$lastNode = null;
				while ($node && $node->depth > $depth) {
					// In example, B has following siblings, thus A has a following child
					if ($lastNode && (count($nodesWithFollowingChildren) || $lastNode->hasFollowingSiblings())) {
						array_unshift($nodesWithFollowingChildren, array($node, $lastNode->order + 1));
					}
					
					$lastNode = $node;
					$node = Query::from(self::table())
						->whereEquals('id', $node->parentID)
						->first();
				}
				$previousSibling = $node;
				
				if ($previousSibling == null)
					throw new RuntimeException('Assertion failed: $previousSibling is null, tree seems to be corrupt');
					
				// Insert after A in example
				$this->parentID = $previousSibling->parentID;
				$this->insertAt($previousSibling->order + 1);
				
				// Add dummy nodes
				$lastID = $this->id;
				// Dummy is only needed for nodes which _contain_ nodes with following siblings
				foreach ($nodesWithFollowingChildren as $arr) {
					$original = $arr[0];
					$order = $arr[1];
					$dummy = clone $original;
					$dummy->parentID = $lastID;
					$dummy->order = 0;
					$dummy->insert();
					$lastID = $dummy->id;
					
					// Make C a child of 1
					DataBase::query(
						"UPDATE ".DataBase::table('Nodes')." ".
						"SET parentID = #0 ".
						"WHERE parentID = #1 ".
							"AND `order` >= #2 ",
						array($dummy->id, $original->id, $order));
				}
					
				// Make D a child of N
				DataBase::query(
					"UPDATE ".DataBase::table('Nodes')." ".
					"SET parentID = #0 ".
					"WHERE parentID = #1 ".
						"AND `order` > #2",
					array($this->id, $previousSibling->id, $reference->order));
			}
		}
	}
	
	public function insertAsLast() {
		$this->order = self::getMaxOrder($this->parentID);
		$this->insert();
	}

	/**
	 * Inserts this node at the specified order, moving following children of the same parent down
	 * 
	 * This method does NOT check whether $order is within the allowed range.
	 * 
	 * @param int $order the position to insert at
	 */
	public function insertAt($order) {
		// Move all following nodes downwards
		DataBase::query(
			"UPDATE ".DataBase::table('Nodes')." ".
			"SET `order` = `order` + 1 ".
			"WHERE parentID = #0 ".
				"AND `order` >= #1 ",
			array($this->parentID, $order));
			
		$this->order = $order;
		$this->insert();
	}
	
	private function insert() {
		DataBase::query(
			"INSERT INTO ".DataBase::table('Nodes')." ".
			"SET parentID = #0, isLeaf = #1, type = #2, depth = #3, `order` = #4, title = #5, ".
				"createTime = NOW(), editTime = NOW() ",
			array($this->parentID, $this->isLeaf, $this->type, $this->depth, $this->order, 
				$this->title));
		$this->id = DataBase::getInsertID();
		$this->createTime = time();
		$this->editTime = time();
		$this->dbTitle = $this->title;
		
		if ($this->content) {
			$this->content->setNodeID($this->id);
			$this->content->insert(); 
		}
	}
	
	public function saveChanges() {
		if ($this->title != $this->dbTitle) {
			DataBase::query(
				"UPDATE ".DataBase::table('Nodes')." ".
				"SET title = #0, editTime = NOW() ".
				"WHERE id = #1",
				array($this->title, $this->id));
			$this->editTime = time();
			$this->dbTitle = $this->title;
		}
	}
	
	public function getContent() {
		if (!isset($this->content)) {
			switch ($this->type) {
				case 'text':
					if ($this->id)
						$this->content = NodeText::forNode($this);
					else
						$this->content = new NodeText();
					break;
				default:
					return null;
			}
		}
		return $this->content;
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
		if (!$this->id)
			throw new Exception("This node is not inserted");
			
		return Query::from(self::table())
			->whereEquals('parentID', $this->id)
			->orderBy('order');
	}
	
	public function createChildHeading($title) {
		if (!$id)
			throw new Exception("This node is not inserted");
		
		$node = new Node();
		$node->parentID = $id;
		$node->type = 'heading';
		$node->isLeaf = false;
		$node->title = $title;
		$node->depth = $this->depth + 1;
		return $node;
	}
	
	public function createChildLeaf($type) {
		if (!$id)
			throw new Exception("This node is not inserted");
		
		$node = new Node();
		$node->parentID = $id;
		$node->type = $type;
		$node->isLeaf = true;
		$node->depth = $this->depth + 1;
		return $node;
	}
	
	public static function createRootNode($title) {
		$node = new Node();
		$node->parentID = 0;
		$node->type = 'heading';
		$node->isLeaf = false;
		$node->title = $title;
		$node->depth = 0;
		return $node;
	}
	
	private static function getMaxOrder($parentID) {
		$result = DataBase::query(
			"SELECT MAX(`order`) AS maxOrder ".
			"FROM ".DataBase::table('Nodes')." ".
			"WHERE parentID = #0",
			array($parentID));
		if ($result) {
			list($maxOrder) = mysql_fetch_array($result);
			return $maxOrder;
		} else
			return null;
	}
	
	private function hasFollowingSiblings() {
		return $this->order < self::getMaxOrder($this->parentID);
	}
	
	public function updateDepthRecursively() {
		self::updateAllDepths($this->id, $this->depth + 1);
	}
	
	public static function updateAllDepths($id = 0, $rootDepth = 0) {
		DataBase::query(
			"UPDATE ".DataBase::table('Nodes')." ".
			"SET depth = '$rootDepth' ".
			"WHERE parentID = '$id'");
		
		$nodes = Query::from(self::table())
			->whereEquals('parentID', $id)
			->select('depth', 'id')
			->all();
		foreach ($nodes as $node) {
			$node->updateDepthRecursively();
		}
	}
}


