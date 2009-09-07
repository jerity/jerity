<?php
require_once(dirname(dirname(dirname(__FILE__))).'/setUp.php');

class ArrayUtilTest extends PHPUnit_Framework_TestCase {

  /**
   * @dataProvider  flattenProvider
   * @covers        ArrayUtil::flatten()
   */
  public function testFlatten($input, $expected) {
    $this->assertSame($expected, ArrayUtil::flatten($input));
  }

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

    for ($i=0; $i<$count; ++$i) {
      $final[] = array($multi[$i], $result[$i]);
    }

    return $final;
  }

}
