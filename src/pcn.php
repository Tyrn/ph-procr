<?php
namespace Procrustes;

function join_paths()
{
  $paths = array();
  foreach (func_get_args() as $arg) {
    if ($arg !== '') {$paths[] = $arg;}
  }
  return preg_replace('#/+#', '/', join('/', $paths));
}

function sans_ext($pth)
/*
  Discards file extension
 */
{
  $parts = pathinfo($pth);
  return join_paths($parts['dirname'], $parts['filename']);
}

function has_ext_of($pth, $ext)
/*
  Returns True, if path has extension ext, case and leading dot insensitive
 */
{
  $parts = pathinfo($pth);
  return strtoupper(trim($parts['extension'], '.')) === strtoupper(trim($ext, '.'));
}

function str_strip_numbers($s)
/*
  Returns a vector of integer numbers
  embedded in a string argument
 */
{
  preg_match_all("!\d+!", $s, $matches);
  return array_map(function($ss) {return intval($ss);}, $matches[0]);
}

if (!debug_backtrace()) {
  print sans_ext('/able/baker/cord.mp3') . "\n";
  preg_match_all("!\d+!", "ab11cdd2k.144", $matches);
  print_r($matches[0]);
}
?>