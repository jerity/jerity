<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class FormConfig {

  /**
   *
   */
  protected $properties = array();

  /**
   *
   */
  public function __construct() {
  }

  /**
   *
   */
  public function getProperty($prop) {
    return isset($this->properties[$prop]) ? $this->properties[$prop] : null;
  }

  /**
   *
   */
  public function getProperties() {
    return $this->properties;
  }

  /**
   *
   */
  public function getPropertiesForTag() {
    $p = array();
    foreach ($this->properties as $k => $v) {
      if ($v === true) {
        $p[$k] = $k;
      } elseif ($v !== false && $v !== null) {
        $p[$k] = $v;
      }
    }
    return $p;
  }

  /**
   *
   */
  public function id($id) {
    $this->properties['id'] = $id;
    return $this;
  }

  /**
   *
   */
  public function addClass($class) {
    if ($this->getProperty('class')) {
      $this->properties['class'] = trim($this->properties['class'].' '.$class);
    } else {
      $this->properties['class'] = trim($class);
    }
    return $this;
  }

  /**
   *
   */
  public function required($v = true) {
    $this->properties['required'] = $v;
    return $this;
  }

}
