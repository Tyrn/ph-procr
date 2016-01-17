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

function array_cmp($x, $y)
{
  if(count($x) === 0) return (count($y) === 0) ? 0 : -1;
  if(count($y) === 0) return (count($x) === 0) ? 0 : 1;

  for($i = 0; $x[$i] === $y[$i]; $i++) {
    if($i === count($x) - 1 || $i === count($y) - 1) {
      // Short array is a prefix of the long one; end reached. All is equal so far.
      if(count($x) === count($y)) return 0;   // Long array is no longer than the short one.
      return (count($x) < count($y)) ? -1 : 1;
    }
  }
  // Difference encountered.
  return ($x[$i] < $y[$i]) ? -1 : 1;
}

if (!debug_backtrace()) {
  print sans_ext('/able/baker/cord.mp3') . "\n";
  preg_match_all("!\d+!", "ab11cdd2k.144", $matches);
  print_r($matches[0]);
}
?>