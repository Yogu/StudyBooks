<?php

class Security {
	public static function generateRandomPassword() {
		$pool = "qwertzupasdfghkyxcvbnm23456789WERTZUPLKJHGFDSAYXCVBNM";
		srand((double)(microtime() * 1000000));
		for ($i = 0; $i < 8; $i++)
    	$result .= substr($pool, rand() % strlen($pool), 1);
  		return $result;
	}
	
	public static function generateHash() {
		return hash('sha256',
			'aaee7dd166c9df6b07dce24ae4d9882e2f32cf7ad44a48650f67ee470c24ee5a'.
			microtime().rand(0, 100000));
	}
}