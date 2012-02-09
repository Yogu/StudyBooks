<?php
defined('IN_APP') or die;

class Uploader {
	private $tempFile;
	public $fileName;
	public $isAjax = false;
	public $error = '';
	public $package;
	public $request;

	public function __construct(Request $request) {
		$this->request = $request;
	}

	public function loadFromPost() {
		$this->tempFile = $_FILES['file']['tmp_name'];
		$this->fileName = $_FILES['file']['name'];

		return $this->import();
	}

	public function loadFromAjax() {
		$this->fileName = $_GET['qqfile'];
		$this->isAjax = true;

		if (!$this->fileName) {
			$this->error = 'No file selected.';
			return false;
		}

		$input = fopen("php://input", "r");
		$this->tempFile = tempnam(ROOT_PATH.'tmp/', 'upload');
		$target = fopen($this->tempFile, "w");
		fseek($target, 0, SEEK_SET);
		$realSize = stream_copy_to_stream($input, $target);
		fclose($input);
		// if the copying task takes a long time, the data base connection
		// might have been gone
		DataBase::checkConnection();

		if (isset($_SERVER["CONTENT_LENGTH"])){
			$specifiedSize = (int)$_SERVER["CONTENT_LENGTH"];
		} else {
			throw new Exception('Getting content length is not supported.');
		}
		 
		if ($realSize != $this->getSize()) {
			$this->error = 'Upload has been cancelled.';
			unlink($this->tempFile);
			$this->tempFile = '';
			return false;
		}

		return $this->import();
	}

	private function import() {
		// ZIP
		$zip = new ZipArchive();
		if ($zip->open($this->tempFile) === true) {
			$type = 'application/zip';
			$fileCount = $zip->numFiles;
			$zip->close();
		}

		// RAR (not supported by default)
		/*$rar = RarArchive::open($tempname);
		 if ($rar) {
			$type = 'application/x-rar-compressed';
			$rar->close();
			}*/

		if ($type) {
			$package = new Package();
			$package->poster = $this->request->user;
			$package->fileName = FileInfo::changeFileExtension(
			FileInfo::getSafeFileName($this->fileName), '.zip');
			$package->size = filesize($this->tempFile);
			$package->fileCount = $fileCount;
			$package->contentType = $type;
			$package->insert();
			mkdir(dirname($package->path));
			file_put_contents(dirname($package->path).'/index.html', '');
			if ($this->isAjax) {
				rename($this->tempFile, $package->path);
			} else {
				move_uploaded_file($this->tempFile, $package->path);
			}
			$package->extract();
			$this->package = $package;
				
			return true;
		} else {
			/*if ($this->isAjax)
				unlink($tempName);*/
			$this->error = 'File '.$this->fileName.' is no valid zip archive.'.
			($this->isAjax ? ' '.$this->tempFile : '');
			return false;
		}
	}
}

?>