<?php
defined('IN_APP') or die;

class FileInfo {
	public static function getMaxUploadSize() {
		return min(self::letToNum(ini_get('post_max_size')),
			self::letToNum(ini_get('upload_max_filesize')));
	}
	
	/**
	 * Formats a given count of bytes as file size
	 * 
	 * @param $bytes the count of bytes
	 * @param $digits the count of digits to display
	 * @return string the formatted file size string
	 */
	public static function formatFileSize($bytes, $digits = 3) {
		$units = array('Byte', 'Bytes', 'KB', 'MB', 'GB', 'TB', 'EB');
		
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		
		$bytes /= pow(1024, $pow);
		                                             
		$int = floor($bytes);
		$precision = $digits - strlen(floor($bytes));
		if ($precision > 0 && $pow != 0) {
			$fractional = round(($bytes-$int) * pow(10, $precision));
			$fractional = "," . str_pad($fractional, $precision, '0');
		}
		$index = $pow + 1;
		if ($index == 1 && $bytes == 1)
			$index = 0;
		return $int.$fractional.' '.$units[$index];
	}
	
	public static function setMaxUploadSize($size) {
		ini_set('upload_max_filesize', $size);
	}
	
	public static function getFreeSpace() {
		$host = Host::getCurrent();
		return $host->totalSpace - $host->getUsedSpace();
	}
	
	public static function deleteDirectory($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir")
						self::deleteDirectory($dir."/".$object);
					else unlink($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}
	
	/**
	 * This function transforms the php.ini notation for numbers (like '2M') to an
	 * integer (2*1024*1024 in this case)
	 */
	private static function letToNum($v){
		$l = substr($v, -1);
		$ret = substr($v, 0, -1);
		switch (strtoupper($l)) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
				break;
		}
		return $ret;
	}
	
	public static function changeFileExtension($fileName, $extension) {
		$dir = dirname($fileName);
		if ($dir == '.')
			$dir = '';
		$file = basename($fileName);
		
		$ext = strrchr($file, '.');
		if (!$ext)
			$file .= $extension;
		else if (strtolower($ext) != strtolower($extension))
			$file = substr($file, 0, -strlen($ext)).$extension;
		return ($dir ? $dir.'/' : '').$file;
	}
	
	public static function getSafeFileName($fileName) {
		$fileName = trim($fileName, '.');
		$fileName = str_replace('/', '', $fileName);
		$fileName = str_replace('..', '', $fileName);
		$ext = trim(strrchr($fileName, '.'));
		$base = trim(substr($fileName, 0, -strlen($ext)));
		if (!$base)
			$fileName = 'file' . ($ext ? '.' . $ext : '');
		return $fileName;
	}
	
	public static function safeFileRewrite($fileName, $dataToSave) {
		if ($fp = fopen($fileName, 'w')) {
			$startTime = microtime();
			do {
				$canWrite = flock($fp, LOCK_EX);
				//If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
				if(!$canWrite)
					usleep(round(rand(0, 100)*1000));
			} while ((!$canWrite)and((microtime()-$startTime) < 1000));
	
			// file was locked so now we can store information
			if ($canWrite) {
				fwrite($fp, $dataToSave);
				flock($fp, LOCK_UN);
			}
			fclose($fp);
		}
	}
}