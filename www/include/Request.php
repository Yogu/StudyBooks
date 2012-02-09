<?php
defined('IN_APP') or die;

class Request {
	public $url;
	public $internalURL;
	public $absoluteURL;
	public $get;
	public $post;
	public $cookies;
	public $files;
	public $ip;
	public $userAgent;
	public $referer;
	public $controller;
	public $action;
	public $data;
	public $startTime;
	public $isHTTPS;
	
	/**
	 * @var Session
	 */
	public $session;
	/**
	 * @var User
	 */
	public $user;
	
	public static function createRequest() {
		$request = new Request();
		$request->startTime = microtime(true);
		$request->url = $_SERVER['REQUEST_URI'];
		$request->internalURL = substr($request->url, strlen(ROOT_URL));
		$request->isHTTPS = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] &&
			$_SERVER['HTTPS'] != 'off' ? 's' : '';
		$request->urlPrefix = 'http'.
			($request->isHTTPS ? 's' : '').'://'.
			$_SERVER['SERVER_NAME'].
			($_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] : '').
			substr($request->url, 0, strlen(ROOT_URL));
		$request->absoluteURL = $request->urlPrefix.$request->internalURL;
		$request->get = $_GET;
		$request->post = $_POST;
		$request->cookies = new CookieManager($_COOKIE);
		$request->files = $_FILES;
		$request->ip = $_SERVER['REMOTE_ADDR'];
		$request->userAgent = $_SERVER['HTTP_USER_AGENT'];
		$request->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$request->load();
		return $request;
	}
	
	public function load() {
		$this->controller = trim($this->get['controller']);
		$this->action = trim($this->get['action']);
		if (!$this->action)
			$this->action = 'index';
			
		// Session
		if ($this->cookies->session) {
			$this->session = Session::getVaildByKey($this->cookies->session);
			if ($this->session) {
				$this->session->hit();
				$this->user = $this->session->user;
			} 
		}
		
		$this->data = new stdClass();
	}
	
	public function getResponse() {
		if ($this->controller) {
			$controllerObj = Controller::getController($this, $this->controller);
			if ($controllerObj) {
				$method = array($controllerObj, $this->action);
				if (is_callable($method))
					return call_user_func($method);
				else {
					$this->data->details =
						'Controller '.$this->controller.' has no action called '.$this->action;
					return new View($this, '404', 'errors');
				}
			} else {
				$this->data->details =
					'Controller '.$this->controller.' does not exist';
				return new View($this, '404', 'errors');
			}
		} else
			return new View($this, '404', 'errors');
	}
}

?>
