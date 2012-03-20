<?php

include('include/Loader.php');

Loader::init();
Loader::loadConfig();

$request = Request::createRequest();
$request->getResponse()->send();

?>