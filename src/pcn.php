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
  if (!is_dir($opt->getOperand(0))) {
    print "Source directory \"{$opt->getOperand(0)}\" is not there.\n";
    exit(2);
  }
  if (!is_dir($opt->getOperand(1))) {
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

function file_name($pth)
/*
  Extracts file name without extension
 */
{
  $parts = pathinfo($pth);
  return $parts['filename'];
}

function file_ext($pth)
/*
  Extracts extension
 */
{
  $parts = pathinfo($pth);
  return $parts['extension'];
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

/*
  Returns true, if pth is a recognized audio file
 */
function is_audio_file($pth)
{
  if (is_dir($pth)) return false;
  $parts = pathinfo($pth);
  if (in_array(mb_strtoupper($parts['extension']), ['MP3', 'M4A', 'M4B', 'OGG', 'WMA', 'FLAC'])) return true;
  return false;
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

function collect_dirs_and_files($absPath, $fileCondition)
/*
  Returns a list of directories in absPath directory, and a list of files filtered by fileCondition
 */
{
  $raw = scandir($absPath); array_shift($raw); array_shift($raw);
  $lst = array_map(function($x) use($absPath) {return join_paths($absPath, $x);}, $raw);
  $dirs = []; $files = [];
  foreach ($lst as $iv) {
    if (is_dir($iv)) array_push($dirs, $iv); else if ($fileCondition($iv)) array_push($files, $iv);
  }
  return [$dirs, $files];
}

function file_count($dirPath, $fileCondition)
/*
  Returns a total number of files in the dirPath directory filtered by fileCondition
 */
{
  $cnt = 0; $haul = collect_dirs_and_files($dirPath, $fileCondition);
  foreach ($haul[0] as $dir) {
    $cnt += file_count($dir, $fileCondition);
  }
  foreach ($haul[1] as $file) {
    if ($fileCondition($file)) $cnt++;
  }
  return $cnt;
}

function compare_path($xp, $yp)
/*
  Compares tho paths, ignoring extensions
 */
{
  $x = sans_ext($xp);
  $y = sans_ext($yp);
  return (arg('sort-lex')) ? strcmp($x, $y) : strcmp_naturally($x, $y);
}

function compare_file($xp, $yp)
/*
  Compares tho paths, file names only, ignoring extensions
 */
{
  $x = file_name($xp);
  $y = file_name($yp);
  return (arg('sort-lex')) ? strcmp($x, $y) : strcmp_naturally($x, $y);
}

function list_dir_groom($absPath, $reverse = false)
/*
  Returns (0) a naturally sorted list of
  offspring directory paths (1) a naturally sorted list
  of offspring file paths.
 */
{
  global $is_audio_file;
  $haul = collect_dirs_and_files($absPath, 'Procrustes\is_audio_file');
  $f = arg('r') ? -1 : 1;
  $cmp = function($xp, $yp) use($f) {return $f * compare_path($xp, $yp);};
  usort($haul[0], $cmp);
  usort($haul[1], $cmp);
  return $haul;
}

function zero_pad($w, $i) {return sprintf("%0{$w}d", $i);}

function space_pad($w, $i) {return sprintf("%{$w}d", $i);}

function decorate_dir_name($i, $name) {return zero_pad(3, $i) . '-' . $name;}

function decorate_file_name($cntw, $i, $name)
{
  return zero_pad($cntw, $i) . '-' . (arg('u') ? arg('u') . '.' . file_ext($name) : $name);
}

function traverse_flat_dst($srcDir, $dstRoot, &$fcount, $cntw)
/*
  Recursively traverses the source directory and yields a sequence of (src, flat dst) pairs;
  the destination directory and file names get decorated according to options
 */
{
  $groom = list_dir_groom($srcDir);
  foreach ($groom[0] as $dir) {
    yield from traverse_flat_dst($dir, $dstRoot, $fcount, $cntw);
  }
  foreach ($groom[1] as $file) {
    $dst = join_paths($dstRoot, decorate_file_name($cntw, $fcount, basename($file)));
    $fcount++;
    yield [$file, $dst];
  }
}

function traverse_flat_dst_r($srcDir, $dstRoot, &$fcount, $cntw)
/*
  Recursively traverses the source directory backwards (-r) and yields a sequence of (src, flat dst) pairs;
  the destination directory and file names get decorated according to options
 */
{
  $groom = list_dir_groom($srcDir, true);
  foreach ($groom[1] as $file) {
    $dst = join_paths($dstRoot, decorate_file_name($cntw, $fcount, basename($file)));
    $fcount--;
    yield [$file, $dst];
  }
  foreach ($groom[0] as $dir) {
    yield from traverse_flat_dst_r($dir, $dstRoot, $fcount, $cntw);
  }
}

function traverse_tree_dst($srcDir, $dstRoot, $dstStep, $cntw)
/*
  Recursively traverses the source directory and yields a sequence of (src, tree dst) pairs;
  the destination directory and file names get decorated according to options
 */
{
  $groom = list_dir_groom($srcDir);
  foreach ($groom[0] as $i => $dir) {
    $step = join_paths($dstStep, decorate_dir_name($i, basename($dir)));
    mkdir(join_paths($dstRoot, $step));
    yield from traverse_tree_dst($dir, $dstRoot, $step, $cntw);
  }
  foreach ($groom[1] as $i => $file) {
    $dst = join_paths($dstRoot, join_paths($dstStep, decorate_file_name($cntw, $i, basename($file))));
    yield [$file, $dst];
  }
}

function groom($src, $dst, $cnt)
/*
  Makes an 'executive' run of traversing the source directory; returns the 'ammo belt' generator
 */
{
  $cntw = strlen(strval($cnt));
  if (arg('t')) {
    return traverse_tree_dst($src, $dst, '', $cntw);
  } else {
    if (arg('r')) {
      $c = $cnt;
      return traverse_flat_dst_r($src, $dst, $c, $cntw);
    } else {
      $c = 1;
      return traverse_flat_dst($src, $dst, $c, $cntw);
    }
  }
}

function build_album()
/*
  Sets up boilerplate required by the options and returns the ammo belt generator
  of (src, dst) pairs
 */
{
  $srcName = basename(arg('src'));
  $prefix = arg('b') ? zero_pad(2, arg('b')) . '-' : '';
  $baseDst = $prefix . (arg('u') ? arg('u') : $srcName);

  $executiveDst = join_paths(arg('dst'), (arg('p') ? '' : $baseDst));

  if (!arg('p')) {
    if (is_dir($executiveDst)) {
      print "Destination directory \"{$executiveDst}\" already exists.\n";
      exit();
    } else {
      mkdir($executiveDst);
    }
  }
  $tot = file_count(arg('src'), 'Procrustes\is_audio_file');
  $belt = groom(arg('src'), $executiveDst, $tot);

  if (!arg('p') && $tot === 0) {
    rmdir($executiveDst);
    print "There are no supported audio files in the source directory \"{arg('src')}\".\n";
    exit();
  }
  return [$tot, $belt];
}

function copy_album()
{
  $alb = build_album();
  foreach ($alb[1] as $round) {
    print $round[0] . ' ' . $round[1] . "\n";
    copy($round[0], $round[1]);
  }
}

function main()
{
  global $args;
  $args = retrieve_args();
  copy_album();
}

if (!debug_backtrace()) {
  main();
}
?>