<?php
defined('IN_APP') or die;

class Language {
	public static $l;
	
	public static function load() {
		$locale = Config::$config->general->locale;
		$fileName = ROOT_PATH . 'config/language.'.$locale.'.ini';
		if (!file_exists($fileName))
			throw new Exception("Language file for locale " . $locale . " does not exist");
		$strings = parse_ini_file($fileName);
		if (!$strings)
			throw new Exception("Corrupt language file for locale " . $locale);
		self::$l = $strings;
	}
}

?>
