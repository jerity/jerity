<?php
/**
 * @todo  [PHP 5.3] Rename file to Array.php when namespaced.
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * A utility class providing useful functions for manipulating arrays.
 *
 * @todo  [PHP 5.3] Rename class to Array when namespaced.
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
final class ArrayUtil {

  /**
   * This is a non-instantiable utility class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Flattens an multi-dimensional array down to a single-dimensional array.
   *
   * @param   array  $array  The array to flatten.
   *
   * @return  array  The flattened array.
   */
  public static function flatten(array $array) {
    if (!$array) return array();
    $flat = array();
    $rai = new RecursiveArrayIterator($array);
    $rii = new RecursiveIteratorIterator($rai, RecursiveIteratorIterator::SELF_FIRST);
    foreach ($rii as $k => $v) {
      if (is_array($v)) continue;
      $flat[] = $v;
    }
    return $flat;
  }

  /**
   * Collapse a multi-dimensional key-value array down to a single-dimensional array, collapsing keys by appending.
   *
   * For example, the array:
   *
   *   array('a' => '16', 'foo' => array('bar' => 'a', 'qux' => array('baz' => 31415)))
   *
   * Will be flattened to:
   *
   *   array(
   *     'a' => 16,
   *     'foo[bar]' => 'a',
   *     'foo[qux][baz]' = 31415
   *   )
   *
   * @param   array   $array   The array to collapse.
   * @param   string  $prefix  A prefix for the array
   *
   * @return  array  The collapsed array.
   */
  public static function collapseKeys(array $array, $prefix=null) {
    $final = array();
    foreach ($array as $k => $v) {
      if (!is_null($prefix)) $k = $prefix."[$k]";
      if (!is_array($v)) {
        $final[$k] = $v;
      } else {
        // recurse and append to key
        $final = array_merge($final, self::collapseKeys($v, $k));
      }
    }
    return $final;
  }

}
