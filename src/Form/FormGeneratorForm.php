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
class FormGeneratorForm extends FormContainer {

  /**
   *
   */
  protected $properties = array();

  /**
   *
   */
  public function __construct($action, $method = null) {
    $this->properties['action'] = $action;
    $this->properties['method'] = $method === null ? 'post' : strtolower($method);
  }

}
