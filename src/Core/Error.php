<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */

namespace Jerity\Core;

/**
 * Default error handling class which provides extensive options for debugging
 * errors and exceptions.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 *
 * @todo  Implement this class.
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
   * @return  bool
   *
   * @todo  Stub - implement.
   */
  public static function hasErred() {
    return false;
  }

  /**
   * Gets the last error that occured.
   *
   * @return  string
   *
   * @todo  Stub - implement.
   */
  public static function getLast() {
    return '';
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
