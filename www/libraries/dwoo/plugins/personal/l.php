<?php

function Dwoo_Plugin_l_compile(Dwoo_Compiler $compiler, $key)
{
  return "Language::\$l[$key]";
}
?>