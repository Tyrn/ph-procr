<?php
require_once '../src/pcn.php';

class PcnTests extends PHPUnit_Framework_TestCase
{
  public function test_sans_ext()
  {
    $this->assertEquals(Procrustes\sans_ext("/alfa/bravo/charlie.dat"), "/alfa/bravo/charlie");
    // $this->assertEquals(Procrustes\sans_ext(""), "");
    $this->assertEquals(Procrustes\sans_ext("/alfa/bravo/charlie"), "/alfa/bravo/charlie");
    $this->assertEquals(Procrustes\sans_ext("/alfa/bravo/charlie/"), "/alfa/bravo/charlie");
    $this->assertEquals(Procrustes\sans_ext("/alfa/bra.vo/charlie.dat"), "/alfa/bra.vo/charlie");
  }
  public function test_has_ext_of()
  {
    $this->assertEquals(Procrustes\has_ext_of("/alfa/bra.vo/charlie.ogg", "OGG"), true);
    $this->assertEquals(Procrustes\has_ext_of("/alfa/bra.vo/charlie.ogg", ".ogg"), true);
    $this->assertEquals(Procrustes\has_ext_of("/alfa/bra.vo/charlie.ogg", "mp3"), false);
  }
  public function test_str_strip_numbers()
  {
    $this->assertEquals(Procrustes\str_strip_numbers("ab11cdd2k.144"), array(11, 2, 144));
    $this->assertEquals(Procrustes\str_strip_numbers("Ignacio Vazkez-Abrams"), array());
  }
}
?>