<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 */

namespace Jerity\Core;

/**
 * Conditional Proxy Interface
 *
 * Proxy for conditional statements in a fluid interface.  Need to replace an
 * object with a substitute handler object when a false condition is 
 * encountered.  The handler needs to silently catch all calls to 
 * non-conditional method calls.
 *
 * In addition to the methods in this interface, the actual conditional proxy
 * class should implement a constructor taking in the object using the proxy,
 * and the magic <code>__call()</code> method to swallow up ignored method 
 * calls.
 *
 * Typically there only needs to be a single conditional proxy handler class 
 * for consumption of undesired method calls in the chain.  Each method chain 
 * supporting class can share the same handler class.
 *
 * Based on an implementation in Propel ORM by Francois Zaninotto.
 *
 * <code>
 * <?php
 * $object->_if(true)    // returns $object
 *          ->method1()  // method is executed on $object
 *        ->_else()      // returns a ConditionalProxy instance
 *          ->method2()  // method is not executed on $object
 *        ->_endif();    // returns $object
 * ?>
 * </code>
 * <code>
 * <?php
 * $object->_if(false)   // returns a ConditionalProxy instance
 *          ->method1()  // method is not executed on $object
 *        ->_else()      // returns $object
 *          ->method2()  // method is executed on $object
 *        ->_endif();    // returns $object
 * ?>
 * </code>
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 *
 * @link  http://www.propelorm.org/wiki/Documentation/1.5/ModelCriteria#FluidConditions
 */
interface ConditionalProxy {

  /**
   * The state when the conditional statement has been started by calling the
   * _if() method.
   *
   * May only be followed by <tt>STATE_ELSEIF</tt>, <tt>STATE_ELSE</tt>, or
   * <tt>STATE_ENDIF</tt>.
   *
   * @var  int
   */
  const STATE_IF     = 0;

  /**
   * The state of the conditional statement when _elseif() has been called.
   *
   * May only be followed by <tt>STATE_ELSEIF</tt>, <tt>STATE_ELSE</tt>, or
   * <tt>STATE_ENDIF</tt>.
   *
   * @var  int
   */
  const STATE_ELSEIF = 1;

  /**
   * The state of the conditional statement when _else() has been called.
   *
   * May only be followed by <tt>STATE_ELSE</tt>.
   *
   * @var  int
   *
   */
  const STATE_ELSE   = 2;

  /**
   * The state when the conditional statement has been finished.
   *
   * May only be followed by <tt>STATE_IF</tt>.
   *
   * @var  int
   */
  const STATE_ENDIF  = 3;

  /**
   * Substitute for an <code>if</code> statement in a method chain.
   *
   * @param  bool  $condition  The condition.
   */
  public function _if($condition);

  /**
   * Substitute for an <code>elseif</code> statement in a method chain.
   *
   * @param  bool  $condition  The condition.
   */
  public function _elseif($condition);

  /**
   * Substitute for an <code>else</code> statement in a method chain.
   */
  public function _else();

  /**
   * Substitute for ending an <code>if</code> statement in a method chain.
   */
  public function _endif();

}
