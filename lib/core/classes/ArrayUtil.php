<?php
/**
 * @todo  [PHP 5.3] Rename file to Array.php when namespaced.
 *
 * @package  JerityCore
 * @author  Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * A utility class providing useful functions for manipulating arrays.
 *
 * @todo  [PHP 5.3] Rename class to Array when namespaced.
 *
 * @package  JerityCore
 * @author  Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
final class ArrayUtil {

  /**
   * This is a non-instantiable utility class.
   */
  private function __construct() {
  }

  /**
   * Flattens an multi-dimensional array down to a single-dimensional array.
   *
   * @param  array  $array  The array to flatten.
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

}
