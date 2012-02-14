<?php

// Plugin name 'count' causes troubles
function Dwoo_Plugin_arrcount_compile(Dwoo_Compiler $compiler, $arr)
{
  return "count($arr)";
}
?>