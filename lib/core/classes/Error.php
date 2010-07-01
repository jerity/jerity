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
 * @package    jerity.core
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * Default error handling class which provides extensive options for debugging
 * errors and exceptions.
 *
 * @todo  Implement this class.
 *
 * @package    jerity.core
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
