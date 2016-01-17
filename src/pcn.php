<?php
namespace Procrustes;

require_once 'vendor/autoload.php';

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

function retrieve_args()
{
  $opt = new Getopt(
    [
      (new Option('h', 'help'))->setDescription('Prints this help'),
      (new Option('v', 'verbose'))->setDescription('Verbose output'),
      (new Option('f', 'file-title'))->setDescription('Use file name for title tag'),
      (new Option('x', 'sort-lex'))->setDescription('Sort files lexicographically'),
      (new Option('t', 'tree-dst'))->setDescription('Retain the tree structure of the source album at destination'),
      (new Option('p', 'drop-dst'))->setDescription('Do not create destination directory'),
      (new Option('r', 'reverse'))->setDescription('Copy files in reverse order (last file first)'),
      (new Option('e', 'file-type', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Accept only audio files of the specified type'),
      (new Option('u', 'unified-name', Getopt::REQUIRED_ARGUMENT))
        ->setDescription('Base name for everything but the "Artist" tag'),
      (new Option('b', 'album-num', Getopt::REQUIRED_ARGUMENT))->setDescription('Album number'),
      (new Option('a', 'artist-tag', Getopt::REQUIRED_ARGUMENT))->setDescription('"Artist" tag'),
      (new Option('g', 'album-tag', Getopt::REQUIRED_ARGUMENT))->setDescription('"Album" tag')
    ]
  );

  $opt->parse();

  if ($opt->getOption('help')) {
    print $opt->getHelpText();
    exit(2);
  }
  if (count($opt->getOperands()) !== 2) {
    print "Command line syntax: <src> and <dst> operands required.\n" . $opt->getHelpText();
    exit(2);
  }
  if (!file_exists($opt->getOperand(0))) {
    print "Source directory \"{$opt->getOperand(0)}\" is not there.\n";
    exit(2);
  }
  if (!file_exists($opt->getOperand(1))) {
    print "Destination path \"{$opt->getOperand(1)}\" is not there.\n";
    exit(2);
  }
  return $opt;
}

$args = null;

function arg($key)
/*
  Returns any command line option or operand
 */
{
  global $args;
  if ($key === 'src') return rtrim($args->getOperand(0), '/\\');
  if ($key === 'dst') return rtrim($args->getOperand(1), '/\\');
  return $args->getOption($key);
}

function join_paths()
/*
  Returns any number of paths correctly joined
 */
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
  return mb_strtoupper(trim($parts['extension'], '.')) === mb_strtoupper(trim($ext, '.'));
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
/*
  Compares arrays of integers using 'string semantics'
 */
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

function strcmp_naturally($x, $y)
/*
  If both strings contain digits, returns numerical comparison based on the numeric
  values embedded in the strings, otherwise returns the standard string comparison.
  The idea of the natural sort as opposed to the standard lexicographic sort is one of coping
  with the possible absence of the leading zeros in 'numbers' of files or directories
 */
{
  $a = str_strip_numbers($x);
  $b = str_strip_numbers($y);
  return ($a && $b) ? array_cmp($a, $b) : strcmp($x, $y);
}

function make_initials($name, $sep = ".", $trail = ".", $hyph = "-")
/*
  Reduces a string of names to initials
 */
{
  preg_match_all("!\"!", $name, $matches);
  $qcnt = count($matches[0]);
  $enm = ($qcnt === 0 || $qcnt % 2) ? $name : preg_replace('/"(.*?)"/', " ", $name);

  $split_by_space = function($nm) use($sep)
  {
    $spl = preg_split('/\s+/', trim($nm));
    $ini = array_map(function($x) {return mb_substr($x, 0, 1);}, $spl);
    return mb_strtoupper(join($sep, $ini));
  };

  $spl = preg_split('!' . $hyph . '!', $enm);
  return join($hyph, array_map($split_by_space, $spl)) . $trail;
}

function main()
{
  global $args;
  $args = retrieve_args();
  print arg('src') . ' ' . arg('dst') . "\n";
  print "Run as script." . "\n";
}

if (!debug_backtrace()) {
  main();
}
?>