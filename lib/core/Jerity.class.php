<?php
/**
 * @package    JerityCore
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2009 Dave Ingram
 */

/**
 * Jerity utility class
 *
 * @todo  Implement harder autoloading...
 *
 * @package    JerityCore
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
  private static $autoload_harder = false;

  /**
   * Private constructor; static-only class
   */
  // @codeCoverageIgnoreStart
  private function __construct() { }
  // @codeCoverageIgnoreEnd

  /**
   * Autoload a class by name. <b>This function should not be called directly.</b>
   *
   * @todo  Implement harder autoloading...
   * @internal
   *
   * @param   string  $name  The name of the class to load
   *
   * @return  bool
   */
  public static function autoload($name) {
    $names = array($name);
    if (self::$autoload_harder) {
      # TODO: Implement harder autoloading...
      #       Break the class name up, so IterableRenderable will search for
      #       Iterable and Renderable and MyFooClass will search for My, MyFoo,
      #       FooClass and Class.
      #       Additional complexity: 2 * (nComponents - 1)
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
