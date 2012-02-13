<?php
defined('IN_APP') or die;

class Query {
	private $sql = null;
	private $table;
	private $order;
	private $limitStart;
	private $limitCount;
	private $where;
	private $select;
	
	const MAX_INT = 2147483647;
	
	public static function from(Table $table) {
		$query = new Query();
		$query->table = $table;
		return $query;
	}
	
	/**
	 *
	 * @param $sql A sql pattern
	 * @param $params The sql parameters 
	 */
	public function where($sql, $params = null) {
		$sql = $this->embedParameters($sql, $params);
		if ($this->where)
			$this->where .= "AND ($sql) ";
		else
			$this->where = "WHERE ($sql) ";
			
		$this->sql = null;
		return $this;
	}
	
	public function whereEquals($column, $value) {
		$sql = $this->formatColumn($column);
		$sql .= " = ".$this->formatValue($value);
		return $this->where($sql);
	}
	
	public function orderBy($column, $dir = "ASC") {
		$column = $this->formatColumnSimple($column);
		if (!is_array($this->order))
			$this->order = array();
		array_unshift($this->order, array($column, $dir));
			
		$this->sql = null;
		return $this;
	}
	
	public function orderByDescending($column) {
		return $this->orderBY($column, "DESC");
	}

	public function limit($startIndex, $count) {
		$this->limitStart = $startIndex === null ? null : (int)$startIndex;
		$this->limitCount = $count === null ? null : (int)$count;
			
		$this->sql = null;
		return $this;
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
		if (!is_array($columns))
			$columns = func_get_args();
		if (!is_array($this->select))
			$this->select = array();
		
		foreach ($columns as $column) {
			if (!isset($this->select[$column])) {
				$this->formatColumnSimple($column); // check if column exists
				$this->select[$column] = true;
			}
		}
			
		$this->sql = null;
		return $this;
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
		if (is_array($this->order)) {
			foreach ($this->order as &$value) {
				$value[1] = $value[1] == 'DESC' ? 'ASC' : 'DESC';
			}
		}
			
		$this->sql = null;
		return $this;
	}
	
	private function embedParameters($sql, $params)  {
		if ($params !== null) {
			if (!is_array($params))
				$params = array($params);
			foreach ($params as &$param) {
				$param = $this->formatValue($param);
			}
			$params = (object)$params;
			static $callback;
			if (!isset($callback))
				$callback = create_function('$matches',
					'$params = json_decode(\''.addcslashes(json_encode($params), '\'').'\'); '."\n".
					'$name = $matches[1]; return $params->$name;');
			$sql = preg_replace_callback('/#([0-9a-zA-Z_]+)/', $callback, $sql);
		}
		return $sql;
	}
	
	private function formatValue($value) {
		if (is_bool($value))
			return $value ? "'1'" : "'0'";
		else
			return "'".DataBase::escape((string)$value)."'";
					
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
		return "t.`$dbField`";
	}
	
	// =============================================================================================
	
	public function getSQL() {
		if ($this->sql === null) {
			$table = DataBase::table($this->table->tableName);
			
			// SELECT
			$columns = $this->select ? $this->select : $this->table->columns;
			$select = '';
			foreach ($columns as $column => $dummy) {
				if ($select)
					$select .= ', ';
				$c = $this->formatColumn($column);
				$select .= "$c AS `$column`";
			}
			$select = "SELECT $select ";
			
			// FROM
			$from = "FROM `$table` AS t ";
			
			// LIMIT
			if ($this->limitStart !== null || $this->limitCount !== null) {
				$start = $this->limitStart !== null ? $this->limitStart : 0;
				$count = $this->limitCount !== null ? $this->limitCount : self::MAX_INT;
				$limit = "LIMIT $start, $count ";
			} else
				$limit = "";
				
			// ORDER
			if (is_array($this->order)) {
				$order = '';
				foreach ($this->order as $arr) {
					if ($order)
						$order .= ', ';
					$order .=  $arr[0].' '.$arr[1]; // column name and direction
				}
				$order = "ORDER BY $order ";
			} else
				$order = "";
			
			$this->sql =
				$select.
				$from.
				$this->where.
				$order.
				$limit;
		}
		return $this->sql;
	}
	
	public function execute() {
		return DataBase::query($this->getSQL());
	}
	
	public function first($count = 1) {
		$query = clone $this;
		$query->limitCount = $count;
		$result = $query->execute();
		if ($data = mysql_fetch_array($result))
			return $this->createObject($data);
		else
			return null;
	}
	
	public function last($count = 1) {
		$query = clone $this;
		$query->reverse();
		$query->limitCount = $count;
		$result = $query->execute();
		if ($data = mysql_fetch_array($result))
			return $this->createObject($data);
		else
			return null;
	}
	
	public function all() {
		$result = $this->execute();
		$objects = array();
		while ($data = mysql_fetch_array($result)) {
			$objects[] = $this->createObject($data);
		}
		return $objects;
	}
	
	private function createObject($data) {
		// MySQL also returns numeric keys for all the values
		foreach ($data as $k => $v) {
			if (is_int($k))
				unset($data[$k]);
		}
		
		$class = $this->table->className;
		$obj = new $class($data);
		return $obj;
	}
}
