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
 * @todo  Sanity checking - i.e. what characters can a URL fragment have?
 *
 * @package    JerityCore
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2009 Nick Pope
 */
class URL {

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

  protected static $current = null;

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
        $c['query'] = $this->splitQueryString($c['query']);
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
    $this->components = self::getCurrent()->components;
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
        break;
      default:
        # Shouldn't get here...
        trigger_error('Unrecognised shorthand URL.');
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
   * Returns a copy of the URL of the current page that can be modified.
   *
   * @return  URL
   */
  public static function cloneCurrent() {
    return clone self::getCurrent();
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
   * @throws  ImmutableObjectException
   */
  public function setScheme($scheme) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['scheme'] = $scheme;
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
   * @throws  ImmutableObjectException
   */
  public function setHost($host) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['host'] = $host;
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
   * @throws  InvalidArgumentException
   * @throws  ImmutableObjectException
   */
  public function setPort($port) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    if (!String::isInteger($port) || $port < 0 || $port > 65535) {
      throw new InvalidArgumentException('Ports must be in the range [0-65535]');
    }
    $this->components['port'] = $port;
  }

  /**
   * Clears the port.
   *
   * @throws  ImmutableObjectException
   */
  public function clearPort() {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['port'] = '';
  }

  /**
   * Gets the user to connect with.
   *
   * @return  string
   */
  public function getUser() {
    return $this->components['user'];
  }

  /**
   * Sets the user to connect with.
   *
   * @param  string  $user  The user to connect with.
   *
   * @throws  ImmutableObjectException
   */
  public function setUser($user) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['user'] = $user;
  }

  /**
   * Clears the user.
   *
   * @throws  ImmutableObjectException
   */
  public function clearUser() {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['user'] = '';
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
   * @throws  ImmutableObjectException
   */
  public function setPassword($password) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['pass'] = $password;
  }

  /**
   * Clears the password.
   *
   * @throws  ImmutableObjectException
   */
  public function clearPassword() {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['pass'] = '';
  }

  /**
   * Appends to the path.
   *
   * @param  string  $path  The string to add to the path.
   *
   * @throws  ImmutableObjectException
   */
  public function appendToPath($path) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['path'] = rtrim($this->components['path'], '/') . '/'
                              . preg_replace('#/+#', '/', ltrim($path, '/'));
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
   * @throws  ImmutableObjectException
   */
  public function setPath($path) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['path'] = preg_replace('#/+#', '/', ltrim($path, '/'));
  }

  /**
   * Clears the path.
   *
   * @throws  ImmutableObjectException
   */
  public function clearPath() {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['path'] = '';
  }

  /**
   * Appends an item to the query string.
   *
   * @param  string   $key        The key to add.
   * @param  mixed    $value      The value to add.
   * @param  boolean  $overwrite  Whether to overwrite existing values.
   *
   * @throws  ImmutableObjectException
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
   * @param  string  $key    The key to delete.
   * @param  mixed   $value  The value to delete - if omitted and the key
   *                         points to an array, the entire array will be
   *                         removed.
   *
   * @throws  ImmutableObjectException
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
   * @throws  ImmutableObjectException
   */
  public function setQueryString(array $query) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['query'] = $query;
  }

  /**
   * Clears the current query string.
   *
   * @throws  ImmutableObjectException
   */
  public function clearQueryString() {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['query'] = array();
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
    if (!is_string) throw new InvalidArgumentException('String expected for splitting.');
    $q = array();
    if (!empty($query)) {
      foreach(explode('&', $query) as $i) {
        list($k, $v) = explode('=', $i);
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
   * @throws  ImmutableObjectException
   */
  public function setFragment($fragment) {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
    $this->components['fragment'] = urlencode($fragment);
  }

  /**
   * Clears the URL fragment.
   *
   * @throws  ImmutableObjectException
   */
  public function clearFragment() {
    if ($this === self::$current) {
      throw new ImmutableObjectException('Current URL object should be cloned.');
    }
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
    $url .= (empty($query) ? '' : '?'.$this->combineQueryString($query));
    $url .= (empty($fragment) ? '' : $fragment);
    return $url;
  }

  /**
   * Checks whether this URL object is valid.
   *
   * @todo  Perform additional checks on other components.
   *
   * @return  bool  True if this URL object is valid.
   */
  public function isValid() {
    if (!$this->components['scheme']) return false;
    if (!$this->components['host'])   return false;
    return true;
  }

  /**
   * Ensures that we have a correct URL object.
   *
   * @param  mixed  $url  A complete or partial URL to check/update.
   *
   * @return  URL
   *
   * @throws  InvalidArgumentException
   */
  public static function ize($url) {
    if (is_null($url)) {
      return URL::getCurrent();
    } elseif (is_string($url)) {
      return new URL($url);
    } elseif ($url instanceof URL) {
      return $url;
    }
    throw new InvalidArgumentException('Unable to get URL information.');
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
    $current = URL::getCurrent();
    $url = '';
    extract($this->components);
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

}
