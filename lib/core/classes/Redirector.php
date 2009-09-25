<?php
/**
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * A class to handle redirection in a tidy and safe manner which
 * can preserve state by passing POST data and any extra information
 * as required in the session.
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class Redirector {

  /**
   * The name of the variable to use in the URL.
   */
  const ITEM_KEY = '_r';

  /**
   * Session variable name under which redirector should store state data.
   */
  const DATA_KEY = '__redirector_states';

  /**
   * The maximum number of redirects to store.
   */
  const MAX_ITEMS = 15;

  /**
   * The redirector should not be instantiated.
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Generates a new key for storing state information.
   *
   * @return  string
   */
  protected static function generateKey() {
    return dechex(microtime(true));
  }

  /**
   * Fetches the redirect requested and performs garbage collection.
   *
   * @param  string  $item_key  The key of a specific state to fetch.
   *
   * @return  array
   */
  protected static function getState($item_key = null) {
    self::garbageCollect();
    if (is_null($item_key)) {
      $item_key = (isset($_REQUEST[self::ITEM_KEY]) ? $_REQUEST[self::ITEM_KEY] : null);
    }
    if (!isset($_SESSION[self::DATA_KEY][$item_key])) {
      return null;
    }
    $r = $_SESSION[self::DATA_KEY][$item_key];
    return (is_array($r) ? $r : null);
  }

  /**
   * Returns the source of the current redirect.
   *
   * @return  string
   */
  public static function getSource() {
    $r = self::getState();
    return (isset($r['source']) ? $r['source'] : null);
  }

  /**
   * Returns the target of the current redirect.
   *
   * @return  string
   */
  public static function getTarget() {
    $r = self::getState();
    return (isset($r['target']) ? $r['target'] : null);
  }

  /**
   * Returns the time the current redirect occured at.
   *
   * @return  string
   */
  public static function getTime() {
    $r = self::getState();
    return (isset($r['time']) ? $r['time'] : null);
  }

  /**
   * Returns the POST data that was available when the redirect occurred.
   *
   * @return  array
   */
  public static function getPostData() {
    $r = self::getState();
    return (isset($r['post_data']) ? $r['post_data'] : null);
  }

  /**
   * Returns the extra data that was stored when the redirect occurred.
   *
   * @return  array
   */
  public static function getExtraData() {
    $r = self::getState();
    return (isset($r['extra_data']) ? $r['extra_data'] : null);
  }

  /**
   * Reduce the amount of stored state information in the session until the
   * maximum threshold is reached.
   *
   * @todo  Also garbage collect based on time of redirect.
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
   * Clears all state information currently stored by the redirector.
   */
  public static function purgeStates() {
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
   * @todo  Make URL absolute
   * @todo  Ensure that we handle standard redirects correctly.
   * @todo  Check RFC 2616
   *
   * @param  string  $url  Where to redirect to.
   *
   * @throws  RedirectorException
   */
  public static function redirect($url = null) {
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
          throw new RedirectorException('Cannot redirect - headers sent and invalid render context.');
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
   * Performs a redirection to the specified URL with storage of state
   * information in the session.
   *
   * @see  Redirector::redirect()
   *
   * @todo  Make URL absolute
   *
   * @param  string  $url         Where to redirect to.
   * @param  mixed   $extra_data  Extra data to preserve across redirect.
   *
   * @throws  RedirectorException
   */
  public static function redirectWithState($url = null, $extra_data = null) {
    # TODO: Update URL to be absolute...

    # Store redirect information in the session
    $item_key = self::generateKey();
    $_SESSION[self::DATA_KEY][$item_key] = array(
      'source'     => (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null),
      'target'     => $url,
      'time'       => microtime(true);
      'post_data'  => $_POST,
      'extra_data' => $extra_data,
    );

    # Append item key to query string
    $url = new URL($url);
    $url->appendToQueryString(self::ITEM_KEY, $item_key);

    # Perform redirect
    self::redirect($url);
  }

}
