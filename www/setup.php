<?php

include('include/Loader.php');
Loader::init();

define('NO_DB', true);
if (!file_exists(ROOT_PATH.'config/config.ini')) {
	if (!copy(ROOT_PATH.'config/config.default.ini', ROOT_PATH.'config/config.ini')) {
		echo "Unable to copy config file. Make sure that php has write permissions in the config directory.";
		die;
	}
}
Loader::loadConfig();

$request = Request::createRequest();
if ($request->post('submit')) {
	$errors = '';
	
	$dataBase = new stdclass();
	$dataBase->host = trim($request->post('dbHost'));
	$dataBase->user = trim($request->post('dbUser'));
	$dataBase->password = (string)$request->post('dbPassword');
	$dataBase->dataBase = trim($request->post('dbDataBase'));
	$dataBase->prefix = trim($request->post('dbPrefix'));
	$admin = new stdclass();
	$admin->name = trim($request->post('adminName'));
	$admin->password = (string)$request->post('adminPassword');
	$admin->passwordConfirmation = (string)$request->post('adminPasswordConfirmation');
	
	if (!$dataBase->host)
		$errors .= '<p>'.Language::$l['SETUP_DB_NO_HOST'];
	if (!$dataBase->user)
		$errors .= '<p>'.Language::$l['SETUP_DB_NO_USER'];
	if (!$dataBase->password)
		$errors .= '<p>'.Language::$l['SETUP_DB_NO_PASSWORD'];
	if (!$dataBase->dataBase)
		$errors .= '<p>'.Language::$l['SETUP_DB_NO_DATABASE'];
	if (!$admin->name)
		$errors .= '<p>'.Language::$l['SETUP_NO_ADMIN_NAME'];
	if (!$admin->password)
		$errors .= '<p>'.Language::$l['SETUP_NO_ADMIN_PASSWORD'];
	if ($admin->password != $admin->passwordConfirmation)
		$errors .= '<p>'.Language::$l['SETUP_INVALID_ADMIN_PASSWORD_CONFIRMATION'];
	
	if (!$errors) {
		// Test connection
		$link = @mysql_connect($dataBase->host, $dataBase->user, $dataBase->password);
		if (!$link) 
			$errors .= '<p>'.Language::$l['SETUP_DB_INVALID_CREDENTIALS'];
		else {
			if (!@mysql_select_db($dataBase->dataBase, $link))
				$errors .= '<p>'.Language::$l['SETUP_DB_INVALID_DATABASE_NAME'];
		}
	}
	
	$data = new stdclass();
	
	if ($errors) {
		$response = new View($request, 'setup', 'Setup');
		$data->errors = $errors;
		$response->send();
	} else {
		Config::$config->dataBase = $dataBase;
		$inflater = new DataBaseInflater();
		$willDrop = !$inflater->canCreateScheme($e);
		
		if ($request->post('isStep2') && (!$willDrop || $request->post('confirmDrop'))) {
			$config = new stdclass();
			$config->dataBase = $dataBase;
			$config->security = new stdclass();
			$config->security->secretCode = Security::generateHash();
			Config::$config->security->secretCode = $config->security->secretCode;
			Config::save($config);
			setup($willDrop);
			createAdmin($admin);
			
			$response = new View($request, $data, 'success', 'Setup');
			$response->send();
		} else {
			$data->errors = $errors;
			$data->willDrop = $willDrop;
			$response = new View($request, $data, 'confirm', 'Setup');
			$response->send();
		}
	}
} else {
	$response = new View($request, null, 'setup', 'Setup');
	$response->send();
}

function setup($drop) {
	$inflater = new DataBaseInflater();
	$inflater->verify();
	if ($drop) {
		$inflater->dropTables();
	}
	$inflater->createScheme();
	$inflater->insertData();
}

function createAdmin($admin) {
	$user = new User();
	$user->name = $admin->name;
	$user->rawPassword = $admin->password;
	$user->role = 'admin'; 
	$user->insert();
}

?>
