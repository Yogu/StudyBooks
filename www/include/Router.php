<?php
defined('IN_APP') or die;

class Router {
	private static $router;
	
	private $rules;
	
	public function loadDefaultFile() {
		$fileName = ROOT_PATH.'config/routing.ini';
		$this->loadFromFile($rileName);
	}
	
	public function loadFromFile($fileName) {
		$lines = file($fileName, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES );
		if (!$lines)
			throw new Exception("Failed to read rules file");
		$this->loadFromLines($lines);
	}
	
	public function loadFromLines($lines) {
		$rules = array();
		foreach ($lines as $line) {
			$line = trim($line);
			if (!$line || $line[0] == '#')
				continue;
				
			$parts = explode(' ', $line);
			$rule = new stdclass();
			$rule->scheme = array_shift($parts);
			
			// Included Parameters
			$rule->includedParameters = array();
			if (preg_match_all('/\{([a-z_]+[a-z_0-9_]*)([^\}]*)}/', $rule->scheme, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $match) {
					list($tmp, $name, $type) = $match;
					$rule->includedParameters[$name] = $type;
				}
			}
			
			// Regex
			$regex = $rule->scheme;
			$regex = preg_replace('/\{([a-z_]+[a-z_0-9_]*)\:([^\}]+)\}/', '(?<$1>$2)', $regex);
			$regex = preg_replace('/\{([a-z_]+[a-z_0-9_]*)\?\}/', '(?<$1>[^/]*)', $regex);
			$regex = preg_replace('/\{([a-z_]+[a-z_0-9_]*)\}/', '(?<$1>[^/]+)', $regex);
			$regex = str_replace('/', '/+', $regex);
			$rule->regex = '%^/+'.$regex.'/+$%i';
			
			// Default Parameters
			$rule->defaultParameters = array();
			$rule->explicitParameters = array();
			foreach ($parts as $part) {
				list($key, $value) = explode('=', $part, 2);
				$rule->defaultParameters[$key] = $value;

				// Explicit parameters are those being set without the possibility of being modified by the url
				if (!array_key_exists($key, $rule->includedParameters))
					$rule->explicitParameters[$key] = $value;
			}
			
			$rules[] = $rule;
		}
		$this->rules = $rules;
	}
	
	public function getRules() {
		return $this->rules;
	}
	
	public function resolveURL($url) {
		// Regex may require multiple slashes
		$slashes = '//////////';
		$url = $slashes . str_replace('/', $slashes, $url) . $slashes;
		foreach ($this->rules as $rule) {
			if (preg_match($rule->regex, $url, $matches)) {
				// Unset numeric matches, only leave named parameters
				foreach ($matches as $k => $v) {
					if (is_int($k) || $v === '')
						unset($matches[$k]);
				}
				// if both arrays contain the same key, the right array wins
				return array_merge($rule->defaultParameters, $matches);
			}
		}
		return array();
	}
	
	public function getURLForParameters($parameters) {
		if (isset($parameters['controller']))
			$parameters['controller'] = Strings::toLower($parameters['controller']);
		if (isset($parameters['view']))
			$parameters['view'] = Strings::toLower($parameters['view']);
		
		$rules = self::getRules();
		foreach ($rules as $rule) {
			// If some parameters must match exactly, check them
			$ok = true;
			foreach ($rule->explicitParameters as $k => $v) {
				if (!isset($parameters[$k]) || Strings::toLower($parameters[$k]) != Strings::toLower($v)) {
					$ok = false;
					break;
				}
			}
			if (!$ok)
				continue;
			
			// Check if every required parameter is given and - where given - matches the regex
			$ok = true; 
			foreach ($rule->includedParameters as $name => $type) {
				if ($type != '?') {
					// 0 would be converted to false, so test with is_numeric
					if (isset($parameters[$name]) && (is_numeric($parameters[$name]) || $parameters[$name])) {
						if ($type != '') {
							$type = substr($type, 1); // remove first ':'
							if (preg_match('%^'.$type.'$%i', $parameters[$name]))
								continue;
						} else
							continue;
					}
					$ok = false;
					break;
				}
			}
			if (!$ok)
				continue;
			
			$url = $rule->scheme;
			$additional = '';
			foreach ($parameters as $key => $value) {
				if (array_key_exists($key, $rule->includedParameters)) {
					$type = $rule->includedParameters[$key];
					if ($type == '?' && Strings::toLower($rule->defaultParameters[$key]) == Strings::toLower($value))
						$value = '';
					
					$url = preg_replace('/\{'.$key.'[^\}]*}/', rawurlencode($value), $url);
				}else if (!array_key_exists($key, $rule->defaultParameters)) {
					if (!$additional)
						$additional = '?';
					else
						$additional .= '&';
					$additional .=  $key;
					if ($value !== '')
						$additional .= '=' . rawurlencode($value);
				}
			}
			$url = str_replace('//', '/', $url);
			$url = trim($url, '/');
			return $url.$additional;
		}
	}
	
	public static function getRouter() {
		if (!isset(self::$router)) {
			self::$router = new Router();
			self::$router->loadDefaultFile();
		}
		return self::$router;
	}
	
	public static function resolve($url) {
		self::getRouter()->resolveURL();
	}
	
	public static function getURL($parameters) {
		return self::getRouter()->getURLForParameters($parameters);
	}
}


