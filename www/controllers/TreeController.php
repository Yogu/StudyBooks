<?php
defined('IN_APP') or die;

class TreeController extends Controller {
	public function index() {
		if ($r = $this->requireAdmin()) return $r;
		
		$this->data->nodes = $this->loadNodes();
		
		return $this->view();
	}
	
	public function details() {
		if ($r = $this->requireAdmin()) return $r;
		
		$id = $this->request->parameters['id'];
		$this->data->nodes = $this->loadNodes($id);
		
		return $this->view('index');
	}
	
	private function loadNodes($parentID = 0) {
		$nodes = Node::getList($parentID);
		foreach ($nodes as &$node) {
			$node = array($node, $this->loadNodes($node->id));
		}
		return $nodes;
	}
}
