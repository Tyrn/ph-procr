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
  public function test_array_cmp()
  {
    $this->assertEquals(Procrustes\array_cmp(array(), array()), 0);
    $this->assertEquals(Procrustes\array_cmp(array(1), array()), 1);
    $this->assertEquals(Procrustes\array_cmp(array(3), array()), 1);
    $this->assertEquals(Procrustes\array_cmp(array(1, 2, 3), array(1, 2, 3, 4, 5)), -1);
    $this->assertEquals(Procrustes\array_cmp(array(1, 4), array(1, 4, 16)), -1);
    $this->assertEquals(Procrustes\array_cmp(array(2, 8), array(2, 2, 3)), 1);
    $this->assertEquals(Procrustes\array_cmp(array(0, 0, 2, 4), array(0, 0, 15)), -1);
    $this->assertEquals(Procrustes\array_cmp(array(0, 13), array(0, 2, 2)), 1);
    $this->assertEquals(Procrustes\array_cmp(array(11, 2), array(11, 2)), 0);
  }
}
?>