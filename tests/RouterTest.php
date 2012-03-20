<?php

require_once(dirname(__FILE__).'/../www/include/Loader.php');
Loader::initAutoloader();

class RouterTest extends PHPUnit_Framework_TestCase {
	public function testComments() {
		$router = new Router();
		$lines = array("#comment");
		$router->loadFromLines($lines);
		$this->assertEquals(0, count($router->getRules()));
	}
	
	public function testStaticRule() {
		$router = new Router();
		$lines = array("imprint");
		$router->loadFromLines($lines);
		$this->assertEquals(1, count($router->getRules()));
	}
}
