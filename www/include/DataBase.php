<?php
defined('IN_APP') or die;

class DataBase {
	private $link;
	private $parameters;
	
	/**
	 * Creates a new data base connection
	 * 
	 * @param object $parameters an object with the properties host, user, password, dataBase and prefix
	 */
	public function __construct($parameters) {
		$this->parameters = $parameters;
	}
	
	private function init() {
		if ($this->link)
			return;
	
		$this->connect();
	}
	
	private function connect() {
		$this->link = mysql_connect(
			$this->parametesr->host,
			$this->parameters->user,
			$this->parametesr->password);
		if (!self::$_dbLink)
			throw new RuntimeException('Unable to connect to data base');
		mysql_select_db(Config::$parameters->dataBase, $this->link);
		self::query("SET NAMES 'utf8'");
		self::query("SET sql_mode = 'STRICT_ALL_TABLES'");
		self::query("SET time_zone = 'UTC';");
	}
	
	public function query($query, $params = null) {
		if ($params !== null) {
			if (!is_array($params))
				$params = array($params);
			foreach ($params as &$param) {
				if (is_bool($param))
					$param = $param ? "'1'" : "'0'";
				else
					$param = "'".DataBase::escape((string)$param)."'";
			}
			$callback = create_function('$matches',
				'$params = (array)json_decode(\''.addcslashes(json_encode($params), '\'\\').'\'); '."\n".
				'$name = $matches[1]; if (isset($params[$name])) return $params[$name]; '.
				'else throw new Exception("Unknown parameter: $name");');
			$query = preg_replace_callback('/#([0-9a-zA-Z_]+)/', $callback, $query);
		}
		
		$this->init();
		$res = mysql_query($query, $this->link);
		if (!$res)
			throw new Exception(mysql_error($this->link));
		return $res;
	}
	
	public function escape($text) {
		$this->init();
		return mysql_real_escape_string((String)$text);
	}
	
	public function getInsertID() {
		$this->init();
		return mysql_insert_id($this->link);
	}
	
	public function table($name) {
		return $this->parameters->prefix.strtolower($name);
	}
	
	public function checkConnection() {
		if ($this->link) {
			try {
				$this->query("SELECT 1");
				return;
			} catch (Exception $e) {
				@mysql_close($this->link);
				$this->link = null;
			}
		}
	}
	
	public function getTables() {
		$result = $this->query("SHOW TABLES");
		$tables = array();
		while ($arr = mysql_fetch_array($result)) {
			$tables[] = $arr[0];
		}
		return $tables;
	}
}


