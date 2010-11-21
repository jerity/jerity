<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

use \Jerity\Core\ConditionalProxy;
use \Jerity\Core\ConditionalProxyHandler;

/**
 * An implementation of a conditional proxy class for the purposes of testing.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */
final class ConditionalProxyImplementation implements ConditionalProxy {

  /**
   *
   */
  private $value = null;

  /**
   *
   */
  public function method1() {
    $this->value = 1;
    return $this;
  }

  /**
   *
   */
  public function method2() {
    $this->value = 2;
    return $this;
  }

  /**
   *
   */
  public function method3() {
    $this->value = 3;
    return $this;
  }

  /**
   *
   */
  public function method4() {
    $this->value = 4;
    return $this;
  }

  /**
   *
   */
  public function getValue() {
    return $this->value;
  }

  /**
   *
   */
  public function _if($condition) {
    return ConditionalProxyHandler::create($this, $condition);
  }

  /**
   *
   */
  public function _elseif($condition) {
    return ConditionalProxyHandler::progress($this, $condition);
  }

  /**
   *
   */
  public function _else() {
    return ConditionalProxyHandler::progress($this);
  }

  /**
   *
   */
  public function _endif() {
    return ConditionalProxyHandler::destroy($this);
  }

}

/**
 * A bad implementation of a conditional proxy class for the purposes of
 * testing.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */
final class ConditionalProxyBadImplementation1 {

  /**
   *
   */
  public function _if($condition) {
    return ConditionalProxyHandler::create($this, $condition);
  }

  /**
   *
   */
  public function _elseif($condition) {
  }

  /**
   *
   */
  public function _else() {
  }

  /**
   *
   */
  public function _endif() {
  }

}

/**
 * A bad implementation of a conditional proxy class for the purposes of
 * testing.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */
final class ConditionalProxyBadImplementation2 implements ConditionalProxy {

  /**
   *
   */
  public function _if($condition) {
    # Workaround to get a conditional proxy handler:
    $handler = ConditionalProxyHandler::create($this, false);
    ConditionalProxyHandler::destroy($this);
    return ConditionalProxyHandler::create($handler, $condition);
  }

  /**
   *
   */
  public function _elseif($condition) {
  }

  /**
   *
   */
  public function _else() {
  }

  /**
   *
   */
  public function _endif() {
  }

}

/**
 * Test for the conditional proxy handler.
 *
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */
class ConditionalProxyHandlerTest extends PHPUnit_Framework_TestCase {

  /**
   *
   */
  public function testIfTrue() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertNull($o->getValue());
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testIfTrueIfTrueNest() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testIfTrueIfFalseNest() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testIfFalseIfTrueNest() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testIfFalseIfFalseNest() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->_if(false);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
  }

  /**
   *
   */
  public function testIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_else();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfTrue() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfFalse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfTrue() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfFalse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertNull($o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_else();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(3, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfTrueElseIfTrue() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfTrueElseIfFalse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfFalseElseIfTrue() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfFalseElseIfFalse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfTrueElseIfTrue() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfTrueElseIfFalse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfFalseElseIfTrue() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(3, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfFalseElseIfFalse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertNull($o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfTrueElseIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method4()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfTrueElseIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method4()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfFalseElseIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method4()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfTrueElseIfFalseElseIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method4()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfTrueElseIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method4()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfTrueElseIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method4()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfFalseElseIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method3()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method4()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(3, $o->getValue());
  }

  /**
   *
   */
  public function testIfFalseElseIfFalseElseIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_else();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method4()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(4, $o->getValue());
  }

  /**
   *
   */
  public function testConsecutiveIfTrueTrue() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testConsecutiveIfTrueFalse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue()); # $value = 1 because object is same!
  }

  /**
   *
   */
  public function testConsecutiveIfFalseTrue() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertNull($o->getValue());
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   *
   */
  public function testConsecutiveIfFalseFalse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertNull($o->getValue());
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertNull($o->getValue());
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testOrphanedElseIf() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testOrphanedElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testOrphanedEndIf() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testElseIfTrueAfterIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testElseIfFalseAfterIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testElseIfTrueAfterIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_else();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_elseif(true);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testElseIfFalseAfterIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_else();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_elseif(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testElseAfterIfTrueElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(true);
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method1()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method2()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(1, $o->getValue());
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testElseAfterIfFalseElse() {
    $o = new ConditionalProxyImplementation();
    $o = $o->_if(false);
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method1()->_else();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $o = $o->method2()->_else();
    $this->assertInstanceOf('\Jerity\Core\ConditionalProxyHandler', $o);
    $o = $o->method3()->_endif();
    $this->assertInstanceOf('ConditionalProxyImplementation', $o);
    $this->assertEquals(2, $o->getValue());
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testNotInstanceOfConditionalProxy() {
    $o = new ConditionalProxyBadImplementation1();
    $o = $o->_if(true);
  }

  /**
   * @expectedException  \Jerity\Core\Exception
   */
  public function testInstanceOfConditionalProxyHandler() {
    $o = new ConditionalProxyBadImplementation2();
    $o = $o->_if(true);
  }

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
