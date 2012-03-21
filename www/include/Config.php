<?php
defined('IN_APP') or die;

class Config {
	private $values;
	private $fileName;
	
	const DEFAULT_FILE_NAME = 'config/config.ini';
	
	public function __construct($fileName = null) {
		$this->fileName = $fileName;
		$this->loadFromFile();
	}
	
	public static function createDefault() {
		return new Config(ROOT_PATH . self::DEFAULT_FILE_NAME);
	}
	
	private function loadFromFile() {	
		$config = self::loadFile();
		$this->values = self::arrayToObject($config);
	}
	
	public function loadFromDatabase($db) {
		if (!$this->general->isOffline) {
			$result = $db->query(
				"SELECT name, value ".
				"FROM ".self::$config->dataBase->prefix."config");
			while (list($name, $value) = mysql_fetch_array($result)) {
				$this->$name = $value;
			}
		}
	}
	
	private static function arrayToObject($array) {
		$obj = new stdClass();
		foreach ($array as $key => $value) {
			if (is_array($value))
				$obj->$key = self::arrayToObject($value);
			else
				$obj->$key = $value;
		}
		return $obj;
	}
	
	private static function objectToArray($obj) {
		$arr = array();
		foreach ($obj as $key => $value) {
			if (is_object($value))
				$arr[$key] = self::objectToArray($value);
			else
				$arr[$key] = $value;
		}
		return $arr;
	}
	
	private static function deepMerge($obj1, $obj2) {
		$new = clone $obj1;
		foreach ($obj2 as $key => $value) {
			if (is_object($value)) {
				if (isset($new->$key))
					$new->$key = self::deepMerge($new->$key, $value);
				else
					$new->$key = self::deepClone($value);
			} else
				$new->$key = $value;
		}
		return $new;
	}
	
	private static function deepClone($obj) {
		$new = clone $obj;
		foreach ($new as $key => $value) {
			if (is_object($value))
				$new->$key = self::deepClone($value);
		}
		return $new;
	}
	
	public function save($values) {
		$res = array();
		$values = self::objectToArray(self::deepMerge(self::arrayToObject(self::loadFile($this->fileName)), $values));
		foreach($values as $key => $val) {
			if(is_array($val)) {
				$res[] = "[$key]";
				foreach($val as $skey => $sval)
					$res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
			} else
				$res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
		}
		FileInfo::safeFileRewrite($this->fileName, implode("\r\n", $res));
	}
	
	private function loadFile($fileName) {
		$config = parse_ini_file($fileName, true);
		if (!$config)
			throw new Exception("Failed to load config file");
		return $config;
	}
	
	public function __get($name) {
		if (strpos('.', $name) !== null) {
			list($section, $key) = explode('.', $name, 2);
		} else {
			$section = $name;
			$key = null;
		}
		
		if (array_key_exists($section, $this->values)) {
			$arr = $this->values[$section];
			if ($key === null)
				return $arr;
			else if (array_key_exists($key, $arr))
				return $arr[$key];
			else
				throw new Exception("Config section ".$section." does not contain the key ".$key);
		} else
			throw new Exception("Config section ".$section." does not exist");
	}
	
	public function __set($name, $value) {
		list($section, $key) = explode('.', $name, 2);
		
		if (!array_key_exists($section, $this->values))
			$this->values[$section] = array();
		$this->values[$section][$key] = $value;
	}
}


