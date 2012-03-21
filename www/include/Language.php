<?php
defined('IN_APP') or die;

class Language {
	private $values;
	
	const DEFAULT_PATTERN = 'config/language.%.ini';
	
	public function __construct($fileName) {
		if (!file_exists($fileName))
			throw new Exception("Language file ".$fileName." does not exist");
		$this->values = parse_ini_file($fileName);
		if (!$this->values)
			throw new Exception("Corrupt language file: ".$fileName);
	}
	
	public static function forLocale($locale) {
		return new Language(str_replace('%', $locale, self::DEFAULT_PATTERN));
	}
	
	public function __get($name) {
		return $this->values[$name];
	}
}


