<?php
defined('IN_APP') or die;

class Session extends Model {
	public $id;
	public $user;
	public $userID;
	public $key;
	public $startTime;
	public $lastAccessTime;
	public $ip;
	public $userAgent;
	public $loggedOut;
	
	public static function table() {
		static $table;
		if (!isset($table)) {
			$table = new Table("Session", "Sessions", array(
				'id',
				'userID',
				'key',
				'startTime' => ':time',
				'lastAccessTime' => ':time',
				'ip',
				'loggedOut',
				'userAgent'));
		}
		return $table;
	}
	
	public function __construct($data = null) {
		parent::__construct($data);
		$this->user = User::getByID($this->userID);
	}
	
	public static function getVaildByKey($key) {
		return self::query()
			->whereEquals('key', $key)
			->where('NOT $loggedOut')
			->where('$lastAccessTime + INTERVAL #0 SECOND > NOW()', Config::$config->security->sessionLength)
			->first();
	}
	
	public function insert() {
		if ($this->user)
			$this->userID = $this->user->id;
		$this->startTime = time();
		$this->lastAccessTime = time();
		
		self::query()
			->whereEquals('userID', $this->userID)
			->update(array('loggedOut' => true));
		
		if (!$this->key)
			$this->key = $this->generateKey();
		$this->insertAll();
	}
	
	public function delete() {
		parent::delete();
	}
	
	public function hit() {
		$this->updateFields(array('lastAccessTime!' => 'NOW()'));
		$this->lastAccessTime = time();
	}
	
	public function logout() {
		$this->loggedOut = true;
		$this->updateFields(array('loggedOut' => true));
	}
	
	private function generateKey() {
		return hash('sha256',
			'f7421cbfb0690749d8f91d3e9190fe546c429a86b40b13fd8099995413ad9ed4'.
			$this->user->name.microtime().rand(0, 100000).$this->ip);
	}
}


