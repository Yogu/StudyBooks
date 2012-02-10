<?php

function Dwoo_Plugin_html_compile(Dwoo_Compiler $compiler, $text)
{
  return "htmlspecialchars($text)";
}
?>