<?php
defined('IN_APP') or die;

class Session {
	public $id;
	public $user;
	public $key;
	public $startTime;
	public $lastAccessTime;
	public $ip;
	public $userAgent;
	
	public static function getList($condition = '', $offset = 0, $count = 2147483647, $params = null) {
		$result = DataBase::query(
			"SELECT session.id AS id, `key`, UNIX_TIMESTAMP(startTime) AS startTime, ".
				"UNIX_TIMESTAMP(lastAccessTime) AS lastAccessTime, ".
				"ip, userAgent, user.id AS userID, user.name AS userName, ".
				"email, password, UNIX_TIMESTAMP(createTime) AS createTime, ". 
				"UNIX_TIMESTAMP(lastLoginTime) AS lastLoginTime, role, isBanned ".
			"FROM ".DataBase::table('Sessions')." AS session ".
			"INNER JOIN ".DataBase::table('Users')." AS user ".
				"ON user.id = session.userID ".
			$condition.' '.
			"LIMIT $offset, $count", $params);
		$list = array();
		while ($item = mysql_fetch_object($result)) {
			$session = new Session();
			$session->id = $item->id;
			$session->key = $item->key;
			$session->startTime = $item->startTime;
			$session->lastAccessTime = $item->lastAccessTime;
			$session->ip = $item->ip;
			$session->userAgent = $userAgent;
			$session->user = new User();
			$session->user->id = $item->userID;
			$session->user->name = $item->userName;
			$session->user->email = $item->email;
			$session->user->role = $item->role;
			$session->user->isBanned = $item->isBanned;
			$session->user->hashedPassword = $item->password;
			$session->user->createTime = $item->createTime;
			$session->user->lastLoginTime = $item->lastLoginTime;
			$list[] = $session;
		}
		return $list;
	}
	
	public static function getSingle($condition, $params = null) {
		$list = self::getList($condition, 0, 1, $params);
		return count($list) ? $list[0] : null;
	}
	
	public static function getVaildByKey($key) {
		return self::getSingle(
			'WHERE `key` = #0 AND NOT loggedOut AND lastAccessTime + INTERVAL '.
				Config::$config->security->sessionLength.' SECOND > NOW()',
			$key);
	}
	
	public function insert() {
		// Logout all other sessions from this user
		DataBase::query(
			"UPDATE ".DataBase::table('sessions')." ".
			"SET loggedOut = '1' ".
			"WHERE userID = #0",
			$this->user->id);
		
		if (!$this->key)
			$this->key = $this->generateKey();
		DataBase::query(
			"INSERT INTO ".DataBase::table('Sessions')." ".
			"SET userID = #0, `key` = #1, startTime = NOW(), lastAccessTime = NOW(), ".
				"ip = #2, userAgent = #3 ",
			array($this->user->id, $this->key, $this->ip, $this->userAgent));
		$this->id = DataBase::getInsertID();
	}
	
	public function delete() {
		DataBase::query(
			"DELETE FROM ".DataBase::table('sessions')." ".
			"WHERE id = #0",
			$this->id);
	}
	
	public function hit() {
		DataBase::query(
			"UPDATE ".DataBase::table('sessions')." ".
			"SET lastAccessTime = NOW() ".
			"WHERE id = #0",
			$this->id);
	}
	
	public function logout() {
		DataBase::query(
			"UPDATE ".DataBase::table('sessions')." ".
			"SET loggedOut = 1 ".
			"WHERE id = #0",
			$this->id);
	}
	
	private function generateKey() {
		return hash('sha256',
			'f7421cbfb0690749d8f91d3e9190fe546c429a86b40b13fd8099995413ad9ed4'.
			$this->user->name.microtime().rand(0, 100000).$this->ip);
	}
}

?>
