<?php
defined('IN_APP') or die;

class TreeController extends Controller {
	public function index() {
		if ($r = $this->requireLogin()) return $r;
		
		$this->data->nodes = $this->loadNodes();
		
		return $this->view();
	}
	
	public function details() {
		if ($r = $this->requireLogin()) return $r;
		
		$id = $this->request->parameters['id'];
		$node = Node::getByID($id);
		if (!$node || $node->type == 'folder')
			$this->redirection('index');
			
		$this->data->book = $node;
		$this->data->nodes = $this->loadNodes($id, true);
		
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
			$this->redirection('details', array('id' => $book->id));
			
		else if ($this->request->method == 'POST') {
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
	
	private function loadNodes($parentID = 0, $onlyOutline = false) {
		$nodes = Query::from(Node::table())
			->whereEquals('parentID', $parentID)
			->orderBy('order')
			->all();
		foreach ($nodes as &$node) {
			if ($node->type == 'book')
				$node = array($node, array());
			else 
				$node = array($node, $this->loadNodes($node->id));
		}
		return $nodes;
	}
	
	private function readPostField($field, &$out) {
		$arr = $this->request->post;
		if (isset($arr[$field]) && trim($arr[$field]))
			$out = trim($arr[$field]);
	}
}
