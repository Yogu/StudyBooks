<?php

class NodeTest extends PHPUnit_Framework_TestCase {
	public function startUp() {
		
	}
	
	public function testCreateRootNode() {
		Node::createRootNode("Title");
		
	}
}
