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
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.core
 *
 * @link  http://www.propelorm.org/wiki/Documentation/1.5/ModelCriteria#FluidConditions
 */
class ConditionalProxyHandler implements ConditionalProxy {

  /**
   * The store of conditional proxy handlers.
   *
   * @var  \SplObjectStorage
   */
  protected static $store = null;

  /**
   * The proxied object.
   *
   * @var  mixed
   */
  protected $object;

  /**
   * Whether a true condition has been encountered.
   *
   * @var  bool
   */
  protected $condition = false;

  /**
   * The current state of the proxy.
   *
   * @var  int
   *
   * @see  STATE_IF
   * @see  STATE_ELSEIF
   * @see  STATE_ELSE
   * @see  STATE_ENDIF
   */
  protected $state = self::STATE_ENDIF;

  /**
   * Creates a new conditional proxy state.
   *
   * This should be called from the object implementing the conditional proxy 
   * <code>_if()</code> method.
   *
   * @param  ConditionalProxy  $object     A conditional proxy object.
   * @param  bool              $condition  The _if() condition.
   *
   * @return  ConditionalProxy  A conditional proxy object or handler.
   */
  public static function create($object, $condition) {
    if (!self::$store instanceof \SplObjectStorage) {
      self::$store = new \SplObjectStorage();
    }
    self::verifyObjectType($object);
    if (self::$store->contains($object)) {
      throw new Exception('_if() cannot be nested.');
    }
    $handler = new self($object, $condition);
    $handler->setState(self::STATE_IF);
    self::$store->attach($object, $handler);
    return ($condition ? $object : $handler);
  }

  /**
   * Progresses the conditional proxy state.
   *
   * This should be called from the object implementing the conditional proxy 
   * <code>_elseif()</code> and <code>_else()</code> methods.
   *
   * If a condition is provided, this will behave as an 'elseif' block, 
   * otherwise this will be an 'else' block.
   *
   * @param  ConditionalProxy  $object     A conditional proxy object.
   * @param  bool              $condition  The optional _elseif() condition.
   *
   * @return  ConditionalProxy  A conditional proxy object or handler.
   */
  public static function progress($object, $condition = null) {
    self::verifyObjectType($object);
    self::checkExistsInStore($object);
    $handler = self::$store[$object];
    $handler->checkState(array(self::STATE_IF, self::STATE_ELSEIF));
    $handler->setState($condition === null ? self::STATE_ELSE
                                           : self::STATE_ELSEIF);
    return $handler;
  }

  /**
   * Destroys a conditional proxy state.
   *
   * This should be called from either the object implementing the conditional
   * proxy <code>_endif()</code> method or the <code>_endif()</code> method 
   * within the conditional proxy handler object.
   *
   * @param  ConditionalProxy  $object  A conditional proxy object.
   *
   * @return  ConditionalProxy  The original conditional proxy object.
   */
  public static function destroy($object) {
    self::verifyObjectType($object);
    self::checkExistsInStore($object);
    self::$store->detach($object);
    return $object;
  }

  /**
   * Checks whether the object is a suitable conditional proxy.
   *
   * Exceptions are thrown if there is a problem which shouldn't occur.
   *
   * @param  ConditionalProxy  $object  A conditional proxy object.
   *
   * @throws  \Jerity\Core\Exception
   */
  protected static function verifyObjectType($object) {
    if (!$object instanceof ConditionalProxy) {
      throw new Exception('Unexpected non-conditional proxy object.');
    }
    if ($object instanceof ConditionalProxyHandler) {
      throw new Exception('Unexpected conditional proxy handler.');
    }
  }

  /**
   * Checks whether the object is a suitable conditional proxy.
   *
   * Exceptions are thrown if there is a problem which shouldn't occur.
   *
   * @param  ConditionalProxy  $object  A conditional proxy object.
   *
   * @throws  \Jerity\Core\Exception
   */
  protected static function checkExistsInStore($object) {
    if (!self::$store instanceof \SplObjectStorage
      || !self::$store->contains($object)) {
      throw new \Jerity\Core\Exception('_if() must be called first.');
    }
  }

  /**
   * Creates a new conditional proxy handler.
   *
   * We take in the initial condition that the proxy was entered with.  This
   * is used to help determine when control should be returned to the object 
   * to ensure that we do not execute more than one "block".
   *
   * @param  mixed  $object     The proxied object.
   * @param  mixed  $condition  The initial condition.
   */
  protected function __construct($object, $condition) {
    $this->object = $object;
    $this->condition = $condition;
  }

  /**
   * Special use of magic method to absorb all unwanted method calls that would
   * have been destined for the object had the condition of the previous 
   * statement been true.
   *
   * @param  string  $name  The name of the method to absorb.
   * @param  string  $name  The name of the method to absorb.
   *
   * @return  This current proxy handler.
   */
  public function __call($name, $args) {
    return $this;
  }

  /**
   * Substitute for an <code>if</code> statement in a method chain.
   *
   * Note that we disallow nesting of <code>if</code> statments.
   *
   * @param  bool  $condition  The condition.
   *
   * @throws  \Jerity\Core\Exception
   */
  public function _if($condition) {
    throw new Exception('_if() cannot be nested.');
  }

  /**
   * Substitute for an <code>elseif</code> statement in a method chain.
   *
   * @param  bool  $condition  The condition.
   *
   * @return  mixed  The proxied object if the condition was true, else this
   *                 current proxy handler.
   */
  public function _elseif($condition) {
    $this->checkState(array(self::STATE_IF, self::STATE_ELSEIF));
    $this->setState(self::STATE_ELSEIF);
    if ($this->condition) return $this;
    $this->condition = $condition;
    return ($condition ? $this->object : $this);
  }

  /**
   * Substitute for an <code>else</code> statement in a method chain.
   *
   * @return  mixed  The proxied object.
   */
  public function _else() {
    $this->checkState(array(self::STATE_IF, self::STATE_ELSEIF));
    $this->setState(self::STATE_ELSE);
    if ($this->condition) return $this;
    $this->condition = true;
    return $this->object;
  }

  /**
   * Substitute for ending an <code>if</code> statement in a method chain.
   *
   * @return  mixed  The proxied object.
   */
  public function _endif() {
    return self::destroy($this->object);
  }

  /**
   * Substitute for ending an <code>if</code> statement in a method chain.
   *
   * @param  int|array  $valid  Valid states for the current branch.
   *
   * @throws  \Jerity\Core\Exception
   */
  public function checkState($valid) {
    if (!is_array($valid)) $valid = array($valid);
    if (!in_array($this->state, $valid)) {
      throw new Exception('Unexpected state encountered.');
    } 
  }

  /**
   * Substitute for ending an <code>if</code> statement in a method chain.
   *
   * @param  int  $state  The new state to set.
   *
   * @return  ConditionalProxyHandler  The current object for method chaining.
   */
  public function setState($state) {
    $this->state = $state;
    return $this;
  }

}
