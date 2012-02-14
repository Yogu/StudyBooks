<?php
defined('IN_APP') or die;

class Lib {
	public static function loadDwoo() {
		require_once(ROOT_PATH.'libraries/dwoo/dwooAutoload.php');
	}

	public static function loadMarkdown() {
		require_once(ROOT_PATH.'libraries/markdown/markdown.php');
	}
}