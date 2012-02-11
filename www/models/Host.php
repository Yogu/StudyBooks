<?php
defined('IN_APP') or die;

class Host {
	public $id;
	public $url;
	public $totalSpace;
	public $isCurrent;
	public $isEnabled;
	
	public static function getList($condition = '', $offset = 0, $count = 2147483647, $params = null) {
		$result = DataBase::query(
			"SELECT id, url, totalSpace, isCurrent, isEnabled ".
			"FROM ".DataBase::table('hosts')." ".
			($condition ? "WHERE $condition " : '').
			"LIMIT $offset, $count", $params);
		$list = array();
		while ($item = mysql_fetch_object($result)) {
			$host = new Host();
			$host->id = $item->id;
			$host->url = $item->url;
			$host->totalSpace = $item->totalSpace;
			$host->isCurrent = $item->isCurrent;
			$host->isEnabled = $item->isEnabled;
			$list[] = $host;
		}
		return $list;
	}

	public static function getSingle($condition, $params = null) {
		$list = self::getList($condition, 0, 1, $params);
		return count($list) ? $list[0] : null;
	}
	
	public static function getByID($id) {
		return self::getSingle('id = #0', (int)$id);
	}
	
	public static function getCurrent() {
		return self::getSingle('isCurrent = 1');
	}
	
	public function getUsedSpace() {
		$result = DataBase::query(
			"SELECT SUM(size) AS size ".
			"FROM ".DataBase::table('packages')." ".
			"WHERE hostID = #0",
			$this->id);
		$row = mysql_fetch_array($result);
		return $row['size'];
	}
}


