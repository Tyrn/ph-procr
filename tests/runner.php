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
    $this->assertEquals(Procrustes\has_ext_of("/alfa/bra.vo/charlie.ogg", "OGG"), True);
    $this->assertEquals(Procrustes\has_ext_of("/alfa/bra.vo/charlie.ogg", ".ogg"), True);
    $this->assertEquals(Procrustes\has_ext_of("/alfa/bra.vo/charlie.ogg", "mp3"), False);
  }
}

?>