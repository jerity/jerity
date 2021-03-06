<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

use \Jerity\Util\Arrays;

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 *
 * @group  utility
 */
class ArraysTest extends PHPUnit_Framework_TestCase {

  /**
   * @dataProvider  flattenProvider
   */
  public function testFlatten($input, $expected) {
    $this->assertSame($expected, Arrays::flatten($input));
  }

  /**
   *
   */
  public static function flattenProvider() {
    $multi  = array();
    $result = array();
    $final = array();

    $count = -1;

    $multi[++$count] = array(0, 1, 2, 3, 4, 5);
    $result[ $count] = array(0, 1, 2, 3, 4, 5);

    $multi[++$count] = array(5, 4, 3, 2, 1, 0);
    $result[ $count] = array(5, 4, 3, 2, 1, 0);

    $multi[++$count] = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>3, 'e'=>4, 'f'=>5);
    $result[ $count] = array(0, 1, 2, 3, 4, 5);

    $multi[++$count] = array(array(0), 1, 2, 3, 4, 5);
    $result[ $count] = array(0, 1, 2, 3, 4, 5);

    $multi[++$count] = array(array(0), array(1), array(2), array(3), array(4), array(5));
    $result[ $count] = array(0, 1, 2, 3, 4, 5);

    $multi[++$count] = array(array(array(0)), array(1, array(2, 3, array(4))), 5);
    $result[ $count] = array(0, 1, 2, 3, 4, 5);

    $multi[++$count] = array(array(array(0)), array('z'=>1, 'a'=>array(2, 3, array(4))), 5);
    $result[ $count] = array(0, 1, 2, 3, 4, 5);

    $multi[++$count] = array(array(array(0)), array('z'=>1, 'a'=>array(2, 'f'=>3, array(4))), 5);
    $result[ $count] = array(0, 1, 2, 3, 4, 5);

    for ($i=0; $i<=$count; ++$i) {
      $final[] = array($multi[$i], $result[$i]);
    }

    return $final;
  }

  /**
   * @dataProvider  collapseKeysProvider
   */
  public function testCollapseKeys($input, $expected) {
    $this->assertSame($expected, Arrays::collapseKeys($input));
  }

  /**
   *
   */
  public static function collapseKeysProvider() {
    $input  = array();
    $output = array();
    $final  = array();

    $count = -1;

    $input[++$count] = array(0, 1, 2, 3, 4, 5);
    $output[ $count] = array(0, 1, 2, 3, 4, 5);

    $input[++$count] = array(5, 4, 3, 2, 1, 0);
    $output[ $count] = array(5, 4, 3, 2, 1, 0);

    $input[++$count] = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>3, 'e'=>4, 'f'=>5);
    $output[ $count] = array('a'=>0, 'b'=>1, 'c'=>2, 'd'=>3, 'e'=>4, 'f'=>5);

    $input[++$count] = array('a'=>0, 'b'=>1, 'c'=>array('d'=>3, 'e'=>4, 'f'=>5));
    $output[ $count] = array('a'=>0, 'b'=>1, 'c[d]'=>3, 'c[e]'=>4, 'c[f]'=>5);

    $input[++$count] = array('a'=>0, 'b'=>1, 'c'=>array('d'=>array('e'=>4, 'f'=>5)));
    $output[ $count] = array('a'=>0, 'b'=>1, 'c[d][e]'=>4, 'c[d][f]'=>5);

    for ($i=0; $i<=$count; ++$i) {
      $final[] = array($input[$i], $output[$i]);
    }

    return $final;
  }

  /**
   * @dataProvider  isNumericallyKeyedProvider
   */
  public function testIsNumericallyKeyed($expected, $input) {
    $this->assertSame($expected, Arrays::isNumericallyKeyed($input));
  }

  /**
   *
   */
  public static function isNumericallyKeyedProvider() {
    return array(
      array(true, array()), # An empty array could be numerically keyed later.
      array(true,  array(1     => true, 2     => true, 3     => true)),
      array(true,  array(1e3   => true, 2e3   => true, 3e3   => true)),
      array(true,  array(-1    => true, -2    => true, -3    => true)),
      array(true,  array(-1.0  => true, -2.0  => true, -3.0  => true)),
      array(true,  array(-1.1  => true, -2.2  => true, -3.3  => true)),
      array(true,  array(-1e3  => true, -2e3  => true, -3e3  => true)),
      array(true,  array('1'   => true, '2'   => true, '3'   => true)),
      array(true,  array('1.0' => true, '2.0' => true, '3.0' => true)),
      array(true,  array('1.1' => true, '2.2' => true, '3.3' => true)),
      array(true,  array('1e3' => true, '2e3' => true, '3e3' => true)),
      array(false, array('a'   => true, 'b'   => true, 'c'   => true)),
      array(false, array('a'   => true, 2     => true, 3     => true)),
      array(false, array(1     => true, 'b'   => true, 3     => true)),
      array(false, array(1     => true, 2     => true, 'c'   => true)),
    );
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
