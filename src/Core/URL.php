<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */

namespace Jerity\Core;

use \Jerity\Util\String;

/**
 * URL utility class.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 *
 * @todo  Add support for multidimensional query arrays 'key[0]..[n]=value'
 * @todo  Add support for named query arrays 'key[name]=value'
 * @todo  Sanity checking - i.e. what characters can a URL fragment have?
 */
class URL {

  /**
   *
   */
  protected $components = array(
    'scheme'   => '',       # Required
    'host'     => '',       # Required
    'port'     => '',
    'name'     => '',
    'pass'     => '',
    'path'     => '',
    'query'    => array(),
    'fragment' => '',
  );

  /**
   *
   */
  protected static $current = null;

  /**
   *
   */
  protected static $ignored_query_parameters = array();

  /**
   * Creates a new URL object.
   *
   * @param  string  $url  A URL to initialise with.
   */
  public function __construct($url = null) {
    if (!empty($url)) {
      if (is_string($url) && strpos('#?&/', $url[0]) !== false) {
        $this->processShorthand($url);
      } else {
        $c = parse_url($url);
        if (isset($c['query'])) {
          $c['query'] = $this->splitQueryString($c['query']);
        }
        $this->components = $c + $this->components;
      }
    }
  }

  /**
   * Processes a shorthand to modify the current URL with.
   *
   * Shorthand URLs work as follows:
   *   - <kbd>/^#/</kbd>  -- Append a URL fragment.
   *   - <kbd>/^?/</kbd>  -- Sets the query string.
   *   - <kbd>/^&/</kbd>  -- Appends all specified queries (Overwrite).
   *   - <kbd>/^&&/</kbd> -- Appends all specified queries (No overwrite).
   *   - <kbd>/^\//</kbd> -- Replaces the path.
   *
   * @param  string  $shorthand  The shorthand modification
   */
  protected function processShorthand($shorthand) {
    $this->makeCloneOf(self::getCurrent());
    switch ($shorthand[0]) {
      case '#':
        $this->setFragment(ltrim($shorthand, '#'));
        break;
      case '?':
        $this->setQueryString($this->splitQueryString(ltrim($shorthand, '?')));
        break;
      case '&':
        $overwrite = ($shorthand[1] !== '&');
        $parts = $this->splitQueryString(ltrim($shorthand, '&'));
        foreach ($parts as $k => $v) {
          $this->appendToQueryString($k, $v, $overwrite);
        }
        break;
      case '/':
        $this->setPath(ltrim($shorthand, '/'));
        $this->setQueryString(array());
        break;
      default:
        # Shouldn't get here...
        trigger_error('Unrecognised shorthand URL.');
    }
  }

  /**
   * Returns a copy of the URL object for the the current page.
   *
   * This URL is always returned as a clone of the static object which allows
   * for modification while keeping the current URL cached.  The current URL is 
   * cached on the first call to this method and can be recreated if required.
   *
   * It is possible to recreate the object, but this is probably only
   * desirable if the ignored query string list has been modified after the
   * first call to this method.
   *
   * @param  bool  $recreate  Recreates the current URL object.
   *
   * @return  URL
   */
  public static function getCurrent($recreate = false) {
    if (is_null(self::$current) || $recreate) {
      $url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://');
      $url .= $_SERVER['SERVER_NAME'];
      if (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] != 443 || $_SERVER['SERVER_PORT'] != 80) {
        $url .= ':'.$_SERVER['SERVER_PORT'];
      }
      $url .= '/'.ltrim($_SERVER['REQUEST_URI'], '/');
      $url = new self($url);
      foreach (self::$ignored_query_parameters as $i) {
        $url->removeFromQueryString($i);
      }
      self::$current = $url;
    }
    return clone self::$current;
  }

  /**
   * Returns a copy of the URL of the current page that can be modified.
   *
   * @return  URL
   *
   * @deprecated  Please use URL::getCurrent() which is now cloned automatically.
   */
  public static function cloneCurrent() {
    trigger_error('Please use URL::getCurrent() which is now cloned automatically.', E_USER_DEPRECATED);
    return self::getCurrent();
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
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function setScheme($scheme) {
    $this->components['scheme'] = $scheme;
    return $this;
  }

  /**
   * Gets the host to connect to.
   *
   * @return  string
   */
  public function getHost() {
    return $this->components['host'];
  }

  /**
   * Sets the host to connect to.
   *
   * @param  string  $host
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function setHost($host) {
    $this->components['host'] = $host;
    return $this;
  }

  /**
   * Gets the port for the service on the host.
   *
   * @return  string
   */
  public function getPort() {
    return $this->components['port'];
  }

  /**
   * Sets the port for service on the host.
   *
   * @param  string  $port  The port to connect to.
   *
   * @throws  \InvalidArgumentException
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function setPort($port) {
    if (!String::isInteger($port) || $port < 0 || $port > 65535) {
      throw new \InvalidArgumentException('Ports must be in the range [0-65535]');
    }
    $this->components['port'] = $port;
    return $this;
  }

  /**
   * Clears the port.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function clearPort() {
    $this->components['port'] = '';
    return $this;
  }

  /**
   * Gets the user to connect with.
   *
   * @return  string
   */
  public function getUser() {
    return isset($this->components['user']) ? $this->components['user'] : null;
  }

  /**
   * Sets the user to connect with.
   *
   * @param  string  $user  The user to connect with.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function setUser($user) {
    $this->components['user'] = $user;
    return $this;
  }

  /**
   * Clears the user.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function clearUser() {
    $this->components['user'] = '';
    return $this;
  }

  /**
   * Gets the password to authenticate with.
   *
   * @return  string
   */
  public function getPassword() {
    return $this->components['pass'];
  }

  /**
   * Sets the password to authenticate with.
   *
   * @param  string  $password  The password to authenticate with.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function setPassword($password) {
    $this->components['pass'] = $password;
    return $this;
  }

  /**
   * Clears the password.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function clearPassword() {
    $this->components['pass'] = '';
    return $this;
  }

  /**
   * Appends to the path.
   *
   * @param  string  $path  The string to add to the path.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function appendToPath($path) {
    $this->components['path'] = rtrim($this->components['path'], '/') . '/'
                              . preg_replace('#/+#', '/', ltrim($path, '/'));
    return $this;
  }

  /**
   * Gets the path to the resource.
   *
   * @return  string
   */
  public function getPath() {
    return $this->components['path'];
  }

  /**
   * Sets the path
   *
   * @param  string  $path  The path to the resource.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function setPath($path) {
    $this->components['path'] = preg_replace('#/+#', '/', ltrim($path, '/'));
    return $this;
  }

  /**
   * Clears the path.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function clearPath() {
    $this->components['path'] = '';
    return $this;
  }

  /**
   * Appends an item to the query string.
   *
   * @param  string   $key        The key to add.
   * @param  mixed    $value      The value to add.
   * @param  boolean  $overwrite  Whether to overwrite existing values.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function appendToQueryString($key, $value = null, $overwrite = true) {
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
    return $this;
  }

  /**
   * Deletes an item from the query string.
   *
   * @param  string  $key    The key to delete.
   * @param  mixed   $value  The value to delete - if omitted and the key
   *                         points to an array, the entire array will be
   *                         removed.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function removeFromQueryString($key, $value = null) {
    $query = &$this->components['query'];
    if (!empty($value)) {
      $v = (is_array($value) ? $value : array($value));
      $query[$key] = array_diff($query[$key], $v);
    } else {
      unset($query[$key]);
    }
    return $this;
  }

  /**
   * Gets the current query string.
   *
   * @param  bool  $split  Whether to return as a string or components.
   *
   * @return  string | array
   */
  public function getQueryString($split = true) {
    $query = $this->components['query'];
    if ($split) return $query;
    return $this->combineQueryString();
  }

  /**
   * Sets the current query string.
   *
   * @param  array  $query  An associative array.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function setQueryString(array $query) {
    $this->components['query'] = $query;
    return $this;
  }

  /**
   * Clears the current query string.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function clearQueryString() {
    $this->components['query'] = array();
    return $this;
  }

  /**
   * Splits an ampersand delimited string into the individual components of
   * the query string.
   *
   * @param  string  $query  The complete query string.
   *
   * @return  array
   */
  protected function splitQueryString($query) {
    if (!is_string($query)) throw new \InvalidArgumentException('String expected for splitting.');
    $q = array();
    if (!empty($query)) {
      foreach(explode('&', $query) as $i) {
        if (strpos($i, '=') !== false) {
          list($k, $v) = explode('=', $i);
        } else {
          list($k, $v) = array($i, 1);
        }
        $q[$k] = $v;
      }
    }
    return $q;
  }

  /**
   * Combines the components of a query string into an ampersand delimited
   * string.
   *
   * @param  array  $parts  The components of the query string.
   *
   * @return  string
   */
  protected function combineQueryString(array $parts) {
    if ($parts) {
      foreach ($parts as $k => &$v) {
        if (is_array($v)) {
          $v = $k.'[]='.join('&'.$k.'[]=', asort($v));
        } else {
          $v = $k.'='.$v;
        }
      }
      unset($v);
      return join('&', $parts);
    }
    return '';
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
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function setFragment($fragment) {
    $this->components['fragment'] = urlencode($fragment);
    return $this;
  }

  /**
   * Clears the URL fragment.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function clearFragment() {
    $this->components['fragment'] = '';
    return $this;
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
    $url .= (empty($host) ? '' : $host);
    if (!empty($port) && empty($scheme)) {
      // don't know the scheme, so can't say what the default port is
      $url .= ':'.$port;
    } elseif (!empty($port) && !self::isStandardPort($port, $scheme)) {
      $url .= ':'.$port;
    }
    $url .= '/' . (empty($path) ? '' : (ltrim($path, '/')));
    $url .= (empty($query) ? '' : '?'.$this->combineQueryString($query));
    $url .= (empty($fragment) ? '' : $fragment);
    return $url;
  }

  /**
   * Checks whether the given port is the standard port for the given scheme.
   *
   * @param  int     $port    The port number to be checked.
   * @param  string  $scheme  The URL scheme to be checked.
   *
   * @return  boolean  True/false, or null to mean "I don't know".
   */
  public static function isStandardPort($port, $scheme) {
    $mapping = array(
      'ftp'   => 21,
      'http'  => 80,
      'https' => 443,
    );
    if (isset($mapping[$scheme])) {
      // not === just in case $port is not an int
      return $mapping[$scheme] == $port;
    }
    return null;
  }

  /**
   * Checks whether this URL object is valid.
   *
   * @return  bool  True if this URL object is valid.
   *
   * @todo  Perform additional checks on other components.
   */
  public function isValid() {
    if (!$this->components['scheme']) return false;
    if (!$this->components['host'])   return false;
    if ($this->components['scheme'] === 'file' && $this->components['port']) return false;
    return true;
  }

  /**
   * Ensures that we have a correct URL object.
   *
   * @param  mixed  $url  A complete or partial URL to check/update.
   *
   * @return  URL
   *
   * @throws  \InvalidArgumentException
   */
  public static function ize($url) {
    if (is_null($url)) {
      return self::getCurrent();
    } elseif (is_string($url)) {
      return new self($url);
    } elseif ($url instanceof URL) {
      return $url;
    }
    throw new \InvalidArgumentException('Unable to get URL information.');
  }

  /**
   * Returns the absolute URL.
   *
   * @return  string
   */
  public function absolute() {
    return $this->__toString();
  }

  /**
   * Returns this URL as a shorthand relative to the current URL.
   *
   * If any one of the components before the path differ, an absolute URL is
   * returned.
   *
   * @param  bool  $minimal  Whether to attempt to exclude the path.
   *
   * @return  string
   */
  public function relative($minimal = true) {
    $current = self::getCurrent();
    $url = '';
    extract($this->components);
    if (!isset($user    )) $user    =null;
    if (!isset($password)) $password=null;
    switch (true) {
      case ($scheme   !== $current->getScheme()):
      case ($user     !== $current->getUser()):
      case ($password !== $current->getPassword()):
      case ($host     !== $current->getHost()):
      case ($port     !== $current->getPort()):
        return $this->absolute();
      case (!$minimal || $path !== $current->getPath()):
        $url .= (empty($path) ? '' : '/' . ltrim($path, '/'));
    }
    $url .= (empty($query) ? '' : '?'.$this->combineQueryString($query));
    $url .= (empty($fragment) ? '' : $fragment);
    return $url;
  }

  /**
   * Gets all of the query string parameters being ignored.
   *
   * @return  array
   */
  public static function getIgnoredQueryParameters($key) {
    return self::$ignored_query_parameters;
  }

  /**
   * Sets the query string parameters to be ignored.
   *
   * @param  array  $keys  The keys of the query string parameters to be
   *                       ignored.
   */
  public static function setIgnoredQueryParameters($keys) {
    self::$ignored_query_parameters = array_combine($keys, $keys);
  }

  /**
   * Specify a query string parameter to ignore when fetching the current URL.
   *
   * @param  string  $key  The key of the query string parameter to ignore.
   */
  public static function ignoreQueryParameter($key) {
    self::$ignored_query_parameters[$key] = $key;
  }

  /**
   * Specify a query string parameter to unignore when fetching the current
   * URL.
   *
   * @param  string  $key  The key of the query string parameter to unignore.
   */
  public static function unignoreQueryParameter($key) {
    unset(self::$ignored_query_parameters[$key]);
  }

  /**
   * Make this object a clone of another URL.
   *
   * @param  URL  $url  The URL object to be cloned.
   *
   * @return  URL  The current object, for fluent method chaining.
   */
  public function makeCloneOf(URL $url) {
    $this->components = $url->components;
    return $this;
  }

  /**
   * Checks whether the current URL matches the regular expression provided.
   *
   * @param  string  $regex      The regular expression to test.
   * @param  bool    $path_only  If true, only match path part.
   *
   * @return  bool  True if the regular expression matches, false otherwise.
   */
  public static function match($regex, $path_only = true) {
    if ($path_only) {
      $url = self::getCurrent()->getPath();
    } else {
      $url = self::getCurrent()->clearQueryString()->clearFragment()->absolute();
    }
    return preg_match($regex, $url);
  }
}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
