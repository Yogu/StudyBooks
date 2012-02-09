<?php
defined('IN_APP') or die;

class AdminController extends Controller {
	public function index() {
		if ($r = $this->requireAdmin()) return $r;
		
		return $this->view();
	}
	
	public function clearCache() {
		if ($r = $this->requireAdmin()) return $r;
		
		if ($this->request->post['deleteThumbnails']) {
			Item::deleteThumbnails();
			return $this->redirection();
		}
	
		if ($this->request->post['deleteDisplayImages']) {
			Item::deleteDisplayImages();
			return $this->redirection();
		}
		
		return $this->view();
	}
}

?>
