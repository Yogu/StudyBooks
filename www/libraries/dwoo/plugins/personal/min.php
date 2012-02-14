<?php

function Dwoo_Plugin_min_compile(Dwoo_Compiler $compiler, $a, $b)
{
  return "min($a, $b)";
}
?>