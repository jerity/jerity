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
 * In addition to the methods in this interface, the actual conditional proxy
 * class should implement a constructor taking in the object using the proxy, 
 * and the magic __call() method to swallow up ignored method calls.
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
interface ConditionalProxy {

  /**
   *
   */
	public function _if($condition);

  /**
   *
   */
	public function _elseif($condition);

  /**
   *
   */
	public function _else();

  /**
   *
   */
	public function _endif();

}
