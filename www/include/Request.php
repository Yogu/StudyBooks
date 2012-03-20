<?php
defined('IN_APP') or die;

class Request {
	public $url;
	public $internalURL;
	public $absoluteURL;
	public $get;
	public $post;
	public $method;
	public $cookies;
	public $files;
	public $ip;
	public $userAgent;
	public $referer;
	public $parameters;
	public $controller;
	public $action;
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
		if (($p = strpos($request->internalURL, '?')) !== false)
			$request->internalURLTrunk = substr($request->internalURL, 0, $p);
		else
			$request->internalURLTrunk = $request->internalURL;
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
		$request->method = strtoupper($_SERVER['REQUEST_METHOD']);
		$request->cookies = new CookieManager($_COOKIE);
		$request->files = $_FILES;
		$request->ip = $_SERVER['REMOTE_ADDR'];
		$request->userAgent = $_SERVER['HTTP_USER_AGENT'];
		$request->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$request->load();
		return $request;
	}
	
	public function load() {
		$this->parameters = Router::resolve($this->internalURLTrunk);
		$this->parameters = array_merge($this->get, $this->parameters);
		$this->controller = trim($this->parameters['controller']);
		$this->action = trim($this->parameters['action']);
			
		// Session
		if ($this->cookies->session && (!defined('NO_DB') || !NO_DB)) {
			$this->session = Session::getVaildByKey($this->cookies->session);
			if ($this->session) {
				$this->session->hit();
				$this->user = $this->session->user;
			} 
		}
		
		$this->data = new stdclass();
		$this->data->config = Config::$config;
		$this->data->rootURL = ROOT_URL;
		$this->data->request = $this;
	}
	
	public function getResponse() {
		if ($this->controller) {
			$controllerObj = Controller::getController($this, $this->controller);
			if ($controllerObj) {
				// Methods beginning with an underscore are no actions, e.g. constructors
				if (strlen($this->action) && $this->action[0] != '_')
					$method = array($controllerObj, $this->action);
				else
					$method = null;
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
	
	public function param($name) {
		if (isset($this->parameters[$name]))
			return $this->parameters[$name];
		else
			return null;
	}
	
	public function post($name) {
		if (isset($this->post[$name]))
			return $this->post[$name];
		else
			return null;
	}
}


