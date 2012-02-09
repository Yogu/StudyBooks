<?php
defined('IN_APP') or die;

class Config {
	public static $config;
	
	public static function load() {
		if (self::$config === null) {
			$config = parse_ini_file(ROOT_PATH . 'include/Config.ini', true);
			if (!$config)
				throw new Exception("Failed to load config file");
			
			self::$config = self::arrayToObject($config);
			
			if (!self::$config->general->isOffline) {
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
			
			try {
				DataBase::query("SET time_zone = 'UTC';");
			} catch (Exception $e) {
				
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
}

?>
