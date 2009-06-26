<?php
/**
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */

/**
 * Top-level template class.
 *
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */
abstract class Template implements Renderable, ArrayAccess {
  protected $templateRender = null;
  protected $templateVars   = null;
  protected static $templateDir = '';
  protected static $siteTemplateDir = '';
  protected static $pageTemplateDir = '';
  protected static $componentDir    = '';

  public function __construct($f) {
    $this->loadTemplate($f);
  }

  /**
   * Check whether a certain property exists.
   *
   * This passes through to the TemplateVars class which will attempt to handle
   * the storage of any specific parameters for the template.
   *
   * @param string $k Template variable name to check
   * @return bool     Whether the variable exists
   *
   * @see ArrayAccess
   */
  public function offsetExists($k) {
    return $this->templateParams->offsetExists($k);
  }

  /**
   * Retrieve a property, or throw an exception if it does not exist.
   *
   * This passes through to the TemplateVars class which will attempt to handle
   * the storage of any specific parameters for the template.
   *
   * @param string $k Template variable name to retrieve
   * @return mixed    Value of the variable
   *
   * @see ArrayAccess
   * @throws OutOfBoundsException
   */
  public function offsetGet($k) {
    return $this->templateParams->offsetGet($k);
  }

  /**
   * Set a property, or throw an exception if it does not exist.
   *
   * This passes through to the TemplateVars class which will attempt to handle
   * the storage of any specific parameters for the template.
   *
   * @param string $k Template variable name to set
   * @param mixed  $v Value to set
   * @return void
   *
   * @see ArrayAccess
   * @throws OutOfBoundsException
   */
  public function offsetSet($k, $v) {
    return $this->templateParams->offsetSet($k, $v);
  }

  /**
   * Return a property to its default value, or throw an exception if it does
   * not exist.
   *
   * This passes through to the TemplateVars class which will attempt to handle
   * the storage of any specific parameters for the template.
   *
   * @param string $k Template variable name to reset
   * @return void
   *
   * @see ArrayAccess
   * @throws OutOfBoundsException
   */
  public function offsetUnset($k) {
    return $this->templateParams->offsetUnset($k);
  }

  /**
   * Handle automatic accessor/mutator calls.
   *
   * Throws an exception if the number of arguments are wrong, or if the method
   * name is not recognised, or if the desired property does not exist.
   *
   * Note: It is suggested that other ways of accessing this data are used, as
   * this does introduce some overhead.
   *
   * This passes through to the TemplateVars class which will attempt to handle
   * the storage of any specific parameters for the template.
   *
   * @param string $f Function call name
   * @param array  $a List of arguments
   * @return void|mixed
   *
   * @throws InvalidArgumentException
   * @throws BadMethodCallException
   * @throws OutOfBoundsException
   */
  public function __call($f, array $a) {
    return $this->templateParams->__call($f, $a);
  }

  protected function preLoadChecks($f) {
    // prevent attempted misuse
    $f = preg_replace('!^(?:\.*/)+!', '', $f);
    # XXX: this is dodgy; should be static but can't be because of early
    # static binding. Roll on PHP 5.3!
    $f = $this->getTemplateDir().$f.'.php';
    if (!file_exists($f) || (!is_file($f) && !is_link($f)) || !is_readable($f)) {
      throw new Exception('Template file not found');
    }
    return $f;
  }

  protected function loadTemplate($f) {
    $f = $this->preLoadChecks($f);
    $_PARAMS = array();
    $_RENDER = null;
    include($f);
    $this->templateRender = $_RENDER;
    $this->templateParams = new TemplateVars($_PARAMS);
  }

  public static function includeComponent($c) {
    $orig_c = $c;
    $c = preg_replace('!^(?:\.*/)+!', '', $c);
    $c .= '.php';
    $c = self::getComponentDir().$c;
    if (!file_exists($c) || !is_file($c) || !is_readable($c)) {
      throw new Exception('Component `'.$orig_c.'\' not found');
    }
    require_once($c);
  }

  /**
   * Return the template directory appropriate to this template class.
   *
   * @return string
   */
  public static function getTemplateDir() {
    return self::getTopTemplateDir();
  }

  /**
   * Return the top-level template directory. If it has not been set, then this
   * script will assume that the templates are stored in
   * <tt>../_templates/</tt>, relative to this script.
   *
   * @return string
   */
  public static final function getTopTemplateDir() {
    if (!self::$templateDir) {
      self::setTopTemplateDir(dirname(dirname(__FILE__)).'/_templates');
    }
    return self::$templateDir;
  }

  /**
   * Set the top-level template directory. This must be an absolute path.
   *
   * @param string $d Top-level template directory.
   * @return void
   */
  public static final function setTopTemplateDir($d) {
    if (!file_exists($d) || !is_dir($d) || !is_readable($d)) {
      throw new InvalidArgumentException('Template directory could not be read');
    }
    self::$templateDir = rtrim($d, '/').'/';
  }

  /**
   * Return the site template directory. If it has not been set, then this
   * script will assume that the templates are stored in the <tt>site</tt>
   * subdirectory of the top-level template directory.
   *
   * @return string
   */
  public static final function getSiteTemplateDir() {
    if (!self::$siteTemplateDir) {
      self::setSiteTemplateDir(self::getTopTemplateDir().'/site');
    }
    return self::$siteTemplateDir;
  }

  /**
   * Set the site template directory. This must be an absolute path.
   *
   * @param string $d New site template directory.
   * @return void
   */
  public static final function setSiteTemplateDir($d) {
    if (!file_exists($d) || !is_dir($d) || !is_readable($d)) {
      throw new InvalidArgumentException('Template directory could not be read');
    }
    self::$siteTemplateDir = rtrim($d, '/').'/';
  }

  /**
   * Return the page template directory. If it has not been set, then this
   * script will assume that the templates are stored in the <tt>pages</tt>
   * subdirectory of the top-level template directory.
   *
   * @return string
   */
  public static final function getPageTemplateDir() {
    if (!self::$pageTemplateDir) {
      self::setPageTemplateDir(self::getTopTemplateDir().'/pages');
    }
    return self::$pageTemplateDir;
  }

  /**
   * Set the page template directory. This must be an absolute path.
   *
   * @param string $d New page template directory.
   * @return void
   */
  public static final function setPageTemplateDir($d) {
    if (!file_exists($d) || !is_dir($d) || !is_readable($d)) {
      throw new InvalidArgumentException('Template directory could not be read');
    }
    self::$pageTemplateDir = rtrim($d, '/').'/';
  }

  /**
   * Return the components directory. If it has not been set, then this script
   * will assume that the components are stored in the <tt>components</tt>
   * subdirectory of the top-level template directory.
   *
   * @return string
   */
  public static final function getComponentDir() {
    if (!self::$componentDir) {
      self::setComponentDir(self::getTopTemplateDir().'/components');
    }
    return self::$componentDir;
  }

  /**
   * Set the component directory. This must be an absolute path.
   *
   * @param string $d New component template directory.
   * @return void
   */
  public static final function setComponentDir($d) {
    if (!file_exists($d) || !is_dir($d) || !is_readable($d)) {
      throw new InvalidArgumentException('Template directory could not be read');
    }
    self::$componentDir = rtrim($d, '/').'/';
  }

  /**
   * Set template parameters from an associative array.
   *
   * @param array $params Template parameters to set.
   * @return void
   */
  public function setParams(array $params) {
    foreach ($params as $k => $v) {
      $this->templateParams[$k] = $v;
    }
  }

  /**
   * Include a template with the given parameters.
   *
   * @param string $file  Path to the template.
   * @param array $params Template parameters, if any.
   * @return string
   *
   * @see Template::__construct()
   */
  public abstract function useTemplate($file, array $params = array());
}

?>
