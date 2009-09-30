<?php
/**
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Default error handling class which provides extensive options for debugging
 * errors and exceptions.
 *
 * @todo  Implement this class.
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Error {

  /**
   * Non-instantiable class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() {
  }
  // @codeCoverageIgnoreEnd

  /**
   * Checks whether the error handler has run into an error.
   *
   * @todo  Stub - implement.
   *
   * @return  bool
   */
  public static function hasErred() {
    return false;
  }

  /**
   * Gets the last error that occured.
   *
   * @todo  Stub - implement.
   *
   * @return  string
   */
  public static function getLast() {
    return '';
  }

}
