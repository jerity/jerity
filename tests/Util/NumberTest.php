<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

use \Jerity\Util\Number;

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 *
 * @group  utility
 */
class NumberTest extends PHPUnit_Framework_TestCase {

  /**
   * @dataProvider  intEqProvider
   */
  public function testIntCmpEQ($a, $b) {
    $this->assertSame(0, Number::intcmp($a, $b));
  }

  /**
   *
   */
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
   */
  public function testIntCmpGT($a, $b) {
    $this->assertSame(1, Number::intcmp($a, $b));
  }

  /**
   *
   */
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
   */
  public function testIntCmpLT($a, $b) {
    $this->assertSame(-1, Number::intcmp($a, $b));
  }

  /**
   *
   */
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
   */
  public function testDblCmpEQ($a, $b) {
    $this->assertSame(0, Number::dblcmp($a, $b));
  }

  /**
   *
   */
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
   */
  public function testDblCmpGT($a, $b) {
    $this->assertSame(1, Number::dblcmp($a, $b));
  }

  /**
   *
   */
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
   */
  public function testDblCmpLT($a, $b) {
    $this->assertSame(-1, Number::dblcmp($a, $b));
  }

  /**
   *
   */
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

  /**
   * @dataProvider  parseBitsProvider
   */
  public function testParseBits($a, $b, $c) {
    $this->assertEquals(Number::parseBits($a, $b), $c);
  }

  /**
   *
   */
  public static function parseBitsProvider() {
    $values          = array(1, 10, 12.34);
    $prefix_symbol   = array('', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'K', 'M', 'G');
    $prefix_name     = array('', 'kilo', 'mega', 'giga', 'tera', 'peta', 'exa', 'zetta', 'kibi', 'mebi', 'gibi', 'tebi', 'pebi', 'exbi', 'kilo', 'mega', 'giga');
    $multiplier_base = array(10, 10, 10, 10, 10, 10, 10, 10, 2, 2, 2, 2, 2, 2, 2, 2, 2);
    $multiplier_exp  = array(0, 3, 6, 9, 12, 15, 18, 21, 10, 20, 30, 40, 50, 60, 10, 20, 30);
    $data = array();
    foreach ($values as $value) {
      for ($i = 0; $i < count($prefix_symbol); $i++) {
        $v = $value * pow($multiplier_base[$i], $multiplier_exp[$i]);
        $jedec = ($i >= count($prefix_symbol) - 4);
        $data[] = array("{$value}{$prefix_symbol[$i]}B",    $jedec, $v * 8);
        $data[] = array("{$value}{$prefix_symbol[$i]}Bps",  $jedec, $v * 8);
        $data[] = array("{$value}{$prefix_symbol[$i]}b",    $jedec, $v);
        $data[] = array("{$value}{$prefix_symbol[$i]}bps",  $jedec, $v);
        $data[] = array("{$value} {$prefix_symbol[$i]}B",   $jedec, $v * 8);
        $data[] = array("{$value} {$prefix_symbol[$i]}Bps", $jedec, $v * 8);
        $data[] = array("{$value} {$prefix_symbol[$i]}b",   $jedec, $v);
        $data[] = array("{$value} {$prefix_symbol[$i]}bps", $jedec, $v);
        $postfix = ($value == 1 ? '' : 's');
        $data[] = array("{$value}{$prefix_name[$i]}byte{$postfix}",  $jedec, $v * 8);
        $data[] = array("{$value}{$prefix_name[$i]}bit{$postfix}",   $jedec, $v);
        $data[] = array("{$value} {$prefix_name[$i]}byte{$postfix}", $jedec, $v * 8);
        $data[] = array("{$value} {$prefix_name[$i]}bit{$postfix}",  $jedec, $v);
      }
    }
    return $data;
  }

  /**
   * @dataProvider  parseBitsExceptionProvider
   */
  public function testParseBitsException($a, $b, $c) {
    $this->setExpectedException($c);
    Number::parseBits($a, $b);
  }

  /**
   *
   */
  public static function parseBitsExceptionProvider() {
    return array(
      array('Not a size.', false, '\Jerity\Util\Exception'),
      array('Not a size.', true,  '\Jerity\Util\Exception'),
      array('1 KB',        false, '\Jerity\Util\Exception'),
      array('1 TB',        true,  '\Jerity\Util\Exception'),
    );
  }

  /**
   * @dataProvider  parseBytesProvider
   */
  public function testParseBytes($a, $b, $c) {
    $this->assertEquals(Number::parseBytes($a, $b), $c);
  }

  /**
   *
   */
  public static function parseBytesProvider() {
    $values          = array(1, 10, 12.34);
    $prefix_symbol   = array('', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'K', 'M', 'G');
    $prefix_name     = array('', 'kilo', 'mega', 'giga', 'tera', 'peta', 'exa', 'zetta', 'kibi', 'mebi', 'gibi', 'tebi', 'pebi', 'exbi', 'kilo', 'mega', 'giga');
    $multiplier_base = array(10, 10, 10, 10, 10, 10, 10, 10, 2, 2, 2, 2, 2, 2, 2, 2, 2);
    $multiplier_exp  = array(0, 3, 6, 9, 12, 15, 18, 21, 10, 20, 30, 40, 50, 60, 10, 20, 30);
    $data = array();
    foreach ($values as $value) {
      for ($i = 0; $i < count($prefix_symbol); $i++) {
        $v = $value * pow($multiplier_base[$i], $multiplier_exp[$i]);
        $jedec = ($i >= count($prefix_symbol) - 4);
        $data[] = array("{$value}{$prefix_symbol[$i]}B",    $jedec, $v);
        $data[] = array("{$value}{$prefix_symbol[$i]}Bps",  $jedec, $v);
        $data[] = array("{$value}{$prefix_symbol[$i]}b",    $jedec, $v / 8);
        $data[] = array("{$value}{$prefix_symbol[$i]}bps",  $jedec, $v / 8);
        $data[] = array("{$value} {$prefix_symbol[$i]}B",   $jedec, $v);
        $data[] = array("{$value} {$prefix_symbol[$i]}Bps", $jedec, $v);
        $data[] = array("{$value} {$prefix_symbol[$i]}b",   $jedec, $v / 8);
        $data[] = array("{$value} {$prefix_symbol[$i]}bps", $jedec, $v / 8);
        $postfix = ($value == 1 ? '' : 's');
        $data[] = array("{$value}{$prefix_name[$i]}byte{$postfix}",  $jedec, $v);
        $data[] = array("{$value}{$prefix_name[$i]}bit{$postfix}",   $jedec, $v / 8);
        $data[] = array("{$value} {$prefix_name[$i]}byte{$postfix}", $jedec, $v);
        $data[] = array("{$value} {$prefix_name[$i]}bit{$postfix}",  $jedec, $v / 8);
      }
    }
    return $data;
  }

  /**
   * @dataProvider  parseBytesExceptionProvider
   */
  public function testParseBytesException($a, $b, $c) {
    $this->setExpectedException($c);
    Number::parseBytes($a, $b);
  }

  /**
   *
   */
  public static function parseBytesExceptionProvider() {
    return array(
      array('Not a size.', false, '\Jerity\Util\Exception'),
      array('Not a size.', true,  '\Jerity\Util\Exception'),
      array('1 KB',        false, '\Jerity\Util\Exception'),
      array('1 TB',        true,  '\Jerity\Util\Exception'),
    );
  }

  /**
   * @dataProvider  romanProvider
   */
  public function testToRoman($a, $b, $c, $d) {
    $this->assertEquals(Number::toRoman($b, $c, $d), $a);
  }

  /**
   * @dataProvider  romanProvider
   */
  public function testFromRoman($a, $b, $c) {
    $this->assertEquals(Number::fromRoman($a, $c), $b);
  }

  public function romanProvider() {
    return array(
      # Check zero:
      array('',           0,    false, true),
      # Check individual numerals:
      array('I',          1,    false, true),
      array('V',          5,    false, true),
      array('X',          10,   false, true),
      array('L',          50,   false, true),
      array('C',          100,  false, true),
      array('D',          500,  false, true),
      array('M',          1000, false, true),
      # Check individual medieval numerals:
      // XXX: array('S',         5000,    true, true),
      // XXX: array('R',         10000,   true, true),
      // XXX: array('P',         50000,   true, true),
      // XXX: array('Q',         100000,  true, true),
      // XXX: array('O',         500000,  true, true),
      // XXX: array('N',         1000000, true, true),
      # Check subtractive numerals:
      array('IV',         4,    false, true),
      array('IX',         9,    false, true),
      array('XL',         40,   false, true),
      array('XC',         90,   false, true),
      array('CD',         400,  false, true),
      array('CM',         900,  false, true),
      # Check subtractive medieval numerals:
      // XXX: array('MS',        4000,    true, true),
      //      ...
      # Check some arbitrary numbers:
      array('XLII',       42,   false, true),
      array('MCMX',       1910, false, true),
      array('MCMXCVIII',  1998, false, true),
      array('MCMXCIX',    1999, false, true),
      array('MM',         2000, false, true),
      array('MMX',        2010, false, true),
      # Check subtractive flag works correctly:
      array('XIII',       13,   false, false),
      array('XIII',       13,   false, true),
      array('XIIII',      14,   false, false),
      array('XIV',        14,   false, true),
      # Check non-subtractive special cases:
      array('IIII',       4,    false, false),
      array('CCCC',       400,  false, false),
      array('MDCCCCX',    1910, false, false),
      array('MDCCCCIIII', 1904, false, false),
      # Check subtractive special cases:
      array('XCIX',       99,   false, true),
      // XXX: array('IC',         99,   false, true),

      // XXX: array('VV',        10,   false, false),
      // XXX: array('LL',        100,   false, false),
      // XXX: array('DD',        1000,   false, false),
    );
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
