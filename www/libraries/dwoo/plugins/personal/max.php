<?php

function Dwoo_Plugin_max_compile(Dwoo_Compiler $compiler, $a, $b)
{
  return "max($a, $b)";
}
?>