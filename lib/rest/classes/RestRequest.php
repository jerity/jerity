<?php

class RestRequest {
  protected static $base_path = '';
  protected static $default_format = 'json';
  protected static $constant_handlers = false;
  protected static $handlers = array(
    'GET'    => array(),
    'POST'   => array(),
    'PUT'    => array(),
    'DELETE' => array(),
    ''       => array(),
  );

  private function __construct() {
  }

  public static function setDefaultFormat($format) {
    self::$default_format = $format;
  }

  public static function setBasePath($base_path) {
    $base_path = '/'.ltrim(rtrim($base_path, '/'), '/');
    self::$base_path = $base_path;
  }

  public static function setConstantHandlers($value=true) {
    self::$constant_handlers = $value;
  }

  protected static function _registerHandler($handler, array $methods = null) {
    if ($methods === null) {
      $methods = array('GET');
    }

    foreach ($methods as $method) {
      if ($method === '*') {
        $method = '';
      }
      self::$handlers[strtoupper($method)][] = &$handler;
    }
  }

  protected static function mutateFunctionName($callable, $verb) {
    $new_callable = $callable;
    if (is_array($new_callable)) {
      $new_callable[1] = strtolower($verb).ucfirst($new_callable[1]);
    } else {
      $new_callable = strtolower($verb).ucfirst($new_callable);
    }
    return is_callable($new_callable) ? $new_callable : $callable;
  }

  /**
   * Register a handler for a given path.
   */
  public static function registerHandler($path, $handler, array $methods = null, $mutate=null) {
    $handler = array(
      'path'    => $path,
      'handler' => $handler,
    );
    if ($mutate !== null) {
      $handler['mutate'] = (bool)$mutate;
    }
    self::_registerHandler($handler, $methods);
  }

  /**
   * Register a handler for a given path.
   */
  public static function registerPatternHandler($pattern, $handler, array $methods = null, $mutate=null) {
    $handler = array(
      'pattern' => '!'.str_replace('!', '\\!', $pattern).'!',
      'handler' => $handler,
    );
    if ($mutate !== null) {
      $handler['mutate'] = (bool)$mutate;
    }
    self::_registerHandler($handler, $methods);
  }

  protected static function getRequestHeaders() {
    static $headers = array();

    if (!count($headers)) {
      foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
          $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
      }
    }
    return $headers;
  }

  protected static function could_dispatch($url, $check_nonstandard=false) {
    foreach (self::$handlers as $verb => $handler_group) {
      if (!$check_nonstandard && !in_array($verb, array('GET', 'POST', 'PUT', 'DELETE'))) {
        continue;
      }
      foreach ($handler_group as $handler) {
        if (isset($handler['path']) && $url == $handler['path']) {
          return true;
        } elseif (isset($handler['pattern']) && preg_match($handler['pattern'], $url)) {
          return true;
        }
      }
    }
  }

  protected static function real_dispatch($url, $verb, $headers, $get_args, $body, $response_format, $handler_verb) {
    $args = func_get_args();
    array_pop($args); // remove $handler_verb
    foreach (self::$handlers[$handler_verb] as $handler) {
      $matches = array();
      if (isset($handler['path']) && $url == $handler['path']) {
        $func = $handler['handler'];
        if (( isset($handler['mutate']) && $handler['mutate']) ||
            (!isset($handler['mutate']) && !self::$constant_handlers)) {
          $func = self::mutateFunctionName($func, $verb);
        }
        $retval = call_user_func_array($func, $args);
        return headers_sent() || $retval;
      } elseif (isset($handler['pattern']) && preg_match_all($handler['pattern'], $url, $matches)) {
        $func = $handler['handler'];
        if (( isset($handler['mutate']) && $handler['mutate']) ||
            (!isset($handler['mutate']) && !self::$constant_handlers)) {
          $func = self::mutateFunctionName($func, $verb);
        }
        $tmp_args = $args;
        $tmp_args[] = $matches;
        $retval = call_user_func_array($func, $tmp_args);
        return headers_sent() || $retval;
      }
    }
    return false;
  }

  public static function dispatch($url, $verb, $headers, $get_args, $body) {
    $response_format = self::getFormat($url, $headers);
    $url = self::cleanURL($url);
    $handler_verb = $verb;

    if (!isset(self::$handlers[$verb])) {
      if (!count(self::$handlers[''])) {
        // error -- invalid verb
        $resp = new RestResponseMethodNotAllowed('Could not find a handler for the "'.$verb.'" HTTP verb.');
        $resp->setFormat($response_format);
        $resp->render();
        return;
      } else {
        $handler_verb = '';
      }
    }
    if ($handler_verb !== '' && self::real_dispatch($url, $verb, $headers, $get_args, $body, $response_format, $handler_verb)) {
      return;
    }
    // try multi-verb handlers
    if (self::real_dispatch($url, $verb, $headers, $get_args, $body, $response_format, '')) {
      return;
    }
    // could this be dispatched for any other verb?
    if (self::could_dispatch($url)) {
      // yes, so the verb is wrong
      $resp = new RestResponseMethodNotAllowed('The HTTP verb "'.$verb.'" cannot be used on the requested URL.');
      $resp->setFormat($response_format);
      $resp->render();
      return;
    }
    // error -- no handler for URL
    $resp = new RestResponseNotFound('Could not find a handler for the requested URL.');
    $resp->setFormat($response_format);
    $resp->render();
    return;
  }

  protected static function cleanURL($url, $remove_extension=true) {
    $cleaned_url = $url;
    $cleaned_url = preg_replace('/\?.*$/',         '', $cleaned_url);
    if ($remove_extension) {
      $cleaned_url = preg_replace('/\.(json|xml)$/', '', $cleaned_url);
    }
    return $cleaned_url;
  }

  public static function getFormat($url, $headers) {
    $cleaned_url = self::cleanURL($url);
    $url = self::cleanURL($url, false);
    $format = self::$default_format;
    if ($cleaned_url != $url) {
      $format = substr($url, strrpos($url, '.')+1);
    } elseif (isset($headers['Accept'])) {
      // examine Accept: header
      $accept = array_map('trim', explode(',', $headers['Accept']));
      foreach ($accept as $type) {
        $type = explode(';', $type);
        $type = trim($type[0]);
        if (in_array($type, array('application/json', 'application/xml', 'text/xml'))) {
          $format = explode('/', $type);
          $format = $format[1];
          break;
        }
      }
    }
    return $format;
  }

  public static function getCurrentUrl($trim_base = true) {
    $url = $_SERVER['REQUEST_URI'];
    if ($trim_base) {
      $url = mb_substr($url, mb_strlen(self::$base_path));
    }
    return $url;
  }

  public static function getFormatFromCurrent() {
    return self::getFormat(self::getCurrentUrl(), self::getRequestHeaders());
  }

  public static function dispatchFromCurrent() {
    return self::dispatch(
      self::getCurrentUrl(),
      strtoupper($_SERVER['REQUEST_METHOD']),
      self::getRequestHeaders(),
      $_GET,
      file_get_contents('php://input')
    );
  }

}
