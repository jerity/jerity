<?php
/**
 * @package JerityCore
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */

/**
 * Jerity utility class
 *
 * @package JerityCore
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */

class Jerity {
  /**
   * List of directories to search for autoloading classes.
   */
  private static $autoload_dirs = array();

  /**
   * Whether to really try hard to autoload a class.
   */
  private static $autoload_harder = false;

  /**
   * Private constructor; static-only class
   */
  private function __construct() {
  }

  /**
   * Split a camel-cased string into an array of its components.
   *
   * For example, splits "ThisCamelString" into ('This', 'Camel', 'String')
   * and "ACamelString" into ('A', 'Camel', 'String'). Note that this will
   * also cause "renderAsHTML" to be split into ('render', 'As', 'H', 'T',
   * 'M', 'L').
   *
   * @param string $str The camel-cased string to be split.
   * @return array The set of components, in order.
   */
  public static function splitCamelCase($str) {
    $output[] = '';
    $max = 0;
    foreach (str_split($str) as $c) {
      if ($c >= 'A' && $c <= 'Z' && $output[$max] !== '') {
        $output[++$max] = $c;
      } else {
        $output[$max] .= $c;
      }
    }
    return $output;
  }

  /**
   * Split a split-cased string into an array of its components.
   *
   * For example, splits "this_split_string" into ('this', 'split', 'string')
   * and "A_SPLIT_STRING" into ('A', 'SPLIT', 'STRING').
   *
   * @param string $str The split-cased string to be split.
   * @return array The set of components, in order.
   */
  public static function splitSplitCase($str) {
    $output = preg_split('/_+/', $str);
    $output = array_filter($output, create_function('$a', 'return $a!=="";'));
    return $output;
  }

  /**
   * Autoload a class by name. This function should not be called directly.
   *
   * @param string $name The name of the class to load
   * @return bool
   */
  public static function autoload($name) {
    $names = array($name);
    if (self::$autoload_harder) {
      // break the class name up, so IterableRenderable will search for Iterable and Renderable too
      // and MyFooClass will search for My, MyFoo, FooClass and Class
      // additional complexity: 2 * (nComponents - 1)
    }
    foreach ($names as $name) {
      foreach (array_keys(self::$autoload_dirs) as $dir) {
        $target_file = $dir.'/'.$name.'.php';
        if (file_exists($target_file)) {
          include_once($target_file);
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Adds a directory to search when autoloading classes.  Also includes
   * classes/interfaces found under 'classes' and 'interfaces' directories
   * beneath the specified directory.
   *
   * Returns true on success; false if the directory is not found.
   *
   * @param string $dir The name of the directory to add
   * @return boolean
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

    $dir = "$base_dir/interfaces";
    if (is_dir($dir)) {
      self::$autoload_dirs[$dir] = 1;
    }

    $dir = "$base_dir/classes";
    if (is_dir($dir)) {
      self::$autoload_dirs[$dir] = 1;
    }

    return true;
  }
}
