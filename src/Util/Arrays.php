<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.util
 */

namespace Jerity\Util;

/**
 * A utility class providing useful functions for manipulating arrays.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.util
 */
final class Arrays {

  /**
   * This is a non-instantiable utility class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Flattens an multi-dimensional array down to a single-dimensional array by
   * recursive preorder traversal, discarding any keys.
   *
   * @param   array  $array  The array to flatten.
   *
   * @return  array  The flattened array.
   */
  public static function flatten(array $array) {
    if (!$array) return array();
    $flat = array();
    $rai = new \RecursiveArrayIterator($array);
    $rii = new \RecursiveIteratorIterator($rai, \RecursiveIteratorIterator::SELF_FIRST);
    foreach ($rii as $k => $v) {
      if (is_array($v)) continue;
      $flat[] = $v;
    }
    return $flat;
  }

  /**
   * Collapse a multi-dimensional key-value array down to a single-dimensional
   * array, collapsing keys by appending.
   *
   * For example, the array:
   *
   * <code>
   * array('a' => '16', 'foo' => array('bar' => 'a', 'qux' => array('baz' => 31415)))
   * </code>
   *
   * Will be flattened to:
   *
   * <code>
   * array(
   *   'a' => 16,
   *   'foo[bar]' => 'a',
   *   'foo[qux][baz]' = 31415
   * )
   * </code>
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

  /**
   * Check whether a given array only has numeric keys.
   *
   * @param   array  $array  The array to be checked
   *
   * @return  bool  Whether or not the array is only numerically keyed.
   */
  public static function isNumericallyKeyed(array $array) {
    foreach (array_keys($array) as $key) {
      if (!is_numeric($key)) {
        return false;
      }
    }
    return true;
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
