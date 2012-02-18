<?php
defined('IN_APP') or die;

class Query implements IteratorAggregate, Countable, ArrayAccess {
	private $sql = null;
	private $table;
	private $order;
	private $where;
	private $select;
	
	const MAX_INT = 2147483647;
	
	public static function from(Table $table) {
		$query = new Query();
		$query->table = $table;
		return $query;
	}
	
	public function getTable() {
		return $table;
	}
	
	/**
	 *
	 * @param $sql A sql pattern
	 * @param $params The sql parameters 
	 */
	public function where($sql, $params = null) {
		$clone = clone $this;
		$sql = $clone->embedParameters($sql, $params);
		if ($clone->where)
			$clone->where .= "AND ($sql) ";
		else
			$clone->where = "WHERE ($sql) ";
			
		$clone->sql = null;
		return $clone;
	}
	
	public function whereEquals($column, $value) {
		$sql = $this->formatColumn($column);
		$sql .= " = ".$this->formatValue($value);
		return $this->where($sql);
	}
	
	public function orderBy($column, $dir = "ASC") {
		$clone = clone $this;
		$column = $clone->formatColumnSimple($column);
		if (!is_array($clone->order))
			$clone->order = array();
		array_unshift($clone->order, array($column, $dir));
			
		$clone->sql = null;
		return $clone;
	}
	
	public function orderByDescending($column) {
		return $this->orderBY($column, "DESC");
	}
	
	/**
	 * Selects the given columns by adding them to the select list.
	 * 
	 * If this method is called twice, every column of every call is added.
	 * 
	 * You can also specify the columns as separate arguments, e.g.: select('id', 'name, 'time')
	 * 
	 * @param $arr an array of column names
	 * @return unknown_type
	 */
	public function select($columns) {
		$clone = clone $this;
		
		if (!is_array($columns))
			$columns = func_get_args();
		if (!is_array($this->select))
			$clone->select = array();
		
		foreach ($columns as $column) {
			if (!isset($clone->select[$column])) {
				$clone->formatColumnSimple($column); // check if column exists
				$clone->select[$column] = true;
			}
		}
			
		$clone->sql = null;
		return $clone;
	}
	
	/**
	 * Selects all available columns
	 * 
	 * Note that when an execute method is called before a select call is maded, all columns are
	 * used implicit (but not added to the select ist).
	 */
	public function selectAll() {
		return $this->select(array_keys($this->table->columns));
	}
	
	/**
	 * Reverses the result of this query by switching the sort direction of all orderBy clauses
	 * 
	 * @return Query this query
	 */
	public function reverse() {
		$clone = clone $this;
		
		if (is_array($clone->order)) {
			foreach ($clone->order as &$value) {
				$value[1] = $value[1] == 'DESC' ? 'ASC' : 'DESC';
			}
		}
			
		$clone->sql = null;
		return $clone;
	}
	
	private function embedParameters($sql, $params)  {
		if ($params !== null) {
			if (!is_array($params))
				$params = array($params);
			foreach ($params as &$param) {
				$param = $this->formatValue($param);
			}
			$callback = create_function('$matches',
				'$params = (array)json_decode(\''.addcslashes(json_encode($params), '\'\\').'\'); '."\n".
				'$name = $matches[1]; if (isset($params[$name])) return $params[$name]; '.
				'else throw new Exception("Unknown parameter: $name");');
			$sql = preg_replace_callback('/#([0-9a-zA-Z_]+)/', $callback, $sql);
		}
		
		// Format columns
		if (!isset($this->embedParameters_callback2))
			$this->embedParameters_callback2 = create_function('$matches',
				'$columns = (array)json_decode(\''.addcslashes(json_encode($this->table->columns), '\'\\').'\'); '."\n".
				'$name = $matches[1]; if (isset($columns[$name])) return \'`\'.Strings::leftOf($columns[$name], \':\').\'`\'; '.
				'else throw new Exception("Unknown column: $name");');
		$sql = preg_replace_callback('/\\$([0-9a-zA-Z_]+)/', $this->embedParameters_callback2, $sql);
		return $sql;
	}
	
	private function formatValue($value, $type = '') {
		if (is_bool($value))
			$value = $value ? "'1'" : "'0'";
		else
			$value = "'".DataBase::escape((string)$value)."'";
			
		if ($type == 'time')
			$value = 'FROM_UNIXTIME('.$value.')';
			
		return $value;
					
	}
	
	private function formatColumn($column) {
		$c = $this->formatColumnSimple($column);
		$type = $this->table->columns[$column];
		if (Strings::endsWidth($type, ':time'))
			return "UNIX_TIMESTAMP($c)";
		else
			return $c;
	}
	
	private function formatColumnSimple($column) {
		if (!isset($this->table->columns[$column]))
			throw new Exception("Table ".$this->table->tableName." does not have column $column");
		$dbField = Strings::leftOf($this->table->columns[$column], ':');
		if (!$dbField)
			$dbField = $column;
		return "`$dbField`";
	}
	
	// =============================================================================================
	
	public function getSQL() {
		if ($this->sql === null) {
			
			$select = $this->buildSelectSQL();
			$from = $this->buildFromSQL();
			$order = $this->buildOrderSQL();
			
			$this->sql =
				$select.
				$from.
				$this->where.
				$order;
		}
		return $this->sql;
	}
	
	private function buildSelectSQL() {
		$columns = $this->select ? $this->select : $this->table->columns;
		$select = '';
		foreach ($columns as $column => $dummy) {
			if ($select)
				$select .= ', ';
			$c = $this->formatColumn($column);
			$select .= "$c AS `$column`";
		}
		return "SELECT $select ";
	}
	
	private function buildFromSQL() {
		$table = DataBase::table($this->table->tableName);
		return "FROM `$table` AS t ";
	}
	
	private function buildOrderSQL() {
		if (is_array($this->order)) {
			$order = '';
			foreach ($this->order as $arr) {
				if ($order)
					$order .= ', ';
				$order .=  $arr[0].' '.$arr[1]; // column name and direction
			}
			return "ORDER BY $order ";
		} else
			return '';
	}
	
	private function buildLimitSQL($start, $count) {
		if ($start !== null || $count !== null) {
			$start = $start !== null ? $start : 0;
			$count = $count !== null ? $count : self::MAX_INT;
			return "LIMIT $start, $count ";
		} else
			return '';
	}
	
	public function execute($sql = null) {
		if ($sql === null)
			$sql = $this->getSQL();
		
		$result = DataBase::query($sql);
		$objects = array();
		while ($data = mysql_fetch_array($result)) {
			$objects[] = $this->table->createObjectFromArray($data);
		}
		return $objects;
	}
	
	public function range($start, $count) {
		$sql = $this->getSQL().
			$this->buildLimitSQL($start, $count);
		return $this->execute($sql);
	}
	
	public function get($index) {
		$arr = $this->range($index, 1);
		return count($arr) ? $arr[0] : null;
	}
	
	public function first($count = null) {
		$arr = $this->range(0, $count === null ? 1 : $count);
		if ($count === null)
			return count($arr) ? $arr[0] : null;
		else
			return $arr;
	}
	
	public function last($count = null) {
		return $query->reverse()->first($count);
	}
	
	public function all() {
		return $this->execute();
	}
	
	public function count() {
		return $this->aggregate('COUNT', false);
	}
	
	public function max($column) {
		return $this->aggregate('MAX', true, $column);
	}
	
	public function min($column) {
		return $this->aggregate('MIN', true, $column);
	}
	
	public function sum($column) {
		return $this->aggregate('SUM', true, $column);
	}
	
	public function average($column) {
		return $this->aggregate('AVG', true, $column);
	}
	
	private function aggregate($function, $requiresColumn, $column = null) {
		if ($column || $requiresColumn)
			$column = $this->formatColumn($column);
		else
			$column = '*';
			
		$sql =
			"SELECT $function($column) ".
			$this->buildFromsQL().
			$this->where;
		
		$result = DataBase::query($sql);
		if (list($count) = mysql_fetch_array($result))
			return $count;
		else
			return 0;
	}
	
	public function getIterator() {
		return new QueryIterator($this);
	}
	
	public function offsetGet($index) {
		return $this->get($index);
	}
	
	public function offsetExists($index) {
		return is_int($index) && $index >= 0 && $index < $this->count();
	}
	
	public function offsetSet($offset, $value) {
		return Exception("Method offsetSet not implemented");
	}
	
	public function offsetUnset($offset) {
		return Exception("Method offsetUnset not implemented");
	}
	
	private function formatSetterColumns($fields, $params = null) {
		if (is_array($fields)) {
			if (!count($fields))
				return false;
			
			$sql = '';
			foreach ($fields as $key => $value) {
				if ($sql)
					$sql .= ', ';
					
				if (is_int($key)) {
					if (is_array($value)) {
						$pattern = array_shift($value);
						$value = $this->embedParameters($pattern, $value);
					} else
						$value = $this->embedParameters($value, array()); // format column names
					$sql .= $value;
				} else {
					$encode = !Strings::endsWidth($key, '!');
					if (!$encode)
						$key = substr($key, 0, -1);
		
					if (!isset($this->table->columns[$key]))
						throw new Exception("Table ".$this->table->tableName." does not have column $key");
					$t = $this->table->columns[$key];
					$dbField = '`'.Strings::leftOf($t, ':').'`';
					$type = Strings::rightOfFirst($t, ':');
					if ($encode) {
						$value = self::formatValue($value, $type);
					} else if (is_array($value)) {
						$pattern = array_shift($value);
						$value = $this->embedParameters($pattern, $value);
					} else
						$value = $this->embedParameters($value, array()); // format column names
					$sql .= $dbField.' = '.$value;
				}
			}
			return $sql;
		} else {
			return self::embedParameters($fields, $params);
		}
	}
	
	public function update($fields, $params = null) {
		$sql = $this->formatSetterColumns($fields, $params);
		
		$sql = "UPDATE ".DataBase::table($this->table->tableName)." AS t ".
			"SET $sql ".
			$this->where;
			
		DataBase::query($sql);
	}
	
	public function insert($fields, $params = null) {
		$sql = $this->formatSetterColumns($fields, $params);
		
		$sql = "INSERT INTO ".DataBase::table($this->table->tableName)." ".
			"SET $sql";
		
		DataBase::query($sql);
		return DataBase::getInsertID();
	}
	
	public function delete() {
		$sql = "DELETE FROM ".DataBase::table($this->table->tableName)." ".
			$this->where;
		DataBase::query($sql);
	}
}
