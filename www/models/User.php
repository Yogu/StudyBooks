<?php
defined('IN_APP') or die;

class User extends Model {
	public $id;
	public $name = '';
	public $email = '';
	public $hashedPassword = '';
	public $rawPassword = '';
	public $createTime = 0;
	public $lastLoginTime = 0;
	public $role = 'poster';
	public $isBanned = false;
	
	public static function table() {
		static $table;
		if (!isset($table)) {
			$table = new Table("User", "Users", array(
				'id',
				'name',
				'email',
				'hashedPassword' => 'password',
				'role',
				'isBanned',
				'createTime' => ':time',
				'lastLoginTime' => ':time'));
		}
		return $table;
	}
	
	public function __construct($data = null) {
		parent::__construct($data);
	}
	
	public static function getByID($id) {
		return Query::from(self::table())
			->whereEquals('id', (int)$id)
			->first();
	}
	
	public static function getByName($name) {
		return Query::from(self::table())
			->whereEquals('name', $name)
			->first();
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


