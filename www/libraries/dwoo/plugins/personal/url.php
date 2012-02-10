<?php

function Dwoo_Plugin_url_compile(Dwoo_Compiler $compiler, $action = '', $controller = '', $parameters = array())
{
  return "Router::getURL(array_merge(is_array($parameters) ? $parameters : array(), array('controller' => $controller ? $controller : \$this->scope['request']->controller, 'action' => $action ? $action : ($controller && $controller != \$this->scope['request']->controller ? 'index' : \$this->scope['request']->action))))";
  
}
?>