<?php
/**
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * A class to handle redirection in a tidy and safe manner while
 * providing useful information and allowing messages on redirect.
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Redirector {

  /**
   * The name of the variable to use in the URL.
   */
  const ITEM_KEY = 'msid';

  /**
   * Session variable name under which redirector should store data.
   */
  const DATA_KEY = 'Redirector_Data';

  /**
   * The maximum number of redirects to store.
   */
  const MAX_ITEMS = 5;

  /**
   * The redirector should not be instantiated.
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Generates a new key for storing the message.
   *
   * @return  string
   */
  protected static function generateKey() {
    return dechex(microtime(true));
  }

  /**
   * Fetches the redirect requested.
   *
   * @return  array
   */
  protected static function getRedirect() {
    $item_key = (isset($_REQUEST[self::ITEM_KEY]) ? $_REQUEST[self::ITEM_KEY] : null);
    if (!isset($_SESSION[self::DATA_KEY][$item_key])) {
      return null;
    }
    $r = $_SESSION[self::DATA_KEY][$item_key];
    return (is_array($r) ? $r : null);
  }

  /**
   * Retrieves the text for the current message.
   * Also performs garbage collection.
   *
   * @param   boolean  $format  Whether to format the output.
   *
   * @return  string
   */
  public static function getMessage($format = true) {
    # Perform garbage collection.
    self::garbageCollect();
    # Get the message.
    $r = self::getRedirect();
    if (isset($r['message'])) {
      if ($format && isset($r['message_type'])) {
        $n = new Notification($r['message'], $r['message_type']);
        return $n->render();
      } else {
        return $r['message'];
      }
    } else {
      return null;
    }
  }

  /**
   * Retrieves the type of the current message.
   *
   * @return  string
   */
  public static function getMessageType() {
    $r = self::getRedirect();
    return (isset($r['message_type']) ? $r['message_type'] : null);
  }

  /**
   * Returns the POST data that was available when the redirect occurred.
   *
   * @return  array
   */
  public static function getPostData() {
    $r = self::getRedirect();
    return (isset($r['post_data']) ? $r['post_data'] : null);
  }

  /**
   * Returns the source of the current redirect.
   *
   * @return  string
   */
  public static function getSource() {
    $r = self::getRedirect();
    return (isset($r['source']) ? $r['source'] : null);
  }

  /**
   * Returns the target of the current redirect.
   *
   * @return  string
   */
  public static function getTarget() {
    $r = self::getRedirect();
    return (isset($r['target']) ? $r['target'] : null);
  }

  /**
   * Reduce the number of messages stored in the session until a maximum
   * threshold is reached.
   */
  protected static function garbageCollect() {
    # We only want to garbage collect once per page load at most:
    static $done = false;
    if ($done) return;
    $done = true;
    # Skip if we have no data stored.
    if (!isset($_SESSION[self::DATA_KEY])) {
      return;
    }
    # Skip if we have less than the maximum number of redirects stored.
    if (count($_SESSION[self::DATA_KEY]) <= self::MAX_ITEMS) {
      return;
    }
    # Reverse sort, chunk into MAX_ITEMS and keep the initial set.
    krsort($_SESSION[self::DATA_KEY], SORT_NUMERIC);
    $_SESSION[self::DATA_KEY] = array_shift(array_chunk($_SESSION[self::DATA_KEY], self::MAX_ITEMS, true));
  }

  /**
   * Clears all messages currently stored by the redirector.
   */
  public static function purgeMessages() {
    $_SESSION[self::DATA_KEY] = array();
  }

  /**
   * Performs a simple redirection to the specified URL (see below for details
   * on partial URLs).
   *
   * Partial URLs work as follows:
   *   - <kbd>/^#/</kbd>  -- Appends a URL hash to the current URL.
   *   - <kbd>/^?/</kbd>  -- Sets the query string for the current page.
   *   - <kbd>/^&/</kbd>  -- Appends all specified queries to the URL (Overwrite).
   *   - <kbd>/^&&/</kbd> -- Appends all specified queries to the URL (No overwrite).
   *   - <kbd>/^\//</kbd> -- Redirects to URL relative to root of site (prepends domain).
   *   - <kbd>/^[a-z]*:\/\//</kbd> -- Redirects to absolute URL.
   *
   * @todo    Make URL absolute
   * @todo    Specific exception for redirect error?
   * @todo    Ensure that we handle standard redirects correctly.
   * @todo    Check RFC 2616
   *
   * @param   string  $url           Where to redirect to.
   * @param   string  $message       The message to display after redirect.
   *
   * @throws  Exception
   */
  public static function redirect($url = null, $message = null) {
    # Skip some processing for most redirects
    if (!is_null($message)) {
      # Need to be very careful not to introduce an infinite loop
      return self::redirectWithState($url, $message);
    }

    # TODO: Update URL to be absolute...

    # Get the current render context
    $ctx = RenderContext::getGlobalContext();

    # Perform redirect
    if (headers_sent()) {
      switch ($ctx->getLanguage()) {
        case RenderContext::LANG_HTML:
        case RenderContext::LANG_XHTML:
          $url = String::escapeJS($url, false);
          echo '<script type="text/javascript">window.location = \''.$url.'\';</script>"';
          break;
        default:
          # TODO: Create a specific exception...
          throw new Exception('Cannot redirect - headers sent and invalid render context.');
      }
    } else {
      # TODO
      if (false /*$permanent*/) {
        header('HTTP/1.1 301 Moved Permanently');
      }
      header('Location: '.$url);
    }

    # Output message just in case we have a silly browser
    # TODO: Check RFC 2616
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'HEAD') {
      switch ($ctx->getLanguage()) {
        case RenderContext::LANG_HTML:
        case RenderContext::LANG_XHTML:
          printf('Redirecting to: <a href="%s">%s</a>.', $url, String::escapeHTML($url));
          break;
        default:
          # Ignore
      }
    }

    # We've redirected, so stop executing now
    exit();
  }

  /**
   * Performs a redirection to the specified URL.
   *
   * If specified, a message can be provided with a specific notification type
   * such that the message can be rendered according to the nature of its
   * content.
   *
   * The message type should be one of the provided constants in the
   * Notification class.
   *
   * @see     Notification
   * @see     Redirector::redirect()
   *
   * @todo    Make URL absolute
   * @todo    Specific exception for redirect error?
   * @todo    Ensure that we handle standard redirects correctly.
   * @todo    Check RFC 2616
   *
   * @param   string  $url           Where to redirect to.
   * @param   string  $message       The message to display after redirect.
   * @param   string  $message_type  The type of message.
   *
   * @throws  Exception
   */
  public static function redirectWithState($url = null, $message = null, $message_type = null) {
    # We will select a default type of message to display if none has been
    # specified.  Informational messages are likely to be the most desired.
    if (!is_null($message) && is_null($message_type)) {
      $message_type = Notification::INFORMATION;
    }

    # TODO: Update URL to be absolute...

    # Store redirect information in the session
    $item_key = self::generateKey();
    $_SESSION[self::DATA_KEY][$item_key] = array(
      'message'      => $message,
      'message_type' => $message_type,
      'post_data'    => $_POST,
      'source'       => (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null),
      'target'       => $url,
    );

    # Append item key to query string
    $url = new URL($url);
    $url->appendToQueryString(self::ITEM_KEY, $item_key);

    # Perform redirect
    self::redirect($url);
  }

}
