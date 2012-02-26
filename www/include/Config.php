<?php
defined('IN_APP') or die;

class Config {
	public static $config;
	
	public static function load() {
		if (self::$config === null) {			
			$config = self::loadFile();
			self::$config = self::arrayToObject($config);
			
			$noDB = (defined('NO_DB') && NO_DB) || self::$config->general->isOffline;
			if (!$noDB) {
				$result = DataBase::query(
					"SELECT name, value ".
					"FROM ".self::$config->dataBase->prefix."config");
				while ($row = mysql_fetch_array($result)) {
					if (strpos($row['name'], '.')) {
						list($category, $field) = explode('.', $row['name'], 2);
						if (!array_key_exists($category, $config))
							$config[$category] = array();
						if (!isset($config[$category][$field]))
							$config[$category][$field] = $row['value'];
					} else
						$config[$row['name']] = $row['value'];
				}
			}
			
			self::$config = self::arrayToObject($config);
			
			if (!$noDB) {
				try {
					DataBase::query("SET time_zone = 'UTC';");
				} catch (Exception $e) {
					
				}
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
	
	public static function save($values) {
		$res = array();
		$values = self::objectToArray(self::deepMerge(self::arrayToObject(self::loadFile()), $values));
		foreach($values as $key => $val) {
			if(is_array($val)) {
				$res[] = "[$key]";
				foreach($val as $skey => $sval)
					$res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
			} else
				$res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
		}
		FileInfo::safeFileRewrite(ROOT_PATH . 'config/config.ini', implode("\r\n", $res));
	}
	
	private static function loadFile() {
		$config = parse_ini_file(ROOT_PATH . 'config/config.ini', true);
		if (!$config)
			throw new Exception("Failed to load config file");
		return $config;
	}
}


