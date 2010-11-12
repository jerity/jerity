<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.rest
 */

namespace Jerity\REST;

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.rest
 */
class Manager {

  /**
   *
   */
  protected static $base_path = '';

  /**
   *
   */
  protected static $default_format = 'json';

  /**
   *
   */
  protected static $constant_handlers = false;

  /**
   *
   */
  protected static $handlers = array(
    'GET'    => array(),
    'POST'   => array(),
    'PUT'    => array(),
    'DELETE' => array(),
    ''       => array(),
  );

  /**
   *
   */
  private function __construct() {
  }

  /**
   *
   */
  public static function setDefaultFormat($format) {
    self::$default_format = $format;
  }

  /**
   *
   */
  public static function setBasePath($base_path) {
    $base_path = '/'.ltrim(rtrim($base_path, '/'), '/');
    self::$base_path = $base_path;
  }

  /**
   *
   */
  public static function setConstantHandlers($value=true) {
    self::$constant_handlers = $value;
  }

  /**
   *
   */
  protected static function _registerHandler($handler, array $methods = null) {
    if ($methods === null) $methods = array('GET');
    foreach ($methods as $method) {
      if ($method === '*') $method = '';
      self::$handlers[strtoupper($method)][] = &$handler;
    }
  }

  /**
   *
   */
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
   * Register a handler that matches a given pattern.
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

  /**
   * Register a handler that matches many endpoints for a given pattern.
   */
  public static function registerClassHandler($pattern, $class, array $methods = null) {
    $handler = array(
      'pattern' => '!'.str_replace('!', '\\!', $pattern).'!',
      'class' => $class,
    );
    self::_registerHandler($handler, $methods);
  }

  /**
   *
   */
  public static function getRequestHeaders() {
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

  /**
   *
   */
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

  /**
   *
   */
  protected static function _dispatch_handler(Request $request, $handler, $handler_verb) {
    $func = $handler['handler'];
    if (( isset($handler['mutate']) && $handler['mutate']) ||
        (!isset($handler['mutate']) && !self::$constant_handlers)) {
      $func = self::mutateFunctionName($func, $handler_verb);
    }
    $retval = call_user_func($func, $request);
    if ($retval instanceof Response) {
      $retval->render();
    }
    return headers_sent() || $retval;
  }

  /**
   *
   */
  protected static function real_dispatch(Request $request, $handler_verb) {
    $url = $request->getUrl();
    foreach (self::$handlers[$handler_verb] as $handler) {
      $matches = array();
      if (isset($handler['path']) && $url == $handler['path']) {
        return self::_dispatch_handler($request, $handler, $handler_verb);
      } elseif (isset($handler['pattern']) && preg_match_all($handler['pattern'], $url, $matches)) {
        $request->setMatches($matches);
        if (!isset($handler['class'])) {
          // straight pattern -> handler
          return self::_dispatch_handler($request, $handler, $handler_verb);
        } else {
          $endpoint = isset($matches['cmd'][0]) ? $matches['cmd'][0] : (isset($matches[1][0]) ? $matches[1][0] : '');
          if (method_exists($handler['class'], strtolower($request->getVerb()).'_'.$endpoint)) {
            $retval = call_user_func(array($handler['class'], strtolower($request->getVerb()).'_'.$endpoint), $request);
            if ($retval instanceof Response) {
              $retval->render();
            }
            return headers_sent() || $retval;
          }
        }
      }
    }
    return false;
  }

  /**
   *
   */
  public static function dispatch(Request $request) { //$url, $verb, $headers, $get_args, $body) {
    $url = $request->getUrl();
    $handler_verb = $verb = $request->getVerb();
    if (!isset(self::$handlers[$verb])) {
      if (!count(self::$handlers[''])) {
        // error -- invalid verb
        $resp = new ResponseMethodNotAllowed('Could not find a handler for the "'.$verb.'" HTTP verb.', $request);
        $resp->render();
        return;
      } else {
        $handler_verb = '';
      }
    }
    if ($handler_verb !== '' && self::real_dispatch($request, $handler_verb)) {
      return;
    }
    // try multi-verb handlers
    if (self::real_dispatch($request, '')) {
      return;
    }
    // could this be dispatched for any other verb?
    if (self::could_dispatch($url)) {
      // yes, so the verb is wrong
      $resp = new ResponseMethodNotAllowed('The HTTP verb "'.$verb.'" cannot be used on the requested URL.', $request);
      $resp->render();
      return;
    }
    // error -- no handler for URL
    $resp = new ResponseNotFound('Could not find a handler for the requested URL.', $request);
    $resp->render();
    return;
  }

  /**
   *
   */
  public static function getCurrentUrl($trim_base = true) {
    $url = $_SERVER['REQUEST_URI'];
    if ($trim_base) {
      $url = mb_substr($url, mb_strlen(self::$base_path));
    }
    return $url;
  }

  /**
   *
   */
  public static function dispatchFromCurrent() {
    return self::dispatch(Request::createFromCurrent());
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
