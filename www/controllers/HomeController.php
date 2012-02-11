<?php
defined('IN_APP') or die;

class HomeController extends Controller {
	public function index() {
		if ($r = $this->requireLogin()) return $r;
		
		return $this->view();
	}
	
	public function imprint() {
		return $this->view();
	}
}


