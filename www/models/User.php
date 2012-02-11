<?php
defined('IN_APP') or die;

class User {
	public $id;
	public $name = '';
	public $email = '';
	public $hashedPassowrd = '';
	public $rawPassword = '';
	public $createTime = 0;
	public $lastLoginTime = 0;
	public $role = 'poster';
	public $isBanned = false;
	
	public static function getList($condition = '', $offset = 0, $count = 2147483647, $params = null) {
		$result = DataBase::query(
			"SELECT id, name, email, password, role, isBanned, ".
				"UNIX_TIMESTAMP(createTime) AS createTime, ".
				"UNIX_TIMESTAMP(lastLoginTime) AS lastLoginTime ".
			"FROM ".DataBase::table('Users')." ".
			$condition.' '.
			"LIMIT $offset, $count", $params);
		$list = array();
		while ($item = mysql_fetch_object($result)) {
			$user = new User();
			$user->id = $item->id;
			$user->name = $item->name;
			$user->email = $item->email;
			$user->role = $item->role;
			$user->isBanned = $item->isBanned;
			$user->hashedPassword = $item->password;
			$user->createTime = $item->createTime;
			$user->lastLoginTime = $item->lastLoginTime;
			$list[] = $user;
		}
		return $list;
	}
	
	public static function getSingle($condition, $params = null) {
		$list = self::getList($condition, 0, 1, $params);
		return count($list) ? $list[0] : null;
	}
	
	public static function getByID($id) {
		return self::getSingle('WHERE id = #0', (int)$id);
	}
	
	public static function getByName($name) {
		return self::getSingle('WHERE name = #0', (string)$name);
	}
	
	public static function hashPassword($rawPassword) {
		return hash('sha256', Config::$config->security->secretCode.$rawPassword);
	}
	
	public function insert() {
		$this->hashedPassowrd = self::hashPassword($this->rawPassword);
		DataBase::query(
			"INSERT INTO ".DataBase::table('users')." ".
			"SET name = #0, email = #1, password = #2, role = #3, isBanned = #4, ".
				"createTime = NOW()",
			array($this->name, $this->email, $this->hashedPassowrd, $this->role,
				$this->isBanned));
		$this->id = DataBase::getInsertID();
		$this->createTime = time();
	}
	
	public function saveChanges() {
		DataBase::query(
			"UPDATE ".DataBase::table('users')." ".
			"SET name = #0, email = #1, role = #2, isBanned = #3 ".
			"WHERE id = #4",
			array($this->name, $this->email, $this->role, $this->isBanned, $this->id));
	}
	
	public function changePassword() {
		$this->hashedPassowrd = self::hashPassword($this->rawPassword);
		DataBase::query(
			"UPDATE ".DataBase::table('users')." ".
			"SET password = #0 ".
			"WHERE id = #1",
			array($this->hashedPassowrd, $this->id));
	}
	
	public function delete() {
		DataBase::query(
			"DELETE FROM ".DataBase::table('users')." ".
			"WHERE id = #0",
			$this->id);
	}
	
	public function updateLoginTime() {
		DataBase::query(
			"UPDATE ".DataBase::table('Users')." ".
			"SET lastLoginTime = NOW() ".
			"WHERE id = #0",
			$this->id);
	}
	
	public function checkPassword($rawPassword) {
		return $this->hashedPassword == self::hashPassword($rawPassword);
	}
}


