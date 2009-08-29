<?php
/**
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */

/**
 * URL utility class.
 *
 * @todo  Add support for multidimensional query arrays 'key[0]..[n]=value'
 * @todo  Add support for named query arrays 'key[name]=value'
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class URL {

  private $components = array(
    'scheme'   => '',       # Required
    'host'     => '',       # Required
    'port'     => '',
    'name'     => '',
    'pass'     => '',
    'path'     => '',
    'query'    => array(),
    'fragment' => '',
  );

  private static $current = null;

  /**
   * Creates a new URL object.
   *
   * @param  string  $url  A URL to initialise with.
   */
  public function __construct($url = null) {
    if (!empty($url)) {
      $c = parse_url($url);
      $q = array();
      if ($c['query'] !== null) {
        $p = explode('&', $c['query']);
        foreach($p as $i) {
          list($k, $v) = explode('=', $i);
          $q[$k] = $v;
        }
        $c['query'] = $q;
      }
      $this->components = $c + $this->components;
    }
  }

  /**
   * Returns the URL of the current page, and stores it statically.
   * This URL cannot be modified - you must clone it first.
   *
   * @return  URL
   */
  public static function getCurrent() {
    if (is_null($current)) {
      $url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://');
      $url .= $_SERVER['SERVER_NAME'];
      if (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] !== 443 || $_SERVER['SERVER_PORT'] !== 80) {
        $url .= ':'.$_SERVER['SERVER_PORT'];
      }
      $url .= '/'.ltrim($_SERVER['REQUEST_URI'], '/');
      self::$current = new self($url);
    }
    return self::$current;
  }

  /**
   * Gets the URL scheme.
   *
   * @return  string
   */
  public function getScheme() {
    return $this->components['scheme'];
  }

  /**
   * Sets the URL scheme.
   *
   * @param  string  $scheme
   */
  public function setScheme($scheme) {
    $this->components['scheme'] = $scheme;
  }

  /**
   * Appends an item to the query string.
   *
   * @param  string   The key to add.
   * @param  mixed    The value to add.
   * @param  boolean  Whether to overwrite existing values.
   */
  public function appendToQueryString($key, $value = null, $overwrite = true) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $query = &$this->components['query'];
    if (isset($query[$key]) && !$overwrite) {
      if (is_array($query[$key])) {
        $query[$key][] = $value;
      } else {
        $query[$key] = array($query[$key], $value);
      }
    } else {
      $query[$key] = $value;
    }
  }

  /**
   * Deletes an item from the query string.
   *
   * @param  string  The key to delete.
   * @param  mixed   The value to delete - if omitted and the key points to an
   *                 array, the entire array will be removed.
   */
  public function removeFromQueryString($key, $value = null) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $query = &$this->components['query'];
    if (!empty($value)) {
      $v = (is_array($value) ? $value : array($value));
      $query[$key] = array_diff($query[$key], $v);
    } else {
      unset($query[$key]);
    }
  }

  /**
   * Sets the current query string.
   *
   * @param  array  $query  An associative array.
   */
  public function setQueryString(array $query) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['query'] = $query;
  }

  /**
   * Clears the current query string.
   */
  public function clearQueryString() {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['query'] = array();
  }

  /**
   * Gets the URL fragment.  We decode it automatically.
   *
   * @return  string
   */
  public function getFragment() {
    return urldecode($this->components['fragment']);
  }

  /**
   * Sets the URL fragment.  We encode it automatically.
   *
   * @param  string  $fragment  The fragment to add after the hash.
   */
  public function setFragment($fragment) {
    $this->components['fragment'] = urlencode($fragment);
  }

  /**
   * Clears the URL fragment.
   */
  public function clearFragment() {
    $this->components['fragment'] = '';
  }

  /**
   * Outputs a string representing the current state of this URL.
   *
   * @return  string
   */
  public function __toString() {
    extract($this->components);
    $url = '';
    $url .= (empty($scheme) ? '' : $scheme.'://'.($scheme === 'file' ? '/' : ''));
    $url .= (empty($user) ? '' : $user.(empty($pass) ? '' : ':'.$pass).'@');
    $url .= (empty($host) ? '' : $host.(empty($port) ? '' : ':'.$port).'/');
    $url .= (empty($path) ? '' : ((empty($host) ? '/' : '') . ltrim($path, '/')));
    if ($query) {
      foreach ($query as $k => &$v) {
        if (is_array($v)) {
          $v = $k.'[]='.join('&'.$k.'[]=', asort($v));
        } else {
          $v = $k.'='.$v;
        }
      }
      unset($v);
      $url .= '?'.join('&', $query);
    }
    $url .= (empty($fragment) ? '' : $fragment);
    return $url;
  }

}
