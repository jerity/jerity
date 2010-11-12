<?php
/**
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */

/**
 * Conditional Proxy Interface
 *
 * Proxy for conditional statements in a fluid interface.  Intended to replace 
 * another class for wrong statements, and silently catches all calls to 
 * non-conditional method calls.
 *
 * Based on an implementation in Propel ORM by Francois Zaninotto.
 *
 * @example
 * <code>
 * $c->_if(true)        // returns $c
 *     ->doStuff()      // executed
 *   ->_else()          // returns a ConditionalProxy instance
 *     ->doOtherStuff() // not executed
 *   ->_endif();        // returns $c
 * $c->_if(false)       // returns a ConditionalProxy instance
 *     ->doStuff()      // not executed
 *   ->_else()          // returns $c
 *     ->doOtherStuff() // executed
 *   ->_endif();        // returns $c
 * </code>
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright © 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.form
 */
class FormConditionalProxy implements ConditionalProxy {

  /**
   *
   */
	protected $object;
	
  /**
   *
   */
	public function __construct($object) {
		$this->object = $object;
	}
	
  /**
   *
   */
	public function _if() {
		throw new FormException('_if() statements cannot be nested.');
	}

  /**
   *
   */
	public function _elseif($condition) {
    return ($condition ? $this->object : $this);
	}

  /**
   *
   */
	public function _else() {
		return $this->object;
	}

  /**
   *
   */
	public function _endif() {
		return $this->object;
	}

  /**
   *
   */
	public function __call($name, $arguments) {
		return $this;
  }

}
