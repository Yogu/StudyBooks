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
		$query = clone $this;
		$query->reverse();
		return $query->first($count);
	}
	
	public function all() {
		return $this->execute();
	}
	
	public function count() {
		$sql =
			"SELECT COUNT(*) AS count ".
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
}
