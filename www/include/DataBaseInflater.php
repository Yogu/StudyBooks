<?php
defined('IN_APP') or die;

class DataBaseInflater {
	private $schemeFileName;
	private $dataFileName;
	
	private $tables;
	private $schemeQueries;
	private $dataQueries;
	
	const SCHEME_FILE_NAME = 'scheme.xml';
	const DATA_FILE_NAME = 'data.xml';
	
	const NAME_REGEX = '/[a-z0-9_]+/i';
	const TYPE_REGEX = '/(?<type>[a-z]+)(\((?<param>[^\)]*)\))?/i';
	
	const CHARSET = 'utf8';
	const COLLATE = 'utf8_bin';

	public function __construct($schemeFileName = self::SCHEME_FILE_NAME, $dataFileName = self::DATA_FILE_NAME) {
		$this->schemeFileName = ROOT_PATH.'config/'.$schemeFileName;
		$this->dataFileName = ROOT_PATH.'config/'.$dataFileName;
	}
	
	public function verify() {
		$this->getSchemeQueries();
		$this->getDataQueries();
	}

	public function getSchemeQueries() {
		if ($this->schemeQueries === null) {
			$xml = simplexml_load_file($this->schemeFileName);
			if (!$xml->getName() == 'database')
				throw new Exception("Invalid scheme file: Root element must be 'database'");
				
			$arr = array();
			$this->tables = array();
			foreach ($xml->children() as $child) {
				if ($child->getName() == 'table') {
					$tableName = '';
					$table = $this->parseTableScheme($child);
					$this->tables[$table->rawName] = $table;
					$arr[] = $table->sql;
				}
			}
			$this->schemeQueries = $arr;
		}
		return $this->schemeQueries;
	}
	
	public function getTables() {
		$this->getSchemeQueries(); // Sets tables field
		return $this->tables;
	}

	public function getSchemeSQL() {
		return implode("\n\n", $this->getSchemeQueries());
	}
	
	public function canCreateScheme(&$existingTable) {
		$existingTables = DataBase::getTables();
		
		foreach ($this->getTables() as $table) {
			if (array_search($table->name, $existingTables) !== false) {
				$existingTable = $table;
				return false;
			}
		}
		return true;
	}
	
	public function dropTables() {
		foreach ($this->getTables() as $table) {
			DataBase::query("DROP TABLE IF EXISTS `$table->name`");
		}
	}
	
	public function createScheme() {
		foreach ($this->getSchemeQueries() as $sql) {
			DataBase::query($sql);
		}
	}

	public function getDataSQL() {
		return implode("\n\n", $this->getDataQueries());
	}

	public function getDataQueries() {
		if ($this->dataQueries === null) {
			$xml = simplexml_load_file($this->dataFileName);
			if (!$xml->getName() == 'data')
				throw new Exception("Invalid data file: Root element must be 'data'");
				
			$arr = array();
			foreach ($xml->children() as $child) {
				if ($child->getName() == 'table') {
					$arr[] = $this->parseTableData($child);
				}
			}
			$this->dataQueries = $arr;
		}
		return $this->dataQueries;
	}
	
	public function insertData() {
		foreach ($this->getDataQueries() as $sql) {
			DataBase::query($sql);
		}
	}
	
	private function parseTableScheme($element) {
		$attributes = $element->attributes();
		$tableName = isset($attributes['name']) ? (string)$attributes['name'] : null;
		if (!$tableName)
			throw new Exception("Invalid scheme file: At least one table misses 'name' attribute");
		if (!preg_match(self::NAME_REGEX, $tableName))
			throw new Exception("Invalid scheme file: '$tableName' is not a valid table name");
	
		$columns = array();
		$columnsSQL = '';
		$keysSQL = '';
		foreach ($element->children() as $child) {
			if ($child->getName() == 'column') {
				if ($columnsSQL)
					$columnsSQL .= ",\n";
				$column = $this->parseColumnScheme($child, $tableName);
				$columns[] = $column;
				$columnsSQL .= "  ".$column->columnSQL;
				if ($column->keySQL) {
					$keysSQL .= ",\n  ".$column->keySQL;
				}
			}
		}
		
		// timestamp column
		if ($columnsSQL)
			$columnsSQL .= ",\n";
		$columnsSQL .= '  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
		
		$table = new stdclass();
		$table->rawName = $tableName;
		$table->columns = $columns;
		$table->name = DataBase::table($table->rawName);
		$table->sql = "CREATE TABLE `$table->name` (\n".
			$columnsSQL.
			$keysSQL.
			"\n) ENGINE=InnoDB DEFAULT CHARSET=".self::CHARSET." COLLATE=".self::COLLATE.";";
		return $table;
	}
	
	private function parseColumnScheme($element, $tableName) {
		$attributes = $element->attributes();
		
		$name = isset($attributes['name']) ? (string)$attributes['name'] : null;
		if (!$name)
			throw new Exception("Invalid scheme file: At least one column in table '$tableName' misses 'name' attribute");
		if (!preg_match(self::NAME_REGEX, $name))
			throw new Exception("Invalid scheme file: '$name' is not a valid column name (in table '$tableName')");
	
		$type = isset($attributes['type']) ? (string)$attributes['type'] : null;
		if (!$type)
			throw new Exception("Invalid scheme file: At least one column in table '$tableName' misses 'type' attribute");
		$typeSQL = $this->parseColumnType($type, $name, $tableName);
		
		if (isset($attributes['default'])) {
			$value = $this->parseValue((string)$attributes['default'], $type);
			if ($value === null)
				throw new Exception("Invalid scheme file: Column '$name' in table '$tableName' has an invalid 'default' attribute");
			$defaultSQL = " DEFAULT '".DataBase::escape($value)."'";
			$default = $value;
		} else {
			$defaultSQL = "";
			$default = null;
		}
		
		$extraSQL = '';
		
		if (!isset($attributes['required']) || $attributes['requried'] == 'true') {
			$required = true;
			$extraSQL .= ' NOT NULL';
		} else {
			$required = false;
			if (!$defaultSQL)
				$defaultSQL = ' DEFAULT NULL';
		}
	
		if (isset($attributes['autoIncrement']) && $attributes['autoIncrement'] == 'true') {
			$extraSQL .= ' AUTO_INCREMENT';
			$required = false;
		}
		
		if (isset($attributes['key'])) 
			$keysSQL = $this->parseKey($attributes['key'], $name, $tableName);
		else
			$keysSQL = '';
			
		$sql = "`$name` $typeSQL$extraSQL$defaultSQL";
		
		$column = new stdclass();
		$column->name = $name;
		$column->type = $type;
		$column->required = $required;
		$column->default = $default;
		$column->key = isset($attributes['key']) ? $attributes['key'] : false;
		$column->columnSQL = $sql;
		$column->keySQL = $keysSQL;
		return $column;
	}
	
	private function parseColumnType($type, $columnName, $tableName) {
		if (!preg_match(self::TYPE_REGEX, $type, $matches))
			throw new Exception("Invalid scheme file: Column '$columnName' in table '$tableName' has an invalid type attribute: '$type'");
		$type = $matches['type'];
		$param = isset($matches['param']) ? $matches['param'] : null;
		switch ($type) {
			case 'int':
				return 'int(10)';
			case 'uint':
				return 'int(10) unsigned';
			case 'long':
				return 'bigint(20)';
			case 'ulong':
				return 'bigint(20) unsigned';
			case 'bool':
				return 'tinyint(1)';
			case 'datetime':
				return "datetime";
			case 'string':
				if ($param === null)
					$param = 255;
				else if (((int)$param != $param) || $param < 0 || $param > 255)
					throw new Exception("Invalid scheme file: Column '$columnName' in table '$tableName' has an invalid type parameter: '$param' is not an integer between 0 and 255");
				return "varchar($param) COLLATE ".self::COLLATE;
			case 'text':
					return "text COLLATE ".self::COLLATE;
			case 'enum':
				$value = explode(',', $param);
				$sql = "";
				foreach ($value as $v) {
					if ($sql)
						$sql .= ", ";
					if (!preg_match(self::NAME_REGEX, $v))
						throw new Exception("Invalid scheme file: Column '$columnName' in table '$tableName' has an invalid enum value: '$v'");
					$sql .= "'$v'";
				}
				if (!$sql)
					throw new Exception("Invalid scheme file: Column '$columnName' in table '$tableName' misses values list for enum type");
				return "enum($sql)";
			default:
				throw new Exception("Invalid scheme file: Column '$columnName' in table '$tableName' has an invalid type attribute: '$type'");
		}
	}
	
	private function parseKey($key, $columnName, $tableName) {
		switch ($key) {
			case 'index':
			case 'true':
				return "KEY `$columnName` (`$columnName`)";
			case 'primary':
				return "PRIMARY KEY (`$columnName`)";
			case 'unique':
				return "UNIQUE KEY `$columnName` (`$columnName`)";
			default:
				throw new Exception("Invalid scheme file: Column '$columnName' in table '$tableName' has an invalid 'key' attribute: '$key'");
		}
	}
	
	private function parseValue($value, $type) {
		switch ($type) {
			case 'bool':
				if ($value == 'true')
					return '1';
				else if ($value == 'false')
					return '0';
				else
					return null;
			case 'int':
			case 'uint':
			case 'long':
			case 'ulong':
				if ((int)$value != $value)
					return null;
				elseif ($type[0] == 'u' && $value < 0)
					return null;
				else
					return $value;
			default:
				return $value;
		}
	}
	
	private function parseTableData($element) {
		$attributes = $element->attributes();
		$tableName = isset($attributes['name']) ? (string)$attributes['name'] : null;
		if (!$tableName)
			throw new Exception("Invalid data file: At least one table misses 'name' attribute");
			
		$tables = $this->getTables();
		if (!isset($tables[$tableName]))
			throw new Exception("Invalid data file: Table '$tableName' does not exist in schemes file");
		$table = $tables[$tableName];
		
		$columnsSQL = '';
		foreach ($table->columns as $column) {
			if ($columnsSQL)
				$columnsSQL .= ', ';
			$columnsSQL .= "`$column->name`";
		}
	
		$sql = '';
		foreach ($element->children() as $child) {
			if ($child->getName() == 'row') {
				if ($sql)
					$sql .= ",\n";
				$sql .= "  (".$this->parseRow($child, $table).")";
			}
		}
		if (!$sql)
			return '';
		
		return "INSERT INTO `$table->name` ($columnsSQL) VALUES \n$sql";
	}
	
	private function parseRow($element, $table) {
		$attributes = $element->attributes();
		$sql = '';
		foreach ($table->columns as $column) {
			$doEscape = true;
			if (isset($attributes[$column->name])) {
				$value = (string)$attributes[$column->name];
				$value = $this->parseValue($value, $column->type);
				if ($value === null)
					throw new Exception("Invalid data file: Table '$table->rawName' contains invalid value for '$column->name'");
			} else {
				if ($column->required)
					throw new Exception("Invalid data file: Table '$table->rawName' misses attribute '$column->name'");
				else if ($column->default !== null)
					$value = $column->default;
				else {
					$value = 'NULL';
					$doEscape = false;
				}
			}
			
			if ($doEscape)
				$value = "'".DataBase::escape($value)."'";
			if ($sql)
				$sql .= ', ';
			$sql .= $value;
		}
		return $sql;
	}
}
