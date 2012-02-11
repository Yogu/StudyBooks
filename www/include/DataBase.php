<?php
defined('IN_APP') or die;

class DataBase {
	private static $_dbLink;
	
	private static function init() {
		if (self::$_dbLink)
			return;
	
		self::connect();
	}
	
	private static function connect() {
		self::$_dbLink = mysql_connect(
			Config::$config->dataBase->host, 
			Config::$config->dataBase->user, 
			Config::$config->dataBase->password);
		if (!self::$_dbLink)
			throw new RuntimeException('Unable to connect to data base');
		mysql_select_db(Config::$config->dataBase->dataBase, self::$_dbLink);
		self::query("SET NAMES 'utf8'");
		self::query("SET sql_mode = 'STRICT_ALL_TABLES'");
	}
	
	public static function query($query, $params = null) {
		if ($params !== null) {
			if (!is_array($params))
				$params = array($params);
			foreach ($params as &$param) {
				if (is_bool($param))
					$param = $param ? "'1'" : "'0'";
				else
					$param = "'".DataBase::escape((string)$param)."'";
			}
			$params = (object)$params;
			$callback = create_function('$matches',
				'$params = json_decode(\''.addcslashes(json_encode($params), '\'').'\'); '."\n".
				'$name = $matches[1]; return $params->$name;');
			$query = preg_replace_callback('/#([0-9a-zA-Z_]+)/', $callback, $query);
		}
		
		self::init();
		$res = mysql_query($query, self::$_dbLink);
		if (!$res)
			throw new Exception(mysql_error(self::$_dbLink));
		return $res;
	}
	
	public static function escape($text) {
		self::init();
		return mysql_real_escape_string($text);
	}
	
	public static function getInsertID() {
		self::init();
		return mysql_insert_id(self::$_dbLink);
	}
	
	public static function table($name) {
		return Config::$config->dataBase->prefix.strtolower($name);
	}
	
	public static function checkConnection() {
		if (self::$_dbLink)
		try {
			self::query("SELECT 1");
			return;
		} catch (Exception $e) {
			@mysql_close(self::$_dbLink);
			self::$_dbLink = null;
		}
	}
}


