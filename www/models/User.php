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
	
	public static function getByName($name) {
		return self::query()
			->where('LOWER($name) = #0', $name)
			->first();
	}
	
	public static function hashPassword($rawPassword) {
		return hash('sha256', Config::$config->security->secretCode.$rawPassword);
	}
	
	public function insert() {
		$this->hashedPassword = self::hashPassword($this->rawPassword);
		$this->createTime = time();
		$this->insertAll();
	}
	
	public function saveChanges() {
		$this->updateFields(array('name', 'email', 'role', 'isBanned'));
	}
	
	public function changePassword() {
		$this->hashedPassowrd = self::hashPassword($this->rawPassword);
		$this->updateFields(array('hashedPassword'));
	}
	
	public function delete() {
		parent::delete();
	}
	
	public function updateLoginTime() {
		$this->lastLoginTime = time();
		$this->updateFields(array('lastLoginTime'));
	}
	
	public function checkPassword($rawPassword) {
		return $this->hashedPassword == self::hashPassword($rawPassword);
	}
}


