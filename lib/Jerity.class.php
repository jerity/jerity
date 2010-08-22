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
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */

/**
 * Jerity utility class
 *
 * @package    jerity.core
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */
class Jerity {

  /**
   * List of directories to search for autoloading classes.
   *
   * @var  array
   */
  private static $autoload_dirs = array();

  /**
   * Whether to really try hard to autoload a class.
   *
   * @var  boolean
   */
  private static $autoload_harder = true;

  /**
   * Private constructor; static-only class
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Autoload a class by name. <b>This function should not be called directly.</b>
   *
   * @internal
   *
   * @param   string  $name  The name of the class to load
   *
   * @return  bool
   */
  public static function autoload($name) {
    $names = array($name);
    if (self::$autoload_harder) {
      if (!class_exists('String')) {
        require_once(dirname(__FILE__).'/classes/String.php');
      }
      // Break the class name up, so IterableRenderable will search for
      // Iterable and Renderable and MyFooClass will search for My, MyFoo,
      // FooClass and Class.
      // Additional complexity: 2 * (nComponents - 1)
      $parts = String::splitCamelCase($name);
      $the_parts = $parts;
      array_pop($the_parts);
      $accumulator = '';
      foreach ($the_parts as $part) {
        $accumulator .= $part;
        $names[] = $accumulator;
      }
      $the_parts = array_reverse($parts);
      array_pop($the_parts);
      $accumulator = '';
      $new_names = array();
      foreach ($the_parts as $part) {
        $accumulator = $part.$accumulator;
        $new_names[] = $accumulator;
      }
      $names = array_merge($names, array_reverse($new_names));
    }
    foreach ($names as $name) {
      foreach (array_keys(self::$autoload_dirs) as $dir) {
        $target_file = $dir.'/'.$name.'.php';
        if (file_exists($target_file)) {
          include_once($target_file);
          if (class_exists($name)) {
            return true;
          }
        }
      }
    }
    return false;
  }

  /**
   * Adds a directory to search when autoloading classes.  Also includes
   * classes/interfaces found under the following directories beneath the
   * specified directory: classes, exceptions, interfaces.
   *
   * Returns true on success; false if the directory is not found.
   *
   * @param   string  $dir  The name of the directory to add
   *
   * @return  boolean
   */
  public static function addAutoloadDir($dir) {
    if (!is_array(spl_autoload_functions()) || !in_array(array('Jerity', 'autoload'), spl_autoload_functions(), true)) {
      spl_autoload_register(array('Jerity', 'autoload'));
    }
    $dir = realpath(rtrim($dir, '/'));
    if ($dir === false || !is_dir($dir)) {
      return false;
    }
    $base_dir = $dir;

    self::$autoload_dirs[$dir] = 1;

    $auto_subdirs = array('interfaces', 'classes', 'exceptions');
    foreach ($auto_subdirs as $subdir) {
      $dir = "$base_dir/$subdir";
      if (is_dir($dir)) {
        self::$autoload_dirs[$dir] = 1;
      }
    }

    return true;
  }

}
