<?php 

include('include/Loader.php');

Loader::load();

if (Config::$config->general->isDebugMode) {
	$user = new User();
	$user->name = 'admin';
	$user->role = 'admin';
	$user->rawPassword = 'admin';
	$user->insert();
	echo 'Admin created';
} else {
	echo 'Not in debug mode';
}

?>