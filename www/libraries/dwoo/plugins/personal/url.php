<?php

function Dwoo_Plugin_url(Dwoo $dwoo, $action = '', $controller = '', $parameters = array())
{
	if (is_array($action))
		$parameters = $action;
	else {
		$action = $action ? $action : $dwoo->scope['request']->action;
		$controller = $controller ? $controller : $dwoo->scope['request']->controller;
		$a = array('action' => $action, 'controller' => $controller);
		if (is_array($parameters))
			$parameters = array_merge($parameters, $a);
		else
			$parameters = $a;
	}
	
	if (($key = array_search('_addCurrent', $parameters)) !== false) {
		$parameters = array_merge($dwoo->scope['request']->parameters, $parameters);
		unset($parameters[$key]);
	}
	
	return Router::getURL($parameters);
}
?>