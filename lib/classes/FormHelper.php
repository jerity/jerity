<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

/**
 * @package    jerity.form
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Utility functions for Form checking, etc.
 *
 * @package    jerity.form
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class FormHelper {

  const METHOD_POST = '_POST';
  const METHOD_GET  = '_GET';

  /**
   * Controls which form-related superglobal(s) we should use when checking.
   *
   * @var  integer
   */
  protected static $method = self::METHOD_POST;

  /**
   * Non-instantiable class.
   */
  protected function __construct() {
  }

  /**
   * Gets the superglobal(s) we should use when checking.
   *
   * @return  integer
   */
  public static function getMethod() {
    return self::$method;
  }

  /**
   * Sets the superglobal(s) we should use when checking.
   *
   * @param  integer  $method
   */
  public static function setMethod($method) {
    self::$method = $method;
  }

  /**
   * Checks whether a form field is empty.
   *
   * @param  string          $field   The field to check.
   * @param  integer | null  $method  The method to check for.
   *
   * @return  boolean
   *
   * @throws  InvalidArgumentException
   */
  public static function isEmpty($field, $method = null) {
    if (is_null($method)) $method = self::$method;
    switch ($method) {
      case self::METHOD_POST:
        if (!isset($_POST[$field])) return true;
        return trim($_POST[$field]) === '';
      case self::METHOD_GET:
        if (!isset($_GET[$field])) return true;
        return trim($_GET[$field]) === '';
      default:
        throw new InvalidArgumentException('Invalid method.');
    }
  }

  /**
   * Checks whether a form field contains an integer.
   *
   * @param  string          $field   The field to check.
   * @param  integer | null  $method  The method to check for.
   *
   * @return  boolean
   *
   * @throws  InvalidArgumentException
   */
  public static function isInteger($field, $method = null) {
    if (is_null($method)) $method = self::$method;
    switch ($method) {
      case self::METHOD_POST:
        if (!isset($_POST[$field])) return false;
        return String::isInteger($_POST[$field]);
      case self::METHOD_GET:
        if (!isset($_GET[$field])) return false;
        return String::isInteger($_GET[$field]);
      default:
        throw new InvalidArgumentException('Invalid method.');
    }
  }

  /**
   * Checks whether a form field contains a floating point number.
   *
   * @param  string          $field   The field to check.
   * @param  integer | null  $method  The method to check for.
   *
   * @return  boolean
   *
   * @throws  InvalidArgumentException
   */
  public static function isFloat($field, $method = null) {
    if (is_null($method)) $method = self::$method;
    switch ($method) {
      case self::METHOD_POST:
        if (!isset($_POST[$field])) return false;
        return String::isFloat($_POST[$field]);
      case self::METHOD_GET:
        if (!isset($_GET[$field])) return false;
        return String::isFloat($_GET[$field]);
      default:
        throw new InvalidArgumentException('Invalid method.');
    }
  }

  /**
   * Checks whether the length of a form field fits in a specified range.
   *
   * @param  string          $field   The field to check.
   * @param  integer | null  $min     The method to check for.
   * @param  integer | null  $max     The method to check for.
   * @param  integer | null  $method  The method to check for.
   *
   * @return  boolean
   *
   * @throws  InvalidArgumentException
   */
  public static function isLengthInRange($field, $min = null, $max = null, $method = null) {
    if (is_null($method)) $method = self::$method;
    $length = 0;
    switch ($method) {
      case self::METHOD_POST:
        if (!isset($_POST[$field])) return false;
        $length = strlen($_POST[$field]);
        break;
      case self::METHOD_GET:
        if (!isset($_GET[$field])) return false;
        $length = strlen($_GET[$field]);
        break;
      default:
        throw new InvalidArgumentException('Invalid method.');
    }
    return ((is_null($min) || $length >= $min) && (is_null($max) || $length <= $max));
  }

  /**
   * Checks whether the form field contains a valid date.
   *
   * @param  string          $field   The field to check.
   * @param  integer | null  $method  The method to check for.
   *
   * @return  boolean
   *
   * @throws  InvalidArgumentException
   */
  public static function isValidDate($field, $method = null) {
    if (is_null($method)) $method = self::$method;
    switch ($method) {
      case self::METHOD_POST:
        if (!isset($_POST[$field])) return false;
        $a = date_parse($_POST[$field]);
        return !$a['error_count'];
      case self::METHOD_GET:
        if (!isset($_GET[$field])) return false;
        $a = date_parse($_GET[$field]);
        return !$a['error_count'];
      default:
        throw new InvalidArgumentException('Invalid method.');
    }
  }

}
