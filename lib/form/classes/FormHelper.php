<?php
/**
 * @package    JerityForm
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Utility functions for Form checking, etc.
 *
 * @package    JerityForm
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
		if (!isset($_POST[$field])) return false;
		return trim($_POST[$field]) === '';
	  case self::METHOD_GET:
		if (!isset($_GET[$field])) return false;
		return trim($_GET[$field]) === '';
	  default:
		throw new InvalidArgumentException('Invalid method.');
	}
  }

}
