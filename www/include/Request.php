<?php
defined('IN_APP') or die;

class Request {
	public $rootURL;
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
		$request->rootURL = 
			substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);
		$request->startTime = microtime(true);
		$request->url = $_SERVER['REQUEST_URI'];
		$request->isHTTPS = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] &&
			$_SERVER['HTTPS'] != 'off' ? 's' : '';
		$request->get = self::undoMagicQuotes($_GET);
		$request->post = self::undoMagicQuotes($_POST);
		$request->method = strtoupper($_SERVER['REQUEST_METHOD']);
		$request->cookies = new CookieManager(self::undoMagicQuotes($_COOKIE));
		$request->files = $_FILES;
		$request->ip = $_SERVER['REMOTE_ADDR'];
		$request->userAgent = $_SERVER['HTTP_USER_AGENT'];
		$request->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$request->urlPrefix = 'http'.
			($request->isHTTPS ? 's' : '').'://'.
			$_SERVER['SERVER_NAME'].
			($_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] : '').
			substr($request->url, 0, strlen($request->rootURL));
			
		$request->load();
		return $request;
	}
	
	private static function undoMagicQuotes($arr) {
		if (get_magic_quotes_gpc()) {
		    $process = array(&$arr);
		    while (list($key, $val) = each($process)) {
		        foreach ($val as $k => $v) {
		            unset($process[$key][$k]);
		            if (is_array($v)) {
		                $process[$key][stripslashes($k)] = $v;
		                $process[] = &$process[$key][stripslashes($k)];
		            } else {
		                $process[$key][stripslashes($k)] = stripslashes($v);
		            }
		        }
		    }
		}
		return $arr;
	}
	
	public function load() {
		$this->internalURL = substr($this->url, strlen($this->rootURL));
		if (($p = strpos($this->internalURL, '?')) !== false)
			$this->internalURLTrunk = substr($this->internalURL, 0, $p);
		else
			$this->internalURLTrunk = $this->internalURL;
		$this->absoluteURL = $this->urlPrefix.$this->internalURL;
		
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
					$data = new stdclass();
					$data->details =
						'Controller '.$this->controller.' has no action called '.$this->action;
					return new View($this, $data, '404', 'errors', 404);
				}
			} else {
				$data = new stdclass();
				$data->details =
					'Controller '.$this->controller.' does not exist';
				return new View($this, $data, '404', 'errors', 404);
			}
		} else
			return new View($this, new stdclass(), '404', 'errors', 404);
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


