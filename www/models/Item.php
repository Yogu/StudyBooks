<?php
defined('IN_APP') or die;

class Item {
	public $id;
	public $package;
	public $fileName;
	public $time;
	public $contentType;
	public $size;
	public $width;
	public $height;
	public $cameraManufacturer;
	public $cameraModel;
	public $path;
	
	public static function getList($condition = '', $offset = 0, $count = -1, $params = null) {
		if ($count == -1)
			$count = 2147483647;
		
		$result = DataBase::query(
			"SELECT item.id, item.fileName, UNIX_TIMESTAMP(item.time) AS time, ".
				"item.contentType, item.size, width, height, cameraManufacturer, cameraModel, ".
				"package.id AS packageID, UNIX_TIMESTAMP(package.postTime) AS postTime, ".
				"package.fileName AS packageFileName, code, isPublished, ".
				"user.id AS posterID, user.name AS posterName ".
			"FROM ".DataBase::table('items')." AS item ".
			"INNER JOIN ".DataBase::table('Packages')." AS package ".
				"ON package.id = item.packageID ".
			"INNER JOIN ".DataBase::table('Users')." AS user ".
				"ON user.id = package.posterID ".
			$condition.' '.
			"LIMIT $offset, $count", $params);
		$list = array();
		while ($row = mysql_fetch_object($result)) {
			$item = new Item();
			$item->id = $row->id;
			$item->fileName = $row->fileName;
			$item->time = $row->time;
			$item->contentType = $row->contentType;
			$item->size = $row->size;
			$item->width = $row->width;
			$item->height = $row->height;
			$item->cameraManufacturer = $row->cameraManufacturer;
			$item->cameraModel = $row->cameraModel;
			$item->package = new Package();
			$item->package->id = $row->packageID;
			$item->package->postTime = $row->postTime;
			$item->package->fileName = $row->packageFileName;
			$item->package->isPublished = $row->isPublished;
			$item->package->code = $row->code;
			$item->package->poster = new User();
			$item->package->poster->id = $row->posterID;
			$item->package->poster->name = $row->posterName;
			$item->package->makePath();
			$item->makePath();
			$list[] = $item;
		}
		return $list;
	}
	
	public static function getFromPackage(Package $package) {
		return self::getList("WHERE package.id = #0 ORDER BY item.fileName ASC",
			0, -1, $package->id);
	}
	
	public static function getSingle($condition, $params = null) {
		$list = self::getList($condition, 0, 1, $params);
		return count($list) ? $list[0] : null;
	}
	
	public static function getByID($id) {
		return self::getSingle('WHERE item.id = #0', (int)$id);
	}
	
	public static function getCount() {
		$result = DataBase::query(
			"SELECT COUNT(id) ".
			"FROM ".DataBase::table('items'));
		list($count) = mysql_fetch_array($result);
		return $count;
	}
	
	public function insert() {
		$this->makePath();
		
		DataBase::query(
			"INSERT INTO ".DataBase::table('Items')." ".
			"SET packageID = #0, fileName = #1, time = FROM_UNIXTIME(#2), ".
				"contentType = #3, size = #4, width = #5, height = #6, ".
				"cameraManufacturer = #7, cameraModel = #8",
			array($this->package->id, $this->fileName, $this->time, $this->contentType,
				$this->size, $this->width, $this->height, $this->cameraManufacturer,
				$this->cameraModel));
		$this->id = DataBase::getInsertID();
	}
	
	public function delete() {
		DataBase::query(
			"DELETE FROM ".DataBase::table('items')." ".
			"WHERE id = #0",
			$this->id);
	}
	
	public function makePath() {
		$this->path = dirname($this->package->path).'/content/'.$this->fileName;
	}
	
	public function createThumbnail() {
		$thumbPath = dirname($this->package->path).'/thumbs/'.$this->fileName;
		if (!file_exists($thumbPath)) {
			$image = new Image($this->path);
			$image->resizeAndCrop(Config::$config->gallery->thumbWidth,
				Config::$config->gallery->thumbHeight);
			mkdir(dirname($thumbPath), 0777, true);
			$image->saveToFile($thumbPath, 'image/jpeg');
		}
		return $thumbPath;
	}
	
	public function createDisplayImage() {
		$thumbPath = dirname($this->package->path).'/display/'.$this->fileName;
		if (!file_exists($thumbPath)) {
			$image = new Image($this->path);
			$image->resizeProportionally(Config::$config->gallery->displayWidth,
				Config::$config->gallery->displayHeight);
			mkdir(dirname($thumbPath), 0777, true);
			$image->saveToFile($thumbPath, 'image/jpeg');
		}
		return $thumbPath;
	}
	
	public static function deleteThumbnails() {
		$packagesPath = ROOT_PATH.'packages/';
		if ($handle = opendir($packagesPath)) {
	    while (($file = readdir($handle)) !== false) {
	    	$thumbsPath = $packagesPath.$file.'/thumbs/';
				if (file_exists($thumbsPath))
					FileInfo::deleteDirectory($thumbsPath);
	    }
		}
	}
	
	public static function deleteDisplayImages() {
		$packagesPath = ROOT_PATH.'packages/';
		if ($handle = opendir($packagesPath)) {
	    while (($file = readdir($handle)) !== false) {
	    	$thumbsPath = $packagesPath.$file.'/display/';
				if (file_exists($thumbsPath))
					FileInfo::deleteDirectory($thumbsPath);
	    }
		}
	}
}


