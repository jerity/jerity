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
 * @package    jerity.core
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Error {

  /**
   * If true, an error has occurred.
   *
   * @var  boolean
   */
  protected static $error_has_occurred = false;

  /**
   * If true, @-suppression of errors will trigger a warning.
   *
   * @var  boolean
   */
  protected static $warn_on_suppression = false;

  /**
   * Non-instantiable class.
   */
  // @codeCoverageIgnoreStart
  private function __construct() {
  }
  // @codeCoverageIgnoreEnd

  /**
   *
   */
  public static function setupHandlers($error_types = (E_ALL | E_STRICT)) {
    self::checkConstants();
    set_exception_handler(array(self, 'handleException'));
    set_error_handler(array(self, 'handleError'), $error_types);
  }

  /**
   *
   */
  protected static function handleException(Exception $exception) {
    self::hasErred(true);
    # Generate message.
    $msg = 'PHP Fatal Error: Uncaught Exception';
    //$msg .= self::getExceptionLogString($exception);
    # Output to the PHP error log.
    //self::log($msg, false, false);
    # Display error message on screen.
    //if (ini_get('display_errors')) {
    //  self::display($msg);
    //} else {
    //  require_once '_layouts/php_error.php';
    //}
    # Terminate the script.
    exit();
  }

  /**
   *
   */
  protected static function handleError($code, $message, $file, $line, $context) {
    # Generate message.
    //
    # Generate backtrace.
    //
    # Check whether we should issue a warning for error suppression.
    $level = error_reporting();
    if (!$level) {
      # Override use of the '@' operator for suppression of errors.  Use of it
      # is strongly discouraged as it hides fatal errors.  If we are debugging,
      # we should warn developers of this.
      # Note: This code is not called for fatal errors, so suppression of fatal
      # errors cannot be warned about.
      if (Debug::isEnabled() && self::warnOnSuppression()) {
        self::hasErred(true);
        # Generate message.
        $warning = 'Warning: @-suppression of errors (or setting error_reporting() level to 0) is bad coding practice as it causes code to die without explanation on fatal error.  Use error_reporting(E_ERROR) or similar instead.'.PHP_EOL;
        # Output to the PHP error log.
        //self::log($warning . $trace_text, false, false);
        # Display error message on screen.
        //if (ini_get('display_errors')) {
        //  self::display($warning . "\n$trace");
        //}
      }
      # Override with E_ERROR to report fatal errors.
      $level = E_ERROR;
    }
    //self::log($msg, false, false);
    # Display error message on screen.
    //if (ini_get('display_errors')) {
    //  self::display($msg);
    //} else {
    //  require_once '_layouts/php_error.php';
    //}
    if ($level & $code || $fatal) {
      self::hasErred(true);
      # Output to the PHP error log.
      //self::log($msg.PHP_EOL.$trace_text, false, false);
    }
    # Display error message on screen.
    //$msg .= PHP_EOL.$trace;
    //if (ini_get('display_errors')) {
    //  if ($level & $code || $fatal) {
    //    self::display($msg);
    //  }
    //} elseif ($fatal) {
    //  require_once '_layouts/php_error.php';
    //}
    # Terminate the script.
    if ($fatal) exit();
  }

  /**
   *
   */
  public static function display() {
  }

  /**
   *
   */
  public static function log($data) {
    $message = '';
    if ($data instanceof Exception) {
      $message .= 'Exception';
    } else {
      $message .= $data;
    }
    error_log($message, 3, 'php-error.log');
  }

  /**
   * Checks whether certain constants are defined, and defines them if
   * required.
   */
  protected static function checkConstants() {
    if (version_compare(PHP_VERSION, '5.2.0', '<')) {
      if (!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
    }
    if (version_compare(PHP_VERSION, '5.3.0', '<')) {
      if (!defined('E_DEPRECATED'))      define('E_DEPRECATED',      8192);
      if (!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED', 16384);
    }
  }

  /**
   * Can be set to trigger a warning if @-suppression of errors is used.
   * Suppressing errors in this way is bad practice because important issues
   * could be missed and using the '@' operator can have a performance hit.
   *
   * @param  bool  $v  Whether to show warnings on error suppression.
   *
   * @return  bool  Whether to show warnings on error suppression.
   */
  public static function warnOnSuppression($v = null) {
    if (!is_null($v)) self::$warn_on_suppression = $v;
    return self::$warn_on_suppression;
  }

  /**
   * Can be used to disable/enable suppression of errors using the @-operator 
   * if the scream extension is available.
   *
   * @param  bool  $v  Whether to disable suppression of errors.
   *
   * @return  Whether the extension is loaded and thus whether we succeeded.
   *
   * @see  http://uk3.php.net/manual/en/book.scream.php
   */
  public static function disableSuppression($v) {
    if (extension_loaded('scream')) {
      ini_set('scream.enabled', (bool) $v);
      return true;
    }
    return false;
  }

  /**
   * Keeps track of whether an error has occurred because error_get_last() can
   * be reset.
   *
   * @param  bool  $v  True if an error has occurred.
   *
   * @return  bool  Whether an error has occurred.
   */
  public static function hasErred($v = null) {
    if ($v) self::$error_has_occurred = true;
    return self::$error_has_occurred;
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
