<?php
// ensure we get all errors
$__er = error_reporting(E_ALL | E_STRICT | E_NOTICE);
/**
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */

/**
 * Template variable storage class.
 *
 * Example usage:
 * <code>
 * <?php
 * $a = new TemplateVars(array('foo'=>'bar', 'baz'=>'qux', 'spam'=>'eggs'));
 * $a['spam'] = 'beans';
 * $a->setFoo('xuq');
 * unset($a['spam']); // $a['spam'] is now 'eggs'
 * $a->resetFoo();    // $a['foo'] is now 'bar'
 *
 * // these should fail because 'fooBar' was not specified in the constructor
 * try {
 *   $a['fooBar'] = 'rab';
 * } catch (Exception $e) {
 *   print $e."\n\n";
 * }
 * try {
 *   $a->setFooBar('rab');
 * } catch (Exception $e) {
 *   print $e."\n\n";
 * }
 * ?>
 * </code>
 *
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */
class TemplateVars implements ArrayAccess, IteratorAggregate {
  /**
   * List of default values for variables.
   *
   * @var array
   */
  protected $defaults;

  /**
   * List of current values for variables.
   *
   * @var array
   */
  protected $vals;


  /**
   * Create an instance of a template variable storage class.
   *
   * Note that if property names are not defined here, then they cannot be
   * specified later.
   *
   * @param array $defaults Default property values.
   */
  public function __construct(array $defaults) {
    $this->defaults = $defaults;
    $this->resetToDefaults();
  }

  /**
   * Reset all variables to their defaults.
   *
   * @return void
   */
  public function resetToDefaults() {
    foreach ($this->defaults as $k=>$v) {
      $this->vals[$k] = $v;
    }
  }

  /**
   * Check whether a certain property exists.
   *
   * @param string $k Template variable name to check
   * @return bool     Whether the variable exists
   *
   * @see ArrayAccess
   */
  public function offsetExists($k) {
    return isset($this->defaults[$k]);
  }

  /**
   * Retrieve a property, or throw an exception if it does not exist.
   *
   * @param string $k Template variable name to retrieve
   * @return mixed    Value of the variable
   *
   * @see ArrayAccess
   * @throws OutOfBoundsException
   */
  public function offsetGet($k) {
    if (!isset($this->vals[$k])) {
      throw new OutOfBoundsException('"'.$k.'" is not a valid template variable');
    }
    return $this->vals[$k];
  }

  /**
   * Set a property, or throw an exception if it does not exist.
   *
   * @param string $k Template variable name to set
   * @param mixed  $v Value to set
   * @return void
   *
   * @see ArrayAccess
   * @throws OutOfBoundsException
   */
  public function offsetSet($k, $v) {
    if (!isset($this->vals[$k])) {
      throw new OutOfBoundsException('"'.$k.'" is not a valid template variable');
    }
    $this->vals[$k] = $v;
  }

  /**
   * Return a property to its default value, or throw an exception if it does
   * not exist.
   *
   * @param string $k Template variable name to reset
   * @return void
   *
   * @see ArrayAccess
   * @throws OutOfBoundsException
   */
  public function offsetUnset($k) {
    if (!isset($this->vals[$k])) {
      throw new OutOfBoundsException('"'.$k.'" is not a valid template variable');
    }
    $this->vals[$k] = $this->defaults[$k];
  }

  public function getIterator() {
    return new ArrayIterator($this->vals);
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
   * @param string $f Function call name
   * @param array  $a List of arguments
   * @return void|mixed
   *
   * @throws InvalidArgumentException
   * @throws BadMethodCallException
   * @throws OutOfBoundsException
   */
  public function __call($f, array $a) {
    list($t, $v) = array(substr($f, 0, 3), strtolower($f[3]).substr($f, 4));
    switch ($t) {
      case 'get':
        if (count($a)!=1) throw new InvalidArgumentException('Method requires one argument: '.$f.'()');
        return $this->offsetGet($a[0]);
      case 'set':
        if (count($a)!=1) throw new InvalidArgumentException('Method requires one argument: '.$f.'()');
        return $this->offsetSet($v, $a[0]);
      case 'has':
        if (count($a)!=1) throw new InvalidArgumentException('Method requires one argument: '.$f.'()');
        return $this->offsetExists($a[0]);
      case 'res':
        list($t, $v) = array(substr($f, 0, 5), strtolower($f[5]).substr($f, 6));
        if ($t == 'reset') {
          if (count($a)!=1) throw new InvalidArgumentException('Method requires one argument: '.$f.'()');
          return $this->offsetUnset($a[0]);
        }
        // otherwise fall through
      default:
        throw new BadMethodCallException('Unrecognised method: '.$f.'()');
    }
  }
}


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

  public function offsetExists($k) {
    return $this->templateParams->offsetExists($k);
  }

  public function offsetSet($k, $v) {
    return $this->templateParams->offsetSet($k, $v);
  }

  public function offsetGet($k) {
    return $this->templateParams->offsetGet($k);
  }

  public function offsetUnset($k) {
    return $this->templateParams->offsetUnset($k);
  }

  protected function loadTemplate($f) {
    // prevent attempted misuse
    $f = preg_replace('!^(?:\.*/)+!', '', $f);
    $f .= '.php';
    if (substr($f, 0, 1)!='/') {
      # XXX: this is dodgy; should be static but can't be because of early
      # static binding. Roll on PHP 5.3!
      $f = $this->getTemplateDir().$f;
    }
    if (!file_exists($f) || !is_file($f) || !is_readable($f)) {
      throw new Exception('Template file not found');
    }
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

/**
 * A wrapper class for rendering simple content.
 *
 * @package JerityTemplate
 * @author Dave Ingram <dave@dmi.me.uk>
 * @copyright Copyright (c) 2009 Dave Ingram
 */
class SimpleContent implements Renderable {
  /**
   * The content to be rendered.
   *
   * @var mixed
   */
  protected $content='';

  /**
   * Create the wrapper around some content.
   *
   * @param mixed $content Content to be output when render() is called; should
   * ideally be a string.
   */
  public function __construct($content) {
    $this->content = $content;
  }

  /**
   * Return the content passed to the class.
   *
   * @return string
   */
  public function render() {
    echo $this->content;
  }
}

class SiteTemplate extends Template {
  protected $content = null;

  public static function getTemplateDir() {
    return self::getSiteTemplateDir();
  }

  public function getContent() {
    if (!$this->content) {
      $this->setContent('');
    }
    return $this->content;
  }

  public function setContent($content) {
    if (!is_object($content)) {
      $content = new SimpleContent($content);
    }
    $this->content = $content;
  }

  public function useTemplate($file, array $params = array()) {
    $tpl = new self($file);
    $tpl->setParams($params);
    return $tpl->render();
  }

  public function render() {
    return call_user_func_array($this->templateRender, array($this->templateParams, $this->getContent()));
  }
}

class PageTemplate extends Template {
  public static function getTemplateDir() {
    return self::getPageTemplateDir();
  }

  public function useTemplate($file, array $params = array()) {
    $tpl = new self($file);
    $tpl->setParams($params);
    return $tpl->render();
  }

  public function render() {
    return '';
  }
}

/* ******************** START: defaults and end-of-file ******************* */

// reset error reporting
error_reporting($__er);
