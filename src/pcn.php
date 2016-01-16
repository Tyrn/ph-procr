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

if (!debug_backtrace()) {
  print sans_ext('/able/baker/cord.mp3') . "\n";
}
?>