<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */

use \Jerity\Layout\AbstractTemplate;

if (!class_exists('Template')) {
  /**
   * AbstractTemplate is an abstract class, so we need a simple concrete 
   * instantiation for testing purposes.
   */
  class Template extends AbstractTemplate {

    /**
     *
     */
    public static function nullizePath() {
      parent::$base_path = '';
    }

  }
}

if (!class_exists('StaticPostRenderHookTestClass')) {
  /**
   *
   */
  class StaticPostRenderHookTestClass {

    /**
     *
     */
    public static function hookfunc($a) {
      return strtoupper($a);
    }

  }
}

if (!class_exists('DynamicPostRenderHookTestClass')) {
  /**
   *
   */
  class DynamicPostRenderHookTestClass {

    /**
     *
     */
    public static function hookfunc($a) {
      return strtoupper($a);
    }

  }
}

/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity.test
 */
class Templateest extends PHPUnit_Framework_TestCase {

  /**
   *
   */
  public function setUp() {
    AbstractTemplate::setPath(DATA_DIR.'templates');
  }

  ############################################################################
  # Template validity tests {{{

  /**
   * Test that an exception is thrown with an uninitialised template path.
   *
   * Note that this relies on a "cheating" method on Template to reset the
   * path that was set in the setUp() for the test.
   *
   * @expectedException  \UnexpectedValueException
   *
   */
  public function testNullPath() {
    Template::nullizePath();
    try {
      $path = AbstractTemplate::getPath();
    } catch (\InvalidArgumentException $e) {
      return;
    }
    $this->fail();
  }

  /**
   * Check a template path with a trailing slash.
   */
  public function testValidPath1() {
    $path = DATA_DIR.'templates/';
    AbstractTemplate::setPath($path);
    $this->assertSame($path, AbstractTemplate::getPath());
  }

  /**
   * Check a template path without a trailing slash is automagically given
   * one.
   */
  public function testValidPath2() {
    $path = DATA_DIR.'templates';
    AbstractTemplate::setPath($path);
    $this->assertNotSame($path, AbstractTemplate::getPath());
    $this->assertSame("$path/", AbstractTemplate::getPath());
  }

  /**
   * Fail if a non-existant template directory is given.
   *
   * @expectedException  \InvalidArgumentException
   */
  public function testInvalidPath() {
    AbstractTemplate::setPath(DATA_DIR.'no-templates');
  }

  /**
   * Ensure no errors when a valid template is loaded
   */
  public function testValidTemplate() {
    $t = new Template('foo-succeed');
  }

  /**
   * Test support for late static binding.
   *
   * With late static binding introduced in PHP 5.3, AbstractTemplate::create() 
   * should create a new instance of the child class without the need for 
   * overriding the static create method.
   *
   * Note that the Template class used in this test should not declare a 
   * create() method.
   */
  public function testCreate() {
    $this->assertInstanceOf('Template', Template::create('foo-succeed'));
  }

  /**
   * Fail if a non-existant template file is given.
   *
   * @expectedException  \RuntimeException
   *
   * @todo  This behaviour will change when automatic per-context templates are implemented
   */
  public function testInvalidTemplate() {
    $t = new Template('foo-fail');
  }

  /**
   * Fail if a template file doesn't exist at render time.
   *
   * @todo  This behaviour may change when automatic per-context templates are implemented
   */
  public function testInvalidRenderTemplate() {
    touch(AbstractTemplate::getPath().'foo-fail.tpl.php');
    $t = new Template('foo-fail');
    unlink(AbstractTemplate::getPath().'foo-fail.tpl.php');
    try {
      $t->render();
      $this->fail();
    } catch (\RuntimeException $e) {
    }
  }

  # }}} Template validity tests
  ############################################################################

  ############################################################################
  # Jailbreak tests {{{

  /**
   * Attempt to escape the template directory.
   *
   * @dataProvider       jailbreakProvider
   * @expectedException  \InvalidArgumentException
   */
  public function testJailbreak($path) {
    $t = new Template($path);
  }

  /**
   * Provides a set of tests paths for testJailbreak() that attempt to break
   * out of the template directory.
   */
  public static function jailbreakProvider() {
    return array(
      array('../foo-fail'),
      array('./../foo-fail'),
      array('./../foo-fail'),
      array('../../foo-fail'),
      array('./../../foo-fail'),
      array('no-dir/../foo-fail'),
      array('./no-dir/../foo-fail'),
      array('no-dir/../../foo-fail'),
      array('./no-dir/../../foo-fail'),
      array('chrome/../foo-fail'),
      array('./chrome/../foo-fail'),
      array('chrome/../../foo-fail'),
      array('./chrome/../../foo-fail'),
      array('/foo-fail'),
      array('/abc/foo-fail'),
      array('/abc/../foo-fail'),
      array('/abc/../../foo-fail'),
    );
  }

  # }}} Jailbreak tests
  ############################################################################

  ############################################################################
  # Template variable tests {{{

  /**
   * Simple assignment/retrieval test.
   */
  public function testSingleGetSet() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    $this->assertSame('bar', $t->get('foo'));
  }

  /**
   * Simple assignment/retrieval test.
   */
  public function testMultipleGetSet1() {
    $t = new Template('simple');
    $t->set(array('foo' => 'bar'));
    $this->assertSame('bar', $t->get('foo'));
  }

  /**
   * Simple assignment/retrieval test.
   */
  public function testMultipleGetSet2() {
    $t = new Template('simple');
    $t->set(array('foo'), array('bar'));
    $this->assertSame('bar', $t->get('foo'));
  }

  /**
   * Simple assignment/retrieval test.
   */
  public function testMultipleGetSet3() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    $t->set('baz', 'qux');
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
  }

  /**
   * Simple assignment/retrieval test.
   */
  public function testMultipleGetSet4() {
    $t = new Template('simple');
    $t->set(array('foo' => 'bar', 'baz' => 'qux'));
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
  }

  /**
   * Simple assignment/retrieval test.
   */
  public function testMultipleGetSet5() {
    $t = new Template('simple');
    $t->set(array('foo', 'baz'), array('bar', 'qux'));
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
  }

  /**
   * Simple assignment/retrieval/clear test.
   */
  public function testGetSetSingleClearSingle() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    $this->assertSame('bar', $t->get('foo'));
    $t->clear('foo');
    $this->assertSame(null, $t->get('foo'));
  }

  /**
   * Simple assignment/retrieval/clear test.
   */
  public function testGetSetSingleClearAll() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    $this->assertSame('bar', $t->get('foo'));
    $t->clear();
    $this->assertSame(null, $t->get('foo'));
  }

  /**
   * Simple assignment/retrieval/clear test.
   */
  public function testGetSetMultipleClearIndividual() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    $t->set('baz', 'qux');
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
    $t->clear('foo');
    $this->assertSame(null, $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
    $t->clear('baz');
    $this->assertSame(null, $t->get('foo'));
    $this->assertSame(null, $t->get('baz'));
  }

  /**
   * Simple assignment/retrieval/clear test.
   */
  public function testGetSetMultipleClearGroup() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    $t->set('baz', 'qux');
    $t->set('abc', 'def');
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
    $this->assertSame('def', $t->get('abc'));
    $t->clear(array('foo', 'baz'));
    $this->assertSame(null, $t->get('foo'));
    $this->assertSame(null, $t->get('baz'));
    $this->assertSame('def', $t->get('abc'));
    $t->clear();
    $this->assertSame(null, $t->get('foo'));
    $this->assertSame(null, $t->get('baz'));
    $this->assertSame(null, $t->get('abc'));
  }

  /**
   * Simple assignment/retrieval/clear test.
   */
  public function testGetSetMultipleClearAll() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    $t->set('baz', 'qux');
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
    $t->clear();
    $this->assertSame(null, $t->get('foo'));
    $this->assertSame(null, $t->get('baz'));
    $vars = $t->get();
    $this->assertEquals(0, count($vars));
  }

  /**
   * Assignment/retrieval test with non-trivial values.
   */
  public function testComplexSingleGetSet1() {
    $t = new Template('simple');
    $t->set('foo', array('bar'));
    $t->set('bar', array('baz', 'qux'));
    $this->assertSame(array('bar'), $t->get('foo'));
    $this->assertSame(array('baz', 'qux'), $t->get('bar'));
  }

  /**
   * Assignment/retrieval test with non-trivial values.
   */
  public function testComplexSingleGetSet2() {
    $t = new Template('simple');
    $obj = new \stdClass();
    $obj->foo = 0xba2;
    $obj->bar = 'quux';
    $t->set('test', $obj);
    $this->assertEquals($obj, $t->get('test'));
    $this->assertSame($obj, $t->get('test'));
  }

  /**
   * Assignment/retrieval test with non-trivial values.
   *
   * Note: Checks that objects are set by reference.
   */
  public function testComplexSingleGetSet3() {
    $t = new Template('simple');
    $obj = new \stdClass();
    $obj->foo = 0xba2;
    $obj->bar = 'quux';
    $t->set('test', $obj);
    $obj->abc = 'def';
    $this->assertEquals($obj, $t->get('test'));
    $this->assertSame($obj, $t->get('test'));
  }

  /**
   * Assignment/retrieval test with non-trivial values.
   *
   * Note: Checks that objects are set by reference, and breaking that 
   *       reference.
   */
  public function testComplexSingleGetSet4() {
    $t = new Template('simple');
    $obj = new \stdClass();
    $obj->foo = 0xba2;
    $obj->bar = 'quux';
    $t->set('test', $obj);
    $obj = clone $obj;
    $obj->abc = 'def';
    $this->assertNotSame($obj, $t->get('test'));
    $this->assertNotEquals($obj, $t->get('test'));
  }

  /**
   * @expectedException  \LengthException
   */
  public function testSetFail1() {
    $t = new Template('simple');
    $t->set(array('foo', 'bar'), array('baz'));
  }

  /**
   * @expectedException  \InvalidArgumentException
   */
  public function testSetFail2() {
    $t = new Template('simple');
    $t->set(array('foo', 'bar'), 'baz');
  }

  /**
   *
   */
  public function testSetFail3() {
    $t = new Template('simple');
    try {
      $t->set(false, 'foo');
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
    try {
      $t->set(new \stdClass(), 'foo');
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
    try {
      $t->set(0, 'foo');
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
  }

  /**
   *
   */
  public function testClearFail() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    try {
      $t->clear(false);
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
    try {
      $t->clear(new \stdClass());
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
    try {
      $t->clear(0);
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
  }

  /**
   *
   */
  public function testVarPrefix() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    $t->setVariablePrefix('foobar');
    $this->assertSame('foobar', $t->getVariablePrefix());
  }

  /**
   *
   */
  public function testVarPrefixFail() {
    $t = new Template('simple');
    $t->set('foo', 'bar');
    try {
      $t->setVariablePrefix(false);
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
    try {
      $t->setVariablePrefix(new \stdClass());
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
    try {
      $t->setVariablePrefix(0);
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
  }

  # }}} Template variable tests
  ############################################################################

  ############################################################################
  # Rendering tests {{{

  /**
   *
   */
  public function testSimpleRender() {
    $t = new Template('simple');
    $t->set('content', 'Simple string');
    $this->assertSame('Simple string', $t->render());
  }

  /**
   *
   */
  public function testDumpRender() {
    $t = new Template('vardump');
    $t->set('content', 'Simple string');
    ob_start();
    $content = 'Simple string';
    var_dump(array('content'=>&$content));
    $dump = ob_get_clean();
    $this->assertSame($dump, $t->render());
  }

  /**
   * We shouldn't be able to overwrite $this
   */
  public function testDumpRender2() {
    $t = new Template('thistest');
    $t->set('this', 'that');
    $this->assertSame('PASS', $t->render());
  }

  /**
   *
   */
  public function testPrefixRender() {
    $t = new Template('vardump');
    $t->set('content', 'Simple string');
    $t->setVariablePrefix('my');
    $this->assertSame('my', $t->getVariablePrefix());
    ob_start();
    $content = 'Simple string';
    var_dump(array('my_content'=>&$content));
    $dump = ob_get_clean();
    $this->assertSame($dump, $t->render());
  }

  /**
   *
   */
  public function testToString() {
    $t = new Template('simple');
    $t->set('content', 'Simple string');
    $this->assertSame($t->render(), (string)$t);
    $this->assertSame($t->render(), $t->__toString());

    $t = new Template('vardump');
    $t->set('content', 'Simple string');
    $this->assertSame($t->render(), (string)$t);
    $this->assertSame($t->render(), $t->__toString());
  }

  # }}} Rendering tests
  ############################################################################

  ############################################################################
  # Post-rendering hook tests {{{

  /**
   *
   */
  public function testSingleSimplePostRenderHook() {
    $t = new Template('simple');
    $t->set('content', 'pass');
    $t->addPostRenderHook('strtoupper');
    $this->assertSame(1, count($t->getPostRenderHooks()));
    $this->assertSame('PASS', $t->render());
    $t->clearPostRenderHooks();
    $this->assertSame(0, count($t->getPostRenderHooks()));
    $this->assertSame('pass', $t->render());
  }

  /**
   *
   */
  public function testSimplePostRenderHookPriority() {
    $t = new Template('simple');
    $t->set('content', 'pass');
    $t->addPostRenderHook('strtolower', 20);
    $t->addPostRenderHook('strtoupper', 70);
    $this->assertSame(2, count($t->getPostRenderHooks()));
    $this->assertSame('PASS', $t->render());
    $t->clearPostRenderHooks();
    $this->assertSame(0, count($t->getPostRenderHooks()));
    $this->assertSame('pass', $t->render());
  }

  /**
   *
   */
  public function testSimplePostRenderHookPriority2() {
    $t = new Template('simple');
    $t->set('content', 'pass');
    $t->addPostRenderHook('strtoupper', 70);
    $t->addPostRenderHook('strtolower', 20);
    $this->assertSame(2, count($t->getPostRenderHooks()));
    $this->assertSame('PASS', $t->render());
    $t->removePostRenderHook('strtoupper');
    $this->assertSame(1, count($t->getPostRenderHooks()));
    $this->assertSame('pass', $t->render());
    $t->removePostRenderHook('strtolower');
    $this->assertSame(0, count($t->getPostRenderHooks()));
    $this->assertSame('pass', $t->render());
  }

  /**
   *
   */
  public function testSingleStaticPostRenderHook() {
    $t = new Template('simple');
    $t->set('content', 'pass');
    $t->addPostRenderHook(array('StaticPostRenderHookTestClass', 'hookfunc'));
    $this->assertSame('PASS', $t->render());
  }

  /**
   *
   */
  public function testSingleDynamicPostRenderHook() {
    $t = new Template('simple');
    $t->set('content', 'pass');
    $d = new DynamicPostRenderHookTestClass();
    $t->addPostRenderHook(array($d, 'hookfunc'));
    $this->assertSame('PASS', $t->render());
  }

  /**
   *
   */
  public function testPostRenderHookFail() {
    $t = new Template('simple');
    $t->set('content', 'pass');
    try {
      $t->addPostRenderHook('strtoupper', 100);
      $this->fail();
    } catch (\OutOfRangeException $e) {
    }
    try {
      $t->addPostRenderHook('strtolower', -5);
      $this->fail();
    } catch (\OutOfRangeException $e) {
    }
    try {
      $t->addPostRenderHook('fee fie fail func');
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
    $t->addPostRenderHook(array('StaticPostRenderHookTestClass', 'hookfunc'));
    try {
      $t->removePostRenderHook('fee fie fail func');
      $this->fail();
    } catch (\InvalidArgumentException $e) {
    }
  }

  # }}} Post-rendering hook tests
  ############################################################################

}

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
