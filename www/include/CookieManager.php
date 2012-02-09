<?php
defined('IN_APP') or die;

class CookieManager {
	private $array;
	
	public function __construct(array $cookies) {
		$this->array = $cookies;
	}
	
	public function __get($name) {
		$key = Config::$config->general->cookiePrefix.$name;
		return isset($this->array[$key]) ? $this->array[$key] : null;
	}
	
	public function __set($name, $value) {
		$this->set($name, $value);
	}
	
	public function set($name, $value, $expire = 0) {
		$name = Config::$config->general->cookiePrefix.$name;
		$this->array[$name] = $value;

		if (!$expire)
			$expirationTime = 0;
		else
			$expirationTime = time() . $expire;

		if (!setcookie($name, $value, $expirationTime, ROOT_URL))
			throw new Exception(
				'Failed to set cookie: page output has already been started');
	}
}

?>
