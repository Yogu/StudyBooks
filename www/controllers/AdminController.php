<?php
defined('IN_APP') or die;

class AdminController extends Controller {
	public function index() {
		if ($r = $this->requireAdmin()) return $r;
		
		return $this->view();
	}
}

?>
