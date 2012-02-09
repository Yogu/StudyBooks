<?php
defined('IN_APP') or die;

class Package {
	public $id;
	public $poster;
	public $postTime;
	public $fileName;
	public $contentType;
	public $size;
	public $fileCount;
	public $isPublished;
	public $code;
	public $path;
	public $isExtracted = false;
	
	public static function getList($condition = '', $offset = 0, $count = 2147483647, $params = null) {
		$result = DataBase::query(
			"SELECT package.id, UNIX_TIMESTAMP(package.postTime) AS postTime, ".
				"fileName, size, fileCount, contentType, code, isPublished, isExtracted, ".
				"user.id AS posterID, user.name AS posterName ".
			"FROM ".DataBase::table('packages')." AS package ".
			"INNER JOIN ".DataBase::table('Users')." AS user ".
				"ON user.id = package.posterID ".
			$condition.' '.
			"ORDER BY postTime DESC ".
			"LIMIT $offset, $count", $params);
		$list = array();
		while ($item = mysql_fetch_object($result)) {
			$package = new Package();
			$package->id = $item->id;
			$package->postTime = $item->postTime;
			$package->fileName = $item->fileName;
			$package->contentType = $item->contentType;
			$package->size = $item->size;
			$package->fileCount = $item->fileCount;
			$package->isPublished = $item->isPublished;
			$package->isExtracted = $item->isExtracted;
			$package->code = $item->code;
			$package->poster = new User();
			$package->poster->id = $item->posterID;
			$package->poster->name = $item->posterName;
			$package->makePath();
			$list[] = $package;
		}
		return $list;
	}
	
	public static function getSingle($condition, $params = null) {
		$list = self::getList($condition, 0, 1, $params);
		return count($list) ? $list[0] : null;
	}
	
	public static function getByID($id) {
		return self::getSingle('WHERE package.id = #0', (int)$id);
	}
	
	public function insert() {
		if (!$this->code)
			$this->code = $this->generateCode();
		
		DataBase::query(
			"INSERT INTO ".DataBase::table('Packages')." ".
			"SET posterID = #0, postTime = NOW(), isPublished = '1', fileName = #1, ".
				"size = #2, contentType = #3, hostID = #4, code = #5, fileCount = #6",
			array($this->poster->id, $this->fileName, $this->size, $this->contentType,
				Host::getCurrent()->id, $this->code, $this->fileCount));
		$this->id = DataBase::getInsertID();
		$this->makePath();
	}
	
	public function delete() {
		DataBase::query(
			"DELETE FROM ".DataBase::table('packages')." ".
			"WHERE id = #0",
			$this->id);
			
		DataBase::query(
			"DELETE FROM ".DataBase::table('items')." ".
			"WHERE packageID = #0",
			$this->id);
	}
	
	public function extract() {
		DataBase::query(
			"DELETE FROM ".DataBase::table('items')." ".
			"WHERE packageID = #0",
			$this->id);
		
		$this->extractArchive($this->path, '');
			
		DataBase::query(
			"UPDATE ".DataBase::table('packages')." ".
			"SET isExtracted = '1' ".
			"WHERE id = #0",
			$this->id);
	}
	
	private function extractArchive($fileName, $prefix) {
		$directory = dirname($this->path).'/content/'.$prefix;
		
		mkdir($directory, 0777, true);
		$zip = new ZipArchive();
		if ($zip->open($fileName) === true) {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$stat = $zip->statIndex($i);
				$fileName = $stat['name'];
				$ext = strtolower(strrchr($fileName, '.'));
				$itemPath = $directory.$fileName;
				if ($ext == '.jpg' || $ext == '.jpeg') {
				 	$zip->extractTo($directory, $fileName);
					$info = GetImageSize($itemPath);
					$imageType = $info[2];
					if ($imageType == IMAGETYPE_JPEG || $imageType == IMAGETYPE_JPEG2000) {
						$item = new Item();
						$item->package = $this;
						$item->fileName = $prefix.$fileName;
						$item->size = $stat['size'];
						$item->width = $info[0];
						$item->height = $info[1];
						$exif = exif_read_data($itemPath, 0, true);
						$item->cameraManufacturer = $exif['IFD0']['Make'];
						$item->cameraModel = $exif['IFD0']['Model'];
						$item->time = strtotime($exif['EXIF']['DateTimeOriginal']);
						if (!$item->time)
							$item->time = $stat['mtime'];
						$item->insert();
					} else {
						unlink($itemPath);
					}
				} else if ($ext == '.zip') {
				 	$zip->extractTo($directory, $fileName);
					$this->extractArchive($itemPath, $prefix.$fileName.'.content/');
				}
			}
		}
	}
	
	private function generateCode() {
		return substr(
			str_replace('/', '-',
				base64_encode(
					hash(
						'sha256', microtime().
						'b05ad9cfd8de115f6bc19040ea6def98e98bea29635d7f20e82ef60380f11ff2'.
						$this->fileName.rand(0, 100000), true
					)
				)
			), 0, 16
		);
	}
	
	public function makePath() {
		$this->path = ROOT_PATH.'packages/'.$this->id.'-'.$this->code.'/'.$this->fileName;
	}
}

?>
