<?php

class Security {
	public static function generateRandomPassword() {
		$pool = "qwertzupasdfghkyxcvbnm23456789WERTZUPLKJHGFDSAYXCVBNM";
		srand((double)(microtime() * 1000000));
		for ($i = 0; $i < 8; $i++)
    	$result .= substr($pool, rand() % strlen($pool), 1);
  	return $result;
	}
}