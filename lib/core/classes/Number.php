<?php
/**
 * @package    JerityCore
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */

/**
 * Number utility class.
 *
 * @package    JerityCore
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */
class Number {

  /**
   * This is a non-instantiable utility class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Perform an integer comparison on two numbers.
   *
   * @param  int  $a  The first number to be compared.
   * @param  int  $b  The second number to be compared.
   *
   * @return  int  0 if they are equal, -1 if $a < $b, 1 if $a > $b
   */
  public static function intcmp($a, $b) {
    $a = intval($a);
    $b = intval($b);
    return ($a == $b) ? 0 : ( ($a < $b) ? -1 : 1);
  }

  /**
   * Perform a floating-point comparison on two numbers. Note that the epsilon
   * value used is currently 1.0e-8.
   *
   * @param  double  $a  The first number to be compared.
   * @param  double  $b  The second number to be compared.
   *
   * @return  int  0 if they are equal, -1 if $a < $b, 1 if $a > $b
   */
  public static function dblcmp($a, $b) {
    $EPSILON = 1.0e-8;
    $a = doubleval($a);
    $b = doubleval($b);
    return (abs($a - $b)<$EPSILON) ? 0 : ( ($a < $b) ? -1 : 1);
  }

}
