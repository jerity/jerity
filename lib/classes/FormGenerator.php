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
class FormGenerator {

  /**
   *
   */
  protected $form = null;

  /**
   *
   */
  public function __construct() {
  }

  /**
   *
   */
  public function beginForm($action, $method = null) {
    return ($this->form = new FormGeneratorForm($action, $method));
  }

  /**
   *
   */
  public function getForm() {
    return $this->form;
  }

}
