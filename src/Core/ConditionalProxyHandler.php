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
   * The proxied object.
   *
   * @var  mixed
   */
  protected $object;

  /**
   * Whether <code>_endif()</code> has been called on the handler.
   *
   * @var  bool
   */
  protected $has_ended = false;

  /**
   * Creates a new conditional proxy handler.
   *
   * @param  mixed  $object  The proxied object.
   */
  public function __construct($object) {
    $this->object = $object;
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
   * Checks whether <code>_endif()</code> has been called on the handler.
   *
   * @return  bool  Whether if block has ended.
   */
  public function hasEnded() {
    return $this->has_ended;
  }

  /**
   * Substitute for an <code>if</code> statement in a method chain.
   *
   * Note that we disallow nesting of <code>if</code> statments.
   *
   * @param  bool  $condition  The condition.
   *
   * @throw  \Jerity\Core\Exception
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
    return ($condition ? $this->object : $this);
  }

  /**
   * Substitute for an <code>else</code> statement in a method chain.
   *
   * @return  mixed  The proxied object.
   */
  public function _else() {
    return $this->object;
  }

  /**
   * Substitute for ending an <code>if</code> statement in a method chain.
   *
   * @return  mixed  The proxied object.
   */
  public function _endif() {
    $this->has_ended = true;
    return $this->object;
  }

}
