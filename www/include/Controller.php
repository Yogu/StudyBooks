<?php
defined('IN_APP') or die;

class Controller {
	/**
	 * @var Request
	 */
	public $request;
	public $data;

	private static $controllers;

	public static function getControllers() {
		if (!isset(self::$controllers)) {
			$folder = ROOT_PATH."controllers";
			$dir = dir($folder);
			$controllers = array();
			while ( ($file = $dir->read()) !== false) {
				$info = pathinfo($file);
				if ($info['extension'] == 'php') {
					$fileName = $info['filename'];
					$controllers[strtolower($fileName)] = $fileName;
				}
			}
			$dir->close();
			self::$controllers = $controllers;
		}
		return self::$controllers;
	}

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
		$controllers = self::getControllers();
		// Correct case
		$className = $controllers[strtolower($className)];
		if (isset($className)) {
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
