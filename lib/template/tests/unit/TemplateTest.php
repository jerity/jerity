<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/jerity.php');

if (!class_exists('TemplateT')) {
  // Template is an abstract class, so we need aa simple concrete implementation for testing
  class TemplateT extends Template { }
}

class TemplateTest extends PHPUnit_Framework_TestCase {
  // preparation step run before each test
  public function setUp() {
    Template::setPath(dirname(dirname(__FILE__)).'/data/templates');
  }

  ############################################################################
  # Template validity tests {{{

  /**
   * Ensure no errors when a valid template is loaded
   */
  public function testValidTemplate1() {
    $t = new TemplateT('foo-succeed');
  }

  /**
   * Fail if a non-existant template file is given.
   *
   * @todo  This behaviour will change when automatic per-context templates are implemented
   *
   * @expectedException  RuntimeException
   */
  public function testInvalidTemplate() {
    $t = new TemplateT('foo-fail');
  }

  # }}} Template validity tests
  ############################################################################

  ############################################################################
  # Jailbreak tests {{{

  /**
   * Attempt to escape the template directory.
   *
   * @dataProvider       jailbreakProvider
   * @expectedException  InvalidArgumentException
   */
  public function testJailbreak($path) {
    $t = new TemplateT($path);
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
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $this->assertSame('bar', $t->get('foo'));
  }

  /**
    * Simple assignment/retrieval test.
    */
  public function testMultipleGetSet1() {
    $t = new TemplateT('chrome/simple');
    $t->set(array('foo' => 'bar'));
    $this->assertSame('bar', $t->get('foo'));
  }

  /**
    * Simple assignment/retrieval test.
    */
  public function testMultipleGetSet2() {
    $t = new TemplateT('chrome/simple');
    $t->set(array('foo'), array('bar'));
    $this->assertSame('bar', $t->get('foo'));
  }

  /**
    * Simple assignment/retrieval test.
    */
  public function testMultipleGetSet3() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $t->set('baz', 'qux');
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
  }

  /**
    * Simple assignment/retrieval test.
    */
  public function testMultipleGetSet4() {
    $t = new TemplateT('chrome/simple');
    $t->set(array('foo' => 'bar', 'baz' => 'qux'));
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
  }

  /**
    * Simple assignment/retrieval test.
    */
  public function testMultipleGetSet5() {
    $t = new TemplateT('chrome/simple');
    $t->set(array('foo', 'baz'), array('bar', 'qux'));
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
  }

  /**
    * Simple assignment/retrieval/clear test.
    */
  public function testGetSetSingleClearSingle() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $this->assertSame('bar', $t->get('foo'));
    $t->clear('foo');
    $this->assertSame(null, $t->get('foo'));
  }

  /**
    * Simple assignment/retrieval/clear test.
    */
  public function testGetSetSingleClearAll() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $this->assertSame('bar', $t->get('foo'));
    $t->clear();
    $this->assertSame(null, $t->get('foo'));
  }

  /**
    * Simple assignment/retrieval/clear test.
    */
  public function testGetSetMultipleClearIndividual() {
    $t = new TemplateT('chrome/simple');
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
  public function testGetSetMultipleClearAll() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', 'bar');
    $t->set('baz', 'qux');
    $this->assertSame('bar', $t->get('foo'));
    $this->assertSame('qux', $t->get('baz'));
    $t->clear();
    $this->assertSame(null, $t->get('foo'));
    $this->assertSame(null, $t->get('baz'));
  }

  /**
    * Assignment/retrieval/clear test with non-trivial values.
    */
  public function testComplexSingleGetSet1() {
    $t = new TemplateT('chrome/simple');
    $t->set('foo', array('bar'));
    $t->set('bar', array('baz', 'qux'));
    $this->assertSame(array('bar'), $t->get('foo'));
    $this->assertSame(array('baz', 'qux'), $t->get('bar'));
  }

  /**
    * Assignment/retrieval/clear test with non-trivial values.
    */
  public function testComplexSingleGetSet2() {
    $t = new TemplateT('chrome/simple');
    $obj = new StdClass();
    $obj->foo = 0xba2;
    $obj->bar = 'quux';
    $t->set('test', $obj);
    $this->assertEquals($obj, $t->get('test'));
    $this->assertSame($obj, $t->get('test'));
  }

  /**
    * Assignment/retrieval/clear test with non-trivial values.
    *
    * NOTE: checks that objects are set by reference
    */
  public function testComplexSingleGetSet3() {
    $t = new TemplateT('chrome/simple');
    $obj = new StdClass();
    $obj->foo = 0xba2;
    $obj->bar = 'quux';
    $t->set('test', $obj);
    $obj->abc = 'def';
    $this->assertEquals($obj, $t->get('test'));
    $this->assertSame($obj, $t->get('test'));
  }

  /**
    * Assignment/retrieval/clear test with non-trivial values.
    *
    * NOTE: checks that objects are set by reference, and breaking that reference
    */
  public function testComplexSingleGetSet4() {
    $t = new TemplateT('chrome/simple');
    $obj = new StdClass();
    $obj->foo = 0xba2;
    $obj->bar = 'quux';
    $t->set('test', $obj);
    $obj = clone $obj;
    $obj->abc = 'def';
    $this->assertNotSame($obj, $t->get('test'));
    $this->assertNotEquals($obj, $t->get('test'));
  }

  # }}} Template variable tests
  ############################################################################
}

# vim: ts=2 sw=2 et foldmethod=marker
