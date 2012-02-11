<?php

/**
 * Provides several methods related to utf-8 strings
 */
class Strings {
	/**
	 * Gets the length of a string
	 * 
	 * @param string $str
	 * @return int
	 */
	public static function length($str) {
		return mb_strlen($str, 'utf-8');
	}
	
	/**
	 * Transforms a string to upper case
	 * 
	 * @param string $str
	 * @return string
	 */
	public static function toUpper($str) {
		return mb_strtoupper($str, 'utf-8');
	}
	
	/**
	 * Transforms a string to upper case
	 * 
	 * @param string $str
	 * @return string
	 */
	public static function toLower($str) {
		return mb_strtolower($str, 'utf-8');
	}
	
	/**
	 * Gets a part of a string
	 * 
	 * @param string $str
	 * @param int $start the start index of the part
	 * @param int $length the length of the part
	 * @return string
	 */
	public static function substring($str, $start, $length = null) {
		if (func_num_args() == 2)
			return mb_substr($str, $start, self::length($str), 'utf-8');
		else
			return mb_substr($str, $start, $length, 'utf-8');
	}
	
	/**
	 * Gets the index of the first occurance of a string in another string
	 * Enter description here ...
	 * @param string $haystack the string being checked
	 * @param string $needle the string to find
	 * @param int $offset the search offset
	 */
	public static function indexOf($haystack, $needle, $offset = 0) {
		return mb_strpos($haystack, $needle, $offset, 'utf-8');
	}
	
	/**
	 * Finds the last occurance of a needle and returns the part beginning at the
	 * index of the occurance.
	 * 
	 * @param string $haystack the string to search in
	 * @param string $needle the string to find
	 * @return string the part beginning at the last occurance of $needle
	 */
	public static function strrchr($haystack, $needle) {
		return mb_strrchr($haystack, $needle, false, 'utf-8');
	}
	
	// 
	/**
	 * Replaces whitespace sequences by a single space and removes control chars
	 * 
	 * @param string $name
	 * @return string
	 */
	public static function normalize($name) {
		return trim(preg_replace('/\x00-\x1F/', '',
			preg_replace('/[\s]+/', ' ', $name)));
	}
	
	/**
	 * Gets the normalizes lower-case variant of a string
	 * 
	 * Returns a lower-case and trimmed version of $name which can be -
	 * ESCAPED (!) - compared to LOWER()-converted data base names
	 * 
	 * @param string $name
	 * @return string
	 */
	public static function unitize($name) {
	 	return self::toLower(self::normalize($name));
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
}


