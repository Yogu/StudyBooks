<?php
defined('IN_APP') or die;

class Controller {
	/**
	 * @var Request
	 */
	public $request;
	public $data;
	
	public function __construct(Request $request) {
		$this->request = $request;
	}
	
	protected function redirection($url = null, $code = 303) {
		return new Redirection($this->request, $url, $code);
	}
	
	protected function view($action = '', $controller = '') {
		if (!$action)
			$action = $this->request->action;
		if (!$controller)
			$controller = $this->request->controller;
		return new View($this->request, $action, $controller);
	}
	
	public static function getController(Request $request, $name) {
		$className = $name.'Controller';
		if (class_exists($className)) {
			$reflection = new ReflectionClass($className);
			if ($reflection->isSubclassOf('Controller'))
				return new $className($request);
		}
	}
	
	protected function requireLogin() {
		if (!$this->request->session)
			return $this->view('login', 'account');
	}
	
	protected function requirePoster() {
		if (!$this->request->session)
			return $this->view('login', 'account');
		if ($this->request->user->role == 'guest')
			return new View($this->request, '403', 'errors', 403);
	}
	
	protected function requireAdmin() {
		if (!$this->request->session)
			return $this->view('login', 'account');
		if ($this->request->user->role != 'admin')
			return new View($this->request, '403', 'errors', 403);
	}
}

?>
