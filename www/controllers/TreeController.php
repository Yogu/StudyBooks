<?php
defined('IN_APP') or die;

class TreeController extends Controller {
	public function index() {
		if ($r = $this->requireLogin()) return $r;
		
		$this->data->nodes = $this->loadNodes(0, false);
		
		return $this->view();
	}
	
	public function details() {
		if ($r = $this->requireLogin()) return $r;
		
		$id = $this->request->parameters['id'];
		$node = Node::getByID($id);
		if (!$node || $node->type == 'folder')
			$this->redirection('index');
			
		$this->data->book = $node;
		$this->data->nodes = $this->loadNodes($id, false);
		
		return $this->view();
	}
	
	public function edit() {
		if ($r = $this->requirePoster()) return $r;
		
		$book = Node::getByID($this->request->parameters['id']);
		if (!$book || $book->type == 'folder')
			$this->redirection('index');

		// The node to edit
		$node = Node::getByID($this->request->parameters['node']);
		if (!$node)
			return $this->redirection('details', array('id' => $book->id));
		
		if ($this->request->method == 'POST') {
			if (!isset($this->request->post['cancel'])) {
				$this->readPostField('title', $node->title);
				switch ($node->type) {
					case 'text':
						$this->readPostField('text', $node->getContent()->text);
						$node->getContent()->saveChanges();
						break;
				}
				$node->saveChanges();
			}
			
			return $this->redirectToURL(Router::getURL(
				array('id' => $book->id, 'controller' => 'Tree', 'action' => 'details')).'#n'.$node->id);
		}
			
		$this->data->edit = $node;
		$this->data->nodes = $this->loadNodes($book->id, true);
		$this->data->book = $book;
		
		return $this->view('details');
	}
	
	public function add() {
		if ($r = $this->requirePoster()) return $r;
		
		$book = Node::getByID($this->request->parameters['id']);
		if (!$book || $book->type == 'folder')
			$this->redirection('index');

		// The after which to add the new node
		$reference = Node::getByID($this->request->param('after'));
		if (!$reference)
			$this->redirection('details', 'Tree', 303, array('id' => $book->id));
			
		$this->data->nodes = $this->loadNodes($book->id, true);
		
		// TODO: work in progress
		$newNode = new Node();
		$type = $this->request->param('type');
		switch ($type) {
			case 'heading1':
			case 'heading2':
			case 'heading3':
			case 'heading4':
				$relativeDepth = (int)substr($type, strlen('heading'));
				$newDepth = $book->depth + $relativeDepth;
				$newNode->type = 'heading';
				$newNode->isLeaf = false;
				$newNode->title = $this->request->post('title');
				break;
			case 'text':
				if ($reference->isLeaf)
					$newDepth = $reference->depth;
				else
					$newDepth = $reference->depth + 1; 
				$newNode->type = 'text';
				$newNode->isLeaf = true;
				$newNode->order = $reference->order + 1;
				$content = new NodeText();
				$content->text = $this->request->post('text');
				breaK;
		}

		if ($this->request->method == 'POST') {
			if (isset($this->request->post['cancel'])) {
				return $this->redirectToURL(Router::getURL(
					array('id' => $book->id, 'controller' => 'Tree', 'action' => 'details')).'#n'.$reference->id);
			} else {
				$newNode->insertAsElementAfter($reference, $newDepth);
				if (isset($content)) {
					$content->nodeID = $newNode->id;
					$content->insert();
				}
				return $this->redirectToURL(Router::getURL(
					array('id' => $book->id, 'controller' => 'Tree', 'action' => 'details')).'#n'.$newNode->id);
			}
		}
		
		$this->data->adding = true;
		$this->data->reference = $reference;
		$this->data->newNode = $newNode;
		$this->data->book = $book;
		
		return $this->view('details');
	}
	
	public function updateDepth() {
		if ($r = $this->requireAdmin()) return $r;
		
		Node::updateAllDepths();
		return new TextResponse("Updated depths.");
	}
	
	private function loadNodes($parentID = 0, $onlyOutline = false) {
		$nodes = Query::from(Node::table())
			->whereEquals('parentID', $parentID)
			->orderBy('order')
			->all();
		foreach ($nodes as &$node) {
			if ($node->isLeaf || ($onlyOutline && $node->type == 'book'))
				$node = array($node, array());
			else 
				$node = array($node, $this->loadNodes($node->id, $onlyOutline));
		}
		return $nodes;
	}
	
	private function readPostField($field, &$out) {
		$arr = $this->request->post;
		if (isset($arr[$field]) && trim($arr[$field]))
			$out = trim($arr[$field]);
	}
}
