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

  /**
   * Non-instantiable class.
   */
  protected function __construct() {
  }

  /**
   * Checks whether a POST item is empty.
   *
   * @param  string  $field  The POST field to check.
   *
   * @return  boolean
   */
  public static function isEmpty($field) {
	if (!isset($_POST[$field])) return false;
	return trim($_POST[$field]) === '';
  }

}
