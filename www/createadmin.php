<?php 

include('include/init.php');

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