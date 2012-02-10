<?php

function Dwoo_Plugin_url_compile(Dwoo_Compiler $compiler, $text)
{
  return "rawurlencode($text)";
}
?>