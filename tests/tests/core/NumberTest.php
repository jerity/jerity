<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');

class NumberTest extends PHPUnit_Framework_TestCase {

  /**
   * @dataProvider  intEqProvider
   * @covers        Number::intcmp()
   */
  public function testIntCmpEQ($a, $b) {
    $this->assertSame(0, Number::intcmp($a, $b));
  }

  public static function intEqProvider() {
    return array(
      array(-25, -25),
      array(-15, -15),
      array(-5, -5),
      array(-1, -1),
      array(0, 0),
      array(1, 1),
      array(5, 5),
      array(15, 15),
      array(25, 25),
    );
  }

  /**
   * @dataProvider  intGtProvider
   * @covers        Number::intcmp()
   */
  public function testIntCmpGT($a, $b) {
    $this->assertSame(1, Number::intcmp($a, $b));
  }

  public static function intGtProvider() {
    return array(
      array(-24, -25),
      array(-15, -20),
      array(-5, -10),
      array(0, -1),
      array(1, 0),
      array(5, 1),
      array(10, 5),
      array(25, 12),
      array(25, 24),
    );
  }

  /**
   * @dataProvider  intLtProvider
   * @covers        Number::intcmp()
   */
  public function testIntCmpLT($a, $b) {
    $this->assertSame(-1, Number::intcmp($a, $b));
  }

  public static function intLtProvider() {
    return array(
      array(-25, -24),
      array(-20, -15),
      array(-10,  -5),
      array( -1,   0),
      array(  0,   1),
      array(  1,   5),
      array(  5,  10),
      array( 12,  25),
      array( 24,  25),
    );
  }

  /**
   * @dataProvider  dblEqProvider
   * @covers        Number::dblcmp()
   */
  public function testDblCmpEQ($a, $b) {
    $this->assertSame(0, Number::dblcmp($a, $b));
  }

  public static function dblEqProvider() {
    return array(
      array(-25, -25),
      array(-15, -15),
      array(-5, -5),
      array(-1, -1),
      array(-0.000000001, 0),
      array(0, 0),
      array(0, 0.000000001),
      array(1, 1),
      array(5, 5),
      array(15, 15),
      array(25, 25),
    );
  }

  /**
   * @dataProvider  dblGtProvider
   * @covers        Number::dblcmp()
   */
  public function testDblCmpGT($a, $b) {
    $this->assertSame(1, Number::dblcmp($a, $b));
  }

  public static function dblGtProvider() {
    return array(
      array(-24, -25),
      array(-15, -20),
      array(-5, -10),
      array(0, -1),
      array(0, -0.00000001),
      array(0.00000001, 0),
      array(1, 0),
      array(5, 1),
      array(10, 5),
      array(25, 12),
      array(25, 24),
    );
  }

  /**
   * @dataProvider  dblLtProvider
   * @covers        Number::dblcmp()
   */
  public function testDblCmpLT($a, $b) {
    $this->assertSame(-1, Number::dblcmp($a, $b));
  }

  public static function dblLtProvider() {
    return array(
      array(-25, -24),
      array(-20, -15),
      array(-10,  -5),
      array( -1,   0),
      array(-0.00000001, 0),
      array(0, 0.00000001),
      array(  0,   1),
      array(  1,   5),
      array(  5,  10),
      array( 12,  25),
      array( 24,  25),
    );
  }

}
