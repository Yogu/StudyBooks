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
	public $title = '';
	
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
				$reference->getChildren()
					->where('isLeaf')
					->update(array(
						'parentID' => $this->id,
						'depth!' => '$depth + 1'));
			} elseif ($depth == $reference->depth) {
				$this->parentID = $reference->parentID;
				$this->insertAt($reference->order + 1);
					
				if ($reference->isLeaf) {
					// Leaf-children of parent, beginning below reference, become children of new node
					$reference->getSucceedingSiblings()
						->where('isLeaf')
						->update(array(
							'parentID' => $this->id,
							'$depth = $depth + 1'));
				} else { // reference is not a leaf
					// All children of reference become children of this node
					$reference->getChildren()
						->update(array('parentID' => $this->id));
				}
			} else if ($depth >= 0) {
				// P         P    <- preceding sibling
				//  A         A
				//   R         R   <- reference
				//    C   => N     <- this node
				//   D        1    <- dummy node has to be created
				//  E          2   <-  "
				// F            C  <- following child of a node
				//             D   <-  "
				//            E    <- following child of preceding sibling 
				//           F
	
				$node = $reference;
				// second item of array: order of first following child
				if ($reference->getChildren()->count())
					$nodesWithFollowingChildren = array(array($reference, 0));
				else
					$nodesWithFollowingChildren = array();
				$lastNode = null;
				while ($node && $node->depth > $depth) {
					$lastNode = $node;
					$node = Query::from(self::table())
						->whereEquals('id', $node->parentID)
						->first();
				
					// In example, B has following siblings, thus A has a following child
					if ($node && (count($nodesWithFollowingChildren) || $lastNode->hasFollowingSiblings())) {
						array_unshift($nodesWithFollowingChildren, array($node, $lastNode->order + 1));
					}
				}
				$precedingSibling = $node;
				
				if ($precedingSibling == null)
					throw new RuntimeException('Assertion failed: $previousSibling is null, tree seems to be corrupt');
					
				// Insert after A in example
				$this->parentID = $precedingSibling->parentID;
				$this->insertAt($precedingSibling->order + 1);
				
				// Clone headings to keep the depths and add succeeding content to the new headings
				$lastID = $this->id;
				foreach ($nodesWithFollowingChildren as $arr) {
					$original = $arr[0];
					$order = $arr[1];
					if ($original == $precedingSibling)
						$clone = $this;
					else {
						$clone = clone $original;
						$clone->parentID = $lastID;
						$clone->order = 0;
						$clone->insert();
					}
					
					// Make C a child of 2 and D a child of 1 and E a child of this node
					$original->getChildren()
						->where('$order > #0', $order)
						->update(array(
							'parentID' => $clone->id,
							'order!' => array('$order - #0', $order)));
				}
			}
		}
	}
	
	public function canDeleteAsElement() {
		if ($this->isLeaf)
			return true;
		
		// Way 1: Children will be moved to preceeding sibling
		$newParent = $this->getPreviousHeading();
		if (!$newParent) {
			// Way 2: This node will be replaced by its children
			$newParent = $this->getParent();
			if (!$newParent || $newParent->type == 'folder')
				return false;
				
			// Depth will be changed, so headings are not allowed
			if ($this->getChildren()->where("NOT isLeaf")->count())
				return false;
		}
			
		return true;
	}
	
	public function deleteAsElement() {
		// TODO: Sometimes, the order fields are not updated correctly.
		if (!$this->isLeaf) {		
			// Way 1: Children will be moved to preceeding sibling
			$newParent = $this->getPreviousHeading();
			if ($newParent) {
				$startOrder = self::getMaxOrder($newParent->id);
				// Append to the end
				$this->getChildren()
					->update(array(
						'parentID' => $newParent->id,
						array('$order = $order + #0', $startOrder)));
			}else {
				// Way 2: This node will be replaced by its children
				$newParent = $this->getParent();
				if (!$newParent || $newParent->type == 'folder')
					return false;
					
				// Depth will be changed, so headings are not allowed
				$hasHeadingChildren = $this->getChildren()
					->where("NOT isLeaf")
					->count() > 0;
				if ($hasHeadingChildren)
					return false;
					
				// Insert children at same position as this node
				$childCount = $this->getChildren()->count();
				$this->getSucceedingSiblings()
					->update('$order = $order + #0', $childCount - 1); // replacing 1 child by $childCount children
				if ($childCount) {
					$this->getChildren()
						->update(array(
							'parentID' => $newParent->id,
							array('$order = $order + #0', $this->order),
							'$depth = $depth - 1'));
				}
			}
		}
			
		parent::delete();
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
		$this->getSiblings()
			->where('$order >= #0', $order)
			->update('$order = $order + 1');
			
		$this->order = $order;
		$this->insert();
	}
	
	private function insert() {
		$this->createTime = time();
		$this->editTime = time();
		$this->insertAll();
		$this->dbTitle = $this->title;
		
		if ($this->content) {
			$this->content->setNodeID($this->id);
			$this->content->insert(); 
		}
	}
	
	public function saveChanges() {
		if ($this->title != $this->dbTitle) {
			$this->update(array('title', 'editTime!' => 'NOW()'));
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
		$this->getPrecedingSiblings()
			->where('$order < #0', $target)
			->update('$order = $order - 1');
			
		// Move D upwards
		parent::update(array('order' => $target));
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
		$this->getSucceedingSiblings()
			->where('$order < #0', $target)
			->update('$order = $order - 1');
			
		// Move B downwards
		parent::update(array('order' => $target));
		$this->order = $target;
	}
	
	public function delete() {
		//    0 1 2 3
		//    A B C D   remove B
		// -> A C D
		
		// Move C and D (in example above) upwards
		$this->getSucceedingSiblings()
			->update('$order = $order - 1');
			
		// Delete B
		parent::delete();
	}
	
	public function getChildren() {
		if (!$this->id)
			throw new Exception("This node is not inserted");
			
		return Query::from(self::table())
			->whereEquals('parentID', $this->id)
			->orderBy('order');
	}
	
	public function getSiblings() {
		return Query::from(self::table())
			->whereEquals('parentID', $this->parentID)
			->orderBy('order');
	}
	
	public function getSucceedingSiblings() {
		return $this->getSiblings()
			->where('$order > #0', $this->order);
	}
	
	public function getPrecedingSiblings() {
		return $this->getSiblings()
			->where('$order < #0', $this->order);
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
	
	public function getParent() {
		if ($this->parentID)
			return self::getByID($this->parentID);
		else
			return null;
	}
	
	public function getPrevious() {
		if ($this->order > 0) {
			return $this->getSiblings()
				->whereEquals('order', $this->order - 1)
				->first();
		} else {
			return $this->getParent();
		}
	}
	
	public function getPreviousHeading() {
		if ($this->order > 0) {
			$heading = $this->getSiblings()
				->where('$order < #0', $this->order)
				->where('NOT $isLeaf')
				->orderByDescending('order')
				->first();
			if ($heading)
				return $heading;
		}
		return null;
	}
	
	public function getNextSibling() {
		return Query::from(self::table())
			->whereEquals('order', $this->order + 1)
			->first();
	}
	
	public function getNext($includeChildren = true) {
		$obj = null;
			
		if (!$this->isLeaf)
			$obj = $this->getChildren()->first();
		
		if (!$obj) {
			$obj = $this->getNextSibling();
			if (!$obj) {
				$parent = $this->getParent();
				if ($parent)
					$obj = $parent->getNext(false);
			}
		}
		return $obj;
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
		return Query::from(self::table())
			->whereEquals('parentID', $parentID)
			->max('order');
	}
	
	private function hasFollowingSiblings() {
		return $this->order < self::getMaxOrder($this->parentID);
	}
	
	public function updateDepthRecursively() {
		self::updateAllDepths($this->id, $this->depth + 1);
	}
	
	public static function updateAllDepths($id = 0, $rootDepth = 0) {
		Query::from(self::table())
			->where('parentID', $id)
			->update('depth', $rootDepth);
		
		$nodes = Query::from(self::table())
			->whereEquals('parentID', $id)
			->select('depth', 'id');
		foreach ($nodes as $node) {
			$node->updateDepthRecursively();
		}
	}
}


