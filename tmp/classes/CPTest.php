<?php
/**
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */

/**
 * CPTest
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class CPTest implements ConditionalProxy {

  /**
   * Returns the current object if the condition is true, otherwise we return
   * a FormConditionalProxy instance.  Allows for conditional statements in a 
   * fluid interface.
   *
   * @param  bool  $condition  The condition to check.
   *
   * @return  FormConditionalProxy|CPTest
   */
  public function _if($condition) {
    return ($condition ? $this : new FormConditionalProxy($this));
  }

  /**
   * Returns a FormConditionalProxy instance to allow us to skip over all 
   * subsequent method calls until we hit an _endif().  Allows for conditional
   * statements in a fluid interface.
   *
   * @param  bool  $condition  This condition is ignored.
   *
   * @return  FormConditionalProxy
   */
  public function _elseif($condition) {
    return new FormConditionalProxy($this);
  }

  /**
   * Returns a FormConditionalProxy instance to allow us to skip over all
   * subsequent method calls until we hit an _endif().  Allows for conditional 
   * statements in a fluid interface.
   *
   * @return  FormConditionalProxy
   */
  public function _else() {
    return new FormConditionalProxy($this);
  }

  /**
   * Returns the current object and ends the conditional proxying.  Allows for 
   * conditional statements in a fluid interface.
   *
   * @return  CPTest
   */
  public function _endif() {
    return $this;
  }

}
